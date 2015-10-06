#!/bin/bash

## VARIABLES

### FUNCTIONS
wp_cli() {
  # WP-CLI Install
  if [[ ! -d "/srv/www/wp-cli" ]]; then
    echo -e "\nDownloading wp-cli, see http://wp-cli.org"
    git clone "https://github.com/wp-cli/wp-cli.git" "/srv/www/wp-cli"
    cd /srv/www/wp-cli
    composer install
  else
    echo -e "\nUpdating wp-cli..."
    cd /srv/www/wp-cli
    git pull --rebase origin master
    composer update
  fi
  # Link `wp` to the `/usr/local/bin` directory
  sudo ln -sf "/srv/www/wp-cli/bin/wp" "/usr/local/bin/wp"
}

wordpress_default() {
  # Install and configure the latest stable version of WordPress
  if [[ ! -d "/srv/www/wordpress-default" ]]; then
    echo "Downloading WordPress Stable, see http://wordpress.org/"
    cd /srv/www/
    curl -L -O "https://wordpress.org/latest.tar.gz"
    tar -xvf latest.tar.gz
    mv wordpress wordpress-default
    rm latest.tar.gz
    cd /srv/www/wordpress-default
    echo "Configuring WordPress Stable..."
    wp core config --dbname=wordpress_default --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
// Match any requests made via xip.io.
if ( isset( \$_SERVER['HTTP_HOST'] ) && preg_match('/^(local.wordpress.)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(.xip.io)\z/', \$_SERVER['HTTP_HOST'] ) ) {
define( 'WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] );
define( 'WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] );
}

define( 'WP_DEBUG', true );
PHP
    echo "Installing WordPress Stable..."
    wp core install --url=local.wordpress.dev --quiet --title="Local WordPress Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
  else
    echo "Updating WordPress Stable..."
    cd /srv/www/wordpress-default
    wp core upgrade
  fi
}

wpsvn_check() {
  # Test to see if an svn upgrade is needed
  svn_test=$( svn status -u "/srv/www/wordpress-develop/" 2>&1 );

  if [[ "$svn_test" == *"svn upgrade"* ]]; then
  # If the wordpress-develop svn repo needed an upgrade, they probably all need it
    for repo in $(find /srv/www -maxdepth 5 -type d -name '.svn'); do
      svn upgrade "${repo/%\.svn/}"
    done
  fi;
}

wordpress_trunk() {
  # Checkout, install and configure WordPress trunk via core.svn
  if [[ ! -d "/srv/www/wordpress-trunk" ]]; then
    echo "Checking out WordPress trunk from core.svn, see https://core.svn.wordpress.org/trunk"
    svn checkout "https://core.svn.wordpress.org/trunk/" "/srv/www/wordpress-trunk"
    cd /srv/www/wordpress-trunk
    echo "Configuring WordPress trunk..."
    wp core config --dbname=wordpress_trunk --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
// Match any requests made via xip.io.
if ( isset( \$_SERVER['HTTP_HOST'] ) && preg_match('/^(local.wordpress-trunk.)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(.xip.io)\z/', \$_SERVER['HTTP_HOST'] ) ) {
define( 'WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] );
define( 'WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] );
}

define( 'WP_DEBUG', true );
PHP
    echo "Installing WordPress trunk..."
    wp core install --url=local.wordpress-trunk.dev --quiet --title="Local WordPress Trunk Dev" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
  else
    echo "Updating WordPress trunk..."
    cd /srv/www/wordpress-trunk
    svn up
  fi
}

wordpress_develop(){
  # Checkout, install and configure WordPress trunk via develop.svn
  if [[ ! -d "/srv/www/wordpress-develop" ]]; then
    echo "Checking out WordPress trunk from develop.svn, see https://develop.svn.wordpress.org/trunk"
    svn checkout "https://develop.svn.wordpress.org/trunk/" "/srv/www/wordpress-develop"
    cd /srv/www/wordpress-develop/src/
    echo "Configuring WordPress develop..."
    wp core config --dbname=wordpress_develop --dbuser=wp --dbpass=wp --quiet --extra-php <<PHP
// Match any requests made via xip.io.
if ( isset( \$_SERVER['HTTP_HOST'] ) && preg_match('/^(src|build)(.wordpress-develop.)\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(.xip.io)\z/', \$_SERVER['HTTP_HOST'] ) ) {
define( 'WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] );
define( 'WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] );
} else if ( 'build' === basename( dirname( __FILE__ ) ) ) {
// Allow (src|build).wordpress-develop.dev to share the same Database
define( 'WP_HOME', 'http://build.wordpress-develop.dev' );
define( 'WP_SITEURL', 'http://build.wordpress-develop.dev' );
}

define( 'WP_DEBUG', true );
PHP
    echo "Installing WordPress develop..."
    wp core install --url=src.wordpress-develop.dev --quiet --title="WordPress Develop" --admin_name=admin --admin_email="admin@local.dev" --admin_password="password"
    cp /srv/config/wordpress-config/wp-tests-config.php /srv/www/wordpress-develop/
    cd /srv/www/wordpress-develop/
    echo "Running npm install for the first time, this may take several minutes..."
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
    echo "Updating npm packages..."
    npm install &>/dev/null
  fi

  if [[ ! -d "/srv/www/wordpress-develop/build" ]]; then
    echo "Initializing grunt in WordPress develop... This may take a few moments."
    cd /srv/www/wordpress-develop/
    grunt
  fi
}

### SCRIPT
# Time for WordPress!
echo " "
echo "Installing/updating WP-CLI, WordPress Stable & Trunk"

wp_cli
wordpress_default
wpsvn_check
wordpress_trunk

# WP Develop site optional. Uncomment below.
# wordpress_develop
