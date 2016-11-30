<?php
/**
 * WordCamp Talks Options.
 *
 * List of options used to customize the plugins
 * @see  admin/settings
 *
 * Mainly inspired by bbPress way of dealing with options
 * @see bbpress/includes/core/options.php
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the default plugin's options and their values.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return array Filtered option names and values
 */
function wct_get_default_options() {
	// Default options

	$default_options = array(

		/** DB Version ********************************************************/
		'_wc_talks_version' => wct_get_version(),

		/** Core Settings **********************************************************/
		'_wc_talks_archive_title'       => 'Talks',
		'_wc_talks_closing_date'        => '',
		'_wc_talks_submit_status'       => 'private',
		'_wc_talks_editor_image'        => 1,
		'_wc_talks_editor_link'         => 1,
		'_wc_talks_moderation_message'  => '',
		'_wc_talks_login_message'       => '',
		'_wc_talks_hint_list'           => array(),
		'_wc_talks_private_fields_list' => array(),
		'_wc_talks_public_fields_list'  => array(),
		'_wc_talks_signup_fields'       => array(),
		'_wc_talks_sticky_talks'        => 1,
		'_wc_talks_disjoin_comments'    => 1,
		'_wc_talks_allow_comments'      => 1,
		'_wc_talks_embed_profile'       => 0,
		'_wc_talks_featured_images'     => 1,
		'_wc_talks_to_rate_disabled'    => 0,
		'_wc_talks_autolog_enabled'     => 0,
	);

	// Pretty links customization
	if ( wct_is_pretty_links() ) {
		$default_options = array_merge( $default_options, array(
			'_wc_talks_root_slug'          => _x( 'talks', 'default root slug', 'wordcamp-talks' ),
			'_wc_talks_talk_slug'          => _x( 'talk', 'default talk slug', 'wordcamp-talks' ),
			'_wc_talks_category_slug'      => _x( 'category', 'default category slug', 'wordcamp-talks' ),
			'_wc_talks_tag_slug'           => _x( 'tag', 'default tag slug', 'wordcamp-talks' ),
			'_wc_talks_user_slug'          => _x( 'user', 'default user slug', 'wordcamp-talks' ),
			'_wc_talks_user_comments_slug' => _x( 'comments', 'default comments slug', 'wordcamp-talks' ),
			'_wc_talks_user_rates_slug'    => _x( 'ratings', 'default ratings slug', 'wordcamp-talks' ),
			'_wc_talks_user_to_rate_slug'  => _x( 'to-rate', 'default to rate slug', 'wordcamp-talks' ),
			'_wc_talks_signup_slug'        => _x( 'sign-up', 'default sign-up action slug', 'wordcamp-talks' ),
			'_wc_talks_action_slug'        => _x( 'action', 'default action slug', 'wordcamp-talks' ),
			'_wc_talks_addnew_slug'        => _x( 'add', 'default add talk action slug', 'wordcamp-talks' ),
			'_wc_talks_edit_slug'          => _x( 'edit', 'default edit talk action slug', 'wordcamp-talks' ),
			'_wc_talks_cpage_slug'         => _x( 'cpage', 'default comments pagination slug', 'wordcamp-talks' ),
		) );
	}

	// Multisite options
	if ( is_multisite() ) {
		$default_options = array_merge( $default_options, array(
			'_wc_talks_allow_signups'     => 0,
			'_wc_talks_user_default_role' => 0,
		) );
	}

	/**
	 * @param  array $default_options list of options
	 */
	return apply_filters( 'wct_get_default_options', $default_options );
}

/**
 * Add default plugin's options
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 */
function wct_add_options() {

	// Add default options
	foreach ( wct_get_default_options() as $key => $value ) {
		add_option( $key, $value );
	}

	// Allow plugins to append their own options.
	do_action( 'wct_add_options' );
}

/**
 * Main archive page title
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string          default value or customized one
 */
function wct_archive_title( $default = 'Talks' ) {
	return apply_filters( 'wct_archive_title', get_option( '_wc_talks_archive_title', $default ) );
}

