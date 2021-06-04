<?php
/**
 * Output the phpinfo for our setup
 */
?>
<style type="text/css">
body {
    color: #222;
    font-family: sans-serif;
}

hr {
    width: 934px;
    background-color: #ccc;
    border: 0;
    height: 1px;
}

.center {
	text-align: center;
}

.warning {
	text-align: center;
}
</style>
<nav class="center">
	<a href="//vvv.test/phpinfo/">PHP Info</a>
	<?php
	if ( function_exists( 'xdebug_info' ) ) {
		?>&middot; <a href="//vvv.test/xdebuginfo/">Xdebug Info</a><?php
	}
	?>
</nav>
<hr/>

<div id="xdebuginfo">
	<?php
	if ( function_exists( 'xdebug_info' ) ) {
		xdebug_info();
	} else {
		echo '<div class="warning"><strong>Warning:</strong> Xdebug not enabled.</div>';
	}
	?>
</div>
