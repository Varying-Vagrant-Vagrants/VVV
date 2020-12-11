#!/bin/bash
# git
#
# apt-get does not have latest version of git,
# so let's the use ppa repository instead.
#

function git_register_packages() {
  if ! vvv_src_list_has "git-core/ppa"; then
    # Add ppa repo.
    echo " * Adding ppa:git-core/ppa repository"
    sudo add-apt-repository -y ppa:git-core/ppa &>/dev/null
    echo " * git-core/ppa added"
  else
    echo " * git-core/ppa already present, skipping"
  fi

  if ! vvv_src_list_has "github/git-lfs"; then
    cp -f "/srv/provision/core/git/sources.list" "/etc/apt/sources.list.d/vvv-git-sources.list"
  fi

  if ! vvv_apt_keys_has 'git-lfs'; then
    # Apply the PackageCloud signing key which signs git lfs
    echo " * Applying the PackageCloud Git-LFS signing key..."
    apt-key add /srv/config/apt-keys/git-lfs.key
  fi

  VVV_PACKAGE_LIST+=(
    git
    git-lfs
    git-svn
  )
}
vvv_add_hook before_packages git_register_packages