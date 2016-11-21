<?php
/**
 * WordCamp Talks Talks functions.
 *
 * Functions that are specifics to talks
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Set/Get Talk(s) ***********************************************************/

/**
 * Default status used in talk 'get' queries
 *
 * By default, 'publish'
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @return array          the post status of talks to retrieve
 */
function wct_talks_get_status() {
	$status = array( 'publish' );

	if ( wct_user_can( 'read_private_talks' ) ) {
		$status[] = 'private';
	}

	/**
	 * Use this filter to override post status of talks to retieve
	 *
	 * @param  array $status
	 */
	return apply_filters( 'wct_talks_get_status', $status );
}

/**
 * Gets all WordPress built in post status (to be used in filters)
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $status
 * @return array          the available post status
 */
function wct_talks_get_all_status( $status = array() ) {
	return array_keys( get_post_statuses() );
}

/**
 * How much talks to retrieve per page ?
 *
 * By default, same value than regular posts
 * Uses the WordPress posts per page setting
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @return array           the post status of talks to retrieve
 */
function wct_talks_per_page() {
	return apply_filters( 'wct_talks_per_page', wct_get_global( 'per_page' ) );
}

/**
 * Get Talks matching the query args
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $args custom args to merge with default ones
 * @return array        requested talks
 */
function wct_talks_get_talks( $args = array() ) {
	$get_args = array();
	$talks    = array();

	$default = array(
		'author'     => 0,
		'per_page'   => wct_talks_per_page(),
		'page'       => 1,
		'search'     => '',
		'exclude'    => '',
		'include'    => '',
		'orderby'    => 'date',
		'order'      => 'DESC',
		'meta_query' => array(),
		'tax_query'  => array(),
	);

	if ( ! empty( $args ) ) {
		$get_args = $args;
	} else {
		$main_query = wct_get_global( 'main_query' );

		if ( ! empty( $main_query['query_vars'] ) ) {
			$get_args = $main_query['query_vars'];
			unset( $main_query['query_vars'] );
		}

		$talks = $main_query;
	}

	// Parse the args
	$r = wp_parse_args( $get_args, $default );

	if ( empty( $talks ) ) {
		$talks = WordCamp_Talks_Talk::get( $r );

		// Reset will need to be done at the end of the loop
		wct_set_global( 'needs_reset', true );
	}

	$talks = array_merge( $talks, array( 'get_args' => $r ) );

	/**
	 * @param  array $talks     associative array to find talks, total count and loop args
	 * @param  array $r         merged args
	 * @param  array $get_args  args before merge
	 */
	return apply_filters( 'wct_talks_get_talks', $talks, $r, $get_args );
}

/**
 * Gets an talk with additional metas and terms
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $id_or_name ID or post_name of the talk to get
 * @return WordCamp_Talks_Talk  the talk object
 */
function wct_talks_get_talk( $id_or_name = '' ) {
	if ( empty( $id_or_name ) ) {
		return false;
	}

	$talk = new WordCamp_Talks_Talk( $id_or_name );

	/**
	 * @param  WordCamp_Talks_Talk $talk the talk object
	 * @param  mixed               $id_or_name  the ID or slug of the talk
	 */
	return apply_filters( 'wct_talks_get_talk', $talk, $id_or_name );
}

/**
 * Gets an talk by its slug without additional metas or terms
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $name the post_name of the talk to get
 * @return WP_Post the talk object
 */
function wct_talks_get_talk_by_name( $name = '' ) {
	if ( empty( $name ) ) {
		return false;
	}

	$talk = WordCamp_Talks_Talk::get_talk_by_name( $name );

	/**
	 * @param  WP_Post $talk the talk object
	 * @param  string  $name the post_name of the talk
	 */
	return apply_filters( 'wct_talks_get_talk_by_name', $talk, $name );
}

/**
 * Registers a new talks meta
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $meta_key  the identifier of the meta key to register
 * @param  string $meta_args the arguments (array of callback functions)
 */
function wct_talks_register_meta( $meta_key = '', $meta_args = '' ) {
	if ( empty( $meta_key ) || ! is_array( $meta_args ) ) {
		return false;
	}

	$wc_talks_metas = wct_get_global( 'wc_talks_metas' );

	if ( empty( $wc_talks_metas ) ) {
		$wc_talks_metas = array();
	}

	$key = sanitize_key( $meta_key );

	$args = wp_parse_args( $meta_args, array(
		'meta_key' => $key,
		'label'    => '',
		'admin'    => 'wct_meta_admin_display',
		'form'     => '',
		'single'   => 'wct_meta_single_display',
	) );

	$wc_talks_metas[ $key ] = (object) $args;

	wct_set_global( 'wc_talks_metas', $wc_talks_metas );
}

