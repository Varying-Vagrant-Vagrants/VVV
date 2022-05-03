#!/usr/bin/env bash
# @description This file is specified in Vagrantfile and is loaded by Vagrant
#  as the primary provisioning script whenever the commands `vagrant up`,
# `vagrant provision`, or `vagrant reload` are used. It provides all of the
# default packages and configurations included with VVV.

set -eo pipefail

# source bash_aliases before anything else so that PATH is properly configured on
# this shell session
[ -s "/home/vagrant/.bashrc" ] && \. "/home/vagrant/.bashrc"
. "/srv/provision/core/env/homedir/.bash_aliases"
if [ -s "/home/vagrant/.nvm/nvm.sh" ]; then
  . "/home/vagrant/.nvm/nvm.sh"
else
  echo "NVM NOT PRESENT!"
fi

export VVV_CONFIG=/vagrant/config.yml

# initialize provisioner helpers a bit later
. "/srv/provision/provisioners.sh"

export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1

. "/srv/provision/core/composer/provision.sh"
. "/srv/provision/core/phpcs/provision.sh"
. "/srv/provision/core/grunt/provision.sh"
. "/srv/provision/core/wp-cli/provision.sh"

### SCRIPT

# Package and Tools Install
vvv_hook tools_pre_setup
vvv_hook tools_setup

# For tool provisioners that have trouble with parallelisation
vvv_hook tools_setup_synchronous
vvv_hook tools_finalize

# And it's done

provisioner_success
