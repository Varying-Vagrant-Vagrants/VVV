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
