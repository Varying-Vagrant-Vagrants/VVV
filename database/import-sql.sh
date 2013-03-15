#!/bin/bash

# Move into the newly mapped backups directory, where mysqldump(ed) SQL files are stored
cd /srv/database/backups/

printf "\nStart DB Import"

# Parse through each file in the directory and use the file name to
# import the SQL file into the database of the same name
for file in $( ls *.sql )
do
pre_dot=${file%%.*}
if [ ! -d "/srv/database/data/$pre_dot" ]
then
	echo "mysql -u root -pblank $pre_dot < $pre_dot.sql"
    mysql -u root -pblank $pre_dot < $pre_dot.sql
else
	echo "skipped $pre_dot ..."
fi
done
printf "\nDatabases imported - press return for prompt\n"
