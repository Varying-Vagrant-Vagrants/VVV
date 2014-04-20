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
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Varying Vagrant Vagrants Dashboard</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/jumbotron-narrow.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
<div class="container">
	<div class="header">
		<ul class="nav nav-pills pull-right">
			<li><a href="https://github.com/Varying-Vagrant-Vagrants/VVV">Github</a></li>
			<li><a href="https://github.com/Varying-Vagrant-Vagrants/VVV/wiki">Documentation</a></li>
		</ul>
		<h3 class="text-muted">VVV</h3>
	</div>

	<h3>Bundled Sites</h3>
	<div class="row marketing">
		<div class="col-lg-6">
			<h4><a href="https://github.com/varying-vagrant-vagrants/vvv/">Repository</a></h4>
			<p>VVV on github</p>

			<h4><a href="database-admin/">phpMyAdmin</a></h4>
			<p>MySQL Database access</p>

			<h4><a href="memcached-admin/">phpMemcachedAdmin</a></h4>
			<p>Graphic stand-alone administration for memcached to monitor and debug purpose</p>

			<h4><a href="opcache-status/opcache.php">Opcache Status</a></h4>
			<p>A one-page opcache status page for the PHP opcode cache.</p>

			<h4><a href="webgrind/">Webgrind</a></h4>
			<p>Webgrind is an Xdebug profiling web frontend in PHP5.</p>

			<h4><a href="phpinfo/">PHP Info</a></h4>
			<p>Useful information about this server</p>
		</div>
		<div class="col-lg-6">
			<h4><a href="http://local.wordpress.dev/">http://local.wordpress.dev</a></h4>
			<p>for WordPress stable (/www/wordpress-default)</p>

			<h4><a href="http://local.wordpress-trunk.dev/">http://local.wordpress-trunk.dev</a></h4>
			<p>for WordPress trunk (/www/wordpress-trunk)</p>

			<h4><a href="http://src.wordpress-develop.dev/">http://src.wordpress-develop.dev</a></h4>
			<p>for trunk WordPress development files (/www/wordpress-developer/src)</p>

			<h4><a href="http://build.wordpress-develop.dev/">http://build.wordpress-develop.dev</a></h4>
			<p>for a Grunt build of those development files (/www/wordpress-developer/build)</p>
		</div>

		<?php

		$directory = "../";

		//get all files in specified directory
		$files = glob($directory . "*");

		$sites = array();

		//print each file name
		foreach ( $files as $file) {
			//check to see if the file is a folder/directory
			if ( is_dir( $file ) ) {
				if ( is_file( $file.'/vvv-hosts' ) ) {
					$sites [] = array(
						'folder' => $file,
						'hosts' => file_get_contents( $file.'/vvv-hosts' )
					);
				}
			}
		}

		$columns = array();
		$columns[] = array_splice( $sites, 0, count( $sites )/2 );
		$columns[] = array_splice( $sites, count( $sites )/2 );
		?>
	</div>
	<h3>Sites</h3>
	<div class="row marketing">
		<?php
		foreach ( $columns as $c ) {
			?>
			<div class="col-lg-6">
				<?php
				foreach ( $c as $install ) {
					$links = array();
					echo '<h4>'.str_replace('../', '', $install['folder'] ).'</h4>';
					echo '<p>';
					$sites = explode( "\n",$install['hosts']);
					foreach ( $sites as $site ) {
						$site = trim( $site );
						// filter out comments
						$hashpos = strpos( $site, '#' );
						if ( $hashpos !== false) {
							$site = substr( $site, 0, $hashpos );
						}
						if ( !empty( $site ) ) {
							$links[] = '<a href="http://'.$site.'">'.$site.'</a>';
						}
					}
					echo implode( ', ', $links );
					echo '</p>';
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
