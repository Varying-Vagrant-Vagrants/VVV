#!/bin/bash
#
# provision.sh
#
# This file is specified in Vagrantfile and is loaded by Vagrant as the primary
# provisioning script whenever the commands `vagrant up`, `vagrant provision`,
# or `vagrant reload` are used. It provides all of the default packages and
# configurations included with Varying Vagrant Vagrants.

. "/srv/provision/core/env.sh"
setup_vvv_env

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

. '/srv/provision/core/deprecated.sh'
remove_v2_resources
support_v2_certificate_path
depreacted_distro

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

phpfpm_setup() {
  # Copy php-fpm configuration from local
  echo " * Copying /srv/config/php-config/php7.2-fpm.conf   to /etc/php/7.2/fpm/php-fpm.conf"
  cp -f "/srv/config/php-config/php7.2-fpm.conf" "/etc/php/7.2/fpm/php-fpm.conf"

  echo " * Copying /srv/config/php-config/php7.2-www.conf   to /etc/php/7.2/fpm/pool.d/www.conf"
  cp -f "/srv/config/php-config/php7.2-www.conf" "/etc/php/7.2/fpm/pool.d/www.conf"

  echo " * Copying /srv/config/php-config/php7.2-custom.ini to /etc/php/7.2/fpm/conf.d/php-custom.ini"
  cp -f "/srv/config/php-config/php7.2-custom.ini" "/etc/php/7.2/fpm/conf.d/php-custom.ini"

  echo " * Copying /srv/config/php-config/opcache.ini       to /etc/php/7.2/fpm/conf.d/opcache.ini"
  cp -f "/srv/config/php-config/opcache.ini" "/etc/php/7.2/fpm/conf.d/opcache.ini"

  echo " * Copying /srv/config/php-config/xdebug.ini        to /etc/php/7.2/mods-available/xdebug.ini"
  cp -f "/srv/config/php-config/xdebug.ini" "/etc/php/7.2/mods-available/xdebug.ini"

  echo " * Copying /srv/config/php-config/mailhog.ini       to /etc/php/7.2/mods-available/mailhog.ini"
  cp -f "/srv/config/php-config/mailhog.ini" "/etc/php/7.2/mods-available/mailhog.ini"

  if [[ -f "/etc/php/7.2/mods-available/mailcatcher.ini" ]]; then
    echo " * Cleaning up mailcatcher.ini from a previous install"
    rm -f /etc/php/7.2/mods-available/mailcatcher.ini
  fi

  # Copy memcached configuration from local
  echo " * Copying /srv/config/memcached-config/memcached.conf to /etc/memcached.conf and /etc/memcached_default.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached.conf"
  cp -f "/srv/config/memcached-config/memcached.conf" "/etc/memcached_default.conf"
}

mailhog_setup() {
  if [[ -f "/etc/init/mailcatcher.conf" ]]; then
    echo " * Cleaning up old mailcatcher.conf"
    rm -f /etc/init/mailcatcher.conf
  fi

  if [[ ! -e /usr/local/bin/mailhog ]]; then
    echo " * Installing MailHog"
    curl --silent -L -o /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64
    chmod +x /usr/local/bin/mailhog
  fi
  if [[ ! -e /usr/local/bin/mhsendmail ]]; then
    echo " * Installing MHSendmail"
    curl --silent -L -o /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
    chmod +x /usr/local/bin/mhsendmail
  fi

  if [[ ! -e /etc/systemd/system/mailhog.service ]]; then
    echo " * Mailhog service file missing, setting up"
    # Make it start on reboot
    tee /etc/systemd/system/mailhog.service <<EOL
[Unit]
Description=MailHog
After=network.service vagrant.mount
[Service]
Type=simple
ExecStart=/usr/bin/env /usr/local/bin/mailhog > /dev/null 2>&1 &
[Install]
WantedBy=multi-user.target
EOL
  fi

  # Start on reboot
  echo " * Enabling MailHog Service"
  systemctl enable mailhog

  echo " * Starting MailHog Service"
  systemctl start mailhog
}

services_restart() {
  # RESTART SERVICES
  #
  # Make sure the services we expect to be running are running.
  echo -e "\n * Restarting services..."
  service nginx restart
  service memcached restart
  service mailhog restart
  service ntp restart

  # Disable PHP Xdebug module by default
  echo " * Disabling XDebug PHP extension"
  phpdismod xdebug

  # Enable PHP MailHog sendmail settings by default
  echo " * Enabling MailHog for PHP"
  phpenmod -s ALL mailhog

  # Restart all php-fpm versions
  find /etc/init.d/ -name "php*-fpm" -exec bash -c 'sudo service "$(basename "$0")" restart' {} \;

  # Add the vagrant user to the www-data group so that it has better access
  # to PHP and Nginx related files.
  usermod -a -G www-data vagrant
}

