<?php
return call_user_func(function () {

	$_env = array(
		'DB_NAME' => '__DB_NAME__',
		'DB_USER' => '__DB_USER__',
		'DB_PASSWORD' => '__DB_PASSWORD__',

		'memcached_servers' => array(
			'127.0.0.1:11211',
		),

		'WP_CACHE' => false,
		'batcache' => array(),

		'WP_DEBUG' => true,
		'SCRIPT_DEBUG' => true,

		'CONCATENATE_SCRIPTS' => false,
		'COMPRESS_SCRIPTS' => false,
		'COMPRESS_CSS' => false,
		'SAVEQUERIES' => true,
		'DISABLE_WP_CRON' => false, // use traditional wp-cron; we can really slam our system if all sites get pinged every minute

	);

	// @todo The assoc merges here need to be recursive

	$default_config_path = __DIR__ . '/default.env.php';
	if ( file_exists( $default_config_path ) ) {
		$_env = array_merge( require( $default_config_path ), $_env );
	}

	$overrides_config_path = __DIR__ . '/' . str_replace('.env.php', '-overrides.env.php', basename( __FILE__ ));
	if ( file_exists( $overrides_config_path ) ) {
		$_env = array_merge( $_env, require( $overrides_config_path ) );
	}

	return $_env;
});
