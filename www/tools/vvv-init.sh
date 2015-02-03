echo -e "\n ================================== "
echo -e "\n Provision VVV Tools"
echo -e "\n ================================== "

# Nginx
touch /srv/log/logs.vvv.dev_error.log
touch /srv/log/logs.vvv.dev_access.log

touch /srv/log/opcache-status.vvv.dev_error.log
touch /srv/log/opcache-status.vvv.dev_access.log

touch /srv/log/webgrind.vvv.dev_error.log
touch /srv/log/webgrind.vvv.dev_access.log

# Install stuff via Composer
cd /srv/www/tools
if [[ ! -f /srv/www/tools/composer.lock ]]; then
	composer install --prefer-dist --no-autoloader
else
	composer update --prefer-dist --no-autoloader
fi

cp /srv/config/phpmyadmin-config/config.inc.php /srv/www/tools/vendor/phpmyadmin/phpmyadmin/
