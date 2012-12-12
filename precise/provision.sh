apt-get update --force-yes -y
#apt-get upgrade --force-yes -y
apt-get install --force-yes -y  php5 php5-fpm php-pear php5-common php5-mcrypt php5-mysql php5-cli php5-gd php-apc

sudo cp /srv/server-conf/www.conf /etc/php5/fpm/pool.d/www.conf

sudo /etc/init.d/php5-fpm restart

apt-get install --force-yes -y nginx

sudo cp /srv/server-conf/nginx.conf /etc/nginx/nginx.conf
sudo cp /srv/server-conf/nginx-wp-common.conf /etc/nginx/nginx-wp-common.conf

sudo /etc/init.d/nginx restart

echo mysql-server mysql-server/root_password password blank | sudo debconf-set-selections
echo mysql-server mysql-server/root_password_again password blank | sudo debconf-set-selections

apt-get install --force-yes -y mysql-server php5-mysql
apt-get install --force-yes -y git-core
apt-get install --force-yes -y curl

# I like vi and I like vim better
apt-get install --force-yes -y vim

echo All set!
