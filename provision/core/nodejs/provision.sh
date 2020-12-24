#!/usr/bin/env bash
# @description Instal and configure Node v10
set -eo pipefail

function nodejs_register_packages() {
  if ! vvv_src_list_has "nodesource"; then
    cp -f "/srv/provision/core/nodejs/sources.list" "/etc/apt/sources.list.d/vvv-nodejs-sources.list"
  fi

  if ! vvv_apt_keys_has 'NodeSource'; then
    # Retrieve the NodeJS signing key from nodesource.com
    vvv_info " * Applying NodeSource NodeJS signing key..."
    apt-key add /srv/config/apt-keys/nodesource.gpg.key
  fi

  VVV_PACKAGE_LIST+=(
    # nodejs for use by grunt
    g++
    nodejs
  )
}
vvv_add_hook before_packages nodejs_register_packages

function node_setup() {
  if [[ $(nodejs -v | sed -ne 's/[^0-9]*\(\([0-9]\.\)\{0,4\}[0-9][^.]\).*/\1/p') != '10' ]]; then
    vvv_info " * Downgrading to Node v10."
    apt remove nodejs -y
    apt install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --fix-broken nodejs
  fi

  # npm
  #
  # Make sure we have the latest npm version and the update checker module
  vvv_info " * Installing/updating npm..."
  npm_config_loglevel=error npm install -g npm
  vvv_info " * Installing/updating npm-check-updates..."
  npm_config_loglevel=error npm install -g npm-check-updates
}
export -f node_setup

vvv_add_hook after_packages node_setup