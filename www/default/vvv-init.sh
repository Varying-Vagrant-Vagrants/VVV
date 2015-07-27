# Provision tools via composer
echo -e "\n\n "
echo -e "\n=================================="
echo -e "\n Provision default vhost"
echo -e "\n=================================="

# Constants
DIR="$(dirname $SITE_CONFIG_FILE)"
DESTDIR="/srv/www/default"
LOGDIR="/srv/log/default"

# Nginx logs
if [[ ! -d $LOGDIR ]]; then
	mkdir $LOGDIR
fi

touch $LOGDIR/error.log
touch $LOGDIR/access.log

# Provision tools
cd /srv/www/default
if [[ ! -f /srv/www/default/composer.lock ]]; then
	echo -e "\nProvisioning tools via Composer..."
	composer install --prefer-dist --no-autoloader
else
	echo -e "\nUpdating tools via Composer..."
	composer update --prefer-dist --no-autoloader
fi

# Copy phpMyAdmin config
echo -e "\nCopying phpMyAdmin config..."
cp /srv/config/phpmyadmin-config/config.inc.php /srv/www/default/vendor/wp-cloud/phpmyadmin/
