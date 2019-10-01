# About this directory

This directory, `database/data`, is specified as the data directory
for MySQL as Vagrant boots up. Once databases are imported on the
initial `vagrant up` from database/backups, they will persist in
Vagrant even after a 'vagrant destroy'. 

If you would like to remove one of these databases so that it can be
reimported, a `DROP DATABASE db_name` would need to be run though the
mysql command line or external tool. 
