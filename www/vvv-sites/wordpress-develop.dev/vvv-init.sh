#!/bin/bash
#
# vvv-init.sh

if [[ $ping_result == *bytes?from* ]]; then
	# Checkout, install and configure WordPress trunk via develop.svn
	if [[ ! -d /srv/www/wordpress-develop ]]; then
		echo "Checking out WordPress trunk from develop.svn, see http://develop.svn.wordpress.org/trunk"
		svn checkout http://develop.svn.wordpress.org/trunk/ /srv/www/wordpress-develop
		cd /srv/www/wordpress-develop/src/
		echo "Configuring WordPress develop..."
		wp core config --dbname=wordpress_develop --dbuser=wp --dbpass=wp --quiet --extra-php --allow-root <<PHP
// Allow (src|build).wordpress-develop.dev to share the same database
if ( 'build' == basename( dirname( __FILE__) ) ) {
	define( 'WP_HOME', 'http://build.wordpress-develop.dev' );
	define( 'WP_SITEURL', 'http://build.wordpress-develop.dev' );
}

define( 'WP_DEBUG', true );
PHP
		wp core install --url=src.wordpress-develop.dev --quiet --title="WordPress Develop" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password" --allow-root
		cp /srv/config/wordpress-config/wp-tests-config.php /srv/www/wordpress-develop/
		cd /srv/www/wordpress-develop/
		npm install &>/dev/null
	else
		echo "Updating WordPress develop..."
		cd /srv/www/wordpress-develop/
		if [[ -e .svn ]]; then
			svn up
		else
			if [[ $(git rev-parse --abbrev-ref HEAD) == 'master' ]]; then
				git pull --no-edit git://develop.git.wordpress.org/ master
			else
				echo "Skip auto git pull on develop.git.wordpress.org since not on master branch"
			fi
		fi
		npm install &>/dev/null
	fi

	if [[ ! -d /srv/www/wordpress-develop/build ]]; then
		echo "Initializing grunt in WordPress develop... This may take a few moments."
		cd /srv/www/wordpress-develop/
		grunt
	fi
else
	echo -e "\nNo network available, skipping provisioning of this site"
fi
