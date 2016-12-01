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
		'create_posts'           => 'create_talks',
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
 * Get Raters capabilities
 *
 * @since  1.0.0
 *
 * @return array The list of caps for the rater roles.
 */
function wct_get_rater_caps() {
	$caps = array(
		'view_other_profiles' => true,
		'comment_talks'       => true,
		'rate_talks'          => true,
		'view_talk_rates'     => true,
		'view_talk_comments'  => true,
	);

	$post_type_object = get_post_type_object( wct_get_post_type() );

	if ( ! empty( $post_type_object->cap ) ) {
		$caps = array_merge( $caps, array(
			$post_type_object->cap->publish_posts      => false,
			$post_type_object->cap->read_post          => true,
			$post_type_object->cap->read_private_posts => true,
		) );
	}

	/**
	 * Filter here to edit rater caps.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $caps The list of caps for the rater roles.
	 */
	return apply_filters( 'wct_get_rater_caps', $caps );
}

/**
 * Register Rater roles.
 *
 * @since  1.0.0
 */
function wct_register_roles() {
	$rater       = get_role( 'rater' );
	$blind_rater = get_role( 'blind_rater' );
	$juror       = get_role( 'juror' );

	$caps = array(
		'read' => true,
	);

	if ( is_null( $rater ) || is_null( $blind_rater || is_null( $juror ) ) ) {
		$caps = $caps + wct_get_rater_caps();
	}

	// Create the role if not already done.
	if ( is_null( $rater ) ) {
		add_role( 'rater', __( 'Rater', 'wordcamp-talks' ), $caps );
	}

	// Create the role if not already done.
	if ( is_null( $blind_rater ) ) {
		$blind_rater_caps = array_diff_key( $caps, array(
			'view_other_profiles' => false,
			'view_talk_rates'     => false,
			'view_talk_comments'  => false,
		) );

		add_role( 'blind_rater', __( 'Blind Rater', 'wordcamp-talks' ), $blind_rater_caps );
	}

	if ( is_null( $juror ) ) {
		$juror_caps = array_fill_keys( wct_get_post_type_caps(), true );
		$juror_caps = array_merge( $caps, $juror_caps, array(
			'assign_talk_categories' => true,
			'assign_talk_tags'       => true,
			'select_talks'           => true,
		) );

		add_role( 'juror', __( 'Juror', 'wordcamp-talks' ), $juror_caps );
	}
}
add_action( 'wct_admin_init', 'wct_register_roles' );

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
	$user = wp_get_current_user();

	if ( $user->ID !== $user_id && $user_id ) {
		$user = get_user_by( 'id', $user_id );
	}

	// The user has the cap set (rater, blind_rater or juror)
	if ( isset( $user->allcaps[ $cap ] ) ) {
		if ( ! $user->allcaps[ $cap ] ) {
			$caps = array( 'do_not_allow' );
		}

	// For any other cases, use the caps mapping!
	} else {
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
			case 'select_talks'         :
			case 'create_talks'         :
				$caps = array( 'manage_options' );
				break;

			case 'comment_talks'       :
			case 'rate_talks'          :
			case 'view_talk_rates'     :
			case 'view_talk_comments'  :
				if ( 'private' === wct_default_talk_status() ) {
					$caps = array( 'manage_options' );
				} elseif ( 'rate_talks' === $cap && empty( $user_id ) ) {
					$caps = array( 'do_not_allow' );
				} else {
					$caps = array( 'exist' );
				}

				break;

			case 'view_other_profiles' :
				if ( 'private' === wct_default_talk_status() ) {
					$caps = array( 'manage_options' );

					if ( ! empty( $args[0] ) && ! empty( $user_id ) && (int) $args[0] === (int) $user_id ) {
						$caps = array( 'exist' );
					}

				} else {
					$caps = array( 'exist' );
				}

				break;

			case 'edit_comment' :

				// Get the comment
				$_comment = get_comment( $args[0] );

				if ( ! is_admin() ) {

					if ( ! empty( $_comment ) && wct_get_post_type() == get_post_type( $_comment->comment_post_ID ) ) {
						$caps = array( 'manage_options' );
					}

				// Specific case for jurors.
				} elseif ( isset( $user->allcaps[ 'juror' ] ) ) {
					if ( ! empty( $_comment ) && (int) $_comment->user_id !== (int) $user->ID ) {
						$caps = array( 'do_not_allow' );
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
