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

// Allow cross origin access, for Ajax check for IP addresses.
header( 'Access-Control-Allow-Origin: *' );
?>
<!DOCTYPE html>
<html>
<head>
	<title>Varying Vagrant Vagrants Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		.hide-me { display: none; }
	</style>
</head>
<body>
<ul class="nav">
	<li class="active"><a href="#">Home</a></li>
	<li><a href="https://github.com/varying-vagrant-vagrants/vvv/">Repository</a></li>
	<li><a href="database-admin/">phpMyAdmin</a></li>
	<li><a href="memcached-admin/">phpMemcachedAdmin</a></li>
	<li><a href="opcache-status/opcache.php">Opcache Status</a></li>
	<li><a href="http://vvv.dev:1080">Mailcatcher</a></li>
	<li><a href="webgrind/">Webgrind</a></li>
	<li><a href="phpinfo/">PHP Info</a></li>
	<li><a href="php-status?html&amp;full">PHP Status</a></li>
</ul>

<ul class="nav">
	<li><a href="http://local.wordpress.dev/">http://local.wordpress.dev</a> for WordPress stable (www/wordpress-default)</li>
	<li><a href="http://src.wordpress-develop.dev/">http://src.wordpress-develop.dev</a> for trunk WordPress development files (www/wordpress-develop/src)</li>
	<li><a href="http://build.wordpress-develop.dev/">http://build.wordpress-develop.dev</a> for a Grunt build of those development files (www/wordpress-develop/build)</li>
</ul>

<div>
	<h2>IP Addresses that load VVV</h2>

	<ul class="nav detected-ip-addresses">
		<?php $ips = vvv_ip_addresses_from_within_vagrant(); ?>
		<?php foreach ( $ips as $ip ) : ?>

			<li class="hide-me">
				<a href="http://<?php echo $ip; ?>">
					<?php echo $ip; ?>
				</a>
			</li>

		<?php endforeach; ?>
	</ul>


<script src="js/jquery-2.1.4.min.js"></script>
<script>
jQuery(document).ready( function($) {

	var titleTagOnVVVDashboard = 'Varying Vagrant Vagrants Dashboard';

	var lis = $('.detected-ip-addresses>li');

	$(lis).each( function( i, li ) {
		hideLiWithNonResolvingUrl( li );
	});

	function hideLiWithNonResolvingUrl( li ) {
		var $li = $(li),
			url = $li.find('>a').attr('href');

		$.ajax({
			'url': url,
			'success': function( responseHtml ) {
				if ( doesVVVTitleTagAppearInContent( responseHtml ) ) {
					show( $li );
				} else {
					hide( $li );
				}
			},
			'error': function() {
				// page failed to load
				hide( $li );
			}
		});
	}

	function doesVVVTitleTagAppearInContent( html ) {
		var titleTag = $( html ).filter( 'title' ).text();
		return titleTagOnVVVDashboard === titleTag;
	}

	function hide( $li ) {
		$li.addClass('hide-me');
	}
	function show( $li ) {
		$li.removeClass('hide-me');
	}
});
</script>

</body>
</html>
<?php
/**
 * Find possible IP addresses from within Vagrant
 *
 * @return string[] List of IP addresses found within Vagrant using ifconfig
 */
function vvv_ip_addresses_from_within_vagrant() {
	$ips = array();

	$ifconfig_output = shell_exec( '/sbin/ifconfig' );

	$ifconfig_output_array = explode( "\n", $ifconfig_output );

	$adapter = false;
	foreach ( $ifconfig_output_array as $ifconfig_output_line ) {
		if (
			0 < strpos( $ifconfig_output_line, 'inet addr:' ) // String contains "inet addr:".
		) {
			$pattern = '/inet\saddr:(\d{1,3}\.\d{1,3}\.\d{1,3}.\d{1,3})/';
			preg_match( $pattern, $ifconfig_output_line, $matches );
			// $matches[1] will contain the IP address
			$ips[] = $matches[1];
		}
	}

	return $ips;
}
