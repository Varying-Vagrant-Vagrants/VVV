<?php
/**
 * WordCamp Talks Users functions.
 *
 * Functions specific to users
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Set/Get User Datas **********************************************************/

/**
 * Gets current user ID
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return int the logged in user ID
 */
function wct_users_current_user_id() {
	return (int) wct()->current_user->ID;
}

/**
 * Gets current user user nicename
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return string the logged in username
 */
function wct_users_current_user_nicename() {
	return wct()->current_user->user_nicename;
}

/**
 * Gets displayed user ID
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return int the displayed user ID
 */
function wct_users_displayed_user_id() {
	return (int) apply_filters( 'wct_users_displayed_user_id', wct()->displayed_user->ID );
}

/**
 * Gets displayed user user nicename
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return string the displayed user username
 */
function wct_users_get_displayed_user_username() {
	return apply_filters( 'wct_users_get_displayed_user_username', wct()->displayed_user->user_nicename );
}

/**
 * Gets displayed user display name
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return string the displayed user display name
 */
function wct_users_get_displayed_user_displayname() {
	return apply_filters( 'wct_users_get_displayed_user_displayname', wct()->displayed_user->display_name );
}

/**
 * Gets displayed user description
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return string the displayed user description
 */
function wct_users_get_displayed_user_description() {
	return apply_filters( 'wct_users_get_displayed_user_description', wct()->displayed_user->description );
}

/**
 * Gets one specific or all attribute about a user
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 * 
 * @return mixed WP_User/string/array/int the user object or one of its attribute
 */
function wct_users_get_user_data( $field = '', $value ='', $attribute = 'all'  ) {
	$user = get_user_by( $field, $value );

	if ( empty( $user ) ) {
		return false;
	}

	if ( 'all' == $attribute ) {
		return $user;
	} else {
		return $user->{$attribute};
	}
}

/** User urls *****************************************************************/

/**
 * Gets the displayed user's profile url
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param  string $type profile, rates, comments
 * @return string url of the profile type
 */
function wct_users_get_displayed_profile_url( $type = 'profile' ) {
	$user_id = wct_users_displayed_user_id();
	$username = wct_users_get_displayed_user_username();

	$profile_url = call_user_func_array( 'wct_users_get_user_' . $type . '_url', array( $user_id, $username ) );

	/**
	 * @param  string $profile_url url to the profile part
	 * @param  string $type the requested part (profile, rates or comments)
	 */
	return apply_filters( 'wct_users_get_displayed_profile_url', $profile_url, $type );
}

/**
 * Gets the logged in user's profile url
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param  string $type profile, rates, comments
 * @return string url of the profile type
 */
function wct_users_get_logged_in_profile_url( $type = 'profile' ) {
	$user_id = wct_users_current_user_id();
	$username = wct_users_current_user_nicename();

	$profile_url = call_user_func_array( 'wct_users_get_user_' . $type . '_url', array( $user_id, $username ) );

	/**
	 * @param  string $profile_url url to the profile part
	 * @param  string $type the requested part (profile, rates or comments)
	 */
	return apply_filters( 'wct_users_get_logged_in_profile_url', $profile_url, $type );
}

/**
 * Gets URL to the main profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @return string User profile url
 */
function wct_users_get_user_profile_url( $user_id = 0, $user_nicename = '', $nofilter = false ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	if ( false === $nofilter ) {
		/**
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		$early_profile_url = apply_filters( 'wct_users_pre_get_user_profile_url', (int) $user_id, $user_nicename );
		if ( is_string( $early_profile_url ) ) {
			return $early_profile_url;
		}
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wct_user_slug() . '/%' . wct_user_rewrite_id() . '%';

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wct_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wct_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wct_user_rewrite_id() => $user_id ), home_url( '/' ) );
	}

	if ( false === $nofilter ) {
		/**
		 * Filter the user profile url once it has been built
		 *
		 * @param string $url           Profile Url
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wct_users_get_user_profile_url', $url, $user_id, $user_nicename );
	} else {
		return $url;
	}
}

/**
 * Gets URL to the rates profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @return string Rates profile url
 */
function wct_users_get_user_rates_url( $user_id = 0, $user_nicename = '' ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	$early_profile_url = apply_filters( 'wct_users_pre_get_user_rates_url', (int) $user_id, $user_nicename );
	if ( is_string( $early_profile_url ) ) {
		return $early_profile_url;
	}


	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wct_user_slug() . '/%' . wct_user_rewrite_id() . '%/' . wct_user_rates_slug();

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wct_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wct_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wct_user_rewrite_id() => $user_id, wct_user_rates_rewrite_id() => '1' ), home_url( '/' ) );
	}

	/**
	 * Filter the rates profile url once it has been built.
	 *
	 * @param string $url           Rates profile Url
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	return apply_filters( 'wct_users_get_user_rates_url', $url, $user_id, $user_nicename );
}

/**
 * Gets URL to the comments profile page of a user
 *
 * Inspired by bbPress's bbp_get_user_profile_url() function
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global $wp_rewrite
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @return string Comments profile url
 */
