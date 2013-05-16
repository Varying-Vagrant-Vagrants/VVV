# Default projects to be installed with the Vagrant

# wp-cli: http://wp-cli.org/
class {
	wp::cli:
		ensure => installed,
		install_path => '/vagrant/projects/wp-cli';
}


# WordPress trunk

exec { "svn co wordpress trunk":
	command			=> "/usr/bin/svn co https://core.svn.wordpress.org/trunk wordpress-trunk.dev",
	cwd				=> "/vagrant/projects",
	creates			=> "/vagrant/projects/wordpress-trunk.dev",
}

nginx::resource::vhost { 'wordpress-trunk.dev':
	ensure		=> present,
	www_root	=> '/srv/www/wordpress-trunk.dev',
}

mysql::grant { 'wordpress_trunk':
  mysql_privileges => 'ALL',
  mysql_password => 'wordpress_trunk',
  mysql_db => 'wordpress_trunk',
  mysql_user => 'wordpress_trunk',
  mysql_host => 'localhost',
}