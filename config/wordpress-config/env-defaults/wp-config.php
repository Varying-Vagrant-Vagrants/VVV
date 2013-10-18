<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// Note we store the configs in arrays so they can be merged and extended
// UPPERCASE array keys correspond to constants, and lowercase keys are global variables

$active_env = trim( file_get_contents( __DIR__ . '/../config/active-env' ) );
if ( empty( $active_env ) || ! preg_match( '/^\w+$/', $active_env ) ) {
	header( 'HTTP/1.0 500 Server Misconfiguration' );
	die( 'Missing or invalid active-env' );
}

$config = include( sprintf( '%s/../config/%s.env.php', __DIR__, $active_env ) );
if ( empty( $config ) ) {
	header( 'HTTP/1.0 500 Server Misconfiguration' );
	die( 'Missing or empty config for active-env' );
}
foreach ( $config as $config_key => $config_value ) {
	if ( strtoupper( $config_key ) === $config_key ) {
		define( $config_key, $config_value );
	}
	else {
		$GLOBALS[$config_key] = $config_value;
	}
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
