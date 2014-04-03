#!/bin/bash
#
# vvv-init.sh

# Make a database, if we don't already have one
echo -e "\nCreating database 'wordpress_default' (if it's not already there)"
mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS wordpress_default"
mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON wordpress_default.* TO wp@localhost IDENTIFIED BY 'wp';"
echo -e "\n DB operations done.\n\n"

# Install and configure the latest stable version of WordPress
if [[ ! -d /srv/www/wordpress-default ]]; then
	echo "Downloading WordPress Stable, see http://wordpress.org/"
	cd /srv/www/
	curl -O http://wordpress.org/latest.tar.gz
	tar -xvf latest.tar.gz
	mv wordpress wordpress-default
	rm latest.tar.gz
	cd /srv/www/wordpress-default
	echo "Configuring WordPress Stable..."
	wp core config --dbname=wordpress_default --dbuser=wp --dbpass=wp --quiet --extra-php --allow-root <<PHP
define( 'WP_DEBUG', true );
PHP
	wp core install --url=local.wordpress.dev --quiet --title="Local WordPress Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password" --allow-root
else
	echo "Updating WordPress Stable..."
	cd /srv/www/wordpress-default
	wp core upgrade --allow-root
fi
