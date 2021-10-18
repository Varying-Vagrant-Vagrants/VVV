#!/usr/bin/env bash
# @description Installs MemCached
set -eo pipefail

function vvv_memcached_register_apt_packages() {
  # MemCached
  VVV_PACKAGE_LIST+=(
    # memcached is made available for object caching
    memcached
  )
}
export -f vvv_memcached_register_apt_packages
vvv_add_hook register_apt_packages vvv_memcached_register_apt_packages

function vvv_memcached_setup() {
  # Copy memcached configuration from local
  vvv_info " * Copying /srv/provision/core/memcached/config/memcached.conf to /etc/memcached.conf and /etc/memcached_default.conf"
  cp -f "/srv/provision/core/memcached/config/memcached.conf" "/etc/memcached.conf"
  cp -f "/srv/provision/core/memcached/config/memcached.conf" "/etc/memcached_default.conf"
}
export -f vvv_memcached_setup
vvv_add_hook after_packages vvv_memcached_setup 60

function vvv_memcached_restart() {
  if [ "${VVV_DOCKER}" != 1 ]; then
    service memcached restart
  fi
}

vvv_add_hook services_restart vvv_memcached_restart
