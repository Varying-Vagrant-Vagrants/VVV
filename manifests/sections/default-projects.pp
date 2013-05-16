# Default projects to be installed with the Vagrant

# wp-cli: http://wp-cli.org/
class {
	wp::cli:
		ensure => installed,
		install_path => '/vagrant/projects/wp-cli';
}