# NGINX
# Configure nginx with some basic config files
sudo cp /srv/server-conf/nginx.conf /etc/nginx/nginx.conf
sudo cp /srv/server-conf/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf
sudo /etc/init.d/nginx restart

# When installing PECL Memcache below, we run into a phpize error
# if the php5-dev package is not available.
# @todo Include php5-dev and pecl memcache in package
sudo apt-get install --force-yes -y php5-dev

# MEMCACHED
# Install the PECL Memcache extension.
#
# We have to enter yes once. If this install changes, this will no longer work
# Might consider installing yum package mamager and using yum to install to get
# around this requiremnt. Without this, the script stalls upon completion.
printf "yes\n" | pecl install memcache

# Copy custom configuration files over and restart php5-fpm
sudo cp /srv/server-conf/www.conf /etc/php5/fpm/pool.d/www.conf
sudo cp /srv/server-conf/php.ini /etc/php5/fpm/php.ini
sudo /etc/init.d/php5-fpm restart

# Import any SQL files into databases based on their names
# these databases must first be created in the create-dbs.sql
# file so that they exist for the import script to do its job.
mysql -u root -pblank < /srv/server-conf/create-dbs.sql
/srv/server-conf/db-dumps/import-sql.sh

# grab the IP for configuring host entries on your local machine
ifconfig

echo All set!
