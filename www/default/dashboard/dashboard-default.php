<!DOCTYPE html>
<html>
	<head>
		<title>Varying Vagrant Vagrants Dashboard</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="//vvv.test/dashboard/style.css?t=<?php echo intval( filemtime( __DIR__.'/style.css' ) ); ?>">
	</head>
	<body>
		<h2 id="vvv_logo">
			<img src="//vvv.test/dashboard/vvv-tight.png"/> Varying Vagrant Vagrants
		</h2>
		<?php
		require_once( __DIR__ . '/php/notices.php' );
		require_once( __DIR__ . '/php/blocks/intro.php' );
		?>
		<div class="grid">
			<div class="column left-column">
				<?php
				require_once( __DIR__ . '/php/blocks/main/bundled-environments-intro.php' );
				require_once( __DIR__ . '/php/blocks/main/sites.php' );
				require_once( __DIR__ . '/php/blocks/main/adding-site-doc.php' );
				?>
			</div>
			<div class="column right-column">
				<?php
				require_once( __DIR__ . '/php/blocks/sidebar/search-docs.php' );
				require_once( __DIR__ . '/php/blocks/sidebar/find-out-more-vvv.php' );
				require_once( __DIR__ . '/php/blocks/sidebar/bundled-tools.php' );
				require_once( __DIR__ . '/php/blocks/sidebar/vvv1.php' );
				require_once( __DIR__ . '/php/blocks/sidebar/contribute-wp.php' );
				require_once( __DIR__ . '/php/blocks/sidebar/terminal-power.php' );
				?>
			</div>
		</div>
	</body>
</html>
