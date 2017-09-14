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

require( __DIR__. '/yaml.php' );

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

<div class="grid">
	<div class="column">
		<div class="box">
			<h2>Varying Vagrant Vagrants</h2>
			<p>VVV is a local web development environment powered by Vagrant and Virtual Machines.</p>
			<p>To add, remove, or change sites, modify <code>vvv-custom.yml</code> then reprovision using <code>vagrant reload --provision</code></p>
		</div>
		<div class="box">
			<h3>Bundled Environments</h3>
			<p>VVV reads a config file to discover and provision sites named <code>vvv-custom.yml</code>. If it doesn't exist, it falls back to <code>vvv-config.yml</code>. Below is a list of the sites in <code>vvv-custom.yml</code>, remember to reprovision if you change it!</p>
		</div>
		<div class="grid50">
			<?php
			$yaml = new Alchemy\Component\Yaml\Yaml();
			$data = $yaml->load('/vagrant/vvv-custom.yml');
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
				<div class="column box <?php echo implode( ',', $classes ); ?>">
					<h4><?php
					echo $name;
					if ( true == $skip_provisioning ) {
						echo '<br><a target="_blank" href="https://varyingvagrantvagrants.org/docs/en-US/vvv-config/#skip_provisioning"><small class="site_badge">skipped</small></a>';
					}
					?></h4>
					<p><?php echo $description; ?></p>
					<p><strong>URL:</strong> <a href="<?php echo 'http://'.$site['hosts'][0]; ?>" target="_blank"><?php echo 'http://'.$site['hosts'][0]; ?></a><br/>
					<strong>Folder:</strong> <code>www/<?php echo $name;?></code></p>
				</div>
				<?php
			}
			//yaml_parse_file( '' );
			?>
		</div>
		<div class="box">
			<h3>Adding a New Site</h3>
			<p>Modify <code>vvv-custom.yml</code> under the sites section to add a site, here's an example:</p>
<pre>
  # Add a new WordPress single install
  newsite:
    repo: https://github.com/Varying-Vagrant-Vagrants/custom-site-template
    skip_provisioning: false
    hosts:
      - newsite.localhost
</pre>
			<p>This will create a site in <code>www/newsite</code> at <code>http://newsite.localhost</code></p>
			<p><em>Remember</em>, in YAML whitespace matters, and you need to reprovision on changes, so run <code>vagrant reload --provision</code></p>
			<p>For more information, visit our docs:</p>
			<a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/">How to add a new site</a></p>
		</div>
	</div>
	<div class="column">
		<?php /*
		<div class="box">
			<h3>Search the Documentation</h3>
			<form method="get" action="https://tomjn.github.io/varyingvagrantvagrants.org/search/" >
				<input type="text" name="q" placeholder="search query"/>
				<input type="submit" value="Search"/>
			</form>
		</div> */ ?>
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
			<a class="button" href="http://vvv.dev:1080" target="_blank">Mailcatcher</a>
			<a class="button" href="webgrind/" target="_blank">Webgrind</a>
			<a class="button" href="phpinfo/" target="_blank">PHP Info</a>
			<a class="button" href="php-status?html&amp;full" target="_blank">PHP Status</a>
		</div>
		<div class="box">
			<h3>VVV 1.x Sites not Showing?</h3>
			<p>Sites need to be listed in <code>vvv-custom.yml</code> for VVV to find them, luckily it's super easy and fast to add them back! click below to find out how to migrate your sites.</p>
			<a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/migrate-vvv-1/">Migrating VVV 1 sites</a>
		</div>
	</div>
</div>


<script>
// If it's not vvv.dev then this site has failed to provision, let the user know
if ( location.hostname != "vvv.dev" ){
	var notice = document.getElementById( 'vvv_provision_fail' );
	notice.style.display = 'block';
}
</script>
</body>
</html>
