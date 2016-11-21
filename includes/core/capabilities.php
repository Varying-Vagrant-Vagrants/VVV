<?php
/**
 * WordCamp Talks Capabilities.
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return Talks post type capabilities
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 *
 * @return array Talks capabilities
 */
function wct_get_post_type_caps() {
	return apply_filters( 'wct_get_post_type_caps', array (
		'edit_post'              => 'edit_talk',
		'read_post'              => 'read_talk',
		'delete_post'            => 'delete_talk',
		'edit_posts'             => 'edit_talks',
		'edit_others_posts'      => 'edit_others_talks',
		'publish_posts'          => 'publish_talks',
		'read_private_posts'     => 'read_private_talks',
		'delete_posts'           => 'delete_talks',
		'delete_private_posts'   => 'delete_private_talks',
		'delete_published_posts' => 'delete_published_talks',
		'delete_others_posts'    => 'delete_others_talks',
		'edit_private_posts'     => 'edit_private_talks',
		'edit_published_posts'   => 'edit_published_talks',
	) );
}

/**
 * Return Talks tag capabilities
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 *
 * @return array Talks tag capabilities
 */
function wct_get_tag_caps() {
	return apply_filters( 'wct_get_tag_caps', array (
		'manage_terms' => 'manage_talk_tags',
		'edit_terms'   => 'edit_talk_tags',
		'delete_terms' => 'delete_talk_tags',
		'assign_terms' => 'assign_talk_tags'
	) );
}

/**
 * Return Talks category capabilities
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 *
 * @return array Talks category capabilities
 */
function wct_get_category_caps() {
	return apply_filters( 'wct_get_category_caps', array (
		'manage_terms' => 'manage_talk_categories',
		'edit_terms'   => 'edit_talk_categories',
		'delete_terms' => 'delete_talk_categories',
		'assign_terms' => 'assign_talk_categories'
	) );
}

/**
 * Maps Talks capabilities
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 *
 * @param  array $caps Capabilities for meta capability
 * @param  string $cap Capability name
 * @param  int $user_id User id
 * @param  mixed $args Arguments
 * @return array Actual capabilities for meta capability
 */
function wct_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		case 'publish_talks' :
			if ( ! empty( $user_id ) ) {
				$closing   = (int) wct_get_closing_date( true );
				$is_closed = false;

				// No closing date defined, free to post if you can!
				if ( ! empty( $closing ) ) {
					$now = strtotime( date_i18n( 'Y-m-d H:i' ) );

					if ( $closing < $now ) {
						$is_closed = true;
					}
				}

				if ( ! $is_closed ) {
					$caps = array( 'exist' );
				} else {
					$caps = array( 'manage_options' );
				}

			} else {
				$caps = array( 'manage_options' );
			}

			break;

		case 'read_talk' :
		case 'edit_talk' :

			// Get the post
			$_post = get_post( $args[0] );

			if ( ! empty( $_post ) ) {

				$caps = array();

				if ( ! is_admin() && ( (int) $user_id === (int) $_post->post_author ) ) {
					$caps = array( 'exist' );

				// Unknown, so map to manage_options
				} else {
					$caps[] = 'manage_options';
				}
			}

			break;

		case 'edit_talks'           :
		case 'edit_others_talks'    :
		case 'edit_private_talks'   :
		case 'edit_published_talks' :
		case 'read_private_talks'   :
			$caps = array( 'manage_options' );
			break;

		case 'comment_talks'       :
		case 'rate_talks'          :
			if ( 'private' === wct_default_talk_status() ) {
				$caps = array( 'manage_options' );
			} elseif ( 'rate_talks' === $cap && empty( $user_id ) ) {
				$caps = array( 'do_not_allow' );
			} else {
				$caps = array( 'exist' );
			}

			break;

		case 'view_other_profiles' :
			if ( 'private' === wct_default_talk_status() && ! wct_is_current_user_profile() ) {
				$caps = array( 'manage_options' );
			} else {
				$caps = array( 'exist' );
			}

			break;

		case 'edit_comment' :

			if ( ! is_admin() ) {

				// Get the comment
				$_comment = get_comment( $args[0] );

				if ( ! empty( $_comment ) && wct_get_post_type() == get_post_type( $_comment->comment_post_ID ) ) {
					$caps = array( 'manage_options' );
				}
			}

			break;

		case 'delete_talk'            :
		case 'delete_talks'           :
		case 'delete_others_talks'    :
		case 'delete_private_talks'   :
		case 'delete_published_talks' :
			$caps = array( 'manage_options' );
			break;

		/** Taxonomies ****************************************************************/

		case 'manage_talk_tags'       :
		case 'edit_talk_tags'         :
		case 'delete_talk_tags'       :
		case 'manage_talk_categories' :
		case 'edit_talk_categories'   :
		case 'delete_talk_categories' :
			$caps = array( 'manage_options' );
			break;

		// Open to all users that have an ID
		case 'assign_talk_tags'       :
		case 'assign_talk_categories' :
			if ( ! empty( $user_id ) ) {
				$caps = array( 'exist' );
			} else {
				$caps = array( 'manage_options' );
			}
			break;

		/** Admin *********************************************************************/

		case 'wct_talks_admin' :
			$caps = array( 'manage_options' );
			break;
	}

	/**
	 * @param  array $caps Capabilities for meta capability
	 * @param  string $cap Capability name
	 * @param  int $user_id User id
	 * @param  mixed $args Arguments
	 */
	return apply_filters( 'wct_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Check wether a user has the capability to perform an action
 *
 * @package WordCamp Talks
 * @subpackage core/capabilities
 *
 * @since 1.0.0
 *
 * @param  string $capability Capability to check
 * @param  array  $args additional args to help
 * @return bool True|False
 */
function wct_user_can( $capability = '', $args = false ) {
	$can = false;

	if ( ! empty( $args ) ) {
		$can = current_user_can( $capability, $args );
	} else {
		$can = current_user_can( $capability );
	}

	return apply_filters( 'wct_user_can', $can, $capability );
}
