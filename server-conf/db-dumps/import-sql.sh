#!/bin/bash
cd /srv/server-conf/db-dumps/

for file in $( ls *.sql )
do
pre_dot=${file%%.*}
echo "mysql -u root -pblank $pre_dot < $pre_dot.sql"
mysql -u root -pblank "$pre_dot" < $pre_dot.sql
done
