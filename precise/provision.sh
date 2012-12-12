# update all of the package references before installing anything
apt-get update --force-yes -y

# PHP-FPM
# kick of the install swarm with PHP5 and php-fpm
apt-get install --force-yes -y  php5 php5-fpm php-pear php5-common php5-mcrypt php5-mysql php5-cli php5-gd php-apc

# add our own default configuration for php-fpm and restart the service
sudo cp /srv/server-conf/www.conf /etc/php5/fpm/pool.d/www.conf
sudo /etc/init.d/php5-fpm restart

# NGINX
# Install and configure nginx with some basic config files
apt-get install --force-yes -y nginx
sudo cp /srv/server-conf/nginx.conf /etc/nginx/nginx.conf
sudo cp /srv/server-conf/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf
sudo /etc/init.d/nginx restart

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
# I like vi and I like vim better
apt-get install --force-yes -y vim

# Import any SQL files into databases based on their names
# these databases must first be created in the create-dbs.sql
# file so that they exist for the import script to do its job.
mysql -u root -pblank < /srv/server-conf/create-dbs.sql
/srv/server-conf/db-dumps/import-sql.sh

# grab the IP for configuring host entries on your local machine
ifconfig

echo All set!
