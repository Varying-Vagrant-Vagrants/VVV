# provision.sh
#
# This file is specified in Vagrantfile and is loaded by Vagrant as the primary
# provisioning script whenever the commands `vagrant up`, `vagrant provision`,
# or `vagrant reload` are used. It provides all of the default packages and
# configurations included with Varying Vagrant Vagrants.

# By storing the date now, we can calculate the duration of provisioning at the
# end of this script.
start_seconds=`date +%s`

# Capture a basic ping result to Google's primary DNS server to determine if
# outside access is available to us. If this does not reply after 2 attempts,
# we try one of Level3's DNS servers as well. If neither of these IPs replies to
# a ping, then we'll skip a few things further in provisioning rather than
# creating a bunch of errors.
ping_result=`ping -c 2 8.8.4.4 2>&1`
if [[ $ping_result != *bytes?from* ]]
then
	ping_result=`ping -c 2 4.2.2.2 2>&1`
fi

# PACKAGE INSTALLATION
#
# Build a bash array to pass all of the packages we want to install to a single
# apt-get command. This avoids doing all the leg work each time a package is
# set to install. It also allows us to easily comment out or add single
# packages. We set the array as empty to begin with so that we can append
# individual packages to it as required.
apt_package_install_list=()

# Start with a bash array containing all packages we want to install in the
# virtual machine. We'll then loop through each of these and check individual
# status before adding them to the apt_package_install_list array.
apt_package_check_list=(

	# PHP5
	#
	# Our base packages for php5. As long as php5-fpm and php5-cli are
	# installed, there is no need to install the general php5 package, which
	# can sometimes install apache as a requirement.
	php5-fpm
	php5-cli

	# Common and dev packages for php
	php5-common
	php5-dev

	# Extra PHP modules that we find useful
	php5-memcache
	php5-imagick
	php5-xdebug
	php5-mcrypt
	php5-mysql
	php5-imap
	php5-curl
	php-pear
	php5-gd
	php-apc

	# nginx is installed as the default web server
	nginx

	# memcached is made available for object caching
	memcached

	# mysql is the default database
	mysql-server

	# other packages that come in handy
	imagemagick
	subversion
	git-core
	unzip
	ngrep
	curl
	make
	vim
	colordiff
	postfix

	# Req'd for i18n tools
	gettext

	# Req'd for Webgrind
	graphviz

	# dos2unix
	# Allows conversion of DOS style line endings to something we'll have less
	# trouble with in Linux.
	dos2unix

	# nodejs for use by grunt
	g++
	nodejs

)

echo "Check for apt packages to install..."

# Loop through each of our packages that should be installed on the system. If
# not yet installed, it should be added to the array of packages to install.
for pkg in "${apt_package_check_list[@]}"
do
	package_version=`dpkg -s $pkg 2>&1 | grep 'Version:' | cut -d " " -f 2`
	if [[ $package_version != "" ]]
	then
		space_count=`expr 20 - "${#pkg}"` #11
		pack_space_count=`expr 30 - "${#package_version}"`
		real_space=`expr ${space_count} + ${pack_space_count} + ${#package_version}`
		printf " * $pkg %${real_space}.${#package_version}s ${package_version}\n"
	else
		echo " *" $pkg [not installed]
		apt_package_install_list+=($pkg)
	fi
done

# MySQL
#
# Use debconf-set-selections to specify the default password for the root MySQL
# account. This runs on every provision, even if MySQL has been installed. If
# MySQL is already installed, it will not affect anything. 
echo mysql-server mysql-server/root_password password root | debconf-set-selections
echo mysql-server mysql-server/root_password_again password root | debconf-set-selections

# Postfix
#
# Use debconf-set-selections to specify the selections in the postfix setup. Set
# up as an 'Internet Site' with the host name 'vvv'. Note that if your current
# Internet connection does not allow communication over port 25, you will not be
# able to send mail, even with postfix installed.
echo postfix postfix/main_mailer_type select Internet Site | debconf-set-selections
echo postfix postfix/mailname string vvv | debconf-set-selections

# Provide our custom apt sources before running `apt-get update`
ln -sf /srv/config/apt-source-append.list /etc/apt/sources.list.d/vvv-sources.list | echo "Linked custom apt sources"

