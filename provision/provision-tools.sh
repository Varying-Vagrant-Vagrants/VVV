#!/usr/bin/env bash
# @description This file is specified in Vagrantfile and is loaded by Vagrant
#  as the primary provisioning script whenever the commands `vagrant up`,
# `vagrant provision`, or `vagrant reload` are used. It provides all of the
# default packages and configurations included with VVV.

# source bash_aliases before anything else so that PATH is properly configured on
# this shell session
. "/srv/provision/core/env/homedir/.bash_aliases"


export VVV_CONFIG=/vagrant/config.yml

# initialize provisioner helpers a bit later
. "/srv/provision/provisioners.sh"

export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1

. "/srv/provision/core/env/provision.sh"
. '/srv/provision/core/deprecated.sh'
. "/srv/provision/core/vvv/provision.sh"
. "/srv/provision/core/git/provision.sh"
. "/srv/provision/core/mariadb/provision.sh"
. "/srv/provision/core/postfix/provision.sh"
. "/srv/provision/core/nginx/provision.sh"
. "/srv/provision/core/memcached/provision.sh"
. "/srv/provision/core/php/provision.sh"
. "/srv/provision/core/composer/provision.sh"
. "/srv/provision/core/nodejs/provision.sh"
. "/srv/provision/core/grunt/provision.sh"
. "/srv/provision/core/mailhog/provision.sh"
. "/srv/provision/core/wp-cli/provision.sh"
. "/srv/provision/core/phpcs/provision.sh"

### SCRIPT
#set -xv

vvv_hook init

if ! network_check; then
  exit 1
fi

# Package and Tools Install
vvv_info " * Running tools_install"
vvv_hook tools_setup

vvv_info " * Finalizing Tools"
vvv_hook tools_finalize

#set +xv
# And it's done

provisioner_success
