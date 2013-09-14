SQL files created with mysqldump should be stored in database/backups so that they are automatically imported as the Vagrant VM boots up.

For this to work properly, databases should be created in your database/init-custom.sql file as explained in database/init-custom.sql.sample.

Once these original files have been imported, they will exist in database/data as the actual mysql data directory and will not need to be reimported on the next vagrant up.