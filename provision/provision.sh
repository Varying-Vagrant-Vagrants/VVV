# Install dos2unix, which allows conversion of DOS style line endings to
# something we'll have less trouble with in linux.
sudo apt-get install --force-yes -y dos2unix

# NGINX
# Configure nginx with some basic config files
sudo cp /srv/server-conf/nginx.conf /etc/nginx/nginx.conf | echo "copied nginx.conf to /etc/nginx/"
sudo cp /srv/server-conf/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf | echo "copied nginx-wp-common.conf to /etc/nginx/"

# Copy custom configuration files over and restart php5-fpm
sudo cp /srv/server-conf/www.conf /etc/php5/fpm/pool.d/www.conf | echo "copied www.conf to /etc/php5/fpm/pool.d/"
sudo cp /srv/server-conf/php.ini /etc/php5/fpm/php.ini | echo "copied php.ini to /etc/php5/fpm/"

# Make sure the services we expect to be running are running
sudo service nginx restart
sudo service php5-fpm restart
sudo service memcached restart
sudo service mysql restart

# Import any SQL files into databases based on their names
# these databases must first be created in the create-dbs.sql
# file so that they exist for the import script to do its job.
mysql -u root -pblank < /srv/server-conf/default-dbs.sql | echo "Imported default databases..."
mysql -u root -pblank < /srv/server-conf/create-dbs.sql | echo "Created additional databases..."
/srv/server-conf/db-dumps/import-sql.sh

cp /srv/server-conf/bash_aliases /home/vagrant/.bash_aliases | echo "Copied bash aliases to home directory..."

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway.
# Enter domains space delimited
DOMAINS='local.wordpress.dev'
if ! grep -q "$DOMAINS" /etc/hosts
then echo "127.0.0.1 $DOMAINS" >> /etc/hosts
fi

# Your host IP is set in Vagrantfile, but it's nice to see the interfaces anyway
ifconfig | grep "inet addr"

echo All set!
