<div id="vvv_provision_fail" class="top-notice box" style="display:none">
	<p><strong>Problem:</strong> Could not load the site, this implies that provisioning the site failed, please check there were no errors during provisioning, and reprovision.</p>
	<p><em><strong>Note</strong>, sometimes this is because provisioning hasn't finished yet, if it's still running, wait and refresh the page.</em> If that doesn't fix the issue, re-read our docs on adding sites, check the syntax of all your provisioner files, and double check our troubleshooting page:</p>
	<p><a class="button" href="https://varyingvagrantvagrants.org/docs/en-US/troubleshooting/">Troubleshooting</a> <a href="https://varyingvagrantvagrants.org/docs/en-US/adding-a-new-site/" class="button">Adding a Site</a>
</div>
<div id="vvv_hosts_fail" class="top-notice box" style="display:none">
	<p><strong>Info:</strong> It appears you've accessed the dashboard via the IP, you should visit <a href="http://vvv.test">http://vvv.test</a>, but if this isn't working, make sure you've installed the hosts updater vagrant plugin</p>
	<p>If you're trying to access a site, you need to visit the host/domain given to the site if one was set.</p>
	<p><a href="http://vvv.test" class="button">Visit the Dashboard</a></p>
</div>

<?php
if ( ! file_exists('/vagrant/vvv-custom.yml') ) {
	?>
	<div id="vvv_custom_missing" class="top-notice-box box">
		<p><strong>Super Important:</strong> You need to copy <code>vvv-config.yml</code> to <code>vvv-custom.yml</code> or your changes will be destroyed when you update!</p>
	</div>
	<?php
}
?>

<script>
// If it's not vvv.test then this site has failed to provision, let the user know
// also notify if the dashboard is being shown on the raw IP
if ( location.hostname.indexOf( "192.168") !== -1 ) {
	var notice = document.getElementById( 'vvv_hosts_fail' );
	notice.style.display = 'block';
} else if ( ( location.hostname != "vvv.dev" )
	&& ( location.hostname != "vvv.test" )
	&& ( location.hostname != "vvv.local" )
	&& ( location.hostname != "vvv.localhost" ) )
{
	var notice = document.getElementById( 'vvv_provision_fail' );
	notice.style.display = 'block';
}
</script>