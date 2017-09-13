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
	<link rel="stylesheet" type="text/css" href="http://vvv.dev/style.css">
</head>
<body>
<p id="vvv_provision_fail" style="display:none"><strong>Problem:</strong> Could not load the site, this implies that provisioning the site failed, please check there were no errors during provisioning, and reprovision.<br><br>
<em><strong>Note</strong>, sometimes this is because provisioning hasn't finished yet, if it's still running, wait and refresh the page.</em> If that doesn't fix the issue, <a href="https://varyingvagrantvagrants.org/docs/en-US/troubleshooting/">see here for troubleshooting steps</a></p>
<p id="vvv_logo">
<span class="v1">__     _</span><span class="v2">__     _</span><span class="v3">__     __</span> <span class="v4"> ____  </span>
<span class="v1">\ \   / </span><span class="v2">\ \   / </span><span class="v3">\ \   / /</span> <span class="v4">|___ \ </span>
<span class="v1"> \ \ / /</span><span class="v2"> \ \ / /</span><span class="v3"> \ \ / / </span> <span class="v4">  __) |</span>
<span class="v1">  \ V / </span><span class="v2">  \ V / </span><span class="v3">  \ V /  </span> <span class="v4"> / __/ </span>
<span class="v1">   \_/  </span><span class="v2">   \_/  </span><span class="v3">   \_/   </span> <span class="v4">|_____|</span>

</p>

<p><strong>Varying Vagrant Vagrants</strong></p>

<div>
</div>
<ul class="nav">
	<li class="active"><a href="#">Home</a></li>
	<li><a href="https://varyingvagrantvagrants.org/" target="_blank">Documentation</a></li>
	<li><a href="https://github.com/varying-vagrant-vagrants/vvv/" target="_blank">Repository</a></li>
</ul>

<h3>Bundled Tools</h3>
<ul class="nav">
	<li><a href="database-admin/" target="_blank">phpMyAdmin</a></li>
	<li><a href="memcached-admin/" target="_blank">phpMemcachedAdmin</a></li>
	<li><a href="opcache-status/opcache.php" target="_blank">Opcache Status</a></li>
	<li><a href="http://vvv.dev:1080" target="_blank">Mailcatcher</a></li>
	<li><a href="webgrind/" target="_blank">Webgrind</a></li>
	<li><a href="phpinfo/" target="_blank">PHP Info</a></li>
	<li><a href="php-status?html&amp;full" target="_blank">PHP Status</a></li>
</ul>

<h3>Bundled Environments</h3>

<ul class="nav">
	<li><a href="http://local.wordpress.dev/" target="_blank">http://local.wordpress.dev</a> for WordPress stable (www/wordpress-default)</li>
	<li><a href="http://src.wordpress-develop.dev/" target="_blank">http://src.wordpress-develop.dev</a> for trunk WordPress development files (www/wordpress-develop/src)</li>
	<li><a href="http://build.wordpress-develop.dev/" target="_blank">http://build.wordpress-develop.dev</a> for a Grunt build of those development files (www/wordpress-develop/build)</li>
</ul>
<p>Want to add your own site? <a href="https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/">Learn how to add a new site in VVV</a></p>
<script>
// If it's not vvv.dev then this site has failed to provision, let the user know
if ( location.hostname != "vvv.dev" ){
	var notice = document.getElementById( 'vvv_provision_fail' );
	notice.style.display = 'block';
}
</script>
</body>
</html>