/**
 * Gets the timestamp or mysql date closing limit
 *
 * @since 1.0.0
 *
 * @param  bool $timestamp true to get the timestamp
 * @return mixed int|string timestamp or mysql date closing limit
 */
function wct_get_closing_date( $timestamp = false ) {
	$closing = get_option( '_wc_talks_closing_date', '' );

	if ( ! empty( $timestamp ) ) {
		return $closing;
	}

	if ( is_numeric( $closing ) ) {
		$closing = date_i18n( 'Y-m-d H:i', $closing );
	}

	return $closing;
}

/**
 * Default publishing status (private/publish/pending)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string default value or customized one
 */
function wct_default_talk_status( $default = 'private' ) {
	$default_status = get_option( '_wc_talks_submit_status', $default );

	// Make sure admins will have a publish status whatever the settings choice
	if ( 'pending' === $default_status && wct_user_can( 'wct_talks_admin' ) ) {
		$wct            = wct();
		$current_screen = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
		}

		// In administration screens we need to be able to change the status
		if ( empty( $wct->admin->is_plugin_settings ) && ( empty( $current_screen->post_type ) || wct_get_post_type() !== $current_screen->post_type ) ) {
			$default_status = 'publish';
		}
	}

	/**
	 * @param  string $default_status
	 */
	return apply_filters( 'wct_default_talk_status', $default_status );
}

/**
 * Should the editor include the add image url button ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_talk_editor_image( $default = 1 ) {
	return (bool) apply_filters( 'wct_talk_editor_image(', (bool) get_option( '_wc_talks_editor_image', $default ) );
}

/**
 * Should the editor include the add/remove link buttons ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_talk_editor_link( $default = 1 ) {
	return (bool) apply_filters( 'wct_talk_editor_link', (bool) get_option( '_wc_talks_editor_link', $default ) );
}

/**
 * Use a custom moderation message ?
 *
 * This option depends on the default publish status one. If pending
 * is set, it will be possible to customize a moderation message.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the moderation message
 */
function wct_moderation_message( $default = '' ) {
	return apply_filters( 'wct_moderation_message', get_option( '_wc_talks_moderation_message', $default ) );
}

/**
 * Use a custom login message ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the moderation message
 */
function wct_login_message( $default = '' ) {
	return apply_filters( 'wct_login_message', get_option( '_wc_talks_login_message', $default ) );
}

/**
 * Use a custom captions for rating stars ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default default value
 * @return array        the list of rating stars captions.
 */
function wct_hint_list( $default = array() ) {
	if ( ! $default ) {
		$default = array( 'poor', 'good', 'great' );
	}

	return apply_filters( 'wct_hint_list', get_option( '_wc_talks_hint_list', $default ) );
}

/**
 * Are Private profile fields set?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_private_fields_list( $default = array() ) {
	return (array) apply_filters( 'wct_user_private_fields_list', get_option( '_wc_talks_private_fields_list', $default ) );
}

/**
 * Are Public profile fields set?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of private profile fields.
 */
function wct_user_public_fields_list( $default = array() ) {
	return (array) apply_filters( 'wct_user_public_fields_list', get_option( '_wc_talks_public_fields_list', $default ) );
}

/**
 * Get the signup fields.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  array $default Default value
 * @return array          The list of fields to display into the signup form.
 */
function wct_user_signup_fields( $default = array() ) {
	return (array) apply_filters( 'wct_user_signup_fields', get_option( '_wc_talks_signup_fields', $default ) );
}