function wct_users_get_user_comments_url( $user_id = 0, $user_nicename = '' ) {
	global $wp_rewrite;

	// Bail if no user id provided
	if ( empty( $user_id ) ) {
		return false;
	}

	/**
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	$early_profile_url = apply_filters( 'wct_users_pre_get_user_comments_url', (int) $user_id, $user_nicename );
	if ( is_string( $early_profile_url ) ) {
		return $early_profile_url;
	}


	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wct_user_slug() . '/%' . wct_user_rewrite_id() . '%/' . wct_user_comments_slug();

		// Get username if not passed
		if ( empty( $user_nicename ) ) {
			$user_nicename = wct_users_get_user_data( 'id', $user_id, 'user_nicename' );
		}

		$url = str_replace( '%' . wct_user_rewrite_id() . '%', $user_nicename, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wct_user_rewrite_id() => $user_id, wct_user_comments_rewrite_id() => '1' ), home_url( '/' ) );
	}

	/**
	 * Filter the comments profile url once it has been built.
	 *
	 * @param string $url           Rates profile Url
	 * @param int    $user_id       the user ID
	 * @param string $user_nicename the username
	 */
	return apply_filters( 'wct_users_get_user_comments_url', $url, $user_id, $user_nicename );
}

/**
 * Gets the signup url
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global  $wp_rewrite
 * @return string signup url
 */
function wct_users_get_signup_url() {
	global $wp_rewrite;

	/**
	 * Early filter to override form url before being built
	 *
	 * @param mixed false or url to override
	 */
	$early_signup_url = apply_filters( 'wct_users_pre_get_signup_url', false );

	if ( ! empty( $early_signup_url ) ) {
		return $early_signup_url;
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$signup_url = $wp_rewrite->root . wct_action_slug() . '/%' . wct_action_rewrite_id() . '%';

		$signup_url = str_replace( '%' . wct_action_rewrite_id() . '%', wct_signup_slug(), $signup_url );
		$signup_url = home_url( user_trailingslashit( $signup_url ) );

	// Unpretty permalinks
	} else {
		$signup_url = add_query_arg( array( wct_action_rewrite_id() => wct_signup_slug() ), home_url( '/' ) );
	}

	/**
	 * Filter to override form url after being built
	 *
	 * @param string url to override
	 */
	return apply_filters( 'wct_get_form_url', $signup_url );
}

/** Template functions ********************************************************/

/**
 * Enqueues Users description editing scripts
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */
function wct_users_enqueue_scripts() {
	if ( ! wct_is_user_profile() ) {
		return;
	}

	// Viewing another user's profile with no sharing dialog box doesn't need js.
	if ( ! wct_is_current_user_profile() && ! wct_is_embed_profile() ) {
		return;
	}

	$js_vars = array(
		'is_profile' => 1,
	);

	if ( wct_is_current_user_profile() ) {
		$js_vars['profile_editing'] = 1;
	}

	wp_enqueue_script ( 'wc-talks-script', wct_get_js_script( 'script' ), array( 'jquery' ), wct_get_version(), true );
	wp_localize_script( 'wc-talks-script', 'wct_vars', apply_filters( 'wct_users_current_profile_script', $js_vars ) );
}

/**
 * Builds user's profile nav
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param  int $user_id User id
 * @param  string $user_nicename Optional. User nicename
 * @param  bool $nofilter. Whether to fire filters or not.
 * @return array the nav items organized in an associative array
 */
