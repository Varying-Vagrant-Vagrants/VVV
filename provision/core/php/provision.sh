#!/bin/bash

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