apt-get update --force-yes -y
apt-get upgrade --froce-yes -y
apt-get install --force-yes -y  php5 php5-fpm php-pear php5-common php5-mcrypt php5-mysql php5-cli php5-gd php-apc
apt-get install --force-yes -y nginx

echo mysql-server mysql-server/root_password password blank | sudo debconf-set-selections
echo mysql-server mysql-server/root_password_again password blank | sudo debconf-set-selections

apt-get install --force-yes -y mysql-server php5-mysql
apt-get install --force-yes -y git-core
apt-get install --force-yes -y curl

# I like vi and I like vim better
apt-get install --force-yes -y vim

cp /srv/www/creditsesame/index.php /tmp/test.php

echo All set!