/**
 * Gets an talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id  the ID of the talk
 * @param  string  $meta_key the meta key to get
 * @param  bool    $single   whether to get an array of meta or unique one
 * @return mixed             the meta value
 */
function wct_talks_get_meta( $talk_id = 0, $meta_key = '', $single = true ) {
	if ( empty( $talk_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	$meta_value = get_post_meta( $talk_id, '_wc_talks_' . $sanitized_key, $single );

	if ( empty( $meta_value ) ) {
		return false;
	}

	// Custom sanitization
	if ( has_filter( "wct_meta_{$sanitized_key}_sanitize_display" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wct_meta_{$sanitized_key}_sanitize_display", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	return apply_filters( 'wct_talks_get_meta', $sanitized_value, $meta_key, $talk_id );
}

/**
 * Updates an talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id    the ID of the talk
 * @param  string  $meta_key   the meta key to update
 * @param  mixed   $meta_value the meta value to update
 * @return bool                the update meta result
 */
function wct_talks_update_meta( $talk_id = 0, $meta_key = '', $meta_value = '' ) {
	if ( empty( $talk_id ) || empty( $meta_key ) || empty( $meta_value ) ) {
		return false;
	}

	$sanitized_key   = sanitize_key( $meta_key );
	$sanitized_value = false;

	// Custom sanitization
	if ( has_filter( "wct_meta_{$sanitized_key}_sanitize_db" ) ) {
		/**
		 * Use this filter if you need to apply custom sanitization to
		 * the meta value
		 * @param  mixed   $meta_value the meta value
		 * @param  string  $meta_key  the meta_key
		 */
		$sanitized_value = apply_filters( "wct_meta_{$sanitized_key}_sanitize_db", $meta_value, $meta_key );

	// Fallback to generic sanitization
	} else {

		if ( is_array( $meta_value) ) {
			$sanitized_value = array_map( 'sanitize_text_field', $meta_value );
		} else {
			$sanitized_value = sanitize_text_field( $meta_value );
		}
	}

	if ( empty( $sanitized_value ) ) {
		return false;
	}

	return update_post_meta( $talk_id, '_wc_talks_' . $sanitized_key, $sanitized_value );
}

/**
 * Deletes an talk meta data
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int     $talk_id    the ID of the talk
 * @param  string  $meta_key   the meta key to update
 * @return bool                the delete meta result
 */
function wct_talks_delete_meta( $talk_id = 0, $meta_key = '' ) {
	if ( empty( $talk_id ) || empty( $meta_key ) ) {
		return false;
	}

	$sanitized_key = sanitize_key( $meta_key );

	return delete_post_meta( $talk_id, '_wc_talks_' . $sanitized_key );
}

/**
 * Gets talk terms given a taxonomy and args
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $taxonomy the taxonomy identifier
 * @param  array  $args     the arguments to get the terms
 * @return array|WP_Error List of Term Objects and their children. Will return WP_Error, if any of $taxonomies
 *                        do not exist.
 */
function wct_talks_get_terms( $taxonomy = '', $args = array() ) {
	if ( empty( $taxonomy ) || ! is_array( $args ) ) {
		return false;
	}

	// Merge args
	$term_args = wp_parse_args( $args, array(
		'orderby'    => 'count',
		'hide_empty' => 0
	) );

	// get the terms for the requested taxonomy and args
	$terms = get_terms( $taxonomy, $term_args );

	/**
	 * @param  array|WP_Error $terms    the list of terms of the taxonomy
	 * @param  string         $taxonomy the taxonomy of the terms retrieved
	 * @param  array          $args     the arguments to get the terms
	 */
	return apply_filters( 'wct_talks_get_terms', $terms, $taxonomy, $args );
}

/**
 * Sets the post status of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $talkarr the posted arguments
 * @return string          the post status of the talk
 */
function wct_talks_insert_status( $talkarr = array() ) {
	/**
	 * @param  string  the default post status for an talk
	 * @param  array   $talkarr  the arguments of the talk to save
	 */
	return apply_filters( 'wct_talks_insert_status', wct_default_talk_status(), $talkarr );
}

/**
 * Checks if another user is editing an talk, if not
 * locks the talk for the current user.
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int $talk_id The ID of the talk to edit
 * @return int                the user id editing the talk
 */
function wct_talks_lock_talk( $talk_id = 0 ) {
	$user_id = false;

	// Bail if no ID to check
	if ( empty( $talk_id ) ) {
		return $user_id;
	}

	// Include needed file
	require_once( ABSPATH . '/wp-admin/includes/post.php' );

	$user_id = wp_check_post_lock( $talk_id );

	// If not locked, then lock it as current user is editing it.
	if( empty( $user_id ) ) {
		wp_set_post_lock( $talk_id );
	}

	return $user_id;
}

/**
 * HeartBeat callback to check if an talk is being edited by an admin
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $response the heartbeat response
 * @param  array  $data     the data sent by heartbeat
 * @return array            Response to heartbeat
 */
function wct_talks_heartbeat_check_locked( $response = array(), $data = array() ) {

	if ( empty( $data['wc_talks_heartbeat_current_talk'] ) ) {
		return $response;
	}

	$response['wc_talks_heartbeat_response'] = wct_talks_lock_talk( $data['wc_talks_heartbeat_current_talk'] );

	return $response;
}

/**
 * Checks if a user can edit an talk
 *
 * A user can edit the talk if :
 * - he is the author
 *   - and talk was created 0 to 5 mins ago
 *   - no comment was posted on the talk
 *   - no rates was given to the talk
 *   - nobody else is currently editing the talk
 * - he is a super admin.
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post $talk the talk object
 * @return bool          whether the user can edit the talk (true), or not (false)
 */
function wct_talks_can_edit( $talk = null ) {
	// Default to can't edit !
	$retval = false;

	// Bail if we can't check anything
	if ( empty( $talk ) || ! is_a( $talk, 'WP_Post' ) ) {
		return $retval;
	}

	// Map to edit others talks if current user is not the author
	if ( wct_users_current_user_id() != $talk->post_author ) {

		// Do not edit talks of the super admin
		if ( ! is_super_admin( $talk->post_author ) ) {
			return wct_user_can( 'edit_others_talks' );
		} else {
			return $retval;
		}

	}

	/** Now we're dealing with author's capacitiy to edit the talk ****************/

	/**
	 * First, give the possibility to early override
	 *
	 * If you want to avoid the comment/rate and time lock, you
	 * can use this filter.
	 *
	 * @param bool whether to directly check user's capacity
	 * @param WP_Post $talk   the talk object
	 */
	$early_can_edit = apply_filters( 'wct_talks_pre_can_edit', false, $talk );

	if ( ! empty( $early_can_edit ) || is_super_admin() ) {
		return wct_user_can( 'edit_talk', $talk->ID );
	}

	// Talk was commented or rated
	if ( ! empty( $talk->comment_count ) || get_post_meta( $talk->ID, '_wc_talks_average_rate', true ) ) {
		return $retval;
	}

	/**
	 * This part is based on bbPress's bbp_past_edit_lock() function
	 *
	 * In the case of an Talk Management system, i find the way bbPress
	 * manage the time a content can be edited by its author very interesting
	 * and simple (simplicity is allways great!)
	 */

	// Bail if empty date
	if ( empty( $talk->post_date_gmt ) ) {
		return $retval;
	}

	// Period of time
	$lockable  = apply_filters( 'wct_talks_can_edit_time', '+1 hour' );

	// Now
	$cur_time  = current_time( 'timestamp', true );

	// Add lockable time to post time
	$lock_time = strtotime( $lockable, strtotime( $talk->post_date_gmt ) );

	// Compare
	if ( $cur_time <= $lock_time ) {
		$retval = wct_user_can( 'edit_talk', $talk->ID );
	}

	/**
	 * Late filter
	 *
	 * @param bool    $retval whether to allow the user's to edit the talk
	 * @param WP_Post $talk   the talk object
	 */
	return apply_filters( 'wct_talks_can_edit', $retval, $talk );
}

/**
 * Saves an talk entry in posts table
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array  $talkarr the posted arguments
 * @return int    the ID of the created or updated talk
 */
function wct_talks_save_talk( $talkarr = array() ) {
	if ( ! is_array( $talkarr ) ) {
		return false;
	}

	if ( empty( $talkarr['_the_title'] ) || empty( $talkarr['_the_content'] ) ) {
		return false;
	}

	// Init update vars
	$update         = false;
	$old_taxonomies = array();
	$old_metas      = array();

	if ( ! empty( $talkarr['_the_id'] ) ) {
		/**
		 * Passing the id attribute to WordCamp_Talks_Talk will get the previous version of the talk
		 * In this case we don't need to set the author or status
		 */
		$talk = new WordCamp_Talks_Talk( absint( $talkarr['_the_id'] ) );

		if ( ! empty( $talk->id ) ) {
			$update = true;

			// Get old metas
			if ( ! empty( $talk->metas['keys'] ) ) {
				$old_metas = $talk->metas['keys'];
			}

			// Get old taxonomies
			if ( ! empty( $talk->taxonomies ) )  {
				$old_taxonomies = $talk->taxonomies;
			}

		// If we don't find the talk, stop!
		} else {
			return false;
		}

	} else {
		$talk         = new WordCamp_Talks_Talk();
		$talk->author = wct_users_current_user_id();
		$talk->status = wct_talks_insert_status( $talkarr );
	}

	// Set the title and description of the talk
	$talk->title       = $talkarr['_the_title'];
	$talk->description = $talkarr['_the_content'];

	// Handling categories
	if ( ! empty( $talkarr['_the_category'] ) && is_array( $talkarr['_the_category'] ) ) {
		$categories = wp_parse_id_list( $talkarr['_the_category'] );

		$talk->taxonomies = array(
			wct_get_category() => $categories
		);

	// In case of an update, we need to eventually remove all categories
	} else if ( empty( $talkarr['_the_category'] ) && ! empty( $old_taxonomies[ wct_get_category() ] ) ) {

		// Reset categories if some were set
		if ( is_array( $talk->taxonomies ) ) {
			$talk->taxonomies[ wct_get_category() ] = array();
		} else {
			$talk->taxonomies = array( wct_get_category() => array() );
		}
	}

	// Handling tags
	if ( ! empty( $talkarr['_the_tags'] ) && is_array( $talkarr['_the_tags'] ) ) {
		$tags = array_map( 'strip_tags', $talkarr['_the_tags'] );

		$tags = array(
			wct_get_tag() => join( ',', $tags )
		);

		if ( ! empty( $talk->taxonomies ) ) {
			$talk->taxonomies = array_merge( $talk->taxonomies, $tags );
		} else {
			$talk->taxonomies = $tags;
		}

	// In case of an update, we need to eventually remove all tags
	} else if ( empty( $talkarr['_the_tags'] ) && ! empty( $old_taxonomies[ wct_get_tag() ] ) ) {

		// Reset tags if some were set
		if ( is_array( $talk->taxonomies ) ) {
			$talk->taxonomies[ wct_get_tag() ] = '';
		} else {
			$talk->taxonomies = array( wct_get_tag() => '' );
		}
	}

	// Handling metas. By default none, but can be useful for plugins
	if ( ! empty( $talkarr['_the_metas'] ) && is_array( $talkarr['_the_metas'] ) ) {
		$talk->metas = $talkarr['_the_metas'];
	}

	// Check if some metas need to be deleted
	if ( ! empty( $old_metas ) && is_array( $talk->metas ) ) {
		$to_delete = array_diff( $old_metas, array_keys( $talk->metas ) );

		if ( ! empty( $to_delete ) ) {
			$to_delete = array_fill_keys( $to_delete, 0 );
			$talk->metas = array_merge( $talk->metas, $to_delete );
		}
	}

	/**
	 * Do stuff before the talk is saved
	 *
	 * @param  array $talkarr the posted values
	 * @param  bool  $update  whether it's an update or not
	 */
	do_action( 'wct_talks_before_talk_save', $talkarr, $update );

	$saved_id = $talk->save();

	if ( ! empty( $saved_id ) ) {

		$hook = 'insert';

		if ( ! empty( $update ) ) {
			$hook = 'update';
		}

		/**
		 * Do stuff after the talk was saved
		 *
		 * Call wct_talks_after_insert_talk for a new talk
		 * Call wct_talks_after_update_talk for an updated talk
		 *
		 * @param  int    $inserted_id the inserted id
		 * @param  object $talk the talk
		 */
		do_action( "wct_talks_after_{$hook}_talk", $saved_id, $talk );
	}

	return $saved_id;
}

/** Talk urls *****************************************************************/

/**
 * Gets the permalink to the talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post|int  $talk the talk object or its ID
 * @return string|bool     the permalink to the talk, false if the talk is not set
 */
function wct_talks_get_talk_permalink( $talk = null ) {
	// Bail if not set
	if ( empty( $talk ) ) {
		return false;
	}

	// Not a post, try to get it
	if ( ! is_a( $talk, 'WP_Post' ) ) {
		$talk = get_post( $talk );
	}

	if ( empty( $talk->ID ) ) {
		return false;
	}

	/**
	 * @param  string        permalink to the talk
	 * @param  WP_Post $talk the talk object
	 */
	return apply_filters( 'wct_talks_get_talk_permalink', get_permalink( $talk ), $talk );
}

/**
 * Gets the comment link of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post $talk the talk object or its ID
 * @return string          the comment link of an talk
 */
function wct_talks_get_talk_comments_link( $talk = null ) {
	$comments_link = wct_talks_get_talk_permalink( $talk ) . '#comments';

	/**
	 * @param  string  $comments_link comment link
	 * @param  WP_Post $talk          the talk object
	 */
	return apply_filters( 'wct_talks_get_talk_comments_link', $comments_link, $talk );
}

/** Template functions ********************************************************/

/**
 * Adds needed scripts to rate the talk or add tags to it
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_enqueue_scripts() {
	if ( ! wct_is_talks() ) {
		return;
	}

	// Single talk > ratings
	if ( wct_is_single_talk() && ! wct_is_edit() && ! wct_is_rating_disabled() ) {

		$ratings = (array) wct_count_ratings();
		$users_nb = count( $ratings['users'] );
		$hintlist = (array) wct_get_hint_list();

		$js_vars = array(
			'raty_loaded'  => 1,
			'ajaxurl'      => admin_url( 'admin-ajax.php', 'relative' ),
			'wait_msg'     => esc_html__( 'Saving your rating; please wait', 'wordcamp-talks' ),
			'success_msg'  => esc_html__( 'Thanks! The average rating is now:', 'wordcamp-talks' ),
			'error_msg'    => esc_html__( 'Oops! Something went wrong', 'wordcamp-talks' ),
			'average_rate' => $ratings['average'],
			'rate_nb'      => $users_nb,
			'one_rate'     => esc_html__( 'One rating', 'wordcamp-talks' ),
			'x_rate'       => esc_html__( '% ratings', 'wordcamp-talks' ),
			'readonly'     => true,
			'can_rate'     => wct_user_can( 'rate_talks' ),
			'not_rated'    => esc_html__( 'Not rated yet', 'wordcamp-talks' ),
			'hints'        => $hintlist,
			'hints_nb'     => count( $hintlist ),
			'wpnonce'      => wp_create_nonce( 'wct_rate' ),
		);

		$user_id = wct_users_current_user_id();

		if ( wct_user_can( 'rate_talks' ) ) {
			$js_vars['readonly'] = ( 0 != $users_nb ) ? in_array( $user_id, $ratings['users'] ) : false;
		}

		wp_enqueue_script( 'wc-talks-script', wct_get_js_script( 'script' ), array( 'jquery-raty' ), wct_get_version(), true );
		wp_localize_script( 'wc-talks-script', 'wct_vars', apply_filters( 'wct_talks_single_script', $js_vars ) );
	}

	// Form > tags
	if ( wct_is_addnew() || wct_is_edit() ) {
		// Default dependencies
		$deps = array( 'tagging' );

		// Defaul js vars
		$js_vars = array(
			'tagging_loaded'  => 1,
			'taginput_name'   => 'wct[_the_tags][]',
			'duplicate_tag'   => __( 'Duplicate tag:',       'wordcamp-talks' ),
			'forbidden_chars' => __( 'Forbidden character:', 'wordcamp-talks' ),
			'forbidden_words' => __( 'Forbidden word:',      'wordcamp-talks' ),
		);

		// Add HeartBeat if talk is being edited
		if ( wct_is_edit() ) {
			$deps = array_merge( $deps, array( 'heartbeat' ) );
			$js_vars = array_merge( $js_vars, array(
				'talk_id' => wct_get_single_talk_id(),
				'pulse'   => 'fast',
				'warning' => esc_html__( 'An admin is currently editing this talk, please try to edit your talk later.', 'wordcamp-talks' ),
			) );
		}

		// Enqueue and localize script
		wp_enqueue_script( 'wc-talks-script', wct_get_js_script( 'script' ), $deps, wct_get_version(), true );
		wp_localize_script( 'wc-talks-script', 'wct_vars', apply_filters( 'wct_talks_form_script_vars', $js_vars ) );
	}
}

/**
 * Builds the loop query arguments
 *
 * By default,it's an empty array as the plugin is first
 * using WordPress main query & retrieved posts. This function
 * allows to override it with custom arguments usfin the filter
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string $type is this a single talk?
 * @return array        the loop args
 */
function wct_talks_query_args( $type = '' ) {
	/**
	 * Use this filter to overide loop args
	 * @see wct_talks_has_talks() for the list of available ones
	 *
	 * @param  array by default an empty array
	 */
	$query_args = apply_filters( 'wct_talks_query_args', array() );

	if ( 'single' == $type ) {
		$query_arg = array_intersect_key( $query_args, array( 'talk_name' => false ) );
	}

	return $query_args;
}

/**
 * Sets the available orderby possible filters
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_get_order_options() {
	$order_options =  array(
		'date'           => __( 'Latest', 'wordcamp-talks' ),
		'comment_count'  => __( 'Most commented', 'wordcamp-talks' ),
	);

	// Only if not disabled.
	if ( ! wct_is_rating_disabled() && wct_user_can( 'rate_talks' ) ) {
		$order_options['rates_count'] = __( 'Highest Rating', 'wordcamp-talks' );
	}

	if ( ! wct_user_can( 'comment_talks' ) ) {
		unset( $order_options['comment_count'] );
	}

	/**
	 * @param  array $order_options the list of available order options
	 */
	return apply_filters( 'wct_talks_get_order_options', $order_options );
}

/**
 * Sets the title prefix in case of a private talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private talk
 * @param  WP_Post $talk   the talk object
 * @return string          the title prefix
 */
function wct_talks_private_title_prefix( $prefix = '', $talk = null ) {
	// Not an talk ? Bail.
	if ( empty( $talk ) || wct_get_post_type() != $talk->post_type ) {
		return $prefix;
	}

	/**
	 * @param  string        the prefix output
	 * @param  WP_Post $talk the talk object
	 */
	return apply_filters( 'wct_talks_private_title_prefix', '<span class="private-talk"></span> %s', $talk );
}

/**
 * Sets the title prefix in case of a password protected talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  string  $prefix the prefix to apply in case of a private talk
 * @param  WP_Post $talk   the talk object
 * @return string          the title prefix
 */
function wct_talks_protected_title_prefix( $prefix = '', $talk = null ) {
	// Not an talk ? Bail.
	if ( empty( $talk ) || wct_get_post_type() != $talk->post_type ) {
		return $prefix;
	}

	/**
	 * @param  string        the prefix output
	 * @param  WP_Post $talk the talk object
	 */
	return apply_filters( 'wct_talks_protected_title_prefix', '<span class="protected-talk"></span> %s', $talk );
}

/** Handle Talk actions *******************************************************/

/**
 * Handles posting talks
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_post_talk() {
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct'] ) || ! is_array( $_POST['wct'] ) ) {
		return;
	}

	// Bail if it's an update
	if ( ! empty( $_POST['wct']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_save' );

	$redirect = wct_get_redirect_url();

	// Check capacity
	if ( ! wct_user_can( 'publish_talks' ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'You are not allowed to publish talks', 'wordcamp-talks' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}

	$posted = array_diff_key( $_POST['wct'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $posted['_the_title'] ) || empty( $posted['_the_content'] ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'Title and description are required fields.', 'wordcamp-talks' ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	$id = wct_talks_save_talk( $posted );

	if ( empty( $id ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'Something went wrong while trying to save your talk.', 'wordcamp-talks' ),
		) );

		// Redirect to an empty form
		wp_safe_redirect( wct_get_form_url() );
		exit();
	} else {
		$talk             = get_post( $id );
		$feedback_message = array();

		if ( ! empty( $posted['_the_thumbnail'] ) ) {
			$thumbnail = reset( $posted['_the_thumbnail'] );
			$sideload = WordCamp_Talks_Talks_Thumbnail::start( $thumbnail, $id );

			if ( is_wp_error( $sideload->result ) ) {
				$feedback_message[] = __( 'There was a problem saving the featured image, sorry.', 'wordcamp-talks' );
			}
		}

		if ( 'pending' == $talk->post_status ) {
			// Build pending message.
			$feedback_message['pending'] = __( 'Your talk is currently awaiting moderation.', 'wordcamp-talks' );

			// Check for a custom pending message
			$custom_pending_message = wct_moderation_message();
			if ( ! empty( $custom_pending_message ) ) {
				$feedback_message['pending'] = $custom_pending_message;
			}

		// redirect to the talk
		} else {
			$redirect = wct_talks_get_talk_permalink( $talk );
		}

		if ( ! empty( $feedback_message ) ) {
			// Add feedback to the user
			wct_add_message( array(
				'type'    => 'info',
				'content' => join( ' ', $feedback_message ),
			) );
		}

		wp_safe_redirect( $redirect );
		exit();
	}
}

/**
 * Handles updating an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 */
function wct_talks_update_talk() {
	global $wp_query;
	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Bail if not a post talk request
	if ( empty( $_POST['wct'] ) || ! is_array( $_POST['wct'] ) ) {
		return;
	}

	// Bail if it's not an update
	if ( empty( $_POST['wct']['_the_id'] ) ) {
		return;
	}

	// Check nonce
	check_admin_referer( 'wct_save' );

	$redirect = wct_get_redirect_url();

	// Get talk name
	$talk_name = get_query_var( wct_get_post_type() );

	// Get Talk Object
	$talk = get_queried_object();

	// If queried object doesn't match or wasn't helpfull, try to get the talk using core function
	if ( empty( $talk->post_name ) || empty( $talk_name ) || $talk_name != $talk->post_name ) {
		$talk = wct_talks_get_talk_by_name( $talk_name );
	}

	// Found no talk, redirect and inform the user
	if ( empty( $talk->ID ) ) {
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'The talk you are trying to edit does not seem to exist.', 'wordcamp-talks' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}


	// Checks if the user can edit the talk
	if ( ! wct_talks_can_edit( $talk ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'You are not allowed to edit this talk.', 'wordcamp-talks' ),
		) );

		// Redirect to main archive page
		wp_safe_redirect( $redirect );
		exit();
	}

	$updated = array_diff_key( $_POST['wct'], array( 'save' => 'submit' ) );

	// Title & content are required
	if ( empty( $updated['_the_title'] ) || empty( $updated['_the_content'] ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => 'error',
			'content' => __( 'Title and description are required fields.', 'wordcamp-talks' ),
		) );

		// Simply stop, so that the user keeps the posted values.
		return;
	}

	// Reset '_the_id' param to the ID of the talk found
	$updated['_the_id'] = $talk->ID;
	$feedback_message   = array();
	$featured_error     = __( 'There was a problem saving the featured image, sorry.', 'wordcamp-talks' );
	$featured_type      = 'info';

	// Take care of the featured image
	$thumbnail_id = (int) get_post_thumbnail_id( $talk );

	if ( ! empty( $updated['_the_thumbnail'] ) ) {
		$thumbnail_src = key( $updated['_the_thumbnail'] );
		$thumbnail     = reset( $updated['_the_thumbnail'] );

		// Update the Featured image
		if ( ! is_numeric( $thumbnail ) || $thumbnail_id !== (int) $thumbnail ) {
			if ( is_numeric( $thumbnail ) ) {
				// validate the attachment
				if ( ! get_post( $thumbnail ) ) {
					$feedback_message[] = $featured_error;
				// Set the new Featured image
				} else {
					set_post_thumbnail( $talk->ID, $thumbnail );
				}
			} else {
				$sideload = WordCamp_Talks_Talks_Thumbnail::start( $thumbnail_src, $talk->ID );

				if ( is_wp_error( $sideload->result ) ) {
					$feedback_message[] = $featured_error;
				}
			}
		}

	// Delete the featured image
	} elseif ( ! empty( $thumbnail_id ) ) {
		delete_post_thumbnail( $talk );
	}

	// Update the talk
	$id = wct_talks_save_talk( $updated );

	if ( empty( $id ) ) {
		// Set the feedback for the user
		$featured_type    = 'error';
		$feedback_message = __( 'Something went wrong while trying to update your talk.', 'wordcamp-talks' );

		// Redirect to the form
		$redirect = wct_get_form_url( wct_edit_slug(), $talk_name );

	// Redirect to the talk
	} else {
		$redirect = wct_talks_get_talk_permalink( $id );
	}

	if ( ! empty( $feedback_message ) ) {
		// Add feedback to the user
		wct_add_message( array(
			'type'    => $featured_type,
			'content' => join( ' ', $feedback_message ),
		) );
	}

	wp_safe_redirect( $redirect );
	exit();
}

