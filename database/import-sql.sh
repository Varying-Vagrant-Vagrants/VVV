#!/bin/bash

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
cd /srv/database/backups/

printf "\nStart DB Import\n\n"

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
sql_count=`ls -1 *.sql 2>/dev/null | wc -l`
if [ $sql_count != 0 ]
then
	for file in $( ls *.sql )
	do
	pre_dot=${file%%.*}
	mysql_cmd='SHOW TABLES FROM `'$pre_dot'`' # Required to support hypens in database names
	db_exist=`mysql -u root -pblank --skip-column-names -e "$mysql_cmd"`
	if [ "$?" != "0" ]
	then
		printf "Error - Create $pre_dot database via init-custom.sql before attempting import\n\n"
	else
		if [ "" == "$db_exist" ]
		then
			printf "mysql -u root -pblank $pre_dot < $pre_dot.sql\n"
			mysql -u root -pblank $pre_dot < $pre_dot.sql
			printf "Import of $pre_dot successful\n\n"
		else
			printf "Skipped import of $pre_dot - tables exist\n\n"
		fi
	fi
	done
	printf "Databases imported\n"
else
	printf "No custom databases to import\n"
fi