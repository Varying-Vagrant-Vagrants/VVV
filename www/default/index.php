<?php
/**
 * If a custom dashboard file exists, load that instead of the default
 * dashboard provided by Varying Vagrant Vagrants. This file should be
 * located in the `www/default/` directory.
 */
if ( file_exists( 'dashboard-custom.php' ) ) {
	include( 'dashboard-custom.php' );
	exit;
}

// Begin default dashboard.
?>
<!DOCTYPE html>
<html>
<head>
	<title>Varying Vagrant Vagrants Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<ul class="nav">
	<li class="active"><a href="#">Home</a></li>
	<li><a href="https://github.com/varying-vagrant-vagrants/vvv/">Repository</a></li>
	<li><a href="database-admin/">phpMyAdmin</a></li>
	<li><a href="memcached-admin/">phpMemcachedAdmin</a></li>
	<li><a href="opcache-status/opcache.php">Opcache Status</a></li>
	<li><a href="webgrind/">Webgrind</a></li>
	<li><a href="phpinfo/">PHP Info</a></li>
</ul>

<ul class="nav">
	<li><a href="http://local.wordpress.dev/">http://local.wordpress.dev</a> for WordPress stable (www/wordpress-default)</li>
	<li><a href="http://local.wordpress-trunk.dev/">http://local.wordpress-trunk.dev</a> for WordPress trunk (www/wordpress-trunk)</li>
	<li><a href="http://src.wordpress-develop.dev/">http://src.wordpress-develop.dev</a> for trunk WordPress development files (www/wordpress-developer/src)</li>
	<li><a href="http://build.wordpress-develop.dev/">http://build.wordpress-develop.dev</a> for a Grunt build of those development files (www/wordpress-developer/build)</li>
</ul>
</body>
</html>
