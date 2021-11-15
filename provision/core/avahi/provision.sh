#!/usr/bin/env bash
# @description Installs Avahi for Zeroconf/bonjour/MDNS support
set -eo pipefail

# @noargs
function avahi_register_packages() {
  VVV_PACKAGE_LIST+=(
    avahi-daemon
    avahi-utils
  )
}
vvv_add_hook before_packages avahi_register_packages
