if ! vvv_src_list_has "nginx.org"; then
  cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# Provides Nginx mainline
deb https://nginx.org/packages/mainline/ubuntu/ bionic nginx
deb-src https://nginx.org/packages/mainline/ubuntu/ bionic nginx

VVVSRC
fi

# Before running `apt-get update`, we should add the public keys for
# the packages that we are installing from non standard sources via
# our appended apt source.list
if ! vvv_apt_keys_has 'nginx'; then
  # Retrieve the Nginx signing key from nginx.org
  echo " * Applying Nginx signing key..."
  apt-key add /srv/config/apt-keys/nginx_signing.key
fi

VVV_PACKAGE_LIST+=(nginx)

nginx_setup() {
  # Create an SSL key and certificate for HTTPS support.
  if [[ ! -e /root/.rnd ]]; then
    echo " * Generating Random Number for cert generation..."
    vvvgenrnd="$(openssl rand -out /root/.rnd -hex 256 2>&1)"
    echo "$vvvgenrnd"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.key ]]; then
    echo " * Generating Nginx server private key..."
    vvvgenrsa="$(openssl genrsa -out /etc/nginx/server-2.1.0.key 2048 2>&1)"
    echo "$vvvgenrsa"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.crt ]]; then
    echo " * Sign the certificate using the above private key..."
    vvvsigncert="$(openssl req -new -x509 \
            -key /etc/nginx/server-2.1.0.key \
            -out /etc/nginx/server-2.1.0.crt \
            -days 3650 \
            -subj /CN=*.wordpress-develop.test/CN=*.wordpress.test/CN=*.wordpress-develop.dev/CN=*.wordpress.dev/CN=*.vvv.dev/CN=*.vvv.local/CN=*.vvv.localhost/CN=*.vvv.test 2>&1)"
    echo "$vvvsigncert"
  fi

  echo " * Setup configuration files..."

  # Copy nginx configuration from local
  echo " * Copying /srv/config/nginx-config/nginx.conf           to /etc/nginx/nginx.conf"
  cp -f "/srv/config/nginx-config/nginx.conf" "/etc/nginx/nginx.conf"

  echo " * Copying /srv/config/nginx-config/nginx-wp-common.conf to /etc/nginx/nginx-wp-common.conf"
  cp -f "/srv/config/nginx-config/nginx-wp-common.conf" "/etc/nginx/nginx-wp-common.conf"

  if [[ ! -d "/etc/nginx/upstreams" ]]; then
    mkdir -p "/etc/nginx/upstreams/"
  fi
  echo " * Copying /srv/config/nginx-config/php7.2-upstream.conf to /etc/nginx/upstreams/php72.conf"
  cp -f "/srv/config/nginx-config/php7.2-upstream.conf" "/etc/nginx/upstreams/php72.conf"

  if [[ ! -d "/etc/nginx/custom-sites" ]]; then
    mkdir -p "/etc/nginx/custom-sites/"
  fi
  echo " * Rsync'ing /srv/config/nginx-config/sites/             to /etc/nginx/custom-sites"
  rsync -rvzh --delete "/srv/config/nginx-config/sites/" "/etc/nginx/custom-sites/"

  if [[ ! -d "/etc/nginx/custom-utilities" ]]; then
    mkdir -p "/etc/nginx/custom-utilities/"
  fi

  if [[ ! -d "/etc/nginx/custom-dashboard-extensions" ]]; then
    mkdir -p "/etc/nginx/custom-dashboard-extensions/"
  fi

  rm -rf /etc/nginx/custom-{dashboard-extensions,utilities}/*

  echo " * Making sure the Nginx log files and folder exist"
  mkdir -p /var/log/nginx/
  touch /var/log/nginx/error.log
  touch /var/log/nginx/access.log
}
export -f nginx_setup

vvv_add_hook after_packages nginx_setup 40

vvv_add_hook services_restart "service nginx restart"