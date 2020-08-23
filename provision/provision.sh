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

git_ppa_check() {
  # git
  #
  # apt-get does not have latest version of git,
  # so let's the use ppa repository instead.
  #
  local STATUS=1
  if [ ! -z "$(ls -A /etc/apt/sources.list.d/)" ]; then
    grep -Rq "^deb.*git-core/ppa" /etc/apt/sources.list.d/*.list
    STATUS=$?
  fi
  if [ "$STATUS" -ne "0" ]; then
    # Install prerequisites.
    echo " * Setting up Git PPA pre-requisites"
    sudo apt-get install -y python-software-properties software-properties-common &>/dev/null
    # Add ppa repo.
    echo " * Adding ppa:git-core/ppa repository"
    sudo add-apt-repository -y ppa:git-core/ppa &>/dev/null
    # Update apt-get info.
    sudo apt-get update --fix-missing
    echo " * git-core/ppa added"
  else
    echo " * git-core/ppa already present, skipping"
  fi
}

package_install() {

  export APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1
  export VVV_PACKAGE_LIST=()

  . "/srv/provision/core/mariadb/provision.sh"
  . "/srv/provision/core/postfix/provision.sh"

  # Provide our custom apt sources before running `apt-get update`
  echo " * Copying custom apt sources"
  cp -f /srv/config/apt-source-append.list /etc/apt/sources.list.d/vvv-sources.list

  echo " * Checking Apt Keys"
  keys=$( apt-key list )
  if [[ ! $( echo "${keys}" | grep 'NodeSource') ]]; then
    # Retrieve the NodeJS signing key from nodesource.com
    echo " * Applying NodeSource NodeJS signing key..."
    apt-key add /srv/config/apt-keys/nodesource.gpg.key
  fi

  # Before running `apt-get update`, we should add the public keys for
  # the packages that we are installing from non standard sources via
  # our appended apt source.list
  if [[ ! $( echo "${keys}" | grep 'nginx') ]]; then
    # Retrieve the Nginx signing key from nginx.org
    echo " * Applying Nginx signing key..."
    apt-key add /srv/config/apt-keys/nginx_signing.key
  fi

  if [[ ! $( echo "${keys}" | grep 'Ondřej') ]]; then
    # Apply the PHP signing key
    echo " * Applying the Ondřej PHP signing key..."
    apt-key add /srv/config/apt-keys/ondrej_keyserver_ubuntu.key
  fi

  if [[ ! $( echo "${keys}" | grep 'Varying Vagrant Vagrants') ]]; then
    # Apply the VVV signing key
    echo " * Applying the Varying Vagrant Vagrants mirror signing key..."
    apt-key add /srv/config/apt-keys/varying-vagrant-vagrants_keyserver_ubuntu.key
  fi

  if [[ ! $( echo "${keys}" | grep 'MariaDB') ]]; then
    # Apply the MariaDB signing keyg
    echo " * Applying the MariaDB signing key..."
    apt-key add /srv/config/apt-keys/mariadb.key
  fi

  if [[ ! $( echo "${keys}" | grep 'git-lfs') ]]; then
    # Apply the PackageCloud signing key which signs git lfs
    echo " * Applying the PackageCloud Git-LFS signing key..."
    apt-key add /srv/config/apt-keys/git-lfs.key
  fi
  if [[ ! $( echo "${keys}" | grep 'MongoDB 4.0') ]]; then
    echo " * Applying the MongoDB 4.0 signing key..."
    apt-key add /srv/config/apt-keys/mongo-server-4.0.asc
  fi

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

  # Build a bash array to pass all of the packages we want to install to a single
  # apt-get command. This avoids doing all the leg work each time a package is
  # set to install. It also allows us to easily comment out or add single
  # packages.
  apt_package_install_list=(
    # Please avoid apostrophes in these comments - they break vim syntax
    # highlighting.
    #
    software-properties-common

    # PHP7
    #
    # Our base packages for php7.2. As long as php7.2-fpm and php7.2-cli are
    # installed, there is no need to install the general php7.2 package, which
    # can sometimes install apache as a requirement.
    php7.2-fpm
    php7.2-cli

    # Common and dev packages for php
    php7.2-common
    php7.2-dev

    # Extra PHP modules that we find useful
    php-pear
    php-imagick
    php-memcache
    php-memcached
    php-ssh2
    php-xdebug
    php-yaml
    php7.2-bcmath
    php7.2-curl
    php7.2-gd
    php7.2-intl
    php7.2-mbstring
    php7.2-mysql
    php7.2-imap
    php7.2-json
    php7.2-soap
    php7.2-xml
    php7.2-zip

    # nginx is installed as the default web server
    nginx

    # memcached is made available for object caching
    memcached

    # other packages that come in handy
    imagemagick
    subversion
    git
    git-lfs
    git-svn
    zip
    unzip
    ngrep
    curl
    make
    vim
    colordiff
    python-pip
    lftp

    # ntp service to keep clock current
    ntp
    ntpdate

    # Required for i18n tools
    gettext

    # Required for Webgrind
    graphviz

    # dos2unix
    # Allows conversion of DOS style line endings to something less troublesome
    # in Linux.
    dos2unix

    # nodejs for use by grunt
    g++
    nodejs
  )

  # To avoid issues on provisioning and failed apt installation
  dpkg --configure -a
  if ! apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --fix-broken ${apt_package_install_list[@]}; then
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

tools_install() {
  echo " * Running tools_install"
  # Disable xdebug before any composer provisioning.
  sh /srv/config/homebin/xdebug_off

  if [[ $(nodejs -v | sed -ne 's/[^0-9]*\(\([0-9]\.\)\{0,4\}[0-9][^.]\).*/\1/p') != '10' ]]; then
    echo " * Downgrading to Node v10."
    apt remove nodejs -y
    apt install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew install --fix-missing --fix-broken nodejs
  fi

  # npm
  #
  # Make sure we have the latest npm version and the update checker module
  echo " * Installing/updating npm..."
  npm_config_loglevel=error npm install -g npm
  echo " * Installing/updating npm-check-updates..."
  npm_config_loglevel=error npm install -g npm-check-updates

  echo " * Making sure the composer cache is not owned by root"
  mkdir -p /usr/local/src/composer
  mkdir -p /usr/local/src/composer/cache
  chown -R vagrant:www-data /usr/local/src/composer
  chown -R vagrant:www-data /usr/local/bin

  # COMPOSER

  export COMPOSER_ALLOW_SUPERUSER=1
  export COMPOSER_NO_INTERACTION=1

  echo " * Checking for Composer"
  exists_composer="$(which composer)"
  if [[ "/usr/local/bin/composer" != "${exists_composer}" ]]; then
    echo " * Installing Composer..."
    curl -sS "https://getcomposer.org/installer" | php
    chmod +x "composer.phar"
    mv "composer.phar" "/usr/local/bin/composer"
  fi

  github_token=$(shyaml get-value general.github_token 2> /dev/null < "${VVV_CONFIG}")
  if [[ ! -z $github_token ]]; then
    rm /srv/provision/github.token
    echo "$github_token" >> /srv/provision/github.token
    echo " * A personal GitHub token was found, configuring composer"
    ghtoken=$(cat /srv/provision/github.token)
    noroot composer config --global github-oauth.github.com "$ghtoken"
    echo " * Your personal GitHub token is set for Composer."
  fi

  # Update both Composer and any global packages. Updates to Composer are direct from
  # the master branch on its GitHub repository.
  if [[ -n "$(noroot composer --version --no-ansi | grep 'Composer version')" ]]; then
    echo " * Updating Composer..."
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global config bin-dir /usr/local/bin
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi self-update --stable --no-progress --no-interaction
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global require --prefer-dist --no-update --no-progress --no-interaction phpunit/phpunit:^7.5
    COMPOSER_HOME=/usr/local/src/composer noroot composer --no-ansi global update --no-progress --no-interaction
  fi


  function install_grunt() {
    echo " * Installing Grunt CLI"
    npm_config_loglevel=error npm install -g grunt grunt-cli --no-optional
    npm_config_loglevel=error hack_avoid_gyp_errors & npm install -g grunt-sass --no-optional; touch /tmp/stop_gyp_hack
    npm_config_loglevel=error npm install -g grunt-cssjanus --no-optional
    npm_config_loglevel=error npm install -g grunt-rtlcss --no-optional
    echo " * Installed Grunt CLI"
  }

  function update_grunt() {
    echo " * Updating Grunt CLI"
    npm_config_loglevel=error npm update -g grunt grunt-cli --no-optional
    npm_config_loglevel=error hack_avoid_gyp_errors & npm update -g grunt-sass; touch /tmp/stop_gyp_hack
    npm_config_loglevel=error npm update -g grunt-cssjanus --no-optional
    npm_config_loglevel=error npm update -g grunt-rtlcss --no-optional
    echo " * Updated Grunt CLI"
  }
  # Grunt
  #
  # Install or Update Grunt based on current state.  Updates are direct
  # from NPM
  function hack_avoid_gyp_errors() {
    # Without this, we get a bunch of errors when installing `grunt-sass`:
    # > node scripts/install.js
    # Unable to save binary /usr/lib/node_modules/.../node-sass/.../linux-x64-48 :
    # { Error: EACCES: permission denied, mkdir '/usr/lib/node_modules/... }
    # Then, node-gyp generates tons of errors like:
    # WARN EACCES user "root" does not have permission to access the dev dir
    # "/usr/lib/node_modules/grunt-sass/node_modules/node-sass/.node-gyp/6.11.2"
    # TODO: Why do child processes of `npm` run as `nobody`?
    while [ ! -f /tmp/stop_gyp_hack ]; do
      if [ -d /usr/lib/node_modules/grunt-sass/ ]; then
        chown -R nobody:vagrant /usr/lib/node_modules/grunt-sass/
      fi
      sleep .2
    done
    rm /tmp/stop_gyp_hack
  }
  chown -R vagrant:vagrant /usr/lib/node_modules/
  if command -v grunt >/dev/null 2>&1; then
    update_grunt
  else
    install_grunt
  fi

  # Graphviz
  #
  # Set up a symlink between the Graphviz path defined in the default Webgrind
  # config and actual path.
  echo " * Adding graphviz symlink for Webgrind..."
  ln -sf "/usr/bin/dot" "/usr/local/bin/dot"

  # Shyaml
  #
  # Used for passing custom parameters to the bash provisioning scripts
  echo " * Installing Shyaml for bash provisioning.."
  sudo pip install shyaml
}

