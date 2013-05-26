# provision.sh
#
# This file is loaded by Vagrant as the primary provisioning script whenever the
# commands `vagrant up`, `vagrant provision`, or `vagrant reload` are used. It
# provides all of the default packages and configurations included with
# Varying Vagrant Vagrants.

# We calculate the duration of provisioning at the end of this script, hence start_time
start_seconds=`date +%s`

# Setup the default sources.list provided by Ubuntu
cat /srv/config/apt-source-default.list > /etc/apt/sources.list
# Add any custom package sources to help install more current software
cat /srv/config/apt-source-append.list >> /etc/apt/sources.list

# PACKAGE INSTALLATION
#
# Build a bash array to pass all of the packages we want to install to
# a single apt-get command. This avoids having to do all the leg work
# each time a package is set to install. It also allows us to easily comment
# out or add single packages. We set the array as empty to begin with so
# that we can append individual packages to it as required.
apt_package_list=()

echo "Check for packages to install..."

# PHP5
#
# Our base packages for php5. As long as php5-fpm and php5-cli are
# installed, there is no need to install the general php5 package, which
# can sometimes install apache as a requirement.
if dpkg -s php5-fpm | grep -q 'Status: install ok installed';
	then echo "php5-fpm already installed"
	else apt_package_list+=('php5-fpm')
fi

if dpkg -s php5-cli | grep -q 'Status: install ok installed';
	then echo "php5-cli already installed"
	else apt_package_list+=('php5-cli')
fi

# Common and dev packages for php
if dpkg -s php5-common | grep -q 'Status: install ok installed';
	then echo "php5-common already installed"
	else apt_package_list+=('php5-common')
fi

if dpkg -s php5-dev | grep -q 'Status: install ok installed';
	then echo "php5-dev already installed"
	else apt_package_list+=('php5-dev')
fi

# Extra PHP modules that we find useful
if dpkg -s php5-imap | grep -q 'Status: install ok installed';
	then echo "php5-imap already installed"
	else apt_package_list+=('php5-imap')
fi

if dpkg -s php5-memcache | grep -q 'Status: install ok installed';
	then echo "php5-memcache already installed"
	else apt_package_list+=('php5-memcache')
fi

if dpkg -s php5-imagick | grep -q 'Status: install ok installed';
	then echo "php5-imagick already installed"
	else apt_package_list+=('php5-imagick')
fi

if dpkg -s php5-xdebug | grep -q 'Status: install ok installed';
	then echo "php5-xdebug already installed"
	else apt_package_list+=('php5-xdebug')
fi

if dpkg -s php5-mcrypt | grep -q 'Status: install ok installed';
	then echo "php5-mcrypt already installed"
	else apt_package_list+=('php5-mcrypt')
fi

if dpkg -s php5-mysql | grep -q 'Status: install ok installed';
then
	echo "php5-mysql already installed"
else
	apt_package_list+=('php5-mysql')
fi

if dpkg -s php5-curl | grep -q 'Status: install ok installed';
	then echo "php5-curl already installed"
	else apt_package_list+=('php5-curl')
fi

if dpkg -s php-pear | grep -q 'Status: install ok installed';
	then echo "php-pear already installed"
	else apt_package_list+=('php-pear')
fi

if dpkg -s php5-gd | grep -q 'Status: install ok installed';
	then echo "php5-gd already installed"
	else apt_package_list+=('php5-gd')
fi

if dpkg -s php-apc | grep -q 'Status: install ok installed';
	then echo "php-apc already installed"
	else apt_package_list+=('php-apc')
fi

# nginx
if dpkg -s nginx | grep -q 'Status: install ok installed';
	then echo "nginx already installed"
	else apt_package_list+=('nginx')
fi

# mysql
if dpkg -s mysql-server | grep -q 'Status: install ok installed';
then
	echo "mysql-server already installed"
else 
	# We need to set the selections to automatically fill the password prompt
	# for mysql while it is being installed. The password in the following two
	# lines *is* actually set to the word 'blank' for the root user.
	echo mysql-server mysql-server/root_password password blank | debconf-set-selections
	echo mysql-server mysql-server/root_password_again password blank | debconf-set-selections
	apt_package_list+=('mysql-server')
fi

# memcached
if dpkg -s memcached | grep -q 'Status: install ok installed';
	then echo "memcached already installed"
	else apt_package_list+=('memcached')
fi

# imagemagick
if dpkg -s imagemagick | grep -q 'Status: install ok installed';
	then echo "imagemagic already installed" 
	else apt_package_list+=('imagemagick')
fi

# subversion
if dpkg -s subversion | grep -q 'Status: install ok installed';
	then echo "subversion already installed"
	else apt_package_list+=('subversion')
fi

# ack-grep / ack
if dpkg -s ack-grep | grep -q 'Status: install ok installed';
	then echo "ack-grep already installed"
	else apt_package_list+=('ack-grep')
fi

# git
if dpkg -s git-core | grep -q 'Status: install ok installed';
	then echo "git-core already installed"
	else apt_package_list+=('git-core')
