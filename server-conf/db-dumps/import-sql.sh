#!/bin/bash

for file in $( ls *.sql )
do
pre_dot=${file%%.*}
mysql -u root -pblank $pre_dot < $pre_dot.sql
done
