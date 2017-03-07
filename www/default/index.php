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
	<style>
		body {
			font-family:monospace;
			background-color: #2b303b;
			color: #c0c5ce;
			padding:1em;
		}
		a {
			color: #ebcb8b;
			text-decoration: none;
		}
		#vvv_logo {
			white-space: pre;
			line-height:10px;
			font-size:16px;
		}
		.v1 { color: rgb( 246, 60, 37 ); }
		.v2 { color: rgb( 130, 255, 27 ); }
		.v3 { color: rgb( 76, 155, 250 ); }
		.v4 { color: rgb( 255, 224, 27 ); }
	</style>
</head>
<body>

<p id="vvv_logo">
<span class="v1">__     _</span><span class="v2">__     _</span><span class="v3">__     __</span> <span class="v4"> ____  </span>  <br>
<span class="v1">\ \   / </span><span class="v2">\ \   / </span><span class="v3">\ \   / /</span> <span class="v4">|___ \ </span>  <br>
<span class="v1"> \ \ / /</span><span class="v2"> \ \ / /</span><span class="v3"> \ \ / / </span> <span class="v4">  __) |</span>  <br>
<span class="v1">  \ V / </span><span class="v2">  \ V / </span><span class="v3">  \ V /  </span> <span class="v4"> / __/ </span>  <br>
<span class="v1">   \_/  </span><span class="v2">   \_/  </span><span class="v3">   \_/   </span> <span class="v4">|_____|</span></p>

<p>Varying Vagrant Vagrants develop branch</p>

<ul class="nav">
	<li class="active"><a href="#">Home</a></li>
	<li><a href="https://varyingvagrantvagrants.org/">Documentation</a></li>
	<li><a href="https://github.com/varying-vagrant-vagrants/vvv/">Repository</a></li>
</ul>

<h3>Bundled Tools</h3>
<ul class="nav">
	<li><a href="database-admin/">phpMyAdmin</a></li>
	<li><a href="memcached-admin/">phpMemcachedAdmin</a></li>
	<li><a href="opcache-status/opcache.php">Opcache Status</a></li>
	<li><a href="http://vvv.dev:1080">Mailcatcher</a></li>
	<li><a href="webgrind/">Webgrind</a></li>
	<li><a href="phpinfo/">PHP Info</a></li>
	<li><a href="php-status?html&amp;full">PHP Status</a></li>
</ul>

<h3>Bundled Environments</h3>

<ul class="nav">
	<li><a href="http://local.wordpress.dev/">http://local.wordpress.dev</a> for WordPress stable (www/wordpress-default)</li>
	<li><a href="http://src.wordpress-develop.dev/">http://src.wordpress-develop.dev</a> for trunk WordPress development files (www/wordpress-develop/src)</li>
	<li><a href="http://build.wordpress-develop.dev/">http://build.wordpress-develop.dev</a> for a Grunt build of those development files (www/wordpress-develop/build)</li>
</ul>
</body>
</html>
