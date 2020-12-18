<?php
/**
 * Output the phpinfo for our setup
 */
?>
<nav class="center">
	<a href="#phpinfo">PHP Info</a>
	<?php
	if ( function_exists( 'xdebug_info' ) ) {
		?>, <a href="#xdebuginfo">XDebug Info</a><?php
	}
	?>
</nav>
<hr/>
<?php
echo '<div id="phpinfo">';
phpinfo();
echo '</div>';

if ( function_exists( 'xdebug_info' ) ) {
	echo '<hr/><div id="xdebuginfo">';
	xdebug_info();
	echo '</div>';
}