fi

# unzip
if dpkg -s unzip | grep -q 'Status: install ok installed';
	then echo "unzip already installed"
	else apt_package_list+=('unzip')
fi

# ngrep
if dpkg -s ngrep | grep -q 'Status: install ok installed';
	then echo "ngrep already installed"
	else apt_package_list+=('ngrep')
fi

# curl
if dpkg -s curl | grep -q 'Status: install ok installed';
	then echo "curl already installed"
	else apt_package_list+=('curl')
fi

# make
if dpkg -s make | grep -q 'Status: install ok installed';
	then echo "make already installed"
	else apt_package_list+=('make')
fi

# vim
if dpkg -s vim | grep -q 'Status: install ok installed';
	then echo "vim already installed"
	else apt_package_list+=('vim')
fi

# dos2unix
# allows conversion of DOS style line endings to something we'll have
# less trouble with in linux.
if dpkg -s dos2unix | grep -q 'Status: install ok installed';
	then echo "dos2unix already installed"
	else apt_package_list+=('dos2unix')
fi

# If there are any packages to be installed in the apt_package_list array,
# then we'll run `apt-get update` and then `apt-get install` to proceed.
if [ ${#apt_package_list[@]} = 0 ];
then 
	printf "No packages to install.\n\n"
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

	# update all of the package references before installing anything
	printf "Running apt-get update....\n"
	apt-get update --force-yes -y

	# install required packages
	printf "Installing apt-get packages...\n"
	apt-get install --force-yes -y ${apt_package_list[@]}

	# Clean up apt caches
	apt-get clean			
fi

# Make ack respond to its real name
ln -fs /usr/bin/ack-grep /usr/bin/ack

# COMPOSER
#
# Install or Update Composer based on expected hash from repository
if composer --version | grep -q 'Composer version e4b48d39d';
then
	printf "Composer already installed\n"
elif composer --version | grep -q 'Composer version';
then
	printf "Updating Composer...\n"
	composer self-update
else
	printf "Installing Composer...\n"
	curl -sS https://getcomposer.org/installer | php
	chmod +x composer.phar
	mv composer.phar /usr/local/bin/composer
fi

if [ ! -d /usr/local/src/vvv-phpunit ]
then
	printf "Installing PHPUnit, Hamcrest and Mockery...\n"
	mkdir -p /usr/local/src/vvv-phpunit
	cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
	sh -c "cd /usr/local/src/vvv-phpunit && composer install"
else
	cd /usr/local/src/vvv-phpunit
	if composer show -i | grep -q 'mockery'; then echo 'Mockery installed';else vvvphpunit_update=1;fi
	if composer show -i | grep -q 'phpunit'; then echo 'PHPUnit installed'; else vvvphpunit_update=1;fi
	if composer show -i | grep -q 'hamcrest'; then echo 'Hamcrest installed'; else vvvphpunit_update=1;fi
	cd ~/
fi

if [ "$vvvphpunit_update" = 1 ]
then
	printf "Update PHPUnit, Hamcrest and Mockery...\n"
	cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
	sh -c "cd /usr/local/src/vvv-phpunit && composer update"
fi

# SYMLINK HOST FILES
printf "\nLink Directories...\n"

# Configuration for nginx
ln -sf /srv/config/nginx-config/nginx.conf /etc/nginx/nginx.conf | echo "Linked nginx.conf to /etc/nginx/"
ln -sf /srv/config/nginx-config/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo "Linked nginx-wp-common.conf to /etc/nginx/"

# Configuration for php5-fpm
ln -sf /srv/config/php5-fpm-config/www.conf /etc/php5/fpm/pool.d/www.conf | echo "Linked www.conf to /etc/php5/fpm/pool.d/"

# Provide additional directives for PHP in a custom ini file
ln -sf /srv/config/php5-fpm-config/php-custom.ini /etc/php5/fpm/conf.d/php-custom.ini | echo "Linked php-custom.ini to /etc/php5/fpm/conf.d/php-custom.ini"

# Configuration for Xdebug - Mod disabled by default
php5dismod xdebug
ln -sf /srv/config/php5-fpm-config/xdebug.ini /etc/php5/fpm/conf.d/xdebug.ini | echo "Linked xdebug.ini to /etc/php5/fpm/conf.d/xdebug.ini"

# Configuration for APC
ln -sf /srv/config/php5-fpm-config/apc.ini /etc/php5/fpm/conf.d/apc.ini | echo "Linked apc.ini to /etc/php5/fpm/conf.d/"

# Configuration for mysql
cp /srv/config/mysql-config/my.cnf /etc/mysql/my.cnf | echo "Linked my.cnf to /etc/mysql/"

# Configuration for memcached
ln -sf /srv/config/memcached-config/memcached.conf /etc/memcached.conf | echo "Linked memcached.conf to /etc/"

# Custom bash_profile for our vagrant user
ln -sf /srv/config/bash_profile /home/vagrant/.bash_profile | echo "Linked .bash_profile to vagrant user's home directory..."

# Custom bash_aliases included by vagrant user's .bashrc
ln -sf /srv/config/bash_aliases /home/vagrant/.bash_aliases | echo "Linked .bash_aliases to vagrant user's home directory..."

# Custom vim configuration via .vimrc
ln -sf /srv/config/vimrc /home/vagrant/.vimrc | echo "Linked vim configuration to home directory..."

# RESTART SERVICES
#
# Make sure the services we expect to be running are running.
printf "\nRestart services...\n"
printf "service nginx restart\n"
service nginx restart
printf "service php5-fpm restart\n"
service php5-fpm restart
printf "service memcached restart\n"
service memcached restart

# mysql gives us an error if we restart a non running service, which
# happens after a `vagrant halt`. Check to see if it's running before
# deciding whether to start or restart.
exists_mysql=`service mysql status`
if [ "mysql stop/waiting" == "$exists_mysql" ]
then
	printf "service mysql start"
	service mysql start
else
	printf "service mysql restart"
	service mysql restart
fi

# IMPORT SQL
#
# Create the databases (unique to system) that will be imported with
# the mysqldump files located in database/backups/
if [ -f /srv/database/init-custom.sql ]
then
	mysql -u root -pblank < /srv/database/init-custom.sql | printf "\nInitial custom mysql scripting...\n"
else
	printf "\nNo custom mysql scripting found in database/init-custom.sql, skipping...\n"
fi

# Setup mysql by importing an init file that creates necessary
# users and databases that our vagrant setup relies on.
mysql -u root -pblank < /srv/database/init.sql | echo "Initial mysql prep...."

# Process each mysqldump SQL file in database/backups to import 
# an initial data set for mysql.
/srv/database/import-sql.sh

# WP-CLI Install
if [ ! -d /srv/www/wp-cli ]
then
	printf "\nDownloading wp-cli.....http://wp-cli.org\n"
	git clone git://github.com/wp-cli/wp-cli.git /srv/www/wp-cli
	cd /srv/www/wp-cli
	composer install
else
	printf "\nSkip wp-cli installation, already available\n"
fi
# Link wp to the /usr/local/bin directory
ln -sf /srv/www/wp-cli/bin/wp /usr/local/bin/wp

# Install and configure the latest stable version of WordPress
if [ ! -d /srv/www/wordpress-default ]
then
	printf "Downloading WordPress.....http://wordpress.org\n"
	cd /srv/www/
	curl -O http://wordpress.org/latest.tar.gz
	tar -xvf latest.tar.gz
	mv wordpress wordpress-default
	rm latest.tar.gz
	cp /srv/config/wordpress-config/wp-config-sample.php /srv/www/wordpress-default
	cd /srv/www/wordpress-default
	printf "Configuring WordPress...\n"
	wp core config --dbname=wordpress_default --dbuser=wp --dbpass=wp --quiet
	wp core install --url=local.wordpress.dev --quiet --title="Local WordPress Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
else
	printf "Skip WordPress installation, already available\n"
fi

# Checkout, install and configure WordPress trunk
if [ ! -d /srv/www/wordpress-trunk ]
then
	printf "Checking out WordPress trunk....http://core.svn.wordpress.org/trunk\n"
	svn checkout http://core.svn.wordpress.org/trunk/ /srv/www/wordpress-trunk
	cp /srv/config/wordpress-config/wp-config-sample.php /srv/www/wordpress-trunk
	cd /srv/www/wordpress-trunk
	printf "Configuring WordPress trunk...\n"
	wp core config --dbname=wordpress_trunk --dbuser=wp --dbpass=wp --quiet
	wp core install --url=local.wordpress-trunk.dev --quiet --title="Local WordPress Trunk Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
else
	printf "Updating WordPress trunk...\n"
	cd /srv/www/wordpress-trunk
	svn up --ignore-externals
fi

# Checkout and configure the WordPress unit tests
if [ ! -f /home/vagrant/flags/disable_wp_tests ]
then
	if [ ! -d /srv/www/wordpress-unit-tests ]
	then
		printf "Downloading WordPress Unit Tests.....https://unit-tests.svn.wordpress.org\n"
		# Must be in a WP directory to run wp
		cd /srv/www/wordpress-trunk
		wp core init-tests /srv/www/wordpress-unit-tests --dbname=wordpress_unit_tests --dbuser=wp --dbpass=wp
	else
		printf "Updating WordPress unit tests...\n"	
		cd /srv/www/wordpress-unit-tests
		svn up --ignore-externals
	fi
fi

# Add any custom domains to the virtual machine's hosts file so that it
# is self aware. Enter domains space delimited as shown with the default.
DOMAINS='local.wordpress.dev local.wordpress-trunk.dev'
if ! grep -q "$DOMAINS" /etc/hosts
then echo "127.0.0.1 $DOMAINS" >> /etc/hosts
fi

end_seconds=`date +%s`
echo -----------------------------
echo Provisioning complete in `expr $end_seconds - $start_seconds` seconds
echo For further setup instructions, visit http://192.168.50.4
