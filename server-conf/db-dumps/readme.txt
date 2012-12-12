Store .sql dumps in this database, then create and credential a file in the server-conf directory that creates the databases needed to suck in these SQL dumps.

So.. create-dbs.sql lives in vagrant-web/server-conf and contains this:

CREATE DATABASE my_database;
GRANT ALL PRIVILEGES ON my_database.* TO 'thisguy'@'localhost' IDENTIFIED BY 'thatpaass';

CREATE DATABASE my_other_database;

...etc, etc, etc...

Other SQL dumps live in /vagrant-web/server-conf/db-dumps and automatically get sucked in from the shell if they exist and have a .sql file extension. (See provision.sh)