function wct_users_get_profile_nav_items( $user_id = 0, $username ='', $nofilter = false ) {
	// Bail if no id or username are provided.
	if ( empty( $user_id ) || empty( $username ) ) {
		return array();
	}

	$nav_items = array(
		'profile' => array(
			'title'   => __( 'Published', 'wordcamp-talks' ),
			'url'     => wct_users_get_user_profile_url( $user_id, $username ),
			'current' => wct_is_user_profile_talks(),
			'slug'    => sanitize_title( _x( 'talks', 'user talks profile slug for BuddyPress use', 'wordcamp-talks' ) ),
		),
		'comments' => array(
			'title'   => __( 'Commented', 'wordcamp-talks' ),
			'url'     => wct_users_get_user_comments_url( $user_id, $username ),
			'current' => wct_is_user_profile_comments(),
			'slug'    => wct_user_comments_slug(),
		),
	);

	if ( ! wct_is_rating_disabled() ) {
		$nav_items['rates'] = array(
			'title'   => __( 'Rated', 'wordcamp-talks' ),
			'url'     => wct_users_get_user_rates_url( $user_id, $username ),
			'current' => wct_is_user_profile_rates(),
			'slug'    => wct_user_rates_slug(),
		);
	}

	if ( false === $nofilter ) {
		/**
		 * Filter the available user's profile nav items
		 *
		 * @param array  $nav_items     the nav items
		 * @param int    $user_id       the user ID
		 * @param string $username the username
		 */
		return apply_filters( 'wct_users_get_profile_nav_items', $nav_items, $user_id, $username );
	} else {
		return $nav_items;
	}
}

/** Handle User actions *******************************************************/

/**
 * Edit User's profile description
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */
function wct_users_profile_description_update() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct_profile'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_update_description', '_wpis_nonce' );

	$user_id = wct_users_displayed_user_id();

	// Capbility checks
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Capbility checks
	if ( (int) get_current_user_id() !== (int) $user_id ) {
		return;
	}

	$redirect = wct_users_get_user_profile_url( $user_id, wct_users_get_displayed_user_username() );

	$user_description = str_replace( array( '<div>', '</div>'), "\n", $_POST['wct_profile']['description'] );
	$user_description = rtrim( $user_description, "\n" );

	if ( empty( $user_description ) ) {
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'Please enter some content in your description', 'wordcamp-talks' ),
		) );

		wp_safe_redirect( $redirect );
		exit();
	}

	// Remove all html tags
	$user_description = wp_kses( wp_specialchars_decode( $user_description ), array() );

	if ( ! update_user_meta( $user_id, 'description', $user_description ) ) {
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'Something went wrong while trying to update your description.', 'wordcamp-talks' ),
		) );

		wp_safe_redirect( $redirect );
		exit();
	} else {
		wct_add_message( array(
			'type'    => 'success',
			'content' => __( 'Description updated.', 'wordcamp-talks' ),
		) );

		/**
		 * @param int    $user_id          the user ID
		 * @param string $user_description the user description ("about field")
		 */
		do_action( 'wct_users_profile_description_updated', $user_id, $user_description );

		wp_safe_redirect( $redirect );
		exit();
	}
}

/**
 * Hooks to deleted_user to perform additional actions
 *
 * When a user is deleted, we need to be sure the talks he shared are also
 * deleted to avoid troubles in edit screens as the post author field will found
 * no user. I also remove rates.
 *
 * The main problem here (excepting error notices) is ownership of the talk. To avoid any
 * troubles, deleting when user leaves seems to be the safest. If you have a different point
 * of view, you can remove_action( 'deleted_user', 'wct_users_delete_user_data', 10, 1 )
 * and use a different way of managing this. I advise you to make sure talks are reattributed to
 * an existing user ID. About rates, there's no problem if a non existing user ID is in the rating
 * list of an talk.
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */
function wct_users_delete_user_data( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return;
	}

	// Make sure we don't miss any talks
	add_filter( 'wct_talks_get_status', 'wct_talks_get_all_status', 10, 1 );

	// Get user's talks, in case of multisite
	$user_talks = wct_talks_get_talks( array(
		'per_page' => -1,
		'author'   => $user_id,
	) );

	// remove asap
	remove_filter( 'wct_talks_get_status', 'wct_talks_get_all_status', 10, 1 );

	/**
	 * We're forcing talks to be deleted definitively
	 * Using this filter you can set it to only be trashed
	 *
	 * @param bool   $force_delete true to permanently delete, false to trash
	 */
	$force_delete = apply_filters( 'wct_users_delete_user_force_delete', true );

	// If any delete them
	if ( ! empty( $user_talks['talks'] ) ) {
		foreach ( $user_talks['talks'] as $user_talk ) {
			/**
			 * WordPress is using a check on native post types
			 * so we can't just pass $force_delete to wp_delete_post().
			 */
			if ( empty( $force_delete ) ) {
				/**
				 * @param  int ID of the talk being trashed
				 * @param  int $user_id the user id
				 */
				do_action( 'wct_users_before_trash_user_data', $user_talk->ID, $user_id );

				wp_trash_post( $user_talk->ID );
			} else {
				/**
				 * @param  int ID of the talk being trashed
				 * @param  int $user_id the user id
				 */
				do_action( 'wct_users_before_delete_user_data', $user_talk->ID, $user_id );

				wp_delete_post( $user_talk->ID, true );
			}
		}
	}

	// Ratings are on, try to delete them.
	if ( ! wct_is_rating_disabled() ) {
		// Make sure we don't miss any talks
		add_filter( 'wct_talks_get_status', 'wct_talks_get_all_status', 10, 1 );

		// Get user's rates
		$rated_talks = wct_talks_get_talks( array(
			'per_page' => -1,
			'meta_query' => array( array(
				'key'     => '_wc_talks_rates',
				'value'   => ';i:' . $user_id . ';',
				'compare' => 'LIKE'
			) ),
		) );

		// remove asap
		remove_filter( 'wct_talks_get_status', 'wct_talks_get_all_status', 10, 1 );

		// If any delete them.
		if ( ! empty( $rated_talks['talks'] ) ) {

			foreach ( $rated_talks['talks'] as $talk ) {
				wct_delete_rate( $talk->ID, $user_id );
			}

			/**
			 * @param int $user_id the user ID
			 */
			do_action( 'wct_delete_user_rates', $user_id );
		}
	}

	/**
	 * @param int $user_id the user ID
	 */
	do_action( 'wct_users_deleted_user_data', $user_id );
}

