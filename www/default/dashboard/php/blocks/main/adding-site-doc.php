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