# We include a default installation of WordPress with this Vagrant setup.
# In order for that to respond properly, a default database should be
# available for it to use.
CREATE DATABASE IF NOT EXISTS `wordpress_default`;
GRANT ALL PRIVILEGES ON `wordpress_default`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';

# Create an external user with privileges on all databases in mysql so
# that a connection can be made from the local machine without an SSH tunnel
#
# We use a stored procedure for this because the mysql.user table is persistent after
# the first `vagrant up` and an error will be thrown if we try to create a
# user that already exists. So... a lot of lines of code to prevent red in our boot. :)
drop procedure if exists createExternalUser;
delimiter $$
create procedure createExternalUser(username varchar(50), pw varchar(50))
begin
IF (SELECT EXISTS(SELECT 1 FROM `mysql`.`user` WHERE `user` = 'external')) = 0 THEN
    begin
    set @sql = CONCAT('CREATE USER ', username, '@\'%\' IDENTIFIED BY \'external\'');
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    end;
END IF;
end $$
delimiter ;

# Use the stored procedure to create our external user
call createExternalUser( 'external', 'external' );
GRANT ALL PRIVILEGES ON *.* TO 'external'@'%';
