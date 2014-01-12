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
	<li><a href="http://github.com/10up/varying-vagrant-vagrants">Repository</a></li>
	<li><a href="database-admin/">phpMyAdmin</a></li>
	<li><a href="memcached-admin/">phpMemcachedAdmin</a></li>
	<li><a href="webgrind/">Webgrind</a></li>
	<li><a href="phpinfo/">PHP Info</a></li>
</ul>

<ul class="nav">
	<li><a href="http://local.wordpress.dev/">http://local.wordpress.dev</a> for WordPress stable</li>
	<li><a href="http://local.wordpress-trunk.dev/">http://local.wordpress-trunk.dev</a> for WordPress trunk</li>
	<li><a href="http://src.wordpress-develop.dev/">http://src.wordpress-develop.dev</a> for trunk WordPress development files</li>
	<li><a href="http://build.wordpress-develop.dev/">http://build.wordpress-develop.dev</a> for a Grunt build of those development files</li>
	<li><a href="http://local.wordpress-network.dev/">http://local.wordpress-network.dev</a> for WordPress network install</li>
</ul>
</body>
</html>
