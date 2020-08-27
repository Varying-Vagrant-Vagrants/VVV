#!/bin/bash

VVV_BASE_PHPVERSION=${VVV_BASE_PHPVERSION:-"7.2"}
function php_register_packages() {
  if ! vvv_src_list_has "ondrej/php"; then
    cp -f "/srv/provision/core/php/sources.list" "/etc/apt/sources.list.d/vvv-php-sources.list"
  fi

  if ! vvv_apt_keys_has 'Ondřej'; then
    # Apply the PHP signing key
    echo " * Applying the Ondřej PHP signing key..."
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
    php-pear
    php-ssh2
    php-yaml
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

  # MemCached
  VVV_PACKAGE_LIST+=(
    php-memcache
    php-memcached

    # memcached is made available for object caching
    memcached
  )

  # ImageMagick
  VVV_PACKAGE_LIST+=(
    php-imagick
    imagemagick
  )

  # XDebug
  VVV_PACKAGE_LIST+=(
    php-xdebug

    # Required for Webgrind
    graphviz
  )
}
vvv_add_hook before_packages php_register_packages

function graphviz_setup() {
  # Graphviz
  #
  # Set up a symlink between the Graphviz path defined in the default Webgrind
  # config and actual path.
  echo " * Adding graphviz symlink for Webgrind..."
  ln -sf "/usr/bin/dot" "/usr/local/bin/dot"
}
export -f graphviz_setup

vvv_add_hook after_packages graphviz_setup 20

function phpfpm_setup() {
  # Copy php-fpm configuration from local
  echo " * Copying /srv/config/php-config/php-fpm.conf   to /etc/php/${VVV_BASE_PHPVERSION}/fpm/php-fpm.conf"
  cp -f "/srv/config/php-config/php-fpm.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/php-fpm.conf"

  echo " * Copying /srv/config/php-config/php-www.conf   to /etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d/www.conf"
  cp -f "/srv/config/php-config/php-www.conf" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/pool.d/www.conf"

  echo " * Copying /srv/config/php-config/php-custom.ini to /etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/php-custom.ini"
  cp -f "/srv/config/php-config/php-custom.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/php-custom.ini"

  echo " * Copying /srv/config/php-config/opcache.ini       to /etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/opcache.ini"
  cp -f "/srv/config/php-config/opcache.ini" "/etc/php/${VVV_BASE_PHPVERSION}/fpm/conf.d/opcache.ini"

  echo " * Copying /srv/config/php-config/xdebug.ini        to /etc/php/${VVV_BASE_PHPVERSION}/mods-available/xdebug.ini"
  cp -f "/srv/config/php-config/xdebug.ini" "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/xdebug.ini"

  echo " * Copying /srv/config/php-config/mailhog.ini       to /etc/php/${VVV_BASE_PHPVERSION}/mods-available/mailhog.ini"
  cp -f "/srv/config/php-config/mailhog.ini" "/etc/php/${VVV_BASE_PHPVERSION}/mods-available/mailhog.ini"

  if [[ -f "/etc/php/7.2/mods-available/mailcatcher.ini" ]]; then
    echo " * Cleaning up mailcatcher.ini from a previous install"
    rm -f /etc/php/7.2/mods-available/mailcatcher.ini
  fi

  # Copy memcached configuration from local
  echo " * Copying /srv/config/memcached-config/memcached.conf to /etc/memcached.conf and /etc/memcached_default.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached_default.conf"
}
export -f phpfpm_setup

vvv_add_hook after_packages phpfpm_setup 50

function phpfpm_finalize() {
  # Disable PHP Xdebug module by default
  echo " * Disabling XDebug PHP extension"
  phpdismod xdebug

  # Add the vagrant user to the www-data group so that it has better access
  # to PHP and Nginx related files.
  usermod -a -G www-data vagrant

  vvv_hook php_finalize
}
export -f phpfpm_finalize

vvv_add_hook finalize phpfpm_finalize

vvv_add_hook services_restart "service memcached restart"

function phpfpm_services_restart() {
  # Restart all php-fpm versions
  find /etc/init.d/ -name "php*-fpm" -exec bash -c 'sudo service "$(basename "$0")" restart' {} \;
}
export -f phpfpm_services_restart

vvv_add_hook services_restart phpfpm_services_restart
