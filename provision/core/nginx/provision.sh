cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# Provides Nginx mainline
deb https://nginx.org/packages/mainline/ubuntu/ bionic nginx
deb-src https://nginx.org/packages/mainline/ubuntu/ bionic nginx

VVVSRC

# Before running `apt-get update`, we should add the public keys for
# the packages that we are installing from non standard sources via
# our appended apt source.list
if ! vvv_apt_keys_has 'nginx'; then
  # Retrieve the Nginx signing key from nginx.org
  echo " * Applying Nginx signing key..."
  apt-key add /srv/config/apt-keys/nginx_signing.key
fi

VVV_PACKAGE_LIST+=(nginx)
