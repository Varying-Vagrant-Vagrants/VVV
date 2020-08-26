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

. "/srv/provision/core/env.sh"
. '/srv/provision/core/deprecated.sh'

### FUNCTIONS

mini_provisioners() {
  export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1
  export VVV_PACKAGE_LIST=(
    software-properties-common
  )

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
}

package_install() {

  # fix https://github.com/Varying-Vagrant-Vagrants/VVV/issues/2150
  echo " * Cleaning up dpkg lock file"
  rm /var/lib/dpkg/lock*

  echo " * Updating apt keys"
  apt-key update -y

  # Update all of the package references before installing anything
  echo " * Copying /srv/config/apt-conf-d/99hashmismatch to /etc/apt/apt.conf.d/99hashmismatch"
  cp -f "/srv/config/apt-conf-d/99hashmismatch" "/etc/apt/apt.conf.d/99hashmismatch"
  echo " * Running apt-get update..."
  rm -rf /var/lib/apt/lists/*
  apt-get update -y --fix-missing

  # Install required packages
  echo " * Installing apt-get packages..."

  # To avoid issues on provisioning and failed apt installation
  dpkg --configure -a
  if ! apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --fix-broken ${VVV_PACKAGE_LIST[@]}; then
    echo " * Installing apt-get packages returned a failure code, cleaning up apt caches then exiting"
    apt-get clean -y
    return 1
  fi

  # Remove unnecessary packages
  echo " * Removing unnecessary apt packages..."
  apt-get autoremove -y

  # Clean up apt caches
  echo " * Cleaning apt caches..."
  apt-get clean -y

  return 0
}

services_restart() {
  # RESTART SERVICES
  #
  # Make sure the services we expect to be running are running.
  echo -e "\n * Restarting services..."
  vvv_hook services_restart
}
vvv_add_hook finalize services_restart 1000

vvv_hook init

### SCRIPT
#set -xv

if ! network_check; then
  exit 1
fi

mini_provisioners

# Package and Tools Install
echo " "
echo " * Main packages check and install."
if ! package_install; then
  vvv_error " ! Main packages check and install failed, halting provision"
  exit 1
fi

echo " * Running tools_install"
vvv_hook after_packages

echo " * Finalizing"
vvv_hook finalize

#set +xv
# And it's done

provisioner_success
