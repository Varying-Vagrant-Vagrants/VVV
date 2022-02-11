#!/usr/bin/env bash
# @description Install and configure Node

# NVM install to facilitate multiple versions of Nodejs
# Makes it easier to install new versions of Nodejs and
# to work with multiple projects that need different
# versions of Nodejs

set -eo pipefail

echo " * Checking for NVM"

export NVM_DIR="/home/vagrant/.nvm"

function nvm_setup() {
  if [[ -d "${NVM_DIR}" && -f "${NVM_DIR}/nvm.sh" ]]
  then
    vvv_success " ✓ NVM is already installed, checking for updates"
    (
      cd "$NVM_DIR"
      git fetch --tags origin
      git checkout `git describe --abbrev=0 --tags --match "v[0-9]*" $(git rev-list --tags --max-count=1)`
    ) && \. "$NVM_DIR/nvm.sh"
  else
    if [[ -d "${NVMFOLDER}" && ! -f "${NVMFOLDER}/nvm.sh" ]]
    then
      # Possible remnants or something messed
      # up NVM install making it unusable
      vvv_warn " * NVM found in an unusable state, removing it completely"
      rm -rf "${NVM_DIR}"
    fi

    echo " * Installing NVM via git"
    (
      git clone https://github.com/nvm-sh/nvm.git "$NVM_DIR"
      cd "${NVM_DIR}"
      git checkout `git describe --abbrev=0 --tags --match "v[0-9]*" $(git rev-list --tags --max-count=1)`
    ) && \. "$NVM_DIR/nvm.sh"
    vvv_success " ✓ NVM installed"

    echo 'export NVM_DIR="$HOME/.nvm"' >> /home/vagrant/.bashrc
    echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" # This loads nvm' >> /home/vagrant/.bashrc
    echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"  # This loads nvm bash_completion' >> /home/vagrant/.bashrc
  fi

  vvv_info " - Installing Node 14 via nvm"
  nvm install 14

  vvv_info " - Installing Node 16 via nvm"
  nvm install 16

  vvv_info " - setting the default to 16"
  nvm alias default 16
  nvm use 16
}

vvv_add_hook after_packages nvm_setup