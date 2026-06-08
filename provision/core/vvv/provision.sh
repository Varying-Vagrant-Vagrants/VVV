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
    apt-transport-https

    # Daily automatic security package upgrades
    unattended-upgrades

    # other packages that come in handy
    subversion
    zip
    unzip
    ngrep
    make
    colordiff
    python3-pip # needed for shyaml
    python3-setuptools
    jq
    yq
    less

    # Needed for WordPress PDF previews.
    ghostscript

    # Networking tools
    lftp
    curl
    httpie
    iputils-ping
    net-tools

    # Editors
    vim
    neovim
    nano

    # Required for i18n tools
    gettext

    # dos2unix
    # Allows conversion of DOS style line endings to something less troublesome
    # in Linux.
    dos2unix

    # webp support
    libwebp-dev
    webp

    # Shell tools
    stow
    fzf
    tmux

    # chrony keeps the VM clock current (replaces the older ntp/ntpdate,
    # which were removed in Ubuntu 26.04)
    chrony
  )
}
vvv_add_hook register_apt_packages vvv_register_packages 0

function vvv_register_apt_sources() {
  local OSCODENAME
  local OSID

  OSID=$(lsb_release --id --short)
  OSCODENAME=$(lsb_release --codename --short)

  local APTSOURCE="/srv/provision/core/vvv/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources vvv_register_apt_sources 0

function vvv_register_keys() {
  # The VVV mirror this key signs is only enabled on older releases (e.g. bionic),
  # which still rely on the legacy apt-key trust store. apt-key was removed in
  # Ubuntu 26.04+, so skip cleanly where it no longer exists.
  command -v apt-key >/dev/null 2>&1 || return 0
  # apt-key add is idempotent, so re-importing on each run is harmless and lets
  # VVV core avoid depending on the legacy vvv_apt_keys_has() lookup.
  vvv_info " * Applying the VVV mirror signing key..."
  apt-key add /srv/provision/core/vvv/apt-keys/varying-vagrant-vagrants_keyserver_ubuntu.key
}
vvv_add_hook register_apt_sources vvv_register_keys 0

function vvv_before_packages() {
  # this package and another are necessary to ensure certificate trust store is up to date
  # without this, some mirrors will faill due to changing letsencrypt intermediate root certificates
  if ! vvv_is_apt_pkg_installed "ca-certificates"; then
    vvv_info " * Installing updated certificate stores before proceeding"
    apt-get --yes install ca-certificates libgnutls30
    vvv_info " * Installing updated certificate stores completed with code ${?}"
  fi
}
vvv_add_hook before_packages vvv_before_packages 0

function vvv_remove_legacy_ntp() {
  # chrony now keeps the clock current on every release. Remove ntp/ntpdate if a
  # previous provision installed them, so two NTP daemons don't contend for UDP/123.
  if vvv_is_apt_pkg_installed "ntp" || vvv_is_apt_pkg_installed "ntpdate"; then
    vvv_info " * Removing legacy ntp/ntpdate packages (replaced by chrony)"
    apt-get --yes purge ntp ntpdate
  fi
}
vvv_add_hook before_packages vvv_remove_legacy_ntp 0

function shyaml_setup() {
  # Shyaml
  #
  # Used for passing custom parameters to the bash provisioning scripts
  if [ ! -f /usr/local/bin/shyaml ]; then
    vvv_info " * Installing Shyaml for bash provisioning.."

    local OSVERSION_NUMBER
    OSVERSION_NUMBER=$(lsb_release -sr)

    # Ubuntu 24 making it hard to install pip packages, throwing externally-managed-environment error
    # https://stackoverflow.com/a/75722775
    if dpkg --compare-versions "${OSVERSION_NUMBER[@]}" ge "24.04"
    then
      # to make it available globally this is the last workaround, hopefully it doesn't break the system
      # TODO: try to find a better alternative way to install
      sudo pip3 install wheel --break-system-packages
      sudo pip3 install shyaml --break-system-packages
    else
      sudo pip3 install wheel
      sudo pip3 install shyaml
    fi
  fi
}
export -f shyaml_setup

vvv_add_hook after_packages shyaml_setup 0

function vvv_chrony_restart() {
  if [ ! -f /.dockerenv ]; then
    service chrony restart
  fi
}

vvv_add_hook services_restart vvv_chrony_restart

function cleanup_vvv(){
  if test -f "/tmp/hosts"; then
    sudo rm /tmp/hosts
  fi

  # Cleanup the hosts file
  vvv_info " * Cleaning the virtual machine's /etc/hosts file..."
  sed -n '/# vvv-auto$/!p' /etc/hosts > /tmp/hosts
  echo "127.0.0.1 vvv # vvv-auto" >> "/etc/hosts"
  echo "127.0.0.1 vvv.test # vvv-auto" >> "/etc/hosts"
  if is_utility_installed core tideways; then
    echo "127.0.0.1 tideways.vvv.test # vvv-auto" >> "/etc/hosts"
    echo "127.0.0.1 xhgui.vvv.test # vvv-auto" >> "/etc/hosts"
  fi
  sudo cp -rf /tmp/hosts /etc/hosts

  # cleanup
  if test -f "/tmp/hosts"; then
    sudo rm /tmp/hosts
  fi
}
export -f cleanup_vvv

vvv_add_hook finalize cleanup_vvv 15

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
