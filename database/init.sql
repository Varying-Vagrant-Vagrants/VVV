# Create the unit tests DB.
CREATE DATABASE IF NOT EXISTS `wordpress_unit_tests`;
GRANT ALL PRIVILEGES ON `wordpress_unit_tests`.* TO 'wp'@'localhost' IDENTIFIED BY 'wp';
GRANT ALL PRIVILEGES ON `wordpress_unit_tests`.* TO 'wp'@'192.168.50.1' IDENTIFIED BY 'wp';

# Create an external user with privileges on all databases in mysql so
# that a connection can be made from the local machine without an SSH tunnel
GRANT ALL PRIVILEGES ON *.* TO 'external'@'%' IDENTIFIED BY 'external';