/** Sticky talks **************************************************************/

/**
 * Gets the sticky talks
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @return array the list of IDs of sticked to front talks
 */
function wct_talks_get_stickies() {
	$sticky_talks = get_option( 'sticky_talks', array() );

	/**
	 * @param  array $sticky_talks the talks sticked to front archive page
	 */
	return apply_filters( 'wct_talks_get_stickies', $sticky_talks );
}

/**
 * Edit WP_Query posts to append sticky talks
 *
 * Simply a "copy paste" of how WordPress deals with sticky posts
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  array    $posts The array of retrieved posts.
 * @param  WP_Query The WP_Query instance
 * @return array the posts with stickies if some are found
 */
function wct_talks_stick_talks( $posts = array(), $wp_query = null ) {
	// Bail if sticky is disabled
	if ( ! wct_is_sticky_enabled() ) {
		return $posts;
	}

	$q = $wp_query->query_vars;
	$post_type = $q['post_type'];

	if ( 'talks' != $post_type ) {
		return $posts;
	}

	$page = absint( $q['paged'] );
	$search = $q['s'];

	if ( ! empty( $q['orderby'] ) ) {
		return $posts;
	}

	$sticky_posts = wct_talks_get_stickies();

	if ( wct_is_admin() ) {
		return $posts;
	}

	$post_type_landing_page = is_post_type_archive( $post_type ) && $page <= 1 && empty( $search );

	if ( empty( $post_type_landing_page ) || empty( $sticky_posts ) || ! empty( $q['ignore_sticky_posts'] ) ) {
		return $posts;
	}

	// Put sticky talks at the top of the posts array
	$num_posts = count( $posts );
	$sticky_offset = 0;
	// Loop over posts and relocate stickies to the front.
	for ( $i = 0; $i < $num_posts; $i++ ) {
		if ( in_array( $posts[$i]->ID, $sticky_posts ) ) {
			$sticky_post = $posts[$i];
			// Remove sticky from current position
			array_splice( $posts, $i, 1 );
			// Move to front, after other stickies
			array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
			// Increment the sticky offset. The next sticky will be placed at this offset.
			$sticky_offset++;
			// Remove post from sticky posts array
			$offset = array_search( $sticky_post->ID, $sticky_posts );
			unset( $sticky_posts[$offset] );
		}
	}

	// If any posts have been excluded specifically, Ignore those that are sticky.
	if ( ! empty( $sticky_posts ) && ! empty( $q['post__not_in'] ) )
		$sticky_posts = array_diff( $sticky_posts, $q['post__not_in'] );

	// Fetch sticky posts that weren't in the query results
	if ( ! empty( $sticky_posts ) ) {
		$stickies = get_posts( array(
			'post__in' => $sticky_posts,
			'post_type' => $post_type,
			'post_status' => 'publish',
			'nopaging' => true
		) );

		foreach ( $stickies as $sticky_post ) {
			$sticky_post->is_sticky = true;
			array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
			$sticky_offset++;
		}
	}

	return $posts;
}

