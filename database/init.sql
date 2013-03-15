# We include a default installation of WordPress with this Vagrant setup.
# In order for that to respond properly, a default database should be
# available for it to use.
CREATE DATABASE IF NOT EXISTS `wordpress_default`;
GRANT ALL PRIVILEGES ON `wordpress_default`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';

# Create an external user with privileges on all databases in mysql so
# that a connection can be made from the local machine without an SSH tunnel
CREATE USER 'external'@'%' IDENTIFIED BY 'external';
GRANT ALL PRIVILEGES ON *.* TO 'external'@'%';