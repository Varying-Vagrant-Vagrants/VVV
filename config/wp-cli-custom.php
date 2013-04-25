<?php

/**
 * Custom WP-CLI commands
 *
 * Usage (from within a WP directory):
 * $ wp --require=/path/to/wp-cli-custom.php vvv [function name] [parameters]
 *
 * @package wp-cli
 */
class VVV_Command extends WP_CLI_Command {

	/**
	 * Alternate method for setting up the official test suite
	 * using the current WordPress instance. Uses git instead of svn.
	 *
	 * Based on wp-cli 0.9.1
	 *
	 * @subcommand init-tests
	 *
	 * @synopsis [<path>] --dbname=<name> --dbuser=<user> [--dbpass=<password>]
	 */
	function init_tests( $args, $assoc_args ) {
		if ( isset( $args[0] ) )
			$tests_dir = trailingslashit( $args[0] );
		else
			$tests_dir = ABSPATH . 'unit-tests/';

		//WP_CLI::launch( 'svn co https://unit-test.svn.wordpress.org/trunk/ ' . escapeshellarg( $tests_dir ) );
		WP_CLI::launch( 'git clone -v https://github.com/kurtpayne/wordpress-unit-tests.git ' . escapeshellarg( $tests_dir ) );

		$config_file = file_get_contents( $tests_dir . 'wp-tests-config-sample.php' );

		$replacements = array(
			"dirname( __FILE__ ) . '/wordpress/'" => "'" . ABSPATH . "'",
			"yourdbnamehere" => $assoc_args['dbname'],
			"yourusernamehere" => $assoc_args['dbuser'],
			"yourpasswordhere" => isset( $assoc_args['dbpass'] ) ? $assoc_args['dbpass'] : ''
		);

		$config_file = str_replace( array_keys( $replacements ), array_values( $replacements ), $config_file );

		$config_file_path = $tests_dir . 'wp-tests-config.php';

		file_put_contents( $config_file_path, $config_file );

		WP_CLI::success( "Created $config_file_path" );
	}

}

WP_CLI::add_command( 'vvv', 'VVV_Command' );