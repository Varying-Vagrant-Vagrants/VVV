#!/usr/bin/env bash
# @description Set up and configure MariaDB
set -eo pipefail

# MariaDB/MySQL
function mariadb_register_packages() {
  #
  # Use debconf-set-selections to specify the default password for the root MariaDB
  # account. This runs on every provision, even if MariaDB has been installed. If
  # MariaDB is already installed, it will not affect anything.
  echo mariadb-server-10.5 mysql-server/root_password password "root" | debconf-set-selections
  echo mariadb-server-10.5 mysql-server/root_password_again password "root" | debconf-set-selections

  vvv_info " * Setting up MySQL configuration file links..."

  if grep -q 'mysql' /etc/group; then
    vvv_info " * mysql group exists"
  else
    echo " * creating mysql group"
    groupadd -g 9001 mysql
  fi

  if id -u mysql >/dev/null 2>&1; then
    vvv_info " * mysql user present and has uid $(id -u mysql)"
  else
    vvv_info " * adding the mysql user"
    useradd -u 9001 -g mysql -r mysql
    if grep -q vboxsf /etc/group; then
      usermod -G vboxsf mysql
    fi
  fi

  mkdir -p "/etc/mysql/conf.d"
  vvv_info " * Copying /srv/config/mysql-config/vvv-core.cnf to /etc/mysql/conf.d/vvv-core.cnf"
  cp -f "/srv/config/mysql-config/vvv-core.cnf" "/etc/mysql/conf.d/vvv-core.cnf"

  if ! vvv_apt_keys_has 'MariaDB'; then
    # Apply the MariaDB signing keyg
    vvv_info " * Applying the MariaDB signing key..."
    apt-key add /srv/config/apt-keys/mariadb.key
  fi

  if ! vvv_src_list_has "MariaDB"; then
    cp -f "/srv/provision/core/mariadb/sources.list" "/etc/apt/sources.list.d/vvv-mariadb-sources.list"
    ARCH=$(lsb_release -c --short)
    sed -i "s|{ARCH}|${ARCH}|g" "/etc/apt/sources.list.d/vvv-mariadb-sources.list"
  fi

  VVV_PACKAGE_LIST+=(mariadb-server)
}
vvv_add_hook before_packages mariadb_register_packages

function check_mysql_root_password() {
  vvv_info " * Checking the root user password is root"
  # Get if root has correct password and mysql_native_password as plugin
  sql=$( cat <<-SQL
      SELECT count(*) from mysql.user WHERE
      User='root' AND
      authentication_string=PASSWORD('root') AND
      plugin='mysql_native_password';
SQL
)
  root_matches=$(mysql -u root --password=root -s -N -e "${sql}")
  if [[ $? -eq 0 && $root_matches == "1" ]]; then
    # mysql connected and the SQL above matched
    vvv_success " * The database root password is the expected value"
    return 0
  fi
  # Do reset password in safemode
  vvv_warn " * The root password is not root, fixing"
  sql=$( cat <<-SQL
      ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password USING PASSWORD('root');
      FLUSH PRIVILEGES;
SQL
)
  mysql -u root -proot -e "${sql}"
  if [[ $? -eq 0 ]]; then
    vvv_success "   - root user password should now be root"
  else
    vvv_warn "   - could not reset root password"
  fi
}

function mysql_setup() {
  # If MariaDB/MySQL is installed, go through the various imports and service tasks.
  if ! command -v mysql &> /dev/null; then
    vvv_error " ! MySQL is not installed. No databases imported."
    return 1
  fi
  vvv_info " * Setting up database configuration file links..."

  # Copy mysql configuration from local
  cp "/srv/config/mysql-config/my.cnf" "/etc/mysql/my.cnf"
  vvv_info " * Copied /srv/config/mysql-config/my.cnf               to /etc/mysql/my.cnf"

  cp -f  "/srv/config/mysql-config/root-my.cnf" "/home/vagrant/.my.cnf"
  chmod 0644 "/home/vagrant/.my.cnf"
  vvv_info " * Copied /srv/config/mysql-config/root-my.cnf          to /home/vagrant/.my.cnf"

  if [ "${VVV_DOCKER}" != 1 ]; then
    check_mysql_root_password
  fi

  # MySQL gives us an error if we restart a non running service, which
  # happens after a `vagrant halt`. Check to see if it's running before
  # deciding whether to start or restart.
  if [ $(service mysql status|grep 'mysql start/running' | wc -l) -ne 1 ]; then
    vvv_info " * Starting the mysql service"
    service mysql start
  else
    vvv_info " * Restarting mysql service"
    service mysql restart
  fi
  # IMPORT SQL
  #
  # Create the databases (unique to system) that will be imported with
  # the mysqldump files located in database/backups/
  if [[ -f "/srv/database/init-custom.sql" ]]; then
    vvv_info " * Running custom init-custom.sql under the root user..."
    mysql -u "root" -p"root" < "/srv/database/init-custom.sql"
    vvv_success " * init-custom.sql has run"
  else
    vvv_info " * No custom MySQL scripting found in database/init-custom.sql, skipping..."
  fi

  # Setup MySQL by importing an init file that creates necessary
  # users and databases that our vagrant setup relies on.
  mysql -u "root" -p"root" < "/srv/database/init.sql"
  vvv_info " * Initial MySQL prep..."

  # Process each mysqldump SQL file in database/backups to import
  # an initial data set for MySQL.
  "/srv/database/import-sql.sh"
}
export -f mysql_setup

if [ "${VVV_DOCKER}" != 1 ]; then
  vvv_add_hook after_packages mysql_setup 30
fi