/**
 * Get talk authors sorted by count
 *
 * count_many_users_posts() does not match the need
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global  $wpdb
 * @param   int  $max the number of users to limit the query
 * @return  array list of users ordered by talks count.
 */
function wct_users_talks_count_by_user( $max = 10 ) {
	global $wpdb;

	$sql = array();
	$sql['select']  = "SELECT p.post_author, COUNT(p.ID) as count_talks, u.user_nicename";
	$sql['from']    = "FROM {$wpdb->posts} p LEFT JOIN {$wpdb->users} u ON ( p.post_author = u.ID )";
	$sql['where']   = get_posts_by_author_sql( wct_get_post_type(), true, null, true );
	$sql['groupby'] = 'GROUP BY p.post_author';
	$sql['order']   = 'ORDER BY count_talks DESC';
	$sql['limit']   = $wpdb->prepare( 'LIMIT 0, %d', $max );

	$query = apply_filters( 'wct_users_talks_count_by_user_query', join( ' ', $sql ), $sql, $max );

	return $wpdb->get_results( $query );
}

/**
 * Get the default role for a user (used in multisite configs)
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */
function wct_users_get_default_role() {
	return apply_filters( 'wct_users_get_default_role', get_option( 'default_role', 'subscriber' ) );
}

/**
 * Get the signup key if the user registered
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global $wpdb
 * @param  string $user       user login
 * @param  string $user_email user email
 * @param  string $key        activation key
 * @param  array  $meta       the signup's meta data
 */
function wct_users_intercept_activation_key( $user, $user_email = '', $key = '', $meta = array() ) {
	if ( ! empty( $key ) && ! empty( $user_email ) ) {
		wct_set_global( 'activation_key', array( $user_email => $key ) );
	}

	return false;
}

/**
 * Update the $wpdb->signups table in case of a multisite config
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @global $wpdb
 * @param  array $signup the signup required data
 * @param  int $user_id  the user ID
 */
function wct_users_update_signups_table( $user_id = 0 ) {
	global $wpdb;

	if ( empty( $user_id ) ) {
		return;
	}

	$user = wct_users_get_user_data( 'id', $user_id );

	if ( empty( $user->user_login ) || empty( $user->user_email ) ) {
		return;
	}

	add_filter( 'wpmu_signup_user_notification', 'wct_users_intercept_activation_key', 10, 4 );
	wpmu_signup_user( $user->user_login, $user->user_email, array( 'add_to_blog' => get_current_blog_id(), 'new_role' => wct_users_get_default_role() ) );
	remove_filter( 'wpmu_signup_user_notification', 'wct_users_intercept_activation_key', 10, 4 );

	$key = wct_get_global( 'activation_key' );

	if ( empty( $key[ $user->user_email ] ) ) {
		return;

	// Reset the global
	} else {
		wct_set_global( 'activation_key', array() );
	}

	$wpdb->update( $wpdb->signups,
		array( 'active' => 1, 'activated' => current_time( 'mysql', true ) ),
		array( 'activation_key' => $key[ $user->user_email ] )
	);
}

