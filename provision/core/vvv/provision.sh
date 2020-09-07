#!/bin/bash

function vvv_register_packages() {
  cp -f "/srv/provision/core/vvv/sources.list" "/etc/apt/sources.list.d/vvv-sources.list"

  if ! vvv_apt_keys_has 'Varying Vagrant Vagrants'; then
    # Apply the VVV signing key
    echo " * Applying the Varying Vagrant Vagrants mirror signing key..."
    apt-key add /srv/config/apt-keys/varying-vagrant-vagrants_keyserver_ubuntu.key
  fi

  VVV_PACKAGE_LIST+=( 
    software-properties-common

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
}
vvv_add_hook before_packages vvv_register_packages 0

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

function cleanup_vvv(){
  echo " "
  # Cleanup the hosts file
  echo " * Cleaning the virtual machine's /etc/hosts file..."
  sed -n '/# vvv-auto$/!p' /etc/hosts > /tmp/hosts
  echo "127.0.0.1 vvv # vvv-auto" >> "/etc/hosts"
  echo "127.0.0.1 vvv.test # vvv-auto" >> "/etc/hosts"
  if is_utility_installed core tideways; then
    echo "127.0.0.1 tideways.vvv.test # vvv-auto" >> "/etc/hosts"
    echo "127.0.0.1 xhgui.vvv.test # vvv-auto" >> "/etc/hosts"
  fi
  mv /tmp/hosts /etc/hosts
}
export -f cleanup_vvv
vvv_add_hook finalize cleanup_vvv 15

function apt_hash_missmatch_fix() {
  if [ ! -f "/etc/apt/apt.conf.d/99hashmismatch" ]; then
    echo " * Copying /srv/config/apt-conf-d/99hashmismatch to /etc/apt/apt.conf.d/99hashmismatch"
    cp -f "/srv/config/apt-conf-d/99hashmismatch" "/etc/apt/apt.conf.d/99hashmismatch"
  fi
}
export -f apt_hash_missmatch_fix
vvv_add_hook init apt_hash_missmatch_fix

function services_restart() {
  # RESTART SERVICES
  #
  # Make sure the services we expect to be running are running.
  echo -e "\n * Restarting services..."
  vvv_hook services_restart
}
vvv_add_hook finalize services_restart 1000