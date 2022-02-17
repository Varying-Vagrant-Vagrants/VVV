#!/usr/bin/env bash
# @description Installs the default version of PHP
set -eo pipefail

VVV_BASE_PHPVERSION=${VVV_BASE_PHPVERSION:-"7.4"}

function php_before_packages() {
  cp -f "/srv/provision/core/php/ondrej-ppa-pin" "/etc/apt/preferences.d/ondrej-ppa-pin"
}
vvv_add_hook before_packages php_before_packages

function php_register_apt_sources() {
  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE="/srv/provision/core/php/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-php-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources php_register_apt_sources

function php_register_apt_packages() {
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
    "php${VVV_BASE_PHPVERSION}-pcov"
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

  # Xdebug
  VVV_PACKAGE_LIST+=(
    "php${VVV_BASE_PHPVERSION}-xdebug"
  )
}
vvv_add_hook register_apt_packages php_register_apt_packages


function php_register_apt_keys() {
  cp -f "/srv/provision/core/php/apt-keys/php.gpg" "/etc/apt/trusted.gpg.d/php.gpg"

  if ! vvv_apt_keys_has 'Ondřej'; then
    # Apply the PHP signing key
    vvv_info " * Applying the Ondřej PHP signing key..."
    apt-key add /srv/provision/core/php/apt-keys/ondrej_keyserver_ubuntu.key
  fi
}
vvv_add_hook register_apt_sources php_register_apt_keys

function phpfpm_setup() {
  # Copy php-fpm configs from local
  if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm" ]; then
    vvv_info " * Copying PHP configs"
    cp -f "/srv/config/php-config/php-fpm.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/php-fpm.conf"
    if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d" ]; then
      cp -f "/srv/config/php-config/php-www.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d/www.conf"
    fi
    if [ -d "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d" ]; then
      cp -f "/srv/config/php-config/php-custom.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/php-custom.ini"
    fi
  fi

  vvv_info " * Checking supplementary PHP configs"

  for V in /etc/php/*; do
    if [ -d "${V}" ]; then
      if [[ -f "/etc/php/${V}/mods-available/mailcatcher.ini" ]]; then
        vvv_warn " * Cleaning up PHP ${V} mailcatcher.ini from a previous install"
        rm -f "/etc/php/${V}/mods-available/mailcatcher.ini"
      fi
      if [ -d "${V}/mods-available/" ]; then
        cp -f "/srv/config/php-config/mailhog.ini" "${V}/mods-available/mailhog.ini"
        cp -f "/srv/config/php-config/xdebug.ini" "${V}/mods-available/xdebug.ini"
      fi
      if [ -d "${V}/fpm/conf.d/" ]; then
        cp -f "/srv/config/php-config/opcache.ini" "${V}/fpm/conf.d/opcache.ini"
      fi
    fi
  done
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

function vvv_php_memcached_register_packages() {
  VVV_PACKAGE_LIST+=(
    php${VVV_BASE_PHPVERSION}-memcache
    php${VVV_BASE_PHPVERSION}-memcached
  )
}
export -f vvv_php_memcached_register_packages
vvv_add_hook before_packages vvv_php_memcached_register_packages


function vvv_php_redis_register_packages() {
  VVV_PACKAGE_LIST+=(
    php${VVV_BASE_PHPVERSION}-redis
  )
}
export -f vvv_php_redis_register_packages
vvv_add_hook before_packages vvv_php_redis_register_packages