/**
 * Signup a new user
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param bool $exit whether to exit or not
 */
function wct_users_signup_user( $exit = true ) {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct_signup'] ) || ! is_array( $_POST['wct_signup'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_signup' );

	$redirect     = wct_get_redirect_url();
	$is_multisite = is_multisite();

	/**
	 * Before registering the user, check for required field
	 */
	$required_errors = new WP_Error();

	$user_login = false;

	if ( ! empty( $_POST['wct_signup']['user_login'] ) ) {
		$user_login = $_POST['wct_signup']['user_login'];
	}

	// Force the login to exist and to be at least 4 characters long
	if ( 4 > mb_strlen( $user_login ) ) {
		$required_errors->add( 'user_login_fourchars', __( 'Please choose a login having at least 4 characters.', 'wordcamp-talks' ) );
	}

	$user_email = false;
	if ( ! empty( $_POST['wct_signup']['user_email'] ) ) {
		$user_email = $_POST['wct_signup']['user_email'];
	}

	// Do we need to edit the user once created ?
	$edit_user = array_diff_key(
		$_POST['wct_signup'],
		array(
			'signup'     => 'signup',
			'user_login' => 'user_login',
			'user_email' => 'user_email',
		)
	);

	/**
	 * Perform actions before the required fields check
	 *
	 * @param  string $user_login the user login
	 * @param  string $user_email the user email
	 * @param  array  $edit_user  all extra user fields
	 */
	do_action( 'wct_users_before_signup_field_required', $user_login, $user_email, $edit_user );

	foreach ( $edit_user as $key => $value ) {

		if ( ! apply_filters( 'wct_users_is_signup_field_required', false, $key ) ) {
			continue;
		}

		if ( empty( $value ) && 'empty_required_field' != $required_errors->get_error_code() ) {
			$required_errors->add( 'empty_required_field', __( 'Please fill all required fields.', 'wordcamp-talks' ) );
		}
	}

	// Stop the process and ask to fill all fields.
	if ( $required_errors->get_error_code() ) {
		//Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => join( ' ', array_map( 'strip_tags', $required_errors->get_error_messages() ) ),
		) );
		return;
	}

	/**
	 * Perform actions before the user is created
	 *
	 * @param  string $user_login the user login
	 * @param  string $user_email the user email
	 * @param  array  $edit_user  all extra user fields
	 */
	do_action( 'wct_users_before_signup_user', $user_login, $user_email, $edit_user );

	// Defaults to user name and user email
	$signup_array = array( 'user_name' => $user_login, 'user_email' => $user_email );

	// Sanitize the signup on multisite configs.
	if ( true === (bool) $is_multisite ) {
		$signup_array = wpmu_validate_user_signup( $user_login, $user_email );

		if ( is_wp_error( $signup_array['errors'] ) && $signup_array['errors']->get_error_code() ) {
			//Add feedback to the user
			wct_add_message( array(
				'type'    => 'error',
				'content' => join( ' ', array_map( 'strip_tags', $signup_array['errors']->get_error_messages() ) ),
			) );
			return;
		}

		// Filter the rp login url for WordPress 4.3
		add_filter( 'wp_mail', 'wct_multisite_user_notification', 10, 1 );
	}

	// Register the user
	$user = register_new_user( $signup_array['user_name'], $signup_array['user_email'] );

	// Stop filtering the rp login url
	if ( true === (bool) $is_multisite ) {
		remove_filter( 'wp_mail', 'wct_multisite_user_notification', 10, 1 );
	}

	/**
	 * Perform actions after the user is created
	 *
	 * @param  string             $user_login the user login
	 * @param  string             $user_email the user email
	 * @param  array              $edit_user  all extra user fields
	 * @param  mixed int|WP_Error $user the user id or an error
	 */
	do_action( 'wct_users_after_signup_user', $user_login, $user_email, $edit_user, $user );

	if ( is_wp_error( $user ) ) {
		//Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => join( ' ', array_map( 'strip_tags', $user->get_error_messages() ) ),
		) );
		return;

	// User is created, now we need to eventually edit him
	} else {

		if ( ! empty( $edit_user ) )  {

			$userdata = new stdClass();
			$userdata = (object) $edit_user;
			$userdata->ID = $user;

			/**
			 * Just before the user is updated, this will only be available
			 * if custom fields/contact methods are used.
			 *
			 * @param object $userdata the userdata to update
			 */
			$userdata = apply_filters( 'wct_users_signup_userdata', $userdata );

			// Edit the user
			if ( wp_update_user( $userdata ) ) {
				/**
				 * Any extra field not using contact methods or WordPress built in user fields can hook here
				 *
				 * @param int $user the user id
				 * @param array $edit_user the submitted user fields
				 */
				do_action( 'wct_users_signup_user_created', $user, $edit_user );
			}
		}

		// Make sure an entry is added to the $wpdb->signups table
		if ( true === (bool) $is_multisite ) {
			wct_users_update_signups_table( $user );
		}

		// Finally invite the user to check his email.
		wct_add_message( array(
			'type'    => 'success',
			'content' => __( 'Registration complete. Please check your e-mail.', 'wordcamp-talks' ),
		) );

		wp_safe_redirect( $redirect );

		if ( $exit ) {
			exit();
		}
	}
}

