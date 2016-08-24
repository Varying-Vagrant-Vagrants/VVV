<?php
/**
 * WordCamp Talks Upgrade functions.
 *
 * @package WordCamp Talks
 * @subpackage core/upgrade
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Compares the current plugin version to the DB one to check if it's an upgrade
 *
 * @package WordCamp Talks
 * @subpackage core/upgrade
 *
 * @since 1.0.0
 * 
 * @return bool True if update, False if not
 */
function wct_is_upgrade() {
	$db_version     = wct_db_version();
	$plugin_version = wct_get_version();

	return (bool) version_compare( $db_version, $plugin_version, '<' );
}

/**
 * Checks if an upgrade is needed
 *
 * @package WordCamp Talks
 * @subpackage core/upgrade
 *
 * @since 1.0.0
 */
function wct_maybe_upgrade() {
	// Bail if no update needed
	if ( ! wct_is_upgrade() ) {
		return;
	}

	// Let's upgrade!
	wct_upgrade();
}

/**
 * Upgrade routine
 *
 * @package WordCamp Talks
 * @subpackage core/upgrade
 *
 * @since 1.0.0
 */
function wct_upgrade() {
	$db_version = wct_db_version();

	if ( ! empty( $db_version ) ) {
		if ( version_compare( $db_version, '1.0.0', '<' ) ) {

			wct_add_options();

		}

		update_option( '_wc_talks_version', wct_get_version() );

	// It's a new install
	} else {
		wct_install();
	}

	// Force a rewrite rules reset
	wct_delete_rewrite_rules();
}

/**
 * First install routine
 *
 * @since 1.0.0
 */
function wct_install() {
	/**
	 * Filter here if you need to init options in DB
	 *
	 * @since 1.0.0
	 *
	 * @param array $value list of options to init on install
	 */
	$init_options = apply_filters( 'wct_install_init_options', wct_get_default_options()  );

	foreach ( $init_options as $key => $value ) {
		add_option( $key, $value );
	}

	/**
	 * Hook here if you need to perform actions when plugin
	 * is installed for the first time
	 *
	 * @since 1.0.0
	 */
	do_action( 'wct_installed' );
}
