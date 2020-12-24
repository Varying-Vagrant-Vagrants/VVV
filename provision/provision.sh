#!/bin/bash
#
# provision.sh
#
# This file is specified in Vagrantfile and is loaded by Vagrant as the primary
# provisioning script whenever the commands `vagrant up`, `vagrant provision`,
# or `vagrant reload` are used. It provides all of the default packages and
# configurations included with Varying Vagrant Vagrants.

# source bash_aliases before anything else so that PATH is properly configured on
# this shell session
. "/srv/config/bash_aliases"

# cleanup
mkdir -p /vagrant
rm -rf /vagrant/failed_provisioners
mkdir -p /vagrant/failed_provisioners

rm -f /vagrant/provisioned_at
rm -f /vagrant/version
rm -f /vagrant/vvv-custom.yml
rm -f /vagrant/config.yml

touch /vagrant/provisioned_at
echo $(date "+%Y.%m.%d_%H-%M-%S") > /vagrant/provisioned_at

# copy over version and config files
cp -f /home/vagrant/version /vagrant
cp -f /srv/config/config.yml /vagrant

sudo chmod 0644 /vagrant/config.yml
sudo chmod 0644 /vagrant/version
sudo chmod 0644 /vagrant/provisioned_at

# change ownership for /vagrant folder
sudo chown -R vagrant:vagrant /vagrant

export VVV_CONFIG=/vagrant/config.yml

# initialize provisioner helpers a bit later
. "/srv/provision/provisioners.sh"

export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1
export VVV_PACKAGE_LIST=()

. "/srv/provision/core/env.sh"
. '/srv/provision/core/deprecated.sh'
. "/srv/provision/core/vvv/provision.sh"
. "/srv/provision/core/git/provision.sh"
. "/srv/provision/core/mariadb/provision.sh"
. "/srv/provision/core/postfix/provision.sh"
. "/srv/provision/core/nginx/provision.sh"
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

vvv_hook before_packages

# Package and Tools Install
vvv_info " * Main packages check and install."
if ! vvv_package_install ${VVV_PACKAGE_LIST[@]}; then
  vvv_error " ! Main packages check and install failed, halting provision"
  exit 1
fi

vvv_info " * Running tools_install"
vvv_hook after_packages

vvv_info " * Finalizing"
vvv_hook finalize

#set +xv
# And it's done

provisioner_success
