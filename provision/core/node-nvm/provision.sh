#!/usr/bin/env bash
# @description Install and configure Node

# NVM install to facilitate multiple versions of Nodejs
# Makes it easier to install new versions of Nodejs and
# to work with multiple projects that need different
# versions of Nodejs

set -eo pipefail

export NVM_DIR="/home/vagrant/.nvm"

function vvv_nvm_setup() {
  vvv_info " * Checking for NVM"

  if [[ -d "${NVM_DIR}" && -f "${NVM_DIR}/nvm.sh" ]]
  then
    vvv_success " ✓ NVM is already installed, checking for updates"
    cd "${NVM_DIR}"
    noroot git fetch --tags origin
    noroot git checkout $(noroot git describe --abbrev=0 --tags --match "v[0-9]*" $(noroot git rev-list --tags --max-count=1))
    cd -
    vvv_info " - Loading nvm"
    [ -s "${NVM_DIR}/nvm.sh" ] && . "${NVM_DIR}/nvm.sh"
    vvv_info " - nvm loaded"
  else
    if [[ -d "${NVMFOLDER}" && ! -f "${NVMFOLDER}/nvm.sh" ]]
    then
      # Possible remnants or something messed
      # up NVM install making it unusable
      vvv_warn " * NVM found in an unusable state, removing it completely"
      rm -rf "${NVM_DIR}"
    fi

    vvv_info " - Installing NVM via git"
    noroot git clone https://github.com/nvm-sh/nvm.git "${NVM_DIR}"
    cd "${NVM_DIR}"
    noroot git checkout $(noroot git describe --abbrev=0 --tags --match "v[0-9]*" $(noroot git rev-list --tags --max-count=1))
    cd -
    vvv_info " - Loading nvm"
    [ -s "${NVM_DIR}/nvm.sh" ] && . "${NVM_DIR}/nvm.sh"
    vvv_info " - NVM loaded"

    vvv_success " ✓ NVM installed"

    echo 'export NVM_DIR="/home/vagrant/.nvm"' >> /home/vagrant/.bashrc
    echo '[ -s "/home/vagrant/.nvm/nvm.sh" ] && \. "/home/vagrant/.nvm/nvm.sh" # This loads nvm' >> /home/vagrant/.bashrc
    echo '[ -s "/home/vagrant/.nvm/bash_completion" ] && \. "/home/vagrant/.nvm/bash_completion"  # This loads nvm bash_completion' >> /home/vagrant/.bashrc

    echo 'export NVM_DIR="/home/vagrant/.nvm"' >> /root/.bashrc
    echo '[ -s "/home/vagrant/.nvm/nvm.sh" ] && \. "/home/vagrant/.nvm/nvm.sh" # This loads nvm' >> /root/.bashrc
    echo '[ -s "/home/vagrant/.nvm/bash_completion" ] && \. "/home/vagrant/.nvm/bash_completion"  # This loads nvm bash_completion' >> /root/.bashrc

  fi

  vvv_info " - Installing Node 14 via nvm"
  nvm install 14
  nvm use 14

  vvv_info " - Ensuring vagrant user owns its own nvm folder"
  chown -R vagrant:vagrant /home/vagrant/.nvm/

  vvv_success " ✓ NVM setup completed"
}

export -f vvv_nvm_setup;

vvv_add_hook after_packages vvv_nvm_setup