if [[ $ping_result == *bytes?from* ]]
then
	# If there are any packages to be installed in the apt_package_list array,
	# then we'll run `apt-get update` and then `apt-get install` to proceed.
	if [ ${#apt_package_install_list[@]} = 0 ];
	then
		echo -e "No apt packages to install.\n"
	else
		# Before running `apt-get update`, we should add the public keys for
		# the packages that we are installing from non standard sources via
		# our appended apt source.list

		# Nginx.org nginx key ABF5BD827BD9BF62
		gpg -q --keyserver keyserver.ubuntu.com --recv-key ABF5BD827BD9BF62
		gpg -q -a --export ABF5BD827BD9BF62 | apt-key add -

		# Launchpad Subversion key EAA903E3A2F4C039
		gpg -q --keyserver keyserver.ubuntu.com --recv-key EAA903E3A2F4C039
		gpg -q -a --export EAA903E3A2F4C039 | apt-key add -

		# Launchpad PHP key 4F4EA0AAE5267A6C
		gpg -q --keyserver keyserver.ubuntu.com --recv-key 4F4EA0AAE5267A6C
		gpg -q -a --export 4F4EA0AAE5267A6C | apt-key add -

		# Launchpad git key A1715D88E1DF1F24
		gpg -q --keyserver keyserver.ubuntu.com --recv-key A1715D88E1DF1F24
		gpg -q -a --export A1715D88E1DF1F24 | apt-key add -

		# Launchpad nodejs key C7917B12
		gpg -q --keyserver keyserver.ubuntu.com --recv-key C7917B12
		gpg -q -a --export  C7917B12  | apt-key add -

		# update all of the package references before installing anything
		echo "Running apt-get update..."
		apt-get update --assume-yes

		# install required packages
		echo "Installing apt-get packages..."
		apt-get install --assume-yes ${apt_package_install_list[@]}

		# Clean up apt caches
		apt-get clean
	fi

	# ack-grep
	#
	# Install ack-rep directory from the version hosted at beyondgrep.com as the
	# PPAs for Ubuntu Precise are not available yet.
	if [ -f /usr/bin/ack ]
	then
		echo "ack-grep already installed"
	else
		echo "Installing ack-grep as ack"
		curl -s http://beyondgrep.com/ack-2.04-single-file > /usr/bin/ack && chmod +x /usr/bin/ack
	fi

	# COMPOSER
	#
	# Install or Update Composer based on current state. Updates are direct from
	# master branch on GitHub repository.
	if composer --version | grep -q 'Composer version';
	then
		echo "Updating Composer..."
		composer self-update
	else
		echo "Installing Composer..."
		curl -sS https://getcomposer.org/installer | php
		chmod +x composer.phar
		mv composer.phar /usr/local/bin/composer
	fi

	# PHPUnit
	#
	# Check that PHPUnit, Mockery, and Hamcrest are all successfully installed. If
	# not, then Composer should be given another shot at it. Versions for these
	# packages are controlled in the `/srv/config/phpunit-composer.json` file.
	if [ ! -d /usr/local/src/vvv-phpunit ]
	then
		echo "Installing PHPUnit, Hamcrest and Mockery..."
		mkdir -p /usr/local/src/vvv-phpunit
		cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
		sh -c "cd /usr/local/src/vvv-phpunit && composer install"
	else
		cd /usr/local/src/vvv-phpunit
		if composer show -i | grep -q 'mockery' ; then echo "Mockery installed" ; else vvvphpunit_update=1; fi
		if composer show -i | grep -q 'phpunit' ; then echo "PHPUnit installed" ; else vvvphpunit_update=1; fi
		if composer show -i | grep -q 'hamcrest'; then echo "Hamcrest installed"; else vvvphpunit_update=1; fi
		cd ~/
	fi

	if [ "$vvvphpunit_update" = 1 ]
	then
		echo "Update PHPUnit, Hamcrest and Mockery..."
		cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
		sh -c "cd /usr/local/src/vvv-phpunit && composer update"
	fi

	# Grunt
	#
	# Install or Update Grunt based on gurrent state.  Updates are direct
	# from NPM
	if grunt --version ;
	then
		echo "Updating Grunt CLI"
		npm update -g grunt-cli
	else
		echo "Installing Grunt CLI"
		npm install -g grunt-cli
	fi

else
	echo -e "\nNo network connection available, skipping package installation"
fi

# Configuration for nginx
if [ ! -e /etc/nginx/server.key ]; then
	echo "Generate Nginx server private key..."
	vvvgenrsa=`openssl genrsa -out /etc/nginx/server.key 2048 2>&1`
	echo $vvvgenrsa
fi
if [ ! -e /etc/nginx/server.csr ]; then
	echo "Generate Certificate Signing Request (CSR)..."
	openssl req -new -batch -key /etc/nginx/server.key -out /etc/nginx/server.csr
fi
if [ ! -e /etc/nginx/server.crt ]; then
	echo "Sign the certificate using the above private key and CSR..."
	vvvsigncert=`openssl x509 -req -days 365 -in /etc/nginx/server.csr -signkey /etc/nginx/server.key -out /etc/nginx/server.crt 2>&1`
	echo $vvvsigncert
fi

# SYMLINK HOST FILES
echo -e "\nSetup configuration file links..."

ln -sf /srv/config/nginx-config/nginx.conf /etc/nginx/nginx.conf | echo " * /srv/config/nginx-config/nginx.conf -> /etc/nginx/nginx.conf"
ln -sf /srv/config/nginx-config/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo " * /srv/config/nginx-config/nginx-wp-common.conf -> /etc/nginx/nginx-wp-common.conf"

# Configuration for php5-fpm
ln -sf /srv/config/php5-fpm-config/www.conf /etc/php5/fpm/pool.d/www.conf | echo " * /srv/config/php5-fpm-config/www.conf -> /etc/php5/fpm/pool.d/www.conf"

# Provide additional directives for PHP in a custom ini file
ln -sf /srv/config/php5-fpm-config/php-custom.ini /etc/php5/fpm/conf.d/php-custom.ini | echo " * /srv/config/php5-fpm-config/php-custom.ini -> /etc/php5/fpm/conf.d/php-custom.ini"

# Configuration for Xdebug
ln -sf /srv/config/php5-fpm-config/xdebug.ini /etc/php5/fpm/conf.d/xdebug.ini | echo " * /srv/config/php5-fpm-config/xdebug.ini -> /etc/php5/fpm/conf.d/xdebug.ini"

# Configuration for APC
ln -sf /srv/config/php5-fpm-config/apc.ini /etc/php5/fpm/conf.d/apc.ini | echo " * /srv/config/php5-fpm-config/apc.ini -> /etc/php5/fpm/conf.d/apc.ini"

# Configuration for mysql
cp /srv/config/mysql-config/my.cnf /etc/mysql/my.cnf | echo " * /srv/config/mysql-config/my.cnf -> /etc/mysql/my.cnf"

# Configuration for memcached
ln -sf /srv/config/memcached-config/memcached.conf /etc/memcached.conf | echo " * /srv/config/memcached-config/memcached.conf -> /etc/memcached.conf"

# Custom bash_profile for our vagrant user
ln -sf /srv/config/bash_profile /home/vagrant/.bash_profile | echo " * /srv/config/bash_profile -> /home/vagrant/.bash_profile"

# Custom bash_aliases included by vagrant user's .bashrc
ln -sf /srv/config/bash_aliases /home/vagrant/.bash_aliases | echo " * /srv/config/bash_aleases -> /home/vagrant/.bash_aliases"

# Custom home bin directory
ln -nsf /srv/config/homebin /home/vagrant/bin | echo " * /srv/config/homebin -> /home/vagrant/bin"

# Custom vim configuration via .vimrc
ln -sf /srv/config/vimrc /home/vagrant/.vimrc | echo " * /srv/config/vimrc -> /home/vagrant/.vimrc"

# Capture the current IP address of the virtual machine into a variable that
# can be used when necessary throughout provisioning.
vvv_ip=`ifconfig eth1 | ack "inet addr" | cut -d ":" -f 2 | cut -d " " -f 1`

# RESTART SERVICES
#
# Make sure the services we expect to be running are running.
echo -e "\nRestart services..."
service nginx restart
service memcached restart

# Disable PHP Xdebug module by default
php5dismod xdebug
service php5-fpm restart

# MySQL gives us an error if we restart a non running service, which
# happens after a `vagrant halt`. Check to see if it's running before
# deciding whether to start or restart.
exists_mysql=`service mysql status`
if [ "mysql stop/waiting" == "$exists_mysql" ]
then
	echo "service mysql start"
	service mysql start
else
	echo "service mysql restart"
	service mysql restart
fi

# IMPORT SQL
#
# Create the databases (unique to system) that will be imported with
# the mysqldump files located in database/backups/
if [ -f /srv/database/init-custom.sql ]
then
	mysql -u root -proot < /srv/database/init-custom.sql | echo -e "\nInitial custom MySQL scripting..."
else
	echo -e "\nNo custom MySQL scripting found in database/init-custom.sql, skipping..."
fi

# Setup MySQL by importing an init file that creates necessary
# users and databases that our vagrant setup relies on.
mysql -u root -proot < /srv/database/init.sql | echo "Initial MySQL prep..."

# Process each mysqldump SQL file in database/backups to import
# an initial data set for MySQL.
/srv/database/import-sql.sh

if [[ $ping_result == *bytes?from* ]]
then
	# WP-CLI Install
	if [ ! -d /srv/www/wp-cli ]
	then
		echo -e "\nDownloading wp-cli, see http://wp-cli.org"
		git clone git://github.com/wp-cli/wp-cli.git /srv/www/wp-cli
		cd /srv/www/wp-cli
		composer install
	else
		echo -e "\nUpdating wp-cli..."
		cd /srv/www/wp-cli
		git pull --rebase origin master
		composer update
	fi
	# Link `wp` to the `/usr/local/bin` directory
	ln -sf /srv/www/wp-cli/bin/wp /usr/local/bin/wp

	# Download and extract phpMemcachedAdmin to provide a dashboard view and admin interface
	# to the goings on of memcached when running
	if [ ! -d /srv/www/default/memcached-admin ]
	then
		echo -e "\nDownloading phpMemcachedAdmin, see https://code.google.com/p/phpmemcacheadmin/"
		cd /srv/www/default
		wget -q -O phpmemcachedadmin.tar.gz 'https://phpmemcacheadmin.googlecode.com/files/phpMemcachedAdmin-1.2.2-r262.tar.gz'
		mkdir memcached-admin
		tar -xf phpmemcachedadmin.tar.gz --directory memcached-admin
		rm phpmemcachedadmin.tar.gz
	else
		echo "phpMemcachedAdmin already installed."
	fi

	# Webgrind install (for viewing callgrind/cachegrind files produced by
	# xdebug profiler)
	if [ ! -d /srv/www/default/webgrind ]
	then
		echo -e "\nDownloading webgrind, see https://github.com/jokkedk/webgrind"
		git clone git://github.com/jokkedk/webgrind.git /srv/www/default/webgrind
	else
		echo -e "\nUpdating webgrind..."
		cd /srv/www/default/webgrind
		git pull --rebase origin master
	fi

	# PHP_CodeSniffer (for running WordPress-Coding-Standards)
	if [ ! -d /srv/www/phpcs ]
	then
		echo -e "\nDownloading PHP_CodeSniffer (phpcs), see https://github.com/squizlabs/PHP_CodeSniffer"
		git clone git://github.com/squizlabs/PHP_CodeSniffer.git /srv/www/phpcs
	else
		echo -e "\nUpdating PHP_CodeSniffer (phpcs)..."
		cd /srv/www/phpcs
		git pull --rebase origin master
	fi

	# Sniffs WordPress Coding Standards
	if [ ! -d /srv/www/phpcs/CodeSniffer/Standards/WordPress ]
	then
		echo -e "\nDownloading WordPress-Coding-Standards, snifs for PHP_CodeSniffer, see https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards"
		git clone git://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git /srv/www/phpcs/CodeSniffer/Standards/WordPress
	else
		echo -e "\nUpdating PHP_CodeSniffer..."
		cd /srv/www/phpcs/CodeSniffer/Standards/WordPress
		git pull --rebase origin master
	fi

	# Install and configure the latest stable version of WordPress
	if [ ! -d /srv/www/wordpress-default ]
	then
		echo "Downloading WordPress Stable, see http://wordpress.org/"
		cd /srv/www/
		curl -O http://wordpress.org/latest.tar.gz
		tar -xvf latest.tar.gz
		mv wordpress wordpress-default
		rm latest.tar.gz
		cd /srv/www/wordpress-default
		echo "Configuring WordPress Stable..."
		wp core config --dbname=wordpress_default --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
define( 'WP_DEBUG', true );
PHP
		wp core install --url=local.wordpress.dev --quiet --title="Local WordPress Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
	else
		echo "Updating WordPress Stable..."
		cd /srv/www/wordpress-default
		wp core upgrade
	fi

	# Checkout, install and configure WordPress trunk via core.svn
	if [ ! -d /srv/www/wordpress-trunk ]
	then
		echo "Checking out WordPress trunk from core.svn, see http://core.svn.wordpress.org/trunk"
		svn checkout http://core.svn.wordpress.org/trunk/ /srv/www/wordpress-trunk
		cd /srv/www/wordpress-trunk
		echo "Configuring WordPress trunk..."
		wp core config --dbname=wordpress_trunk --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
define( 'WP_DEBUG', true );
PHP
		wp core install --url=local.wordpress-trunk.dev --quiet --title="Local WordPress Trunk Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
	else
		echo "Updating WordPress trunk..."
		cd /srv/www/wordpress-trunk
		svn up --ignore-externals
	fi

	# Checkout, install and configure WordPress trunk via develop.svn
	if [ ! -d /srv/www/wordpress-develop ]
	then
		echo "Checking out WordPress trunk from develop.svn, see http://develop.svn.wordpress.org/trunk"
		svn checkout http://develop.svn.wordpress.org/trunk/ /srv/www/wordpress-develop
		cd /srv/www/wordpress-develop/src/
		echo "Configuring WordPress develop..."
		wp core config --dbname=wordpress_develop --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
define( 'WP_DEBUG', true );
PHP
		wp core install --url=src.wordpress-develop.dev --quiet --title="WordPress Develop" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
		cp /srv/config/wordpress-config/wp-tests-config.php /srv/www/wordpress-develop/
	else
		echo "Updating WordPress trunk..."
		cd /srv/www/wordpress-develop/
		svn up
	fi

	if [ ! -d /srv/www/wordpress-develop/build ]
	then
		echo "Initializing grunt in WordPress develop..."
		cd /srv/www/wordpress-develop/
		npm install
		grunt
	fi

	# Download phpMyAdmin 4.0.5
	if [ ! -d /srv/www/default/database-admin ]
	then
		echo "Downloading phpMyAdmin 4.0.5..."
		cd /srv/www/default
		wget -q -O phpmyadmin.tar.gz 'http://sourceforge.net/projects/phpmyadmin/files/phpMyAdmin/4.0.5/phpMyAdmin-4.0.5-english.tar.gz/download'
		tar -xf phpmyadmin.tar.gz
		mv phpMyAdmin-4.0.5-english database-admin
		rm phpmyadmin.tar.gz
	else
		echo "PHPMyAdmin already installed."
	fi
else
	echo -e "\nNo network available, skipping network installations"
fi

# Add any custom domains to the virtual machine's hosts file so that it
# is self aware. Enter domains space delimited as shown with the default.
DOMAINS='vvv.dev
         local.wordpress.dev
         local.wordpress-trunk.dev
         src.wordpress-develop.dev
         build.wordpress-develop.dev'

if ! grep -q "$DOMAINS" /etc/hosts
then
	DOMAINS=$(echo $DOMAINS)
	echo "127.0.0.1 $DOMAINS" >> /etc/hosts
fi

end_seconds=`date +%s`
echo "-----------------------------"
echo "Provisioning complete in `expr $end_seconds - $start_seconds` seconds"
if [[ $ping_result == *bytes?from* ]]
then
	echo "External network connection established, packages up to date."
else
	echo "No external network available. Package installation and maintenance skipped."
fi
echo "For further setup instructions, visit http://vvv.dev"
