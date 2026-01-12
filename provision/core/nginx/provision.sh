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
  # Modern approach: copy GPG key to keyrings directory
  # This replaces the deprecated apt-key add method

  local SOURCE_KEY="/srv/provision/core/nginx/apt-keys/nginx-archive-keyring.gpg"
  local DEST_KEY="/etc/apt/keyrings/nginx-archive-keyring.gpg"
  local KEY_URL="https://nginx.org/keys/nginx_signing.key"
  local NEEDS_UPDATE=0

  mkdir -p /etc/apt/keyrings

  # Check if we need to update the key
  if [ -f "${DEST_KEY}" ]; then
    # Check if the existing key is expired
    if command -v gpg &> /dev/null; then
      if gpg --show-keys "${DEST_KEY}" 2>/dev/null | grep -q "expired"; then
        vvv_warn " * Installed Nginx GPG key has expired, will update"
        NEEDS_UPDATE=1
      fi
    fi
  else
    NEEDS_UPDATE=1
  fi

  # If we need to update, prefer source file, fallback to download
  if [ "${NEEDS_UPDATE}" -eq 1 ] || [ ! -f "${DEST_KEY}" ]; then
    if [ -f "${SOURCE_KEY}" ]; then
      # Check if source key is also expired
      if command -v gpg &> /dev/null; then
        if gpg --show-keys "${SOURCE_KEY}" 2>/dev/null | grep -q "expired"; then
          vvv_warn " * Source Nginx GPG key is expired, downloading fresh key from ${KEY_URL}"
          if curl -fsSL "${KEY_URL}" | gpg --dearmor -o "${SOURCE_KEY}"; then
            vvv_success " * Downloaded fresh Nginx GPG key"
          else
            vvv_error " ! Failed to download fresh Nginx GPG key from ${KEY_URL}"
            vvv_error " ! Attempting to use existing key anyway"
          fi
        fi
      fi

      vvv_info " * Installing Nginx signing key to ${DEST_KEY}"
      if ! cp -f "${SOURCE_KEY}" "${DEST_KEY}"; then
        vvv_error " ! Failed to copy Nginx GPG key to ${DEST_KEY}"
        return 1
      fi
    else
      # Source file doesn't exist, download directly
      vvv_warn " * Nginx GPG key file not found at ${SOURCE_KEY}, downloading from ${KEY_URL}"
      if curl -fsSL "${KEY_URL}" | gpg --dearmor -o "${DEST_KEY}"; then
        vvv_success " * Downloaded Nginx GPG key to ${DEST_KEY}"
        # Also save to source location for future use
        mkdir -p "$(dirname "${SOURCE_KEY}")"
        cp -f "${DEST_KEY}" "${SOURCE_KEY}"
      else
        vvv_error " ! Failed to download Nginx GPG key from ${KEY_URL}"
        return 1
      fi
    fi

    # Set proper permissions (keys must be world-readable)
    chmod 644 "${DEST_KEY}"

    # Verify the key was installed successfully
    if [ ! -f "${DEST_KEY}" ]; then
      vvv_error " ! Nginx GPG key was not successfully installed"
      return 1
    fi

    # Legacy support: also copy to trusted.gpg.d for older Ubuntu versions
    cp -f "${DEST_KEY}" "/etc/apt/trusted.gpg.d/nginx-archive-keyring.gpg"
    chmod 644 /etc/apt/trusted.gpg.d/nginx-archive-keyring.gpg

    vvv_success " * Nginx GPG key installed successfully"
  else
    vvv_info " * Nginx GPG key is up to date"
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
  rsync -rzh --delete "/srv/provision/core/nginx/config/sites/" "/etc/nginx/custom-sites/"

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
  if service nginx status > /dev/null; then
    service nginx restart
  else
    service nginx start
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
