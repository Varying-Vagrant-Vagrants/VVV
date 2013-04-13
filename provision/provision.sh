start_time=`date`
# This file is specified as the provisioning script to be used during `vagrant up`
# via the `config.vm.provision` parameter in the Vagrantfile.

# When `vagrant up` is first run, a large number of packages are installed through
# apt-get. We then create a file in the Vagrant contained file system that acts as
# a flag to indicate that these do not need to be processed again. This will persist
# through `vagrant suspend`, `vagrant halt`, `vagrant reload`, or `vagrant provision`.
# When `vagrant destroy` is run, the virtual machine's drives disappear, our flag file
# with them. This speeds the boot time up on subsequent `vagrant up` commands significantly.
if [ -f /home/vagrant/initial_provision_run ]
then
	printf "\nSkipping package installation, not initial boot...\n\n"
else
	# update all of the package references before installing anything
	printf "Running apt-get update....\n\n"
	apt-get update --force-yes -y

	# MYSQL
	#
	# We need to set the selections to automatically fill the password prompt
	# for mysql while it is being installed. The password in the following two
	# lines *is* actually set to the word 'blank' for the root user.
	echo mysql-server mysql-server/root_password password blank | sudo debconf-set-selections
	echo mysql-server mysql-server/root_password_again password blank | sudo debconf-set-selections

	# PACKAGE INSTALLATION
	#
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
		php5-memcache
		php5-imagick
		php5-xdebug
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
		subversion
		ack-grep
		git-core
		ngrep
		curl
		make
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

	# Make ack respond to its real name
	sudo ln -fs /usr/bin/ack-grep /usr/bin/ack

	# COMPOSER
	#
	# Install Composer
	if [ ! -f /usr/bin/composer ]
	then
		printf "Install Composer...\n"
		curl -sS https://getcomposer.org/installer | php
		chmod +x composer.phar
		sudo mv composer.phar /usr/local/bin/composer
	else
		printf "Update Composer...\n"
		sudo composer self-update
	fi

	# If our global composer sources don't exist, set them up
	if [ ! -d /usr/local/src/vvv-phpunit ]
	then
		printf "Install PHPUnit and Mockery...\n"
		sudo mkdir -p /usr/local/src/vvv-phpunit
		sudo cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
		sudo sh -c "cd /usr/local/src/vvv-phpunit && composer install"
	else
		printf "Update PHPUnit and Mockery...\n"
		sudo cp /srv/config/phpunit-composer.json /usr/local/src/vvv-phpunit/composer.json
		sudo sh -c "cd /usr/local/src/vvv-phpunit && composer update"
	fi
	touch /home/vagrant/initial_provision_run
fi

# SYMLINK HOST FILES
printf "\nLink Directories...\n"

# Configuration for nginx
sudo ln -sf /srv/config/nginx-config/nginx.conf /etc/nginx/nginx.conf | echo "Linked nginx.conf to /etc/nginx/"
sudo ln -sf /srv/config/nginx-config/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo "Linked nginx-wp-common.conf to /etc/nginx/"

# Configuration for php5-fpm
sudo ln -sf /srv/config/php5-fpm-config/www.conf /etc/php5/fpm/pool.d/www.conf | echo "Linked www.conf to /etc/php5/fpm/pool.d/"
sudo ln -sf /srv/config/php5-fpm-config/php.ini /etc/php5/fpm/php.ini | echo "Linked php.ini to /etc/php5/fpm/"
sudo ln -sf /srv/config/php5-fpm-config/php.xdebug.ini /etc/php5/fpm/php.xdebug.ini | echo "Linked php.xdebug.ini to /etc/php5/fpm/"

# Configuration for mysql
sudo cp /srv/config/mysql-config/my.cnf /etc/mysql/my.cnf | echo "Linked my.cnf to /etc/mysql/"

# Custom bash aliases to include with .bashrc
sudo ln -sf /srv/config/bash_aliases /home/vagrant/.bash_aliases | echo "Linked bash aliases to home directory..."

# Custom vim configuration via .vimrc
sudo ln -sf /srv/config/vimrc /home/vagrant/.vimrc | echo "Linked vim configuration to home directory..."

# RESTART SERVICES
#
# Make sure the services we expect to be running are running.
printf "\nRestart services...\n"
printf "\nservice nginx restart\n"
sudo service nginx restart
printf "\nservice php5-fpm restart\n"
sudo service php5-fpm restart
printf "\nservice memcached restart\n"
sudo service memcached restart

# mysql gives us an error if we restart a non running service, which
# happens after a `vagrant halt`. Check to see if it's running before
# deciding whether to start or restart.
exists_mysql=`service mysql status`
if [ "mysql stop/waiting" == "$exists_mysql" ]
then
	printf "\nservice mysql start"
	sudo service mysql start
else
	printf "\nservice mysql restart"
	sudo service mysql restart
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
if [ ! -f /usr/bin/wp ]
then
	printf "\nDownloading wp-cli.....http://wp-cli.org\n"
	curl --silent http://wp-cli.org/packages/phar/wp-cli.phar > /usr/bin/wp
	chmod +x /usr/bin/wp
else
	printf "\nSkip wp-cli installation, already available\n"
fi

# Install and configure the latest stable version of WordPress
if [ ! -d /srv/www/wordpress-default ]
then
	printf "Downloading WordPress.....http://wordpress.org\n"
	wp core --quiet download --path=/srv/www/wordpress-default
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
	cd /srv/www/wordpress-trunk
	printf "Configuring WordPress trunk...\n"
	wp core config --dbname=wordpress_trunk --dbuser=wp --dbpass=wp --quiet
	wp core install --url=local.wordpress-trunk.dev --quiet --title="Local WordPress Trunk Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
else
	printf "Updating WordPress trunk...\n"
	cd /srv/www/wordpress-trunk
	svn up --ignore-externals
fi

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway.
# Enter domains space delimited
DOMAINS='local.wordpress.dev local.wordpress-trunk.dev'
if ! grep -q "$DOMAINS" /etc/hosts
then echo "127.0.0.1 $DOMAINS" >> /etc/hosts
fi

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway
ifconfig | grep "inet addr"
echo $start_time
date
echo All set!
