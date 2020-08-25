#!/bin/bash

if ! vvv_src_list_has "varying-vagrant-vagrants"; then
  cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# VVV mirror packages
deb http://ppa.launchpad.net/varying-vagrant-vagrants/php/ubuntu bionic main
deb-src http://ppa.launchpad.net/varying-vagrant-vagrants/php/ubuntu bionic main

VVVSRC
fi

if ! vvv_apt_keys_has 'Varying Vagrant Vagrants'; then
  # Apply the VVV signing key
  echo " * Applying the Varying Vagrant Vagrants mirror signing key..."
  apt-key add /srv/config/apt-keys/varying-vagrant-vagrants_keyserver_ubuntu.key
fi

VVV_PACKAGE_LIST+=( 
  # other packages that come in handy
  subversion
  zip
  unzip
  ngrep
  curl
  make
  vim
  colordiff
  python-pip
  lftp

  # ntp service to keep clock current
  ntp
  ntpdate

  # Required for i18n tools
  gettext

  # dos2unix
  # Allows conversion of DOS style line endings to something less troublesome
  # in Linux.
  dos2unix
)

function shyaml_setup() {
  # Shyaml
  #
  # Used for passing custom parameters to the bash provisioning scripts
  echo " * Installing Shyaml for bash provisioning.."
  sudo pip install shyaml
}
export -f shyaml_setup

vvv_add_hook after_packages shyaml_setup 0

vvv_add_hook services_restart "service ntp restart"