/**
 * Checks if an talk is sticky
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  int $id The talk ID
 * @param  array $stickies the list of IDs of the sticky talks
 * @return bool true if it's a sticky talk, false otherwise
 */
function wct_talks_is_sticky( $id = 0, $stickies = array() ) {
	$id = absint( $id );

	if ( empty( $id ) ) {
		if ( ! wct()->query_loop->talk->ID ) {
			return false;
		} else {
			$id = wct()->query_loop->talk->ID;
		}
	}

	if ( empty( $stickies ) ) {
		$stickies = wct_talks_get_stickies();
	}

	if ( ! is_array( $stickies ) ) {
		return false;
	}

	if ( in_array( $id, $stickies ) ) {
		return true;
	}

	return false;
}

/**
 * Make sure sticky talks are not private or password protected
 *
 * @package WordCamp Talks
 * @subpackage talks/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Post $talk   the talk object
 * @return bool true if the talk cannot be sticked, false otherwise
 */
function wct_talks_admin_no_sticky( $talk = null ) {
	// bail if not set
	if ( empty( $talk ) ) {
		return false;
	}

	$no_sticky = ( 'private' == $talk->post_status || ! empty( $talk->post_password ) );

	/**
	 * @param  bool $no_sticky
	 * @param  WP_Post $talk   the talk object
	 */
	return (bool) apply_filters( 'wct_talks_admin_no_sticky', $no_sticky, $talk );
}

/** Featured images ***********************************************************/

/**
 * Simulate a tinymce plugin to intercept images once added to the
 * WP Editor
 *
 * @since 1.0.0
 *
 * @param  array $tinymce_plugins Just what the name of the param says!
 * @return array Tiny MCE plugins
 */
function wct_talks_tiny_mce_plugins( $tinymce_plugins = array() ) {
	if ( ! wct_featured_images_allowed() || ! current_theme_supports( 'post-thumbnails' ) ) {
		return $tinymce_plugins;
	}

	if ( ! wct_is_addnew() && ! wct_is_edit() ) {
		return $tinymce_plugins;
	}

	return array_merge( $tinymce_plugins, array( 'wpTalkStreamListImages' => wct_get_js_script( 'featured-images' ) ) );
}

function wct_do_embed( $content ) {
	global $wp_embed;

	return $wp_embed->autoembed( $content );
}