/**
 * Get user fields
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param  string $type whether we're on a signup form or not
 */
function wct_user_get_fields( $type = 'signup' ) {
	$fields = wp_get_user_contact_methods();

	if ( 'signup' == $type ) {
		$fields = array_merge(
			apply_filters( 'wct_user_get_signup_fields', array(
				'user_login' => __( 'Username',   'wordcamp-talks' ),
				'user_email' => __( 'E-mail',     'wordcamp-talks' ),
			) ),
			$fields
		);
	}

	return apply_filters( 'wct_user_get_fields', $fields, $type );
}

/**
 * Redirect the loggedin user to its profile as already a member
 * Or redirect WP (non multisite) register form to signup form
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 *
 * @param  string $context the template context
 */
function wct_user_signup_redirect( $context = '' ) {
	// Bail if signup is not allowed
	if ( ! wct_is_signup_allowed_for_current_blog() ) {
		return;
	}

	if ( is_user_logged_in() && 'signup' == $context ) {
		wp_safe_redirect( wct_users_get_logged_in_profile_url() );
		exit();
	} else if ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], 'wp-login.php' ) && ! empty( $_REQUEST['action'] ) &&  'register' == $_REQUEST['action'] ) {
		wp_safe_redirect( wct_users_get_signup_url() );
		exit();
	} else {
		if ( 'signup' == $context )  {
			/**
			 * If we are here the signup url has been requested
			 * Before using it let plugins override it. Used internally to
			 * let BuddyPress handle signups if needed
			 */
			do_action( 'wct_user_signup_override' );
		}
		return;
	}
}

/**
 * Filter the user notification content to make sure the password
 * will be set on the Website he registered to
 *
 * @since 1.0.0
 *
 * @param array  $mail_attr
 * @return array $mail_attr
 */
function wct_multisite_user_notification( $mail_attr = array() ) {
	if ( ! did_action( 'retrieve_password_key' ) ) {
		return $mail_attr;
	}

	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	if ( empty( $mail_attr['subject'] ) || sprintf( _x( '[%s] Your username and password info', 'Use the same translation as WP Core', 'wordcamp-talks' ), $blogname ) !== $mail_attr['subject'] ) {
		return $mail_attr;
	}

	if ( empty( $mail_attr['message'] ) ) {
		return $mail_attr;
	}

	preg_match( '/<(.+?)>/', $mail_attr['message'], $match );

	if ( ! empty( $match[1] ) ) {

		$login_url = wct_add_filter_network_site_url( $match[1], '', 'login', false );
		$mail_attr['message'] = str_replace( $match[1], $login_url, $mail_attr['message'] );
	}

	return $mail_attr;
}

/**
 * Dynamically add a filter to network_site_url in case the user
 * is setting his password from the site's login form where the
 * plugin is activated
 *
 * @since 1.0.0
 */
function wct_user_setpassword_redirect() {
	if ( ! is_multisite() || ! wct_is_signup_allowed_for_current_blog() ) {
		return;
	}

	add_filter( 'network_site_url', 'wct_add_filter_network_site_url', 10, 3 );
}

/**
 * Temporarly filter network_site_url to use site_url instead
 *
 * @since 1.0.0
 *
 * @param  string $url      Required. the network site url.
 * @param  string $path     Optional. Path relative to the site url.
 * @param  string $scheme   Optional. Scheme to give the site url context.
 * @param  bool   $redirect whether to include a redirect to query arg to the url or not.
 * @return string Site url link.
 */
