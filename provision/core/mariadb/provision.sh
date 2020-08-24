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

if ! vvv_apt_keys_has 'MariaDB'; then
  # Apply the MariaDB signing keyg
  echo " * Applying the MariaDB signing key..."
  apt-key add /srv/config/apt-keys/mariadb.key
fi

if ! vvv_src_list_has "nginx.org"; then
  cat <<VVVSRC >> /etc/apt/sources.list.d/vvv-sources.list
# MariaDB 10.4 Amsterdam
deb [arch=amd64,arm64,ppc64el] http://ams2.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main
deb-src http://ams2.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main

# MariaDB 10.4 Digital Ocean Singapore
deb [arch=amd64,arm64,ppc64el] http://sgp1.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main
deb-src http://sgp1.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main

# MariaDB 10.4 Digital Ocean San Francisco
deb [arch=amd64,arm64,ppc64el] http://sfo1.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main
deb-src http://sfo1.mirrors.digitalocean.com/mariadb/repo/10.4/ubuntu bionic main

# MariaDB 10.4 Yamagata University Japan
deb [arch=amd64,arm64,ppc64el] http://ftp.yz.yamagata-u.ac.jp/pub/dbms/mariadb/repo/10.4/ubuntu bionic main
deb-src http://ftp.yz.yamagata-u.ac.jp/pub/dbms/mariadb/repo/10.4/ubuntu bionic main

# MariaDB 10.4 UKFast Manchester
deb [arch=amd64,arm64,ppc64el] http://mirrors.ukfast.co.uk/sites/mariadb/repo/10.4/ubuntu bionic main
deb-src http://mirrors.ukfast.co.uk/sites/mariadb/repo/10.4/ubuntu bionic main

# MariaDB 10.4 PicoNets Mumbai
deb [arch=amd64,arm64,ppc64el] http://mirrors.piconets.webwerks.in/mariadb-mirror/repo/10.4/ubuntu bionic main
deb-src http://mirrors.piconets.webwerks.in/mariadb-mirror/repo/10.4/ubuntu bionic main

VVVSRC
fi

VVV_PACKAGE_LIST+=(mariadb-server)