/**
 * Should the user be automagically logged in after a successful signup ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_user_autolog_after_signup( $default = 0 ) {
	return (bool) apply_filters( 'wct_user_autolog_after_signup', (bool) get_option( '_wc_talks_autolog_enabled', $default ) );
}

/**
 * Do talks can be stick to the front of first archive page ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_is_sticky_enabled( $default = 1 ) {
	return (bool) apply_filters( 'wct_is_sticky_enabled', (bool) get_option( '_wc_talks_sticky_talks', $default ) );
}

/**
 * Should we disjoin comments about talks from regular comments ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_is_comments_disjoined( $default = 1 ) {
	return (bool) apply_filters( 'wct_is_comments_disjoined', (bool) get_option( '_wc_talks_disjoin_comments', $default ) );
}

/**
 * Are comments about talks globally allowed
 *
 * Thanks to this option, plugin will be able to neutralize comments about
 * talks without having to rely on WordPress discussion settings. If this
 * option is enabled, it's still possible from the edit Administration screen
 * of the talk to neutralize for each specific talk the comments.
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_is_comments_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wct_is_comments_allowed', (bool) get_option( '_wc_talks_allow_comments', $default ) );
}

/**
 * Can profile be embed ?
 *
 * @since 1.0.0
 *
 * @param  bool $default default value
 * @return bool          The id of the Page Utility if enabled, 0 otherwise
 */
function wct_is_embed_profile( $default = 0 ) {
	return (int) apply_filters( 'wct_is_embed_profile', get_option( '_wc_talks_embed_profile', $default ) );
}

/**
 * Featured images for talks ?
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_featured_images_allowed( $default = 1 ) {
	return (bool) apply_filters( 'wct_featured_images_allowed', (bool) get_option( '_wc_talks_featured_images', $default ) );
}

/**
 * Is user's to rate profile tab disabled ?
 *
 * @since 1.0.0
 *
 * @param  int        $default        default value
 * @param  null|bool  $rates_disabled Whether built-in rating system is disabled or not.
 * @return bool                       True if disabled, false otherwise.
 */
function wct_is_user_to_rate_disabled( $default = 0, $rates_disabled = null ) {
	if ( is_null( $rates_disabled ) ) {
		$rates_disabled = wct_is_rating_disabled();
	}

	if ( $rates_disabled ) {
		return true;
	}

	return (bool) apply_filters( 'wct_is_user_to_rate_disabled', (bool) get_option( '_wc_talks_to_rate_disabled', $default ) );
}

/**
 * Customize the root slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the root slug
 */
function wct_root_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'talks', 'default root slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_root_slug', get_option( '_wc_talks_root_slug', $default ) );
}

/**
 * Build the talk slug (root + talk ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the talk slug (prefixed by the root one)
 */
function wct_talk_slug() {
	return apply_filters( 'wct_talk_slug', wct_root_slug() . '/' . wct_get_talk_slug() );
}

	/**
	 * Customize the talk (post type) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default default value
	 * @return string       the talk slug
	 */
	function wct_get_talk_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'talk', 'default talk slug', 'wordcamp-talks' );
		}

		return apply_filters( 'wct_get_talk_slug', get_option( '_wc_talks_talk_slug', $default ) );
	}

/**
 * Build the category slug (root + category ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string the category slug (prefixed by the root one)
 */
function wct_category_slug() {
	return apply_filters( 'wct_category_slug', wct_root_slug() . '/' . wct_get_category_slug() );
}

	/**
	 * Customize the category (hierarchical taxonomy) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default default value
	 * @return string       the category slug
	 */
	function wct_get_category_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'category', 'default category slug', 'wordcamp-talks' );
		}

		return apply_filters( 'wct_get_category_slug', get_option( '_wc_talks_category_slug', $default ) );
	}

/**
 * Build the tag slug (root + tag ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the tag slug (prefixed by the root one)
 */
function wct_tag_slug() {
	return apply_filters( 'wct_tag_slug', wct_root_slug() . '/' . wct_get_tag_slug() );
}

	/**
	 * Customize the tag (non hierarchical taxonomy) slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default default value
	 * @return string          the tag slug
	 */
	function wct_get_tag_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'tag', 'default tag slug', 'wordcamp-talks' );
		}

		return apply_filters( 'wct_get_tag_slug', get_option( '_wc_talks_tag_slug', $default ) );
	}

