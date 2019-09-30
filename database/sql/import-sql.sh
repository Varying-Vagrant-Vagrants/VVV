#!/bin/bash
#
# Import provided SQL files in to MariaDB/MySQL.
#
# The files in the {vvv-dir}/database/backups/ directory should be created by
# mysqldump or some other export process that generates a full set of SQL commands
# to create the necessary tables and data required by a database.
#
# For an import to work properly, the SQL file should be named `db_name.sql` in which
# `db_name` matches the name of a database already created in {vvv-dir}/database/init-custom.sql
# or {vvv-dir}/database/sql/init.sql.
#
# If a filename does not match an existing database, it will not import correctly.
#
# If tables already exist for a database, the import will not be attempted again. After an
# initial import, the data will remain persistent and available to MySQL on future boots
# through {vvv-dir}/database/data
#
# Let's begin...

VVV_CONFIG=/vagrant/vvv-config.yml
if [[ -f /vagrant/vvv-custom.yml ]]; then
	VVV_CONFIG=/vagrant/vvv-custom.yml
fi

run_restore=`cat ${VVV_CONFIG} | shyaml get-value general.db_restore 2> /dev/null`

if [[ $run_restore == "False" ]]
then
	echo "Skipping DB import script, disabled via the VVV config file\n"
	exit;
fi

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
printf "\nStarting MariaDB Database Import\n"
cd /srv/database/backups/

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
sql_count=`ls -1 *.sql 2>/dev/null | wc -l`
if [ $sql_count != 0 ]
then
	for file in $( ls *.sql )
	do
	pre_dot=${file%%.sql}

	printf " * Creating the ${pre_dot} table if it doesn't already exist, and granting the wp user access"
	mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS \`$pre_dot\`"
	mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON \`$pre_dot\`.* TO wp@localhost IDENTIFIED BY 'wp';"

	mysql_cmd='SHOW TABLES FROM `'$pre_dot'`' # Required to support hypens in database names
	db_exist=`mysql -u root -proot --skip-column-names -e "$mysql_cmd"`
	if [ "$?" != "0" ]
	then
		printf "  * Error - Create ${pre_dot} database via init-custom.sql before attempting import\n\n"
	else
		if [ "" == "$db_exist" ]
		then
			printf "mysql -u root -proot ${pre_dot} < ${pre_dot}.sql\n"
			mysql -u root -proot ${pre_dot} < ${pre_dot}.sql
			printf "  * Import of ${pre_dot} successful\n"
		else
			printf "  * Skipped import of ${pre_dot} - tables exist\n"
		fi
	fi
	done
	printf "Databases imported\n"
else
	printf "No custom databases to import\n"
fi