function wct_add_filter_network_site_url( $site_url, $path = '', $scheme = null, $redirect = true ) {
	if ( ! is_multisite() || ! wct_is_signup_allowed_for_current_blog() ) {
		return $site_url;
	}

	$current_site = get_current_site();
	$url = set_url_scheme( 'http://' . $current_site->domain . $current_site->path, $scheme );

	if ( false !== strpos( $site_url, $url ) ) {
		$blog_url = trailingslashit( site_url() );
		$site_url = str_replace( $url, $blog_url, $site_url );

		if ( true === $redirect ) {
			$site_url = esc_url( add_query_arg( 'wct_redirect_to', urlencode( $blog_url ), $site_url ) );
		}
	}

	return $site_url;
}

/**
 * Remove the filter on network_site_url
 *
 * @since 1.0.0
 */
function wct_remove_filter_network_site_url() {
	if ( ! is_multisite() || ! wct_is_signup_allowed_for_current_blog() ) {
		return;
	}

	remove_filter( 'network_site_url', 'wct_add_filter_network_site_url', 10, 3 );
}
add_action( 'resetpass_form', 'wct_remove_filter_network_site_url' );

/**
 * Add a filter 'login_url' to eventually set the 'redirect_to' query arg
 *
 * @since 1.0.0
 */
function wct_multisite_add_filter_login_url() {
	if ( ! is_multisite() || ! wct_is_signup_allowed_for_current_blog() ) {
		return;
	}

	add_filter( 'login_url', 'wct_multisite_filter_login_url', 1 );
}
add_action( 'validate_password_reset', 'wct_multisite_add_filter_login_url' );

/**
 * Filter to add a 'redirect_to' query arg to login_url
 *
 * @since 1.0.0
 */
function wct_multisite_filter_login_url( $login_url ) {
	if ( ! empty( $_GET['wct_redirect_to'] ) ) {
		$login_url = add_query_arg( 'redirect_to', $_GET['wct_redirect_to'], $login_url );
	}

	return $login_url;
}

/**
 * Set a role on the site of the network if needed
 *
 * @package WordCamp Talks
 * @subpackage users/functions
 *
 * @since 1.0.0
 */
function wct_maybe_set_current_user_role() {
	if ( ! is_multisite() || is_super_admin() ) {
		return;
	}

	$current_user = wct()->current_user;

	if ( empty( $current_user->ID ) || ! empty( $current_user->roles ) || ! wct_get_user_default_role() ) {
		return;
	}

	$current_user->set_role( wct_users_get_default_role() );
}
add_action( 'wct_talks_before_talk_save', 'wct_maybe_set_current_user_role', 1 );

/**
 * Get the stat for the the requested type (number of talks, comments or rates)
 *
 * @since 1.0.0
 *
 * @param string $type    the type of stat to get (eg: 'profile', 'comments', 'rates')
 * @param int    $user_id the User ID to get the stat for
 */
function wct_users_get_stat_for( $type = '', $user_id = 0 ) {
	$count = 0;

	if ( empty( $type ) ) {
		return $count;
	}

	if ( empty( $user_id ) ) {
		$user_id = wct_users_displayed_user_id();
	}

	if ( empty( $user_id ) ) {
		return $$count;
	}

	if ( 'profile' === $type ) {
		$count = count_user_posts( $user_id, wct_get_post_type() );
	} elseif ( 'comments' === $type ) {
		$count = wct_comments_count_comments( $user_id );
	} elseif ( 'rates' === $type ) {
		$count = wct_count_user_rates( $user_id );
	}

	/**
	 * Filter the user stats by type (number of talks "profile", "comments" or "rates").
	 *
	 * @since 1.0.0
	 *
	 * @param  int    $count the stat for the requested type.
	 * @param  string $type "profile", "comments" or "rates".
	 * @param  int    $user_id The user ID.
	 */
	return (int) apply_filters( 'wct_users_get_stat_for', $count, $type, $user_id );
}

/**
 * WordPress requires a post id to allow content to be Embed, As our users are not organized
 * into a post type, we need to use an utility page to get a post ID, and then filter its permalink
 * and title so that the ones of the user's profile will be used instead
 *
 * @since 1.0.0
 *
 * @global WP_Rewrite $wp_rewrite
 * @param int    $post_id the requested post id (should be empty for our users profiles)
 * @param string $url     the requested url which can contain the user's profile
 */