/**
 * Build the user's profile slug (root + user ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string the user slug (prefixed by the root one)
 */
function wct_user_slug() {
	return apply_filters( 'wct_user_slug', wct_root_slug() . '/' . wct_get_user_slug() );
}

	/**
	 * Customize the user's profile slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default default value
	 * @return string       the user slug
	 */
	function wct_get_user_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'user', 'default user slug', 'wordcamp-talks' );
		}

		return apply_filters( 'wct_get_user_slug', get_option( '_wc_talks_user_slug', $default ) );
	}

/**
 * Customize the user's profile rates slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the user's profile rates slug
 */
function wct_user_rates_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'ratings', 'default ratings slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_user_rates_slug', get_option( '_wc_talks_user_rates_slug', $default ) );
}

/**
 * Customize the user's profile to rate slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the user's profile to rate slug
 */
function wct_user_to_rate_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'to-rate', 'default user to rate slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_user_to_rate_slug', get_option( '_wc_talks_user_to_rate_slug', $default ) );
}

/**
 * Customize the user's profile talks section slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string          the user's profile talks section slug
 */
function wct_user_talks_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = wct_root_slug();
	}

	return apply_filters( 'wct_user_talks_slug', $default );
}

/**
 * Customize the user's profile comments slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the user's profile comments slug
 */
function wct_user_comments_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'comments', 'default comments slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_user_comments_slug', get_option( '_wc_talks_user_comments_slug', $default ) );
}

/**
 * Build the action slug (root + action ones)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @return string       the action slug (prefixed by the root one)
 */
function wct_action_slug() {
	return apply_filters( 'wct_action_slug', wct_root_slug() . '/' . wct_get_action_slug() );
}

	/**
	 * Customize the action slug of the plugin
	 *
	 * @package WordCamp Talks
	 * @subpackage core/options
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default default value
	 * @return string       the action slug
	 */
	function wct_get_action_slug( $default = '' ) {
		if ( empty( $default ) ) {
			$default = _x( 'action', 'default action slug', 'wordcamp-talks' );
		}

		return apply_filters( 'wct_get_action_slug', get_option( '_wc_talks_action_slug', $default ) );
	}

/**
 * Customize the add (action) slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the add (action) slug
 */
function wct_addnew_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'add', 'default add talk action slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_addnew_slug', get_option( '_wc_talks_addnew_slug', $default ) );
}

/**
 * Customize the edit (action) slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the add (action) slug
 */
function wct_edit_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'edit', 'default edit talk action slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_edit_slug', get_option( '_wc_talks_edit_slug', $default ) );
}

/**
 * Build the signup slug (root + signup one)
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string the default signup slug
 * @return string the signup slug (prefixed by the root one)
 */
function wct_signup_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'sign-up', 'default sign-up action slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_signup_slug', get_option( '_wc_talks_signup_slug', $default ) );
}

/**
 * Customize the comment pagination slug of the plugin in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  string $default default value
 * @return string       the comment pagination slug
 */
function wct_cpage_slug( $default = '' ) {
	if ( empty( $default ) ) {
		$default = _x( 'cpage', 'default comments pagination slug', 'wordcamp-talks' );
	}

	return apply_filters( 'wct_cpage_slug', get_option( '_wc_talks_cpage_slug', $default ) );
}

/**
 * Should the plugin manage signups for the blog?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_allow_signups( $default = 0 ) {
	return (bool) apply_filters( 'wct_allow_signups', get_option( '_wc_talks_allow_signups', $default ) );
}

/**
 * Should we make sure the user posting an talk on the site has the default role ?
 *
 * @package WordCamp Talks
 * @subpackage core/options
 *
 * @since 1.0.0
 *
 * @param  int $default default value
 * @return bool         True if enabled, false otherwise
 */
function wct_get_user_default_role( $default = 0 ) {
	return (bool) apply_filters( 'wct_get_user_default_role', get_option( '_wc_talks_user_default_role', $default ) );
}
