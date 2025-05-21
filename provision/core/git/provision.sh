#!/usr/bin/env bash
# @description apt-get does not have latest version of git, so let's the use ppa repository instead.
set -eo pipefail

# @noargs
function git_register_apt_sources() {
  local OSCODENAME
  local OSID

  OSCODENAME=$(lsb_release --codename --short)
  OSID=$(lsb_release --id --short)

  if [ "${OSID}" == "Ubuntu" ]; then
    if ! vvv_src_list_has "git-core/ppa"; then
      # Add ppa repo.
      vvv_info " * Adding ppa:git-core/ppa repository"
      add-apt-repository -y ppa:git-core/ppa
      vvv_success " * git-core/ppa added"
    else
      vvv_info " * git-core/ppa already present, skipping"
    fi
  fi

  local APTSOURCE="/srv/provision/core/git/sources-${OSID,,}-${OSCODENAME,,}.list"
  if [ -f "${APTSOURCE}" ]; then
    cp -f "${APTSOURCE}" "/etc/apt/sources.list.d/vvv-git-sources.list"
  else
    vvv_error " ! VVV could not copy an Apt source file ( ${APTSOURCE} ), the current OS/Version (${OSID,,}-${OSCODENAME,,}) combination is unavailable"
  fi
}
vvv_add_hook register_apt_sources git_register_apt_sources

# @noargs
function git_register_apt_packages() {
  VVV_PACKAGE_LIST+=(
    git
    git-lfs
    git-svn
  )
}
vvv_add_hook register_apt_packages git_register_apt_packages

# @noargs
function git_register_apt_keys() {
  cp -f /srv/provision/core/git/apt-keys/github_git-lfs-archive-keyring.gpg /etc/apt/keyrings/github_git-lfs-archive-keyring.gpg
  chmod 0644 /etc/apt/keyrings/github_git-lfs-archive-keyring.gpg
}
vvv_add_hook register_apt_keys git_register_apt_keys

# @noargs
function git_after_packages() {
  # if this setting isn't set, git will exit and provisioning will fail
  if ! git config --global pull.rebase; then
    vvv_info " * Git global config hasn't been told how to merge branches, setting pull.rebase false for the merge strategy"
    git config --global pull.rebase false || vvv_warn "Failed ot update git config pull.rebase to false"
  fi
  if ! noroot git config --global pull.rebase; then
    vvv_info " * Git noroot global config hasn't been told how to merge branches, setting pull.rebase false for the merge strategy"
    noroot git config --global pull.rebase false || vvv_warn "Failed to update noroot git config pull.rebase to false"
  fi

  git config --global --add safe.directory '*' || vvv_warn "Failed to update git config safe.directory"
  noroot git config --global --add safe.directory '*'|| vvv_warn "Failed to update noroot git config safe.directory"
}
vvv_add_hook after_packages git_after_packages
