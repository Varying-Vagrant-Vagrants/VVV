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

source /srv/provision/provision-helpers.sh

VVV_CONFIG=/srv/config/default-config.yml
if [[ -f /srv/config/config.yml ]]; then
	VVV_CONFIG=/srv/config/config.yml
fi

run_restore=$(shyaml get-value general.db_restore 2> /dev/null < ${VVV_CONFIG})
exclude_list=$(get_config_values "general.db_backup.exclude")
restore_list=$(get_config_values "general.db_backup.restore")

if [[ $run_restore == "False" ]]
then
	vvv_info " * Skipping DB import script, disabled via the VVV config file"
	exit;
fi

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
vvv_info " * Starting MariaDB Database Import"
# create the backup folder if it doesn't exist
mkdir -p /srv/database/backups
cd /srv/database/backups/

SAVEIFS=$IFS
IFS=$(echo -en "\n\b")

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
sql_count=$(ls -1 ./*.sql* 2>/dev/null | wc -l)
if [ "$sql_count" != 0 ]
then
	for file in $( ls ./*.sql* )
	do
		# get rid of the extension
		db_name=$(basename "${file}" .sql)
		if [ "${file: -3}" == ".gz" ]; then
			db_name=$(basename "${file}" .sql.gz)
		fi

		# skip these databases
		[ "${db_name}" == "mysql" ] && continue;
		[ "${db_name}" == "information_schema" ] && continue;
		[ "${db_name}" == "performance_schema" ] && continue;
		[ "${db_name}" == "test" ] && continue;

		vvv_info " * Creating the <b>${db_name}</b><info> database if it doesn't already exist, and granting the wp user access"

		skip="false"
		for exclude in ${exclude_list[@]}; do
			if [ "${exclude}" == "${db_name}" ]; then
				skip="true"
			fi
		done

		RESULT=$(mysqlshow ${db_name} | grep -v Wildcard | grep -o ${db_name})
		if [ "$RESULT" == "${db_name}" ] ; then
			for restore in ${restore_list[@]}; do
				if [ "${restore}" == "${db_name}" ]; then
					skip="false"
				fi
			done
		fi

		mysql -u root --password=root -e "CREATE DATABASE IF NOT EXISTS \`${db_name}\`"
		mysql -u root --password=root -e "GRANT ALL PRIVILEGES ON \`${db_name}\`.* TO wp@localhost IDENTIFIED BY 'wp';"

		[ "${db_name}" == "wordpress_unit_tests" ] && continue;

		if [ ${skip} == "true" ]; then
			vvv_info "   - skipped <b>${db_name}</b>" && continue;
		fi

		mysql_cmd="SHOW TABLES FROM \`${db_name}\`" # Required to support hyphens in database names
		db_exist=$(mysql -u root -proot --skip-column-names -e "${mysql_cmd}")
		if [ "$?" != "0" ]
		then
			vvv_error " * Error - Create the <b>${db_name}</b><error> database via init-custom.sql before attempting import"
		else
			if [ "" == "${db_exist}" ]; then
				vvv_info " * Importing <b>${db_name}</b><info> from <b>${file}</b>"
				if [ "${file: -3}" == ".gz" ]; then
					gunzip < "${file}" | mysql -u root -proot "${db_name}"
				else
					mysql -u root -proot "${db_name}" < "${file}"
				fi
				vvv_success " * Import of <b>'${db_name}'</b><success> successful</success>"
			else
				vvv_info " * Skipped import of <b>\`${db_name}\`</b><info> - tables already exist"
			fi
		fi
	done
	vvv_success " * Databases imported"
else
	vvv_success " * No custom databases to import"
fi

IFS=$SAVEIFS
