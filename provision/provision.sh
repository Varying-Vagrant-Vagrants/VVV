# This provisioning script runs during a 'vagrant up' command when building
# from a base box.

# update all of the package references before installing anything
apt-get update --force-yes -y

# PHP-FPM
# kick of the install swarm with PHP5 and php-fpm
apt-get install --force-yes -y  php5-fpm php-pear php5-common php5-imagick php5-mcrypt php5-mysql php5-curl php5-cli php5-gd php-apc php5-dev

# NGINX
# Install and configure nginx with some basic config files
apt-get install --force-yes -y nginx

# MYSQL
# We need to set the selections to automatically fill the password prompt
# for mysql while it is being installed. The password in the following two
# lines *is* actually set to the word 'blank' for the root user.
echo mysql-server mysql-server/root_password password blank | sudo debconf-set-selections
echo mysql-server mysql-server/root_password_again password blank | sudo debconf-set-selections

apt-get install --force-yes -y mysql-server php5-mysql

# MISC PACKAGES
apt-get install --force-yes -y git-core
apt-get install --force-yes -y curl
apt-get install --force-yes -y make
apt-get install --force-yes -y ngrep
# I like vi and I like vim better
apt-get install --force-yes -y vim

# MEMCACHED
# Use memcached and the PECL memcache extension. At some point we can move
# to the PECL memcached extension, but this better mirrors production
# environments
apt-get install --force-yes -y memcached
# We have to enter yes once. If this install changes, this will no longer work
# Might consider installing yum package mamager and using yum to install to get
# around this requiremnt. Without this, the script stalls upon completion.
printf "yes\n" | pecl install memcache

# XDebug
# Install XDebug for PHP
printf "yes\n" | pecl install xdebug

# Install dos2unix, which allows conversion of DOS style line endings to
# something we'll have less trouble with in linux.
sudo apt-get install --force-yes -y dos2unix

# NGINX
# Configure nginx with some basic config files
sudo ln -sf /srv/config/nginx-config/nginx.conf /etc/nginx/nginx.conf | echo "Linked nginx.conf to /etc/nginx/"
sudo ln -sf /srv/config/nginx-config/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo "Linked nginx-wp-common.conf to /etc/nginx/"

# Copy custom configuration files over and restart php5-fpm
sudo ln -sf /srv/config/php5-fpm-config/www.conf /etc/php5/fpm/pool.d/www.conf | echo "Linked www.conf to /etc/php5/fpm/pool.d/"
sudo ln -sf /srv/config/php5-fpm-config/php.ini /etc/php5/fpm/php.ini | echo "Linked php.ini to /etc/php5/fpm/"

# Make sure the services we expect to be running are running
sudo service nginx restart
sudo service php5-fpm restart
sudo service memcached restart
sudo service mysql restart

# Import any SQL files into databases based on their names
# these databases must first be created in the create-dbs.sql
# file so that they exist for the import script to do its job.
mysql -u root -pblank < /srv/database/default-dbs.sql | echo "Imported default databases..."
mysql -u root -pblank < /srv/database/create-dbs.sql | echo "Created additional databases..."
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
