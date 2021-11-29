#!/usr/bin/env bash
# @description Instal and configure Node v14
set -eo pipefail

function nodejs_before_packages() {
  cp -f "/srv/provision/core/nodejs/nodesource-ppa-pin" "/etc/apt/preferences.d/nodesource-ppa-pin"
}
vvv_add_hook before_packages nodejs_before_packages

function nodejs_register_apt_packages() {
  VVV_PACKAGE_LIST+=(
    # nodejs for use by grunt
    g++
    nodejs
  )
}
vvv_add_hook register_apt_packages nodejs_register_apt_packages

function nodejs_register_apt_sources() {
  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE="/srv/provision/core/nodejs/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-nodejs-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi

  cp -f "/srv/provision/core/nodejs/nodesource-ppa-pin" "/etc/apt/preferences.d/nodesource-ppa-pin"
}
vvv_add_hook register_apt_sources nodejs_register_apt_sources

function nodejs_register_apt_keys() {
  if ! vvv_apt_keys_has 'NodeSource'; then
    # Retrieve the NodeJS signing key from nodesource.com
    vvv_info " * Applying NodeSource NodeJS signing key..."
    apt-key add /srv/provision/core/nodejs/apt-keys/nodesource.gpg.key
  fi
}
vvv_add_hook register_apt_keys nodejs_register_apt_keys

function reinstall_node() {
  vvv_info " * Purging NodeJS package."
  apt-get purge nodejs -y
  vvv_info " * Cleaning apt."
  apt-get clean -y
  vvv_info " * Apt autoremove."
  apt-get autoremove -y
  vvv_info " * Installing Node 14 LTS."
  apt-get install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew --fix-missing --fix-broken nodejs
  vvv_success " ✓ Reinstalled Node, if you need another version use the nvm utility"
}

function node_setup() {
  if [[ -f "/usr/bin/node" ]]; then
    if [[ $(node -v | sed -ne 's/[^0-9]*\(\([0-9]\.\)\{0,4\}[0-9][^.]\).*/\1/p') != '14' ]]; then
      vvv_info " * Migrating to Node v14."
      reinstall_node
    fi
  fi

  if [[ ! -f "/usr/bin/npm" ]]; then
    vvv_warn " ! npm is missing in /usr/bin, reinstalling Node"
    reinstall_node
  fi
}
export -f node_setup

vvv_add_hook after_packages node_setup
