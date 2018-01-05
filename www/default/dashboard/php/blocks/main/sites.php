<?php

require( __DIR__ . '/../../yaml.php' );
function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );

    return $length === 0 || 
    ( substr( $haystack, -$length ) === $needle );
}

?>
<div class="grid50">
	<?php
	$yaml = new Alchemy\Component\Yaml\Yaml();

	$data = $yaml->load( (file_exists('/vagrant/vvv-custom.yml')) ? '/vagrant/vvv-custom.yml' : '/vagrant/vvv-config.yml' );
	foreach ( $data['sites'] as $name => $site ) {

		$classes = [];
		$description = 'A WordPress installation';
		if ( 'wordpress-default' === $name ) {
			$description = 'WordPress stable';
		} else if ( 'wordpress-develop' === $name ) {
			$description = 'A dev build of WordPress, with a trunk build in the <code>src</code> subfolder, and a grunt build in the <code>build</code> folder';
		}
		if ( !empty( $site['description'] ) ) {
			$description = $site['description'];
		}
		$skip_provisioning = false;
		if ( !empty( $site['skip_provisioning'] ) ) {
			$skip_provisioning = $site['skip_provisioning'];
			$classes[] = 'site_skip_provision';
		}
		?>
		<div class="box <?php echo strip_tags( implode( ',', $classes ) ); ?>">
			<h4><?php
			echo strip_tags( $name );
			if ( true == $skip_provisioning ) {
				echo ' <a target="_blank" href="https://varyingvagrantvagrants.org/docs/en-US/vvv-config/#skip_provisioning"><small class="site_badge">provisioning skipped</small></a>';
			}
			?></h4>
			<p><?php echo strip_tags( $description ); ?></p>
			<p><strong>URL:</strong> <?php
			$has_dev = false;
			$has_local = false;
			if ( !empty( $site['hosts'] ) ) {
				foreach( $site['hosts'] as $host ) {
					?>
					<a href="<?php echo 'http://'.$host; ?>" target="_blank"><?php echo 'http://'.$host; ?></a>,
					<?php
					if ( false === $has_dev ){
						$has_dev = endsWith( $host, '.dev' );
					}
					if ( false === $has_local ){
						$has_local = endsWith( $host, '.local' );
					}
				}
			}
			?><br/>
			<strong>Folder:</strong> <code>www/<?php echo strip_tags( $name ); ?></code></p>
			<?php
			$warnings = [];
			if ( $has_dev ) {
				$warnings[] = '
				<p><strong>Warning:</strong> the <code>.dev</code> TLD is owned by Google, and will not work in Chrome 58+, you should migrate to URLs ending with <code>.test</code></p>';
			}
			if ( $has_local ) {
				$warnings[] = '
				<p><strong>Warning:</strong> the <code>.local</code> TLD is used by Macs/Bonjour/Zeroconf as quick access to a local machine, this can cause clashes that prevent the loading of sites in VVV. E.g. a MacBook named <code>test</code> can be reached at <code>test.local</code>. You should migrate to URLs ending with <code>.test</code></p>';
			}
			if ( $has_dev || $has_local ) {
				$warnings[] = '<p><a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/troubleshooting/dev-tld/">Click here for instructions for switching to .test</a></p>';
			}
			if ( ! empty( $warnings ) ) {
				echo '<div class="warning">';
				echo implode( '', $warnings );
				echo '</div>';
			}
			?>
		</div>
		<?php
	}
	?>
</div>