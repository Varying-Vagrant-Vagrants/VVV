#!/usr/bin/env bash
# @description This file is specified in Vagrantfile and is loaded by Vagrant
#  as the primary provisioning script whenever the commands `vagrant up`,
# `vagrant provision`, or `vagrant reload` are used. It provides all of the
# default packages and configurations included with VVV.

# source bash_aliases before anything else so that PATH is properly configured on
# this shell session
. "/srv/provision/core/env/homedir/.bash_aliases"

# cleanup
mkdir -p /vagrant
rm -rf /vagrant/failed_provisioners
mkdir -p /vagrant/failed_provisioners

rm -f /vagrant/provisioned_at
rm -f /vagrant/version
rm -f /vagrant/vvv-custom.yml
rm -f /vagrant/config.yml

if [ -x "$(command -v ntpdate)" ]; then
	echo " * Syncing clocks"
	if sudo ntpdate -u ntp.ubuntu.com; then
		echo " * clocks synced"
	else
		vvv_warn " - clock synchronisation failed"
	fi
else
	echo " - skipping ntpdate clock sync, not installed yet"
fi

touch /vagrant/provisioned_at
echo $(date "+%Y.%m.%d_%H-%M-%S") > /vagrant/provisioned_at

# copy over version and config files
cp -f /home/vagrant/version /vagrant
cp -f /srv/config/config.yml /vagrant

chmod 0644 /vagrant/config.yml
chmod 0644 /vagrant/version
chmod 0644 /vagrant/provisioned_at

# change ownership for /vagrant folder
chown -R vagrant:vagrant /vagrant

export VVV_CONFIG=/vagrant/config.yml

# initialize provisioner helpers a bit later
. "/srv/provision/provisioners.sh"

export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1
export VVV_PACKAGE_LIST=()
export VVV_PACKAGE_REMOVAL_LIST=()

. "/srv/provision/core/env/provision.sh"
. '/srv/provision/core/deprecated.sh'
. "/srv/provision/core/vvv/provision.sh"
. "/srv/provision/core/git/provision.sh"
. "/srv/provision/core/mariadb/provision.sh"
. "/srv/provision/core/postfix/provision.sh"
. "/srv/provision/core/nginx/provision.sh"
. "/srv/provision/core/memcached/provision.sh"
. "/srv/provision/core/php/provision.sh"
. "/srv/provision/core/mailhog/provision.sh"
. "/srv/provision/core/node-nvm/provision.sh"
. "/srv/provision/core/avahi/provision.sh"

### SCRIPT
#set -xv

vvv_hook init

# If you need to disable this check then something is terribly wrong, tell us on github/slack
if ! network_check; then
  vvv_warn " =================================================================================================="
  vvv_warn " ! If this check fails despite succeeding in the browser, contact us in Slack or GitHub immediately"
  vvv_warn " =================================================================================================="
fi

vvv_info " * Apt package install pre-checks"
vvv_hook before_packages

vvv_info " * Registering apt keys"
vvv_hook register_apt_keys

vvv_info " * Registering apt sources"
vvv_hook register_apt_sources

vvv_apt_packages_upgrade

vvv_info " * Registering apt packages to install"
vvv_hook register_apt_packages

# Package and Tools Install
vvv_info " * Main packages check and install."
vvv_info " * Checking for apt packages to remove."
if ! vvv_apt_package_remove ${VVV_PACKAGE_REMOVAL_LIST[@]}; then
  vvv_error " ! Main packages removal failed, halting provision"
  exit 1
fi

vvv_info " * Checking for apt packages to install."
if ! vvv_package_install ${VVV_PACKAGE_LIST[@]}; then
  vvv_error " ! Main packages check and install failed, halting provision"
  exit 1
fi

vvv_info " * Running after_packages"
vvv_hook after_packages

vvv_info " * Finalizing"
vvv_hook finalize

#set +xv
# And it's done

provisioner_success
