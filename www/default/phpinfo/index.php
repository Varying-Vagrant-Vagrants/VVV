<?php
/**
 * Output the phpinfo for our setup
 */
?>
<nav class="center">
	<a href="//vvv.test/phpinfo/">PHP Info (v<?php echo phpversion(); ?>)</a>
	<?php
	if ( function_exists( 'xdebug_info' ) ) {
		?>&middot; <a href="//vvv.test/xdebuginfo/">Xdebug Info (v<?php echo phpversion('xdebug'); ?>)</a><?php
	}
	?>
</nav>
<hr/>
<?php
echo '<div id="phpinfo">';
phpinfo();
echo '</div>';
