#!/bin/bash

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
cd /srv/database/backups/

printf "\nStart DB Import"

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
for file in $( ls *.sql )
do
pre_dot=${file%%.*}
db_exist=`mysql -u root -pblank --skip-column-names -e "SHOW DATABASES LIKE '$pre_dot'" | grep $pre_dot`
if [ "$pre_dot" == "$db_exist" ]
then
	echo "skipped $pre_dot"
else
	echo "mysql -u root -pblank $pre_dot < $pre_dot.sql"
    mysql -u root -pblank $pre_dot < $pre_dot.sql
fi
done
printf "\nDatabases imported - press return for prompt\n"
