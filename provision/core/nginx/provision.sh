#!/usr/bin/env bash
# @description Install and configure Nginx
set -eo pipefail

function nginx_register_apt_sources() {
  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE="/srv/provision/core/nginx/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-nginx-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources nginx_register_apt_sources

function nginx_register_apt_keys() {
  # Before running `apt-get update`, we should add the public keys for
  # the packages that we are installing from non standard sources via
  # our appended apt source.list
  if ! vvv_apt_keys_has 'nginx'; then
    # Retrieve the Nginx signing key from nginx.org
    vvv_info " * Applying Nginx signing key..."
    apt-key add /srv/provision/core/nginx/apt-keys/nginx_signing.key
  fi
}
vvv_add_hook register_apt_keys nginx_register_apt_keys

function nginx_register_apt_packages() {
  VVV_PACKAGE_LIST+=(
    nginx
  )
}
vvv_add_hook register_apt_packages nginx_register_apt_packages

function nginx_setup() {
  # Create an SSL key and certificate for HTTPS support.
  if [[ ! -e /root/.rnd ]]; then
    vvv_info " * Generating Random Number for cert generation..."
    local vvvgenrnd="$(openssl rand -out /root/.rnd -hex 256 2>&1)"
    #vvv_info "Rand gen number: ${vvvgenrnd}"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.key ]]; then
    vvv_info " * Generating Nginx server private key..."
    local vvvgenrsa="$(openssl genrsa -out /etc/nginx/server-2.1.0.key 2048 2>&1)"
    #vvv_info "Rand gen rsa: ${vvvgenrsa}"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.crt ]]; then
    vvv_info " * Sign the certificate using the above private key..."
    local vvvsigncert="$(openssl req -new -x509 \
            -key /etc/nginx/server-2.1.0.key \
            -out /etc/nginx/server-2.1.0.crt \
            -days 3650 \
            -subj /CN=*.wordpress-develop.test/CN=*.wordpress.test/CN=*.wordpress-develop.dev/CN=*.wordpress.dev/CN=*.vvv.dev/CN=*.vvv.local/CN=*.vvv.localhost/CN=*.vvv.test 2>&1)"
    #vvv_info "VVV sign cert: ${vvvsigncert}"
  fi

  vvv_info " * Setup configuration files..."

  # Copy nginx configuration from local
  vvv_info " * Copying /srv/provision/core/nginx/config/nginx.conf           to /etc/nginx/nginx.conf"
  cp -f "/srv/provision/core/nginx/config/nginx.conf" "/etc/nginx/nginx.conf"

  vvv_info " * Copying /srv/provision/core/nginx/config/nginx-wp-common.conf to /etc/nginx/nginx-wp-common.conf"
  cp -f "/srv/provision/core/nginx/config/nginx-wp-common.conf" "/etc/nginx/nginx-wp-common.conf"

  # Copy nginx default pages from local
  vvv_info " * Copying /srv/provision/core/nginx/default-pages           to /usr/share/nginx/html"
  cp -f /srv/provision/core/nginx/default-pages/*.html "/usr/share/nginx/html"

  if [[ ! -d "/etc/nginx/upstreams" ]]; then
    mkdir -p "/etc/nginx/upstreams/"
  fi

  vvv_hook nginx_upstreams

  if [[ ! -d "/etc/nginx/custom-sites" ]]; then
    mkdir -p "/etc/nginx/custom-sites/"
  fi
  vvv_info " * Rsync'ing /srv/provision/core/nginx/config/sites/             to /etc/nginx/custom-sites"
  rsync -rvzh --delete "/srv/provision/core/nginx/config/sites/" "/etc/nginx/custom-sites/"

  if [[ ! -d "/etc/nginx/custom-utilities" ]]; then
    mkdir -p "/etc/nginx/custom-utilities/"
  fi

  if [[ ! -d "/etc/nginx/custom-dashboard-extensions" ]]; then
    mkdir -p "/etc/nginx/custom-dashboard-extensions/"
  fi

  rm -rf /etc/nginx/custom-{dashboard-extensions,utilities}/*

  vvv_info " * Making sure the Nginx log files and folder exist"
  mkdir -p /var/log/nginx/
  touch /var/log/nginx/error.log
  touch /var/log/nginx/access.log
}
export -f nginx_setup

vvv_add_hook after_packages nginx_setup 40

function vvv_nginx_restart() {
  if [ "${VVV_DOCKER}" != 1 ]; then
    service nginx restart
  fi
}

vvv_add_hook services_restart vvv_nginx_restart

function nginx_cleanup() {
  vvv_info " * Cleaning up Nginx configs"
  # Kill previously symlinked Nginx configs
  find /etc/nginx/custom-sites -name 'vvv-auto-*.conf' -exec rm {} \;
}
export -f nginx_cleanup
vvv_add_hook finalize nginx_cleanup
