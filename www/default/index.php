<?php
/**
 * If a custom dashboard file exists, load that instead of the default
 * dashboard provided by Varying Vagrant Vagrants. This file should be
 * located in the `www/default/` directory.
 */
if ( file_exists( 'dashboard-custom.php' ) ) {
	include 'dashboard-custom.php';
} else if ( file_exists( 'dashboard/dashboard-default.php' ) ) {
	include 'dashboard/dashboard-default.php';
} else{
	?>
<h2>VVV</h2>
<p>VVV hasn't finished provisioning, and the dashboard hasn't been set up yet. Come back in a minute or two</p>
	<?php
}
