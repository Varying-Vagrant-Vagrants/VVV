# Provision WordPress stable

# Make a database, if we don't already have one
echo -e "\nCreating database 'wordpress_default' (if it's not already there)"
mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS wordpress_default"
mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON wordpress_default.* TO wp@localhost IDENTIFIED BY 'wp';"
echo -e "\n DB operations done.\n\n"

# Nginx Logs
if [[ ! -d /srv/log/wordpress-default ]]; then
	mkdir /srv/log/wordpress-default
	touch /srv/log/wordpress-default/error.log
	touch /srv/log/wordpress-default/access.log
fi

# Install and configure the latest stable version of WordPress
if [[ ! -d /srv/www/wordpress-default ]]; then
	
	mkdir /srv/www/wordpress-default
	cd /srv/www/wordpress-default

	echo "Downloading WordPress Stable, see http://wordpress.org/"
	wp core download

	echo "Configuring WordPress Stable..."
	wp core config --dbname=wordpress_default --dbuser=wp --dbpass=wp --quiet --extra-php --allow-root <<PHP
define( 'WP_DEBUG', true );
PHP
	echo "Installing WordPress Stable..."
	wp core install --url=local.wordpress.dev --quiet --title="Local WordPress Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password" --allow-root
else
	echo "Updating WordPress Stable..."
	cd /srv/www/wordpress-default
	wp core upgrade --allow-root
fi
