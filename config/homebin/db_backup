#!/bin/bash
#
# Create individual SQL files for each database. These files
# are imported automatically during an initial provision if
# the databases exist per the import-sql.sh process.
trap 'rm -rf $TMPFIFODIR' EXIT; TMPFIFODIR=$(mktemp -d); mkfifo $TMPFIFODIR/dbnames

mkdir -p /srv/database/backups
echo " * Performing Database Backups"
databases=()

echo " * Querying Database names"
mysql --user="root" --password="root" -e 'show databases' | \
grep -v -F "Database" > $TMPFIFODIR/dbnames &
echo " * Processing names"
while read db_name
do
    # skip these databases
    [ "${db_name}" == "mysql" ] && echo "   - skipped ${db_name}" && continue;
    [ "${db_name}" == "information_schema" ] && echo "   - skipped ${db_name}" && continue;
    [ "${db_name}" == "performance_schema" ] && echo "   - skipped ${db_name}" && continue;
    [ "${db_name}" == "test" ] && echo "   - skipped ${db_name}" && continue;
    databases+=( "${db_name}" )
done < $TMPFIFODIR/dbnames

count=0
for db in "${databases[@]}"
do
    printf "   - %2s/%s Backing up %-23s to 'database/backups/%s.sql'\n" "${count}" "${#databases[@]}" "'${db}'" "${db}"
    mysqldump -uroot -proot "${db}" > "/srv/database/backups/${db}.sql";
    let "count=count+1"
done
echo " * Finished backing up databases to the database/sql/backups folder"
