#!/usr/bin/env bash
# @description Installs the default version of PHP
set -eo pipefail

VVV_BASE_PHPVERSION=${VVV_BASE_PHPVERSION:-"7.3"}
function php_register_packages() {
  if ! vvv_src_list_has "ondrej/php"; then
    cp -f "/srv/provision/core/php/sources.list" "/etc/apt/sources.list.d/vvv-php-sources.list"
  fi

  if ! vvv_apt_keys_has 'Ondřej'; then
    # Apply the PHP signing key
    vvv_info " * Applying the Ondřej PHP signing key..."
    apt-key add /srv/config/apt-keys/ondrej_keyserver_ubuntu.key
  fi

  VVV_PACKAGE_LIST+=(
    # PHP
    #
    # Our base packages for php. As long as php*-fpm and php*-cli are
    # installed, there is no need to install the general php* package, which
    # can sometimes install apache as a requirement.
    "php${VVV_BASE_PHPVERSION}-fpm"
    "php${VVV_BASE_PHPVERSION}-cli"

    # Common and dev packages for php
    "php${VVV_BASE_PHPVERSION}-common"
    "php${VVV_BASE_PHPVERSION}-dev"

    # Extra PHP modules that we find useful
    "php-pear"
    "php-pcov"
    "php${VVV_BASE_PHPVERSION}-ssh2"
    "php${VVV_BASE_PHPVERSION}-yaml"
    "php${VVV_BASE_PHPVERSION}-bcmath"
    "php${VVV_BASE_PHPVERSION}-curl"
    "php${VVV_BASE_PHPVERSION}-gd"
    "php${VVV_BASE_PHPVERSION}-intl"
    "php${VVV_BASE_PHPVERSION}-mbstring"
    "php${VVV_BASE_PHPVERSION}-mysql"
    "php${VVV_BASE_PHPVERSION}-imap"
    "php${VVV_BASE_PHPVERSION}-json"
    "php${VVV_BASE_PHPVERSION}-soap"
    "php${VVV_BASE_PHPVERSION}-xml"
    "php${VVV_BASE_PHPVERSION}-zip"
  )

  # ImageMagick
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-imagick"
    imagemagick
  )

  # XDebug
  VVV_PACKAGE_LIST+=(
    php-xdebug
  )
}
vvv_add_hook before_packages php_register_packages

function phpfpm_setup() {
  # Copy php-fpm configs from local
  if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/" ]; then
    vvv_info " * Copying PHP configs"
    cp -f "/srv/config/php-config/php-fpm.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/php-fpm.conf"
    cp -f "/srv/config/php-config/php-www.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d/www.conf"
    cp -f "/srv/config/php-config/php-custom.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/php-custom.ini"
    cp -f "/srv/config/php-config/opcache.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/opcache.ini"
    cp -f "/srv/config/php-config/xdebug.ini" "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/xdebug.ini"
    cp -f "/srv/config/php-config/mailhog.ini" "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/mailhog.ini"
  fi

  if [[ -f "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/mailcatcher.ini" ]]; then
    vvv_warn " * Cleaning up mailcatcher.ini from a previous install"
    rm -f "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/mailcatcher.ini"
  fi
}
export -f phpfpm_setup

vvv_add_hook before_packages phpfpm_setup 50
vvv_add_hook after_packages phpfpm_setup 50

function phpfpm_finalize() {
  # Disable PHP Xdebug module by default
  vvv_info " * Disabling XDebug PHP extension"
  phpdismod xdebug
  phpdismod pcov

  # Add the vagrant user to the www-data group so that it has better access
  # to PHP and Nginx related files.
  usermod -a -G www-data vagrant

  vvv_hook php_finalize
}
export -f phpfpm_finalize

vvv_add_hook finalize phpfpm_finalize

function phpfpm_services_restart() {
  # Restart all php-fpm versions
  if [ "${VVV_DOCKER}" != 1 ]; then
    find /etc/init.d/ -name "php*-fpm" -exec bash -c 'sudo service "$(basename "$0")" restart' {} \;
  fi
}
export -f phpfpm_services_restart

vvv_add_hook services_restart phpfpm_services_restart

function php_nginx_upstream() {
  vvv_info " * Copying /srv/config/php-config/upstream.conf to /etc/nginx/upstreams/php${VVV_BASE_PHPVERSION//.}.conf"
  cp -f "/srv/config/php-config/upstream.conf" "/etc/nginx/upstreams/php${VVV_BASE_PHPVERSION//.}.conf"
}
vvv_add_hook nginx_upstreams php_nginx_upstream

function memcached_register_packages() {
  # MemCached
  VVV_PACKAGE_LIST+=(
    php${VVV_BASE_PHPVERSION}-memcache
    php${VVV_BASE_PHPVERSION}-memcached

    # memcached is made available for object caching
    memcached
  )
}
vvv_add_hook before_packages memcached_register_packages
function memcached_setup() {
  # Copy memcached configuration from local
  vvv_info " * Copying /srv/config/memcached-config/memcached.conf to /etc/memcached.conf and /etc/memcached_default.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached_default.conf"
}
vvv_add_hook after_packages memcached_setup 60
if [ "${VVV_DOCKER}" != 1 ]; then
  vvv_add_hook services_restart "service memcached restart"
fi
