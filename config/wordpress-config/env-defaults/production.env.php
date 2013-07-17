<?php
return call_user_func(function () {

	$_env = array(
		'DB_HOST' => '__DB_HOST__',
		'DB_NAME' => '__DB_NAME__',
		'DB_USER' => '__DB_USER__',
		'DB_PASSWORD' => '__DB_PASSWORD__',

		'memcached_servers' => array(
			'__MEMCACHED_SERVER__',
		),

		'WP_CACHE' => true,
		'batcache' => array(
			// Expire batcache items aged this many seconds (zero to disable batcache)
			'max_age' => 300,
			// Send stale-if-error Cache-Control directive for Squid
			'stale_if_error' => 3600,
			// Zero disables sending buffers to remote datacenters (req/sec is never sent)
			'remote' => 0,
			// Only batcache a page after it is accessed this many times... (two or more)
			'times' => 2,
			// ...in this many seconds (zero to ignore this and use batcache immediately)
			'seconds' => 120,
			// Name of memcached group. You can simulate a cache flush by changing this.
			'group' => 'batcache',
			// If you conditionally serve different content, put the variable values here.
			'unique' => array(),
			// Add headers here. These will be sent with every response from the cache.
			'headers' => array(),
			// Set true to enable redirect caching.
			'cache_redirects' => false,
			// This is set to the response code during a redirect.
			'redirect_status' => false,
			// This is set to the redirect location.
			'redirect_location' => false,
			// These headers will never be cached. Apply strtolower.
			'uncached_headers' => array('transfer-encoding'),
			// Set false to hide the batcache info <!-- comment -->
			'debug' => true,
			// Set false to disable Last-Modified and Cache-Control headers
			'cache_control' => true,
			// Change this to cancel the output buffer. Use batcache_cancel();
			'cancel' => false,
		),

		'WP_DEBUG' => false,
		'SCRIPT_DEBUG' => false,

		'CONCATENATE_SCRIPTS' => true,
		'COMPRESS_SCRIPTS' => true,
		'COMPRESS_CSS' => true,
		'SAVEQUERIES' => false,
		'DISABLE_WP_CRON' => true, // System should be pinging  cron is now pinging wp-cron.php regularly so WP Cron spawning is not needed.

		// Highly recommended!!
		#'FORCE_SSL_LOGIN' => true,
		#'FORCE_SSL_ADMIN' => true,
	);

	// @todo The assoc merges here need to be recursive

	$default_config_path = __DIR__ . '/default.env.php';
	if ( file_exists( $local_config_path ) ) {
		$_env = array_merge( require( $default_config_path ), $_env );
	}

	$overrides_config_path = __DIR__ . '/' . str_replace('.env.php', '-overrides.env.php', basename( __FILE__ ));
	if ( file_exists( $overrides_config_path ) ) {
		$_env = array_merge( $_env, require( $overrides_config_path ) );
	}

	return $_env;
});
