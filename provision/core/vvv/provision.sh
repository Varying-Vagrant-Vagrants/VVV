#!/usr/bin/env bash
# @description VVV main packages and misc fixes
set -eo pipefail

function vvv_register_packages() {
  VVV_PACKAGE_REMOVAL_LIST+=(
    # remove the old Python 2 packages to avoid issues with python3-pip
    python-pip
    python-setuptools

    # remove nodesource js etc we have nvm for that
    nodejs
  )

  VVV_PACKAGE_LIST+=(
    software-properties-common
    ca-certificates
    libgnutls30

    # Daily automatic security package upgrades
    unattended-upgrades

    # other packages that come in handy
    subversion
    zip
    unzip
    ngrep
    curl
    make
    vim
    colordiff
    python3-pip # needed for shyaml
    python3-setuptools
    lftp
    jq
    less
    iputils-ping
    net-tools

    # ntp service to keep clock current
    ntp
    ntpdate

    # Required for i18n tools
    gettext

    # dos2unix
    # Allows conversion of DOS style line endings to something less troublesome
    # in Linux.
    dos2unix

    # webp support
    libwebp-dev
    webp
  )
}
vvv_add_hook register_apt_packages vvv_register_packages 0

function vvv_register_apt_sources() {
  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE="/srv/provision/core/vvv/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources vvv_register_apt_sources 0

function vvv_register_keys() {
  if ! vvv_apt_keys_has 'Varying Vagrant Vagrants'; then
    # Apply the VVV signing key
    vvv_info " * Applying the VVV mirror signing key..."
    apt-key add /srv/provision/core/vvv/apt-keys/varying-vagrant-vagrants_keyserver_ubuntu.key
  fi
}
vvv_add_hook register_apt_sources vvv_register_keys 0

function vvv_before_packages() {
  # this package and another are necessary to ensure certificate trust store is up to date
  # without this, some mirrors will faill due to changing letsencrypt intermediate root certificates
  if [ $(dpkg-query -W -f='${Status}' ca-certificates 2>/dev/null | grep -c "ok installed") -eq 0 ]; then
    vvv_info " * Installing updated certificate stores before proceeding"
    apt-get --yes install ca-certificates libgnutls30
    vvv_info " * Installing updated certificate stores completed with code ${?}"
  fi
}
vvv_add_hook before_packages vvv_before_packages 0

function shyaml_setup() {
  # Shyaml
  #
  # Used for passing custom parameters to the bash provisioning scripts
  if [ ! -f /usr/local/bin/shyaml ]; then
    vvv_info " * Installing Shyaml for bash provisioning.."
    sudo pip3 install wheel
    sudo pip3 install shyaml
  fi
}
export -f shyaml_setup

vvv_add_hook after_packages shyaml_setup 0

function vvv_ntp_restart() {
  if [ "${VVV_DOCKER}" != 1 ]; then
    service ntp restart
  fi
}

vvv_add_hook services_restart vvv_ntp_restart

function cleanup_vvv(){
  # Cleanup the hosts file
  vvv_info " * Cleaning the virtual machine's /etc/hosts file..."
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

if [ "${VVV_DOCKER}" != 1 ]; then
  vvv_add_hook finalize cleanup_vvv 15
fi

function apt_hash_missmatch_fix() {
  if [ ! -f "/etc/apt/apt.conf.d/99hashmismatch" ]; then
    vvv_info " * Copying /srv/provision/core/vvv/apt-conf-d/99hashmismatch to /etc/apt/apt.conf.d/99hashmismatch"
    cp -f "/srv/provision/core/vvv/apt-conf-d/99hashmismatch" "/etc/apt/apt.conf.d/99hashmismatch"
  fi

  # Avoid bad hardware implementations that interfere with gcrypt by disabling hardware support
  # reference https://askubuntu.com/a/1242739
  mkdir -p /etc/gcrypt
  echo all >> /etc/gcrypt/hwf.deny
}
export -f apt_hash_missmatch_fix
vvv_add_hook init apt_hash_missmatch_fix

function services_restart() {
  # RESTART SERVICES
  #
  # Make sure the services we expect to be running are running.
  vvv_info " * Restarting services..."
  vvv_hook services_restart
  vvv_info " * Services restarted..."
}
vvv_add_hook finalize services_restart 1000
