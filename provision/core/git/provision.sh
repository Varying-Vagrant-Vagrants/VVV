#!/usr/bin/env bash
# @description apt-get does not have latest version of git, so let's the use ppa repository instead.
set -eo pipefail

# @noargs
function git_register_packages() {
  if ! vvv_src_list_has "git-core/ppa"; then
    # Add ppa repo.
    vvv_info " * Adding ppa:git-core/ppa repository"
    add-apt-repository -y ppa:git-core/ppa
    vvv_success " * git-core/ppa added"
  else
    vvv_info " * git-core/ppa already present, skipping"
  fi

  if ! vvv_apt_keys_has 'git-lfs'; then
    # Apply the PackageCloud signing key which signs git lfs
    vvv_info " * Applying the PackageCloud Git-LFS signing key..."
    apt-key add /srv/config/apt-keys/git-lfs.key
  fi

  local OSID=$(lsb_release --id --short)
  local OSCODENAME=$(lsb_release --codename --short)
  local APTSOURCE="/srv/provision/core/git/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-git-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi

  VVV_PACKAGE_LIST+=(
    git
    git-lfs
    git-svn
  )
}
vvv_add_hook before_packages git_register_packages