wp_cli() {
  # WP-CLI Install
  local exists_wpcli

  # Remove old wp-cli symlink, if it exists.
  if [[ -L "/usr/local/bin/wp" ]]; then
    echo " * Removing old wp-cli symlink"
    rm -f /usr/local/bin/wp
  fi

  exists_wpcli="$(which wp)"
  if [[ "/usr/local/bin/wp" != "${exists_wpcli}" ]]; then
    echo " * Downloading wp-cli nightly, see http://wp-cli.org"
    curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli-nightly.phar
    chmod +x wp-cli-nightly.phar
    sudo mv wp-cli-nightly.phar /usr/local/bin/wp

    echo " * Grabbing WP CLI bash completions"
    # Install bash completions
    curl -s https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash -o /srv/config/wp-cli/wp-completion.bash
  else
    echo " * Updating wp-cli..."
    wp --allow-root cli update --nightly --yes
  fi
}

php_codesniff() {
  export DEBIAN_FRONTEND=noninteractive

  # PHP_CodeSniffer (for running WordPress-Coding-Standards)
  # Sniffs WordPress Coding Standards
  echo -e "\n * Install/Update PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"
  echo -e "\n * Install/Update WordPress-Coding-Standards, sniffs for PHP_CodeSniffer, see https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards"
  cd /srv/provision/phpcs
  noroot composer update --no-ansi --no-autoloader --no-progress

  # Link `phpcbf` and `phpcs` to the `/usr/local/bin` directory so
  # that it can be used on the host in an editor with matching rules
  ln -sf "/srv/www/phpcs/bin/phpcbf" "/usr/local/bin/phpcbf"
  ln -sf "/srv/www/phpcs/bin/phpcs" "/usr/local/bin/phpcs"

  # Install the standards in PHPCS
  phpcs --config-set installed_paths ./CodeSniffer/Standards/WordPress/,./CodeSniffer/Standards/VIP-Coding-Standards/,./CodeSniffer/Standards/PHPCompatibility/,./CodeSniffer/Standards/PHPCompatibilityParagonie/,./CodeSniffer/Standards/PHPCompatibilityWP/
  phpcs --config-set default_standard WordPress-Core
  phpcs -i
}

wpsvn_check() {
  echo " * Searching for SVN repositories that need upgrading"
  # Get all SVN repos.
  svn_repos=$(find /srv/www -maxdepth 5 -type d -name '.svn');

  # Do we have any?
  if [[ -n $svn_repos ]]; then
    for repo in $svn_repos; do
      # Test to see if an svn upgrade is needed on this repo.
      svn_test=$( svn status -u "$repo" 2>&1 );

      if [[ "$svn_test" == *"svn upgrade"* ]]; then
        # If it is needed do it!
        echo " * Upgrading svn repository: ${repo}"
        svn upgrade "${repo/%\.svn/}"
      fi;
    done
  fi;
}

cleanup_vvv(){
  echo " * Cleaning up Nginx configs"
  # Kill previously symlinked Nginx configs
  find /etc/nginx/custom-sites -name 'vvv-auto-*.conf' -exec rm {} \;

  # Cleanup the hosts file
  echo " * Cleaning the virtual machine's /etc/hosts file..."
  sed -n '/# vvv-auto$/!p' /etc/hosts > /tmp/hosts
  echo "127.0.0.1 vvv # vvv-auto" >> "/etc/hosts"
  echo "127.0.0.1 vvv.test # vvv-auto" >> "/etc/hosts"
  if is_utility_installed core tideways; then
    echo "127.0.0.1 tideways.vvv.test # vvv-auto" >> "/etc/hosts"
    echo "127.0.0.1 xhgui.vvv.test # vvv-auto" >> "/etc/hosts"
  fi
  mv /tmp/hosts /etc/hosts
}

### SCRIPT
#set -xv

# Profile_setup
echo " * Bash profile setup and directories."
cleanup_terminal_splash
profile_setup

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

mailhog_setup

phpfpm_setup
services_restart

# WP-CLI and debugging tools
echo " "
echo " * Installing/updating wp-cli and debugging tools"

wp_cli
php_codesniff

if ! network_check; then
  exit 1
fi
# Time for WordPress!
echo " "

wpsvn_check

# VVV custom site import
echo " "
cleanup_vvv

#set +xv
# And it's done

provisioner_success
