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

  if ! vvv_src_list_has "github/git-lfs"; then
    cp -f "/srv/provision/core/git/sources.list" "/etc/apt/sources.list.d/vvv-git-sources.list"
  fi

  if ! vvv_apt_keys_has 'git-lfs'; then
    # Apply the PackageCloud signing key which signs git lfs
    vvv_info " * Applying the PackageCloud Git-LFS signing key..."
    apt-key add /srv/config/apt-keys/git-lfs.key
  fi

  VVV_PACKAGE_LIST+=(
    git
    git-lfs
    git-svn
  )
}
vvv_add_hook before_packages git_register_packages