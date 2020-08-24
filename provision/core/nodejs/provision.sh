#!/bin/bash

if ! vvv_src_list_has "nodesource"; then
  cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# Provides Node.js
deb https://deb.nodesource.com/node_10.x bionic main
deb-src https://deb.nodesource.com/node_10.x bionic main

VVVSRC
fi

if ! vvv_apt_keys_has 'NodeSource'; then
  # Retrieve the NodeJS signing key from nodesource.com
  echo " * Applying NodeSource NodeJS signing key..."
  apt-key add /srv/config/apt-keys/nodesource.gpg.key
fi

VVV_PACKAGE_LIST+=(
  # nodejs for use by grunt
  g++
  nodejs
)

node_setup() {
  if [[ $(nodejs -v | sed -ne 's/[^0-9]*\(\([0-9]\.\)\{0,4\}[0-9][^.]\).*/\1/p') != '10' ]]; then
    echo " * Downgrading to Node v10."
    apt remove nodejs -y
    apt install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --fix-broken nodejs
  fi

  # npm
  #
  # Make sure we have the latest npm version and the update checker module
  echo " * Installing/updating npm..."
  npm_config_loglevel=error npm install -g npm
  echo " * Installing/updating npm-check-updates..."
  npm_config_loglevel=error npm install -g npm-check-updates
}
export -f node_setup

vvv_add_hook after_packages node_setup