#!/bin/bash

function php_register_packages() {
  if ! vvv_src_list_has "ondrej/php"; then
    cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# Provides PHP7
deb http://ppa.launchpad.net/ondrej/php/ubuntu bionic main
deb-src http://ppa.launchpad.net/ondrej/php/ubuntu bionic main

VVVSRC
  fi

  if ! vvv_apt_keys_has 'Ondřej'; then
    # Apply the PHP signing key
    echo " * Applying the Ondřej PHP signing key..."
    apt-key add /srv/config/apt-keys/ondrej_keyserver_ubuntu.key
  fi

  VVV_PACKAGE_LIST+=(
    # PHP7
    #
    # Our base packages for php7.2. As long as php7.2-fpm and php7.2-cli are
    # installed, there is no need to install the general php7.2 package, which
    # can sometimes install apache as a requirement.
    php7.2-fpm
    php7.2-cli

    # Common and dev packages for php
    php7.2-common
    php7.2-dev

    # Extra PHP modules that we find useful
    php-pear
    php-ssh2
    php-yaml
    php7.2-bcmath
    php7.2-curl
    php7.2-gd
    php7.2-intl
    php7.2-mbstring
    php7.2-mysql
    php7.2-imap
    php7.2-json
    php7.2-soap
    php7.2-xml
    php7.2-zip
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
  echo " * Copying /srv/config/php-config/php7.2-fpm.conf   to /etc/php/7.2/fpm/php-fpm.conf"
  cp -f "/srv/config/php-config/php7.2-fpm.conf" "/etc/php/7.2/fpm/php-fpm.conf"

  echo " * Copying /srv/config/php-config/php7.2-www.conf   to /etc/php/7.2/fpm/pool.d/www.conf"
  cp -f "/srv/config/php-config/php7.2-www.conf" "/etc/php/7.2/fpm/pool.d/www.conf"

  echo " * Copying /srv/config/php-config/php7.2-custom.ini to /etc/php/7.2/fpm/conf.d/php-custom.ini"
  cp -f "/srv/config/php-config/php7.2-custom.ini" "/etc/php/7.2/fpm/conf.d/php-custom.ini"

  echo " * Copying /srv/config/php-config/opcache.ini       to /etc/php/7.2/fpm/conf.d/opcache.ini"
  cp -f "/srv/config/php-config/opcache.ini" "/etc/php/7.2/fpm/conf.d/opcache.ini"

  echo " * Copying /srv/config/php-config/xdebug.ini        to /etc/php/7.2/mods-available/xdebug.ini"
  cp -f "/srv/config/php-config/xdebug.ini" "/etc/php/7.2/mods-available/xdebug.ini"

  echo " * Copying /srv/config/php-config/mailhog.ini       to /etc/php/7.2/mods-available/mailhog.ini"
  cp -f "/srv/config/php-config/mailhog.ini" "/etc/php/7.2/mods-available/mailhog.ini"

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
