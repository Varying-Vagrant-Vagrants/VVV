<?php
/**
 * WordCamp Talks Rewrites.
 *
 * Mainly inspired by bbPress way of dealing with rewrites
 * @see bbpress main class.
 *
 * Most of the job is done in the class WordCamp_Talks_Rewrites
 * @see  core/classes
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Checks whether the current site is using default permalink settings or custom one
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return bool True if custom permalink are one, false otherwise
 */
function wct_is_pretty_links() {
	$pretty_links = wct_get_global( 'pretty_links' );
	return (bool) apply_filters( 'wct_is_pretty_links', ! empty( $pretty_links ) );
}

/**
 * Get the slug used for paginated requests
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 *
 * @global object $wp_rewrite The WP_Rewrite object
 * @return string The pagination slug
 */
function wct_paged_slug() {
	global $wp_rewrite;

	if ( empty( $wp_rewrite ) ) {
		return false;
	}

	return $wp_rewrite->pagination_base;
}

/**
 * Rewrite id for the user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string The user's profile rewrite id
 */
function wct_user_rewrite_id( $default = 'is_user' ) {
	return apply_filters( 'wct_user_rewrite_id', $default );
}

/**
 * Rewrite id for the user's rates
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string The user's rates rewrite id
 */
function wct_user_rates_rewrite_id( $default = 'is_rates' ) {
	return apply_filters( 'wct_user_rates_rewrite_id', $default );
}

/**
 * Rewrite id for the user's to rate
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string The user's to rate rewrite id
 */
function wct_user_to_rate_rewrite_id( $default = 'is_to_rate' ) {
	return apply_filters( 'wct_user_to_rate_rewrite_id', $default );
}

/**
 * Rewrite id for the user's comments
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string The user's comments rewrite id
 */
function wct_user_comments_rewrite_id( $default = 'is_comments' ) {
	return apply_filters( 'wct_user_comments_rewrite_id', $default );
}

/**
 * Rewrite id for actions
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string The actions rewrite id
 */
function wct_action_rewrite_id( $default = 'is_action' ) {
	return apply_filters( 'wct_action_rewrite_id', $default );
}

/**
 * Rewrite id for searching in talks
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string Searching in talks rewrite id
 */
function wct_search_rewrite_id( $default = 'talk_search' ) {
	return apply_filters( 'wct_search_rewrite_id', $default );
}

/**
 * Rewrite id for user's comments pagination
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 * 
 * @return string User's comments pagination rewrite id
 */
function wct_cpage_rewrite_id( $default = 'cpaged' ) {
	return apply_filters( 'wct_cpage_rewrite_id', $default );
}

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @package WordCamp Talks
 * @subpackage core/rewrites
 *
 * @since 1.0.0
 */
function wct_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}
