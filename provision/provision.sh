# NGINX
# Configure nginx with some basic config files
sudo cp /srv/server-conf/nginx.conf /etc/nginx/nginx.conf
sudo cp /srv/server-conf/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf

# Copy custom configuration files over and restart php5-fpm
sudo cp /srv/server-conf/www.conf /etc/php5/fpm/pool.d/www.conf
sudo cp /srv/server-conf/php.ini /etc/php5/fpm/php.ini

# Make sure the services we expect to be running are running
sudo service nginx restart
sudo service php5-fpm restart
sudo service memcached restart
sudo service mysql restart

# Import any SQL files into databases based on their names
# these databases must first be created in the create-dbs.sql
# file so that they exist for the import script to do its job.
mysql -u root -pblank < /srv/server-conf/default-dbs.sql
mysql -u root -pblank < /srv/server-conf/create-dbs.sql
/srv/server-conf/db-dumps/import-sql.sh

printf "\nCopy bash aliases to home directory"
cp /srv/server-conf/bash_aliases /home/vagrant/.bash_aliases

printf "\nUse start_config_monitor to begin monitoring config files for changes"
printf "Use stop_config_monitor to stop monitoring these config files\n\n"

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway
ifconfig | grep "inet addr"

echo All set!
