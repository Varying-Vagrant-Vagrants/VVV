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

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );

    return $length === 0 || 
    ( substr( $haystack, -$length ) === $needle );
}

require( __DIR__. '/dashboard/yaml.php' );

// Begin default dashboard.
?>
<!DOCTYPE html>
<html>
<head>
	<title>Varying Vagrant Vagrants Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="//vvv.test/dashboard/style.css?t=<?php echo filemtime('dashboard/style.css' ); ?>">
</head>
<body>

<h2 id="vvv_logo"><img src="//vvv.test/dashboard/vvv-tight.png"/> Varying Vagrant Vagrants</h2>

<?php require_once( 'dashboard/dashboard-notices.php' ); ?>
<div class="box alt-box">
	<p>VVV is a local web development environment powered by Vagrant and Virtual Machines.</p>
	<p>To add, remove, or change sites, modify <code>vvv-custom.yml</code> then reprovision using <code>vagrant reload --provision</code></p>
</div>
<div class="grid">
	<div class="column left-column">
		
		<div class="box">
			<h3>Bundled Environments</h3>
			<p>VVV reads a config file to discover and provision sites named <code>vvv-custom.yml</code>. If it doesn't exist, it falls back to <code>vvv-config.yml</code>.
			<?php if ( ! file_exists('/vagrant/vvv-custom.yml') ): ?>
				<b><code>vvv-custom.yml</code> does not exist! Please copy <code>vvv-config.yml</code> to <code>vvv-custom.yml</code></b>
			<?php else: ?>
				Below is a list of the sites in <code>vvv-custom.yml</code>, remember to reprovision if you change it!
			<?php endif; ?>
		</div>
		<div class="grid50">
			<?php
			$yaml = new Alchemy\Component\Yaml\Yaml();

			$data = (file_exists('/vagrant/vvv-custom.yml')) ? $yaml->load('/vagrant/vvv-custom.yml') : $yaml->load('/vagrant/vvv-config.yml');
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
				<div class="box <?php echo implode( ',', $classes ); ?>">
					<h4><?php
					echo $name;
					if ( true == $skip_provisioning ) {
						echo ' <a target="_blank" href="https://varyingvagrantvagrants.org/docs/en-US/vvv-config/#skip_provisioning"><small class="site_badge">provisioning skipped</small></a>';
					}
					?></h4>
					<p><?php echo $description; ?></p>
					<p><strong>URL:</strong> <?php
					$has_dev = false;
					if ( !empty( $site['hosts'] ) ) {
						foreach( $site['hosts'] as $host ) {
							?>
							<a href="<?php echo 'http://'.$host; ?>" target="_blank"><?php echo 'http://'.$host; ?></a>,
							<?php
							if ( $has_dev ){
								continue;
							}
							$has_dev = endsWith( $host, '.dev' );
						}
					}
					?><br/>
					<strong>Folder:</strong> <code>www/<?php echo $name;?></code></p>
					<?php if ( $has_dev ) {
						?>
					<p class="warning"><strong>Warning:</strong> the <code>.dev</code> TLD is owned by Google, you should migrate to <code>.test</code></p>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>
		</div>
		<div class="box alt-box">
			<h3>Adding a New Site</h3>
			<p>Modify <code>vvv-custom.yml</code> under the sites section to add a site, here's an example:</p>
<pre>
  newsite:
    repo: https://github.com/Varying-Vagrant-Vagrants/custom-site-template
    description: "A WordPress subdir multisite install"
    skip_provisioning: false
    hosts:
      - newsite.test
    custom:
      wp_type: subdirectory
</pre>
			<p>This will create a site in <code>www/newsite</code> at <code>http://newsite.test</code></p>
			<p><em>Remember</em>, in YAML whitespace matters, and you need to reprovision on changes, so run <code>vagrant reload --provision</code></p>
			<p>For more information, visit our docs:</p>
			<a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/">How to add a new site</a></p>
		</div>
	</div>
	<div class="column right-column">
		<div class="box">
			<h3>Search the Documentation</h3>
			<form method="get" action="https://varyingvagrantvagrants.org/search/" >
				<input type="text" name="q" placeholder="search query"/>
				<input type="submit" value="Search"/>
			</form>
		</div>
		<div class="box">
			<h3>Find out more about VVV</h3>
			<a class="button" href="https://varyingvagrantvagrants.org/" target="_blank">Help &amp; Documentation</a>
			<a class="button" href="https://github.com/varying-vagrant-vagrants/vvv/" target="_blank">View the code on GitHub</a>
		</div>

		<div class="box">
			<h3>Bundled Tools</h3>

			<a class="button" href="database-admin/" target="_blank">phpMyAdmin</a>
			<a class="button" href="memcached-admin/" target="_blank">phpMemcachedAdmin</a>
			<a class="button" href="opcache-status/opcache.php" target="_blank">Opcache Status</a>
			<a class="button" href="http://vvv.test:1080" target="_blank">Mailcatcher</a>
			<a class="button" href="webgrind/" target="_blank">Webgrind</a>
			<a class="button" href="phpinfo/" target="_blank">PHP Info</a>
			<a class="button" href="php-status?html&amp;full" target="_blank">PHP Status</a>
		</div>
		<div class="box">
			<h3>VVV 1.x Sites not Showing?</h3>
			<p>Sites need to be listed in <code>vvv-custom.yml</code> for VVV to find them, luckily it's super easy and fast to add them back! click below to find out how to migrate your sites.</p>
			<a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/migrating-from-vvv-1-4-x/">Migrating VVV 1 sites</a>
		</div>
		<div class="box">
			<h3>Contribute to WordPress</h3>
			<p>Whether you're at a contributor day, or just feel like giving back, you can add the WordPress.org Meta environment. This will give you everything from WordCamp to buddypress.org test sites</p>
			<a class="button" href="https://github.com/WordPress/meta-environment">Find out more</a>
		</div>
		<div class="box">
			<h3>Terminal Power!</h3>
			<p>VVV has powerful commands preinstalled, if you need WP CLI or PHP Codesniffer, run <code>vagrant ssh</code> to enter the virtual machine, and get a full command line experience</p>
		</div>
	</div>
</div>
</body>
</html>