function wct_users_oembed_request_post_id( $post_id = 0, $url = '' ) {
	// The post is not empty leave WordPress deal with it!
	if ( ! empty( $post_id ) ) {
		return $post_id;
	}

	$utility_page = wct_is_embed_profile();

	// No utility page, stop!
	if ( ! $utility_page ) {
		return $post_id;
	}

	// Get the WP Rewrites
	global $wp_rewrite;

	$extra_rules = $wp_rewrite->extra_rules_top;

	if ( empty( $extra_rules ) ) {
		return $post_id;
	}

	// Parse the url
	$parse_url = parse_url( $url );

	// Pretty permalinks: Loop through each extra rules to find the username or user id
	if ( $wp_rewrite->using_permalinks() && isset( $parse_url['path'] ) && false !== strpos( $parse_url['path'], wct_user_slug() ) ) {
		// Loop through each extra rules to find the username or user id
		foreach ( (array) $extra_rules as $match => $query ) {
			if ( preg_match( "#^$match#", str_replace( trailingslashit( home_url() ), '', $url ), $matches ) ) {
				if ( isset( $matches[1] ) ) {
					$user = $matches[1];
					break;
				}
			}
		}

	// Default permalinks: find the query var containing the user_id
	} elseif ( isset( $parse_url['query'] ) ) {
		// Parse the query string
		parse_str( $parse_url['query'], $query_vars );

		if ( ! empty( $query_vars[ wct_user_rewrite_id() ] ) ) {
			$user = (int) $query_vars[ wct_user_rewrite_id() ];
		}
	}

	// No username or user id found stop
	if ( empty( $user ) ) {
		return $post_id;
	}

	if ( ! is_numeric( $user ) ) {
		// Get user by his username
		$user = wct_users_get_user_data( 'slug', $user );
	} else {
		// Get user by his id
		$user = wct_users_get_user_data( 'id', $user );
	}

	// A user was found globalize it for a latter use and init some filters
	if ( is_a( $user, 'WP_User' ) ) {
		// If the user is a spammer, do not allow his profile to be embed
		if ( true === apply_filters( 'wct_users_is_spammy', is_multisite() && is_user_spammy( $user ), $user ) ) {
			return $post_id;
		}

		// Set the utility page as the post id
		$post_id = $utility_page;

		wct_set_global( 'embed_user_data', $user );

		// Temporarly only!
		add_filter( 'post_type_link', 'wct_users_oembed_link',  10, 2 );
		add_filter( 'the_title',      'wct_users_oembed_title', 10, 2 );
	}

	return $post_id;
}
add_filter( 'oembed_request_post_id', 'wct_users_oembed_request_post_id', 10, 2 );

/**
 * In case a user's profile is embed, replace the Utility page permalink with
 * the user's profile link
 *
 * @since 1.0.0
 *
 * @param  string  $permalink the link to the post
 * @param  WP_Post $post      the post object relative to the permalink
 * @return string  Unchanged link or the link to the user's profile if needed
 */
function wct_users_oembed_link( $permalink, $post ) {
	if ( ! isset( $post->ID ) || wct_is_embed_profile() !== (int) $post->ID ) {
		return $permalink;
	}

	$user = wct_get_global( 'embed_user_data' );

	if( ! is_a( $user, 'WP_User' ) ) {
		return $permalink;
	}

	return wct_users_get_user_profile_url( $user->ID, $user->user_nicename, true );
}

/**
 * In case a user's profile is embed, replace the Utility page title with
 * the user's title
 *
 * @since 1.0.0
 *
 * @param  string  $title   the title of the post
 * @param  int     $post_id the post ID relative to the title
 * @return string  Unchanged link or the link to the user's title if needed
 */
function wct_users_oembed_title( $title, $post_id ) {
	if ( ! isset( $post_id ) || wct_is_embed_profile() !== (int) $post_id ) {
		return $title;
	}

	$user = wct_get_global( 'embed_user_data' );

	if( ! is_a( $user, 'WP_User' ) ) {
		return $title;
	}

	return sprintf( esc_attr__( '%s&#39;s profile', 'wordcamp-talks' ), $user->display_name );
}

/**
 * In case a user's profile is embed, we need to reset all the parts we've altered
 * to make the user's embed profile works.
 *
 * @since 1.0.0
 *
 * @param  string  $output  the HTML output for the embed object
 * @param  WP_Post $post    the post object relative to the output
 * @return string  Unchanged the HTML output for the embed object
 */
function wct_embed_html( $output, $post ) {
	if ( isset( $post->ID ) && wct_is_embed_profile() === (int) $post->ID ) {
		// Remove temporarly filters
		remove_filter( 'post_type_link', 'wct_users_oembed_link',  10, 2 );
		remove_filter( 'the_title',      'wct_users_oembed_title', 10, 2 );

		// Reset the globalized user.
		wct_set_global( 'embed_user_data', null );
	}

	return $output;
}
add_filter( 'embed_html', 'wct_embed_html', 10, 2 );
