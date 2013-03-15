#!/bin/bash

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
cd /srv/database/backups/

printf "\nStart DB Import\n\n"

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
for file in $( ls *.sql )
do
pre_dot=${file%%.*}
db_exist=`mysql -u root -pblank --skip-column-names -e "SHOW TABLES FROM $pre_dot"`
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
printf "Databases imported - press return for prompt\n"
