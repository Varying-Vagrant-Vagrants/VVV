#!/bin/bash

# MariaDB/MySQL
#
# Use debconf-set-selections to specify the default password for the root MariaDB
# account. This runs on every provision, even if MariaDB has been installed. If
# MariaDB is already installed, it will not affect anything.
echo mariadb-server-10.3 mysql-server/root_password password "root" | debconf-set-selections
echo mariadb-server-10.3 mysql-server/root_password_again password "root" | debconf-set-selections

echo -e "\n * Setting up MySQL configuration file links..."

if grep -q 'mysql' /etc/group; then
echo " * mysql group exists"
else
echo " * creating mysql group"
groupadd -g 9001 mysql
fi

if id -u mysql >/dev/null 2>&1; then
echo " * mysql user present and has uid $(id -u mysql)"
else
echo " * adding the mysql user"
useradd -u 9001 -g mysql -G vboxsf -r mysql
fi

mkdir -p "/etc/mysql/conf.d"
echo " * Copying /srv/config/mysql-config/vvv-core.cnf to /etc/mysql/conf.d/vvv-core.cnf"
cp -f "/srv/config/mysql-config/vvv-core.cnf" "/etc/mysql/conf.d/vvv-core.cnf"

VVV_PACKAGE_LIST+=(mariadb-server)
