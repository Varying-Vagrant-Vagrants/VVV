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
mysql -u root -pblank < /srv/config/default-dbs.sql | echo "Imported default databases..."
mysql -u root -pblank < /srv/config/create-dbs.sql | echo "Created additional databases..."
/srv/config/db-dumps/import-sql.sh

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
