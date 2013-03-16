# This provisioning script runs during a 'vagrant up' command when building
# from a base box.

# Check for our apt_update_run flag. If it exists, then we can skip apt-get update
# and move on. If the flag has not yet been created, then we do want to update
# first before touching the flag file and then installing packages.
cat /srv/config/apt_update_run
if [ -f /srv/config/apt_update_run ]
then
	printf "\nSkipping apt-get update, not initial boot...\n\n"
else
	# update all of the package references before installing anything
	printf "apt-get update....\n\n"
	apt-get update --force-yes -y
	touch /srv/config/apt_update_run
fi

# MYSQL
# We need to set the selections to automatically fill the password prompt
# for mysql while it is being installed. The password in the following two
# lines *is* actually set to the word 'blank' for the root user.
echo mysql-server mysql-server/root_password password blank | sudo debconf-set-selections
echo mysql-server mysql-server/root_password_again password blank | sudo debconf-set-selections

# Build a bash array to pass all of the packages we want to install to
# a single apt-get command. This avoids having to do all the leg work
# each time a package is set to install. It also allows us to easily comment
# out or add single packages.
apt_package_list=(
	# Imagemagick
	imagemagick

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

	# Extra modules that we find useful
	php5-imagick
	php5-mcrypt
	php5-mysql
	php5-curl
	php-pear
	php5-gd
	php-apc
	
	# nginx
	nginx

	# mysql
	mysql-server

	# MISC Packages
	git-core
	curl
	make
	ngrep
	vim

	# memcached
	memcached

	# Install dos2unix, which allows conversion of DOS style line endings to
	# something we'll have less trouble with in linux.
	dos2unix
)

printf "Install all apt-get packages...\n"
apt-get install --force-yes -y ${apt_package_list[@]}

# Clean up apt caches
apt-get clean

printf "\nInstall pear packages...\n"
# PHPUnit
# We need turn on auto-discovery first, otherwise the system won't know where to grab PHPUnit from
sudo pear config-set auto_discover 1
sudo pear install pear.phpunit.de/PHPUnit

printf "\nInstall pecl packages...\n"
# MEMCACHED
# Use memcached and the PECL memcache extension. At some point we can move
# to the PECL memcached extension, but this better mirrors production
# environments
#
# We have to enter yes once. If this install changes, this will no longer work
# Might consider installing yum package mamager and using yum to install to get
# around this requiremnt. Without this, the script stalls upon completion.
printf "yes\n" | pecl install memcache

# XDebug
# Install XDebug for PHP
printf "yes\n" | pecl install xdebug

printf "\nLink Directories...\n"
# NGINX
# Configure nginx with some basic config files
sudo ln -sf /srv/config/nginx-config/nginx.conf /etc/nginx/nginx.conf | echo "Linked nginx.conf to /etc/nginx/"
sudo ln -sf /srv/config/nginx-config/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo "Linked nginx-wp-common.conf to /etc/nginx/"

# Copy custom configuration files over and restart php5-fpm
sudo ln -sf /srv/config/php5-fpm-config/www.conf /etc/php5/fpm/pool.d/www.conf | echo "Linked www.conf to /etc/php5/fpm/pool.d/"
sudo ln -sf /srv/config/php5-fpm-config/php.ini /etc/php5/fpm/php.ini | echo "Linked php.ini to /etc/php5/fpm/"
sudo ln -sf /srv/config/php5-fpm-config/php.xdebug.ini /etc/php5/fpm/php.xdebug.ini | echo "Linked php.xdebug.ini to /etc/php5/fpm/"

# Copy over the mysql configuration file
sudo cp /srv/config/mysql-config/my.cnf /etc/mysql/my.cnf | echo "Linked my.cnf to /etc/mysql/"

printf "\nRestart services...\n"
# Make sure the services we expect to be running are running
sudo service nginx restart
sudo service php5-fpm restart
sudo service memcached restart
sudo service mysql restart

# Create the databases (unique to system) that will be imported with
# the mysqldump files located in database/backups/
mysql -u root -pblank < /srv/database/init-custom.sql | printf "\nInitial custom mysql scripting...\n"

# Setup mysql by importing an init file that creates necessary
# users and databases that our vagrant setup relies on.
mysql -u root -pblank < /srv/database/init.sql | echo "Initial mysql prep...."

# Process each mysqldump SQL file in database/backups to import 
# an initial data set for mysql.
/srv/database/import-sql.sh

sudo ln -sf /srv/config/bash_aliases /home/vagrant/.bash_aliases | echo "Linked bash aliases to home directory..."

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway.
# Enter domains space delimited
DOMAINS='local.wordpress.dev'
if ! grep -q "$DOMAINS" /etc/hosts
then echo "127.0.0.1 $DOMAINS" >> /etc/hosts
fi

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway
ifconfig | grep "inet addr"

echo All set!
