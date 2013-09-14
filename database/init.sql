# We include default installations of WordPress with this Vagrant setup.
# In order for that to respond properly, default databases should be
# available for use.
CREATE DATABASE IF NOT EXISTS `wordpress_default`;
GRANT ALL PRIVILEGES ON `wordpress_default`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';
CREATE DATABASE IF NOT EXISTS `wordpress_trunk`;
GRANT ALL PRIVILEGES ON `wordpress_trunk`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';
CREATE DATABASE IF NOT EXISTS `wordpress_develop`;
GRANT ALL PRIVILEGES ON `wordpress_develop`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';
CREATE DATABASE IF NOT EXISTS `wordpress_unit_tests`;
GRANT ALL PRIVILEGES ON `wordpress_unit_tests`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';

# Create an external user with privileges on all databases in mysql so
# that a connection can be made from the local machine without an SSH tunnel
GRANT ALL PRIVILEGES ON *.* TO 'external'@'%' IDENTIFIED BY 'external';