nginx_setup() {
  # Create an SSL key and certificate for HTTPS support.
  if [[ ! -e /root/.rnd ]]; then
    echo " * Generating Random Number for cert generation..."
    vvvgenrnd="$(openssl rand -out /root/.rnd -hex 256 2>&1)"
    echo "$vvvgenrnd"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.key ]]; then
    echo " * Generating Nginx server private key..."
    vvvgenrsa="$(openssl genrsa -out /etc/nginx/server-2.1.0.key 2048 2>&1)"
    echo "$vvvgenrsa"
  fi
  if [[ ! -e /etc/nginx/server-2.1.0.crt ]]; then
    echo " * Sign the certificate using the above private key..."
    vvvsigncert="$(openssl req -new -x509 \
            -key /etc/nginx/server-2.1.0.key \
            -out /etc/nginx/server-2.1.0.crt \
            -days 3650 \
            -subj /CN=*.wordpress-develop.test/CN=*.wordpress.test/CN=*.wordpress-develop.dev/CN=*.wordpress.dev/CN=*.vvv.dev/CN=*.vvv.local/CN=*.vvv.localhost/CN=*.vvv.test 2>&1)"
    echo "$vvvsigncert"
  fi

  echo " * Setup configuration files..."

  # Copy nginx configuration from local
  echo " * Copying /srv/config/nginx-config/nginx.conf           to /etc/nginx/nginx.conf"
  cp -f "/srv/config/nginx-config/nginx.conf" "/etc/nginx/nginx.conf"

  echo " * Copying /srv/config/nginx-config/nginx-wp-common.conf to /etc/nginx/nginx-wp-common.conf"
  cp -f "/srv/config/nginx-config/nginx-wp-common.conf" "/etc/nginx/nginx-wp-common.conf"

  if [[ ! -d "/etc/nginx/upstreams" ]]; then
    mkdir -p "/etc/nginx/upstreams/"
  fi
  echo " * Copying /srv/config/nginx-config/php7.2-upstream.conf to /etc/nginx/upstreams/php72.conf"
  cp -f "/srv/config/nginx-config/php7.2-upstream.conf" "/etc/nginx/upstreams/php72.conf"

  if [[ ! -d "/etc/nginx/custom-sites" ]]; then
    mkdir -p "/etc/nginx/custom-sites/"
  fi
  echo " * Rsync'ing /srv/config/nginx-config/sites/             to /etc/nginx/custom-sites"
  rsync -rvzh --delete "/srv/config/nginx-config/sites/" "/etc/nginx/custom-sites/"

  if [[ ! -d "/etc/nginx/custom-utilities" ]]; then
    mkdir -p "/etc/nginx/custom-utilities/"
  fi

  if [[ ! -d "/etc/nginx/custom-dashboard-extensions" ]]; then
    mkdir -p "/etc/nginx/custom-dashboard-extensions/"
  fi

  rm -rf /etc/nginx/custom-{dashboard-extensions,utilities}/*

  echo " * Making sure the Nginx log files and folder exist"
  mkdir -p /var/log/nginx/
  touch /var/log/nginx/error.log
  touch /var/log/nginx/access.log
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

check_mysql_root_password() {
  echo " * Checking the root user password is root"
  # Get if root has correct password and mysql_native_password as plugin
  sql=$( cat <<-SQL
      SELECT count(*) from mysql.user WHERE
      User='root' AND
      authentication_string=PASSWORD('root') AND
      plugin='mysql_native_password';
SQL
)
  root_matches=$(mysql -u root --password=root -s -N -e "${sql}")
  if [[ $? -eq 0 && $root_matches == "1" ]]; then
    # mysql connected and the SQL above matched
    echo " * The root password is the expected value"
    return 0
  fi
  # Do reset password in safemode
  echo " * The root password is not root, fixing"
  sql=$( cat <<-SQL
      ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password USING PASSWORD('root');
      FLUSH PRIVILEGES;
SQL
)
  mysql -u root -proot -e "${sql}"
  if [[ $? -eq 0 ]]; then
    echo "   - root user password should now be root"
  else
    vvv_warn "   - could not reset root password"
  fi
}

mysql_setup() {
  # If MariaDB/MySQL is installed, go through the various imports and service tasks.
  local exists_mysql

  exists_mysql="$(service mysql status)"
  if [[ "mysql: unrecognized service" == "${exists_mysql}" ]]; then
    echo -e "\n ! MySQL is not installed. No databases imported."
    return
  fi
  echo -e "\n * Setting up database configuration file links..."

  # Copy mysql configuration from local
  cp "/srv/config/mysql-config/my.cnf" "/etc/mysql/my.cnf"
  echo " * Copied /srv/config/mysql-config/my.cnf               to /etc/mysql/my.cnf"

  cp -f  "/srv/config/mysql-config/root-my.cnf" "/home/vagrant/.my.cnf"
  chmod 0644 "/home/vagrant/.my.cnf"
  echo " * Copied /srv/config/mysql-config/root-my.cnf          to /home/vagrant/.my.cnf"

  check_mysql_root_password

  # MySQL gives us an error if we restart a non running service, which
  # happens after a `vagrant halt`. Check to see if it's running before
  # deciding whether to start or restart.
  if [[ "mysql stop/waiting" == "${exists_mysql}" ]]; then
    echo " * Starting the mysql service"
    service mysql start
  else
    echo " * Restarting mysql service"
    service mysql restart
  fi

  # IMPORT SQL
  #
  # Create the databases (unique to system) that will be imported with
  # the mysqldump files located in database/backups/
  if [[ -f "/srv/database/init-custom.sql" ]]; then
    echo " * Running custom init-custom.sql under the root user..."
    mysql -u "root" -p"root" < "/srv/database/init-custom.sql"
    echo " * init-custom.sql has run"
  else
    echo -e "\n * No custom MySQL scripting found in database/init-custom.sql, skipping..."
  fi

  # Setup MySQL by importing an init file that creates necessary
  # users and databases that our vagrant setup relies on.
  mysql -u "root" -p"root" < "/srv/database/init.sql"
  echo " * Initial MySQL prep..."

  # Process each mysqldump SQL file in database/backups to import
  # an initial data set for MySQL.
  "/srv/database/import-sql.sh"
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
# Package and Tools Install
echo " "
echo " * Main packages check and install."
git_ppa_check
if ! package_install; then
  vvv_error " ! Main packages check and install failed, halting provision"
  exit 1
fi

tools_install

mysql_setup
nginx_setup
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
