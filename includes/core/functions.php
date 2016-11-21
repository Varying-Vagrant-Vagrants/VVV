<?php
/**
 * WordCamp Talks Functions.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Globals *******************************************************************/

/**
 * Get the plugin's current version
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string Plugin's current version
 */
function wct_get_version() {
	return wct()->version;
}

/**
 * Get the DB verion of the plugin
 *
 * Used to check wether to run the upgrade
 * routine of the plugin.
 * @see  core/upgrade > wct_is_upgrade()
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string DB version of the plugin
 */
function wct_db_version() {
	return get_option( '_wc_talks_version', 0 );
}

/**
 * Get plugin's basename
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string Plugin's basename
 */
function wct_get_basename() {
	return apply_filters( 'wct_get_basename', wct()->basename );
}

/**
 * Get plugin's main path
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string plugin's main path
 */
function wct_get_plugin_dir() {
	return apply_filters( 'wct_get_plugin_dir', wct()->plugin_dir );
}

/**
 * Get plugin's main url
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string plugin's main url
 */
function wct_get_plugin_url() {
	return apply_filters( 'wct_get_plugin_url', wct()->plugin_url );
}

/**
 * Get plugin's javascript url
 *
 * That's where the plugin's js file are all available
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string plugin's javascript url
 */
function wct_get_js_url() {
	return apply_filters( 'wct_get_js_url', wct()->js_url );
}

/**
 * Get a specific javascript file url (minified or not)
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  string $script the name of the script
 * @return string         url to the minified or regular script
 */
function wct_get_js_script( $script = '' ) {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	return wct_get_js_url() . $script . $min . '.js';
}

/**
 * Get plugin's path to includes directory
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string includes directory path
 */
function wct_get_includes_dir() {
	return apply_filters( 'wct_get_includes_dir', wct()->includes_dir );
}

/**
 * Get plugin's url to includes directory
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string includes directory url
 */
function wct_get_includes_url() {
	return apply_filters( 'wct_get_includes_url', wct()->includes_url );
}

/**
 * Get plugin's path to templates directory
 *
 * That's where all specific plugin's templates are located
 * You can create a directory called 'wordcamp-talks' in your theme
 * copy the content of this folder in it and customize the templates
 * from your theme's 'wordcamp-talks' directory. Templates in there
 * will override plugin's default ones.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string path to templates directory
 */
function wct_get_templates_dir() {
	return apply_filters( 'wct_get_templates_dir', wct()->templates_dir );
}

/**
 * Set a global var to be used by the plugin at different times
 * during WordPress loading process.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  string $var_key   the key to access to the globalized value
 * @param  mixed  $var_value a value to globalize, can be object, array, int.. whatever
 */
function wct_set_global( $var_key = '', $var_value ='' ) {
	return wct()->set_global( $var_key, $var_value );
}

/**
 * Get a global var set thanks to wct_set_global()
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  string $var_key the key to access to the globalized value
 * @return mixed           the globalized value for the requested key
 */
function wct_get_global( $var_key = '' ) {
	return wct()->get_global( $var_key );
}

/** Post Type (talks) *********************************************************/

/**
 * Outputs the post type identifier (talks) for the plugin
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string the post type identifier
 */
function wct_post_type() {
	echo wct_get_post_type();
}

	/**
	 * Gets the post type identifier (talks)
	 *
	 * @package WordCamp Talks
	 * @subpackage core/functions
	 *
	 * @since 1.0.0
	 *
	 * @return string the post type identifier
	 */
	function wct_get_post_type() {
		return apply_filters( 'wct_get_post_type', wct()->post_type );
	}

/**
 * Gets plugin's main post type init arguments
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array the init arguments for the 'talks' post type
 */
function wct_post_type_register_args() {
	$supports = array( 'title', 'editor', 'author', 'comments', 'revisions' );

	if ( wct_featured_images_allowed() ) {
		$supports[] = 'thumbnail';
	}

	return apply_filters( 'wct_post_type_register_args', array(
		'public'              => true,
		'query_var'           => wct_get_post_type(),
		'rewrite'             => array(
			'slug'            => wct_talk_slug(),
			'with_front'      => false
		),
		'has_archive'         => wct_root_slug(),
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => wct_user_can( 'wct_talks_admin' ),
		'menu_icon'           => 'dashicons-megaphone',
		'supports'            => $supports,
		'taxonomies'          => array(
			wct_get_category(),
			wct_get_tag()
		),
		'capability_type'     => array( 'talk', 'talks' ),
		'capabilities'        => wct_get_post_type_caps(),
		'delete_with_user'    => true,
		'can_export'          => true,
	) );
}

/**
 * Gets the labels for the plugin's post type
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array post type labels
 */
function wct_post_type_register_labels() {
	return apply_filters( 'wct_post_type_register_labels', array(
		'labels' => array(
			'name'                  => __( 'Talks',                     'wordcamp-talks' ),
			'menu_name'             => _x( 'Talks', 'Main Plugin menu', 'wordcamp-talks' ),
			'all_items'             => __( 'All Talks',                 'wordcamp-talks' ),
			'singular_name'         => __( 'Talk',                      'wordcamp-talks' ),
			'add_new'               => __( 'Add New Talk',              'wordcamp-talks' ),
			'add_new_item'          => __( 'Add New Talk',              'wordcamp-talks' ),
			'edit_item'             => __( 'Edit Talk',                 'wordcamp-talks' ),
			'new_item'              => __( 'New Talk',                  'wordcamp-talks' ),
			'view_item'             => __( 'View Talk',                 'wordcamp-talks' ),
			'search_items'          => __( 'Search Talks',              'wordcamp-talks' ),
			'not_found'             => __( 'No Talks Found',            'wordcamp-talks' ),
			'not_found_in_trash'    => __( 'No Talks Found in Trash',   'wordcamp-talks' ),
			'insert_into_item'      => __( 'Insert into talk',          'wordcamp-talks' ),
			'uploaded_to_this_item' => __( 'Uploaded to this talk',     'wordcamp-talks' ),
			'filter_items_list'     => __( 'Filter Talks list',         'wordcamp-talks' ),
			'items_list_navigation' => __( 'Talks list navigation',     'wordcamp-talks' ),
			'items_list'            => __( 'Talks list',                'wordcamp-talks' ),
		)
	) );
}

/**
 * Get plugin's post type "category" identifier (talk_categories)
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string hierarchical taxonomy identifier
 */
function wct_get_category() {
	return apply_filters( 'wct_get_category', wct()->category );
}

/**
 * Gets the "category" taxonomy init arguments
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array taxonomy init arguments
 */
function wct_category_register_args() {
	return apply_filters( 'wct_category_register_args', array(
		'rewrite'               => array(
			'slug'              => wct_category_slug(),
			'with_front'        => false,
			'hierarchical'      => false,
		),
		'capabilities'          => wct_get_category_caps(),
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => wct_get_category(),
		'hierarchical'          => true,
		'show_in_nav_menus'     => false,
		'public'                => true,
		'show_tagcloud'         => false,
	) );
}

/**
 * Get the "category" taxonomy labels
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array "category" taxonomy labels
 */
function wct_category_register_labels() {
	return apply_filters( 'wct_category_register_labels', array(
		'labels' => array(
			'name'             => __( 'Talk Categories',   'wordcamp-talks' ),
			'singular_name'    => __( 'Talk Category',     'wordcamp-talks' ),
			'edit_item'        => __( 'Edit Category',     'wordcamp-talks' ),
			'update_item'      => __( 'Update Category',   'wordcamp-talks' ),
			'add_new_item'     => __( 'Add New Category',  'wordcamp-talks' ),
			'new_item_name'    => __( 'New Category Name', 'wordcamp-talks' ),
			'all_items'        => __( 'All Categories',    'wordcamp-talks' ),
			'search_items'     => __( 'Search Categories', 'wordcamp-talks' ),
			'parent_item'      => __( 'Parent Category',   'wordcamp-talks' ),
			'parent_item_colon'=> __( 'Parent Category:',  'wordcamp-talks' ),
		)
	) );
}

/**
 * Get plugin's post type "tag" identifier (talk_tags)
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string non hierarchical taxonomy identifier
 */
function wct_get_tag() {
	return apply_filters( 'wct_get_tag', wct()->tag );
}

/**
 * Gets the "tag" taxonomy init arguments
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array taxonomy init arguments
 */
function wct_tag_register_args() {
	return apply_filters( 'wct_tag_register_args', array(
		'rewrite'               => array(
			'slug'              => wct_tag_slug(),
			'with_front'        => false,
			'hierarchical'      => false,
		),
		'capabilities'          => wct_get_tag_caps(),
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => wct_get_tag(),
		'hierarchical'          => false,
		'show_in_nav_menus'     => false,
		'public'                => true,
		'show_tagcloud'         => true,
	) );
}

/**
 * Get the "tag" taxonomy labels
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array "tag" taxonomy labels
 */
function wct_tag_register_labels() {
	return apply_filters( 'wct_tag_register_labels', array(
		'labels' => array(
			'name'                       => __( 'Talk Tags',                         'wordcamp-talks' ),
			'singular_name'              => __( 'Talk Tag',                          'wordcamp-talks' ),
			'edit_item'                  => __( 'Edit Tag',                          'wordcamp-talks' ),
			'update_item'                => __( 'Update Tag',                        'wordcamp-talks' ),
			'add_new_item'               => __( 'Add New Tag',                       'wordcamp-talks' ),
			'new_item_name'              => __( 'New Tag Name',                      'wordcamp-talks' ),
			'all_items'                  => __( 'All Tags',                          'wordcamp-talks' ),
			'search_items'               => __( 'Search Tags',                       'wordcamp-talks' ),
			'popular_items'              => __( 'Popular Tags',                      'wordcamp-talks' ),
			'separate_items_with_commas' => __( 'Separate tags with commas',         'wordcamp-talks' ),
			'add_or_remove_items'        => __( 'Add or remove tags',                'wordcamp-talks' ),
			'choose_from_most_used'      => __( 'Choose from the most popular tags', 'wordcamp-talks' )
		)
	) );
}

/** Urls **********************************************************************/

/**
 * Gets plugin's post type main url
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string root url for the post type
 */
function wct_get_root_url() {
	return apply_filters( 'wct_get_root_url', get_post_type_archive_link( wct_get_post_type() ) );
}

/**
 * Gets a specific "category" term url
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  object $category The term to build the url for
 * @return string           Url to reach all talks categorized with the requested term
 */
function wct_get_category_url( $category = null ) {
	if ( empty( $category ) ) {
		$category = wct_get_current_term();
	}

	$term_link = get_term_link( $category, wct_get_category() );

	/**
	 * @param  string $term_link Url to reach the talks categorized with the term
	 * @param  object $category  The term for this taxonomy
	 */
	return apply_filters( 'wct_get_category_url', $term_link, $category );
}

/**
 * Gets a specific "tag" term url
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  object $tag The term to build the url for
 * @return string      Url to reach all talks tagged with the requested term
 */
function wct_get_tag_url( $tag = '' ) {
	if ( empty( $tag ) ) {
		$tag = wct_get_current_term();
	}

	$term_link = get_term_link( $tag, wct_get_tag() );

	/**
	 * @param  string $term_link Url to reach the talks tagged with the term
	 * @param  object $tag       The term for this taxonomy
	 */
	return apply_filters( 'wct_get_tag_url', $term_link, $tag );
}

/**
 * Gets a global redirect url
 *
 * Used after posting an talk failed
 * Defaults to root url
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return string the url to redirect the user to
 */
function wct_get_redirect_url() {
	return apply_filters( 'wct_get_redirect_url', wct_get_root_url() );
}

/**
 * Gets the url to the form to submit new talks
 *
 * So far only adding new talks is supported, but
 * there will surely be an edit action to allow users
 * to edit their talks. Reason of the $type param
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @global $wp_rewrite
 * @param  string $type action (defaults to new)
 * @param  string $talk_name the post name of the talk to edit
 * @return string the url of the form to add talks
 */
function wct_get_form_url( $type = '', $talk_name = '' ) {
	global $wp_rewrite;

	if ( empty( $type ) ) {
		$type = wct_addnew_slug();
	}

	/**
	 * Early filter to override form url before being built
	 *
	 * @param mixed false or url to override
	 * @param string $type (only add new for now)
	 */
	$early_form_url = apply_filters( 'wct_pre_get_form_url', false, $type, $talk_name );

	if ( ! empty( $early_form_url ) ) {
		return $early_form_url;
	}

	// Pretty permalinks
	if ( $wp_rewrite->using_permalinks() ) {
		$url = $wp_rewrite->root . wct_action_slug() . '/%' . wct_action_rewrite_id() . '%';

		$url = str_replace( '%' . wct_action_rewrite_id() . '%', $type, $url );
		$url = home_url( user_trailingslashit( $url ) );

	// Unpretty permalinks
	} else {
		$url = add_query_arg( array( wct_action_rewrite_id() => $type ), home_url( '/' ) );
	}

	if ( $type == wct_edit_slug() && ! empty( $talk_name ) ) {
		$url = add_query_arg( wct_get_post_type(), $talk_name, $url );
	}

	/**
	 * Filter to override form url after being built
	 *
	 * @param string url to override
	 * @param string $type add new or edit
	 * @param string $talk_name the post name of the talk to edit
	 */
	return apply_filters( 'wct_get_form_url', $url, $type, $talk_name );
}

/** Feedbacks *****************************************************************/

/**
 * Add a new message to inform user
 *
 * Inspired by BuddyPress's bp_core_add_message() function
 *
 * package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  array  $message_data the type and content of the message
 */
function wct_add_message( $message_data = array() ) {
	// Success is the default
	if ( empty( $type ) ) {
		$type = 'success';
	}

	$r = wp_parse_args( $message_data, array(
		'type'    => 'success',
		'content' => __( 'Saved successfully', 'wordcamp-talks' ),
	) );

	// Send the values to the cookie for page reload display
	@setcookie( 'wc-talks-feedback',      $r['content'], time() + 60 * 60 * 24, COOKIEPATH );
	@setcookie( 'wc-talks-feedback-type', $r['type'],    time() + 60 * 60 * 24, COOKIEPATH );

	wct_set_global( 'feedback', $r );
}

/**
 * Sets a new message to inform user
 *
 * Inspired by BuddyPress's bp_core_setup_message() function
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 */
function wct_set_user_feedback() {
	// Check Global if any
	$feedback = wct_get_global( 'feedback' );

	// Check cookies if any
	if ( empty( $feedback ) && ! empty( $_COOKIE['wc-talks-feedback'] ) ) {
		wct_set_global( 'feedback', array(
			'type'    => wp_unslash( $_COOKIE['wc-talks-feedback-type'] ),
			'content' => wp_unslash( $_COOKIE['wc-talks-feedback'] ),
		) );
	}

	// Remove cookies if set.
	if ( isset( $_COOKIE['wc-talks-feedback'] ) ) {
		@setcookie( 'wc-talks-feedback', false, time() - 1000, COOKIEPATH );
	}

	if ( isset( $_COOKIE['wc-talks-feedback-type'] ) ) {
		@setcookie( 'wc-talks-feedback-type', false, time() - 1000, COOKIEPATH );
	}
}

/**
 * Displays the feedback message to user
 *
 * Inspired by BuddyPress's bp_core_render_message() function
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 */
function wct_user_feedback() {
	$feedback = wct_get_global( 'feedback' );

	if ( empty( $feedback ) || ! empty( $feedback['admin_notices'] ) ) {
		return;
	}

	// Display the message
	?>
	<div class="message <?php echo esc_attr( $feedback['type'] ); ?>">
		<p><?php echo esc_html( $feedback['content'] ); ?></p>
	</div>
	<?php
}

/** Rating Talks **************************************************************/

/**
 * Checks wether the builtin rating system should be used
 *
 * In previous versions of the plugin this was an option that
 * could be deactivated from plugin settings. This is no more
 * the case, as i think like comments, this is a core functionality
 * when managing talks. To deactivate the ratings, use the filter.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  int  $default   by default enabled
 * @return bool            True if disabled, false if enabled
 */
function wct_is_rating_disabled( $default = 0 ) {
	return (bool) apply_filters( 'wct_is_rating_disabled', $default );
}

/**
 * Gets a fallback hintlist for ratings
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return array the hintlist
 */
function wct_get_hint_list() {
	$hintlist = wct_hint_list();

	if ( empty( $hintlist ) ) {
		$hintlist = array(
			esc_html__( 'bad',      'wordcamp-talks' ),
			esc_html__( 'poor',     'wordcamp-talks' ),
			esc_html__( 'regular',  'wordcamp-talks' ),
			esc_html__( 'good',     'wordcamp-talks' ),
			esc_html__( 'gorgeous', 'wordcamp-talks' )
		);
	}

	return $hintlist;
}

/**
 * Count rating stats for a specific talk or gets the rating of a specific user for a given talk
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  integer $id      the ID of the talk object
 * @param  integer $user_id the user id
 * @param  boolean $details whether to include detailed stats
 * @return mixed            int|array the rate of the user or the stats
 */
function wct_count_ratings( $id = 0, $user_id = 0, $details = false ) {
	// Init a default array
	$retarray = array(
		'average' => 0,
		'users'   => array()
	);
	// Init a default user rating
	$user_rating = 0;

	// No talk, try to find it in the query loop
	if ( empty( $id ) ) {
		if ( ! wct()->query_loop->talk->ID ) {
			return $retarray;
		} else {
			$id = wct()->query_loop->talk->ID;
		}
	}

	// Get all the rates for the talk
	$rates = get_post_meta( $id, '_wc_talks_rates', true );

	// Build the stats
	if ( ! empty( $rates ) && is_array( $rates ) ) {
		foreach ( $rates as $rate => $users ) {
			// We need the user's rating
			if ( ! empty( $user_id ) && in_array( $user_id, (array) $users['user_ids'] ) ) {
				$user_rating = $rate;

			// We need average rating
			} else {
				$retarray['users'] = array_merge( $retarray['users'], (array) $users['user_ids'] );
				$retarray['average'] += $rate * count( (array) $users['user_ids'] );

				if ( ! empty( $details ) ) {
					$retarray['details'][ $rate ] = (array) $users['user_ids'];
				}
			}
		}
	}

	// Return the user rating
	if ( ! empty( $user_id ) ) {
		/**
		 * @param  int $user_rating the rate given by the user to the talk
		 * @param  int $id the ID of the talk
		 * @param  int $user_id the user id who rated the talk
		 */
		return apply_filters( 'wct_get_user_ratings', $user_rating, $id, $user_id );
	}

	if ( ! empty( $retarray['users'] ) ) {
		$retarray['average'] = number_format( $retarray['average'] / count( $retarray['users'] ), 1 );
	} else {
		$retarray['average'] = 0;
	}

	/**
	 * @param  array $retarray the talk rating stats
	 * @param  int $id the ID of the talk
	 * @param  array $rates all talks rates organized in an array
	 */
	return apply_filters( 'wct_count_ratings', $retarray, $id, $rates );
}

/**
 * Delete a specific rate for a given talk
 *
 * This action is only available from the talk edit Administration screen
 * @see  WordCamp_Talks_Admin->maybe_delete_rate() in admin/admin
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  int $talk    the ID of the talk
 * @param  int $user_id the ID of the user
 * @return mixed        string the new average rating or false if no more rates
 */
function wct_delete_rate( $talk = 0, $user_id = 0 ) {
	if ( empty( $talk ) || empty( $user_id ) ) {
		return false;
	}

	$rates = get_post_meta( $talk, '_wc_talks_rates', true );

	if ( empty( $rates ) ) {
		return false;
	} else {
		foreach ( $rates as $rate => $users ) {
			if ( in_array( $user_id, (array) $users['user_ids'] ) ) {
				$rates[ $rate ]['user_ids'] = array_diff( $users['user_ids'], array( $user_id ) );

				// Unset the rate if no more users.
				if ( count( $rates[ $rate ]['user_ids'] ) == 0 ) {
					unset( $rates[ $rate ] );
				}
			}
		}
	}

	if ( update_post_meta( $talk, '_wc_talks_rates', $rates ) ) {
		$ratings = wct_count_ratings( $talk );
		update_post_meta( $talk, '_wc_talks_average_rate', $ratings['average'] );

		/**
		 * @param  int $talk the ID of the talk
		 * @param  int $user_id the ID of the user
		 * @param  string       the formatted average.
		 */
		do_action( 'wct_deleted_rate', $talk, $user_id, $ratings['average'] );

		return $ratings['average'];
	} else {
		return false;
	}
}

/**
 * Saves a new rate for the talk
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  int $talk    the ID of the talk
 * @param  int $user_id the ID of the user
 * @param  int $rate    the rate of the user
 * @return mixed       string the new average rating or false if no more rates
 */
function wct_add_rate( $talk = 0, $user_id = 0, $rate = 0 ) {
	if ( empty( $talk ) || empty( $user_id ) || empty( $rate ) ) {
		return false;
	}

	$rates = get_post_meta( $talk, '_wc_talks_rates', true );

	if ( empty( $rates ) ) {
		$rates = array( $rate => array( 'user_ids' => array( 'u-' . $user_id => $user_id ) ) );
	} else if ( ! empty( $rates[ $rate ] ) && ! in_array( $user_id, $rates[ $rate ]['user_ids'] ) ) {
		$rates[ $rate ]['user_ids'] = array_merge( $rates[ $rate ]['user_ids'], array( 'u-' . $user_id => $user_id ) );
	} else if ( empty( $rates[ $rate ] ) ) {
		$rates = $rates + array( $rate => array( 'user_ids' => array( 'u-' . $user_id => $user_id ) ) );
	} else {
		return false;
	}

	if ( update_post_meta( $talk, '_wc_talks_rates', $rates ) ) {
		$ratings = wct_count_ratings( $talk );
		update_post_meta( $talk, '_wc_talks_average_rate', $ratings['average'] );

		/**
		 * @param  int $talk the ID of the talk
		 * @param  int $user_id the ID of the user
		 * @param  int $rate the user's rating
		 * @param  string       the formatted average.
		 */
		do_action( 'wct_added_rate', $talk, $user_id, $rate, $ratings['average'] );

		return $ratings['average'];
	} else {
		return false;
	}
}

/**
 * Intercepts the user ajax action to rate the talk
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return mixed the average rate or 0
 */
function wct_ajax_rate() {
	if ( ! wct_user_can( 'rate_talks' ) ) {
		exit( '0' );
	}

	$user_id = wct_users_current_user_id();
	$talk = ! empty( $_POST['talk'] ) ? absint( $_POST['talk'] ) : 0;
	$rate = ! empty( $_POST['rate'] ) ? absint( $_POST['rate'] ) : 0;

	check_ajax_referer( 'wct_rate', 'wpnonce' );

	$new_average_rate = wct_add_rate( $talk, $user_id, $rate );

	if ( empty( $new_average_rate ) ) {
		exit( '0' );
	} else {
		exit( $new_average_rate );
	}
}

/**
 * Order the talks by rates when requested
 *
 * This function is hooking to WordPress 'posts_clauses' filter. As the
 * rating query is first built by using a specific WP_Meta_Query, we need
 * to also make sure the ORDER BY clause of the sql query is customized.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  array    $clauses  the talk query sql parts
 * @param  WP_Query $wp_query the WordPress query object
 * @return array              new order clauses if needed
 */
function wct_set_rates_count_orderby( $clauses = array(), $wp_query = null ) {

	if ( ( wct_is_talks() || wct_is_admin() || wct_get_global( 'rating_widget' ) ) && wct_is_orderby( 'rates_count' ) ) {
		preg_match( '/\(?(\S*).meta_key = \'_wc_talks_average_rate\'/', $clauses['where'], $matches );
		if ( ! empty( $matches[1] ) ) {
			// default order
			$order = 'DESC';

			// Specific case for plugin's administration screens.
			if ( ! empty( $clauses['orderby'] ) && 'ASC' == strtoupper( substr( $clauses['orderby'], -3 ) ) ) {
				$order = 'ASC';
			}

			$clauses['orderby'] = "{$matches[1]}.meta_value + 0 {$order}";
		}
	}

	return $clauses;
}

/**
 * Retrieve total rates for a user.
 *
 * @since 1.0.0
 *
 * @global $wpdb
 * @param  int $user_id the User ID.
 * @return int Rates count.
 */
function wct_count_user_rates( $user_id = 0 ) {
	$count = 0;

	if ( empty( $user_id ) ) {
		return $count;
	}

	global $wpdb;
	$user_id = (int) $user_id;

	$count = wp_cache_get( "talk_rates_count_{$user_id}", 'wct' );

	if ( false !== $count ) {
		return $count;
	}

	$like  = '%' . $wpdb->esc_like( ';i:' . $user_id .';' ) . '%';
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( post_id ) FROM {$wpdb->postmeta} WHERE meta_key= %s AND meta_value LIKE %s", '_wc_talks_rates', $like ) );

	wp_cache_set( "talk_rates_count_{$user_id}", $count, 'wct' );

	return $count;
}

/**
 * Clean the user's rates count cache
 *
 * @since 2.3.0
 *
 * @param int $talk_id the talk ID
 * @param int $user_id the user ID
 */
function wct_clean_rates_count_cache( $talk_id, $user_id = 0 ) {
	// Bail if no user id
	if ( empty( $user_id ) ) {
		return;
	}

	$user_id = (int) $user_id;

	wp_cache_delete( "talk_rates_count_{$user_id}", 'wct' );
}

/** Utilities *****************************************************************/

/**
 * Creates a specific excerpt for the content of an talk
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  string  $text   the content to truncate
 * @param  integer $length the number of words
 * @param  string  $more   the more string
 * @return string          the excerpt of an talk
 */
function wct_create_excerpt( $text = '', $length = 55, $more = ' [&hellip;]', $nofilter = false ) {
	if ( empty( $text ) ) {
		return $text;
	}

	$text = strip_shortcodes( $text );

	/**
	 * Used internally to sanitize outputs
	 * @see  core/filters
	 *
	 * @param string $text the content without shortcodes
	 */
	$text = apply_filters( 'wct_create_excerpt_text', $text );

	$text = str_replace( ']]>', ']]&gt;', $text );

	if ( false === $nofilter ) {
		/**
		 * Filter the number of words in an excerpt.
		 */
		$excerpt_length = apply_filters( 'excerpt_length', $length );
		/**
		 * Filter the string in the "more" link displayed after a trimmed excerpt.
		 */
		$excerpt_more = apply_filters( 'excerpt_more', $more );
	} else {
		$excerpt_length = $length;
		$excerpt_more   = $more;
	}

	return wp_trim_words( $text, $excerpt_length, $excerpt_more );
}

/**
 * Prepare the content to be output in a csv file
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  string $content the content
 * @return string          the content to be displayed in a csv file
 */
function wct_generate_csv_content( $content = '' ) {
	// Avoid some chars
	$content = str_replace( array( '&#8212;', '"' ), array( 0, "'" ), $content );

	// Strip shortcodes
	$content = strip_shortcodes( $content );

	// Strip slashes
	$content = wp_unslash( $content );

	// Strip all tags
	$content = wp_strip_all_tags( $content, true );

	return apply_filters( 'wct_generate_csv_content', $content );
}

/**
 * Specific tag cloud count text callback
 *
 * By Default, WordPress uses "topic/s", This will
 * make sure "talk/s" will be used instead. Unfortunately
 * it's only possible in front end tag clouds.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  int $count Number of talks associated with the tag
 * @return string     the count text for talks
 */
function wct_tag_cloud_count_callback( $count = 0 ) {
	return sprintf( _nx( '%s talk', '%s talks', $count, 'talks tag cloud count text', 'wordcamp-talks' ), number_format_i18n( $count )  );
}

/**
 * Filters the tag cloud args by referencing a specific count text callback
 * if the plugin's "tag" taxonomy is requested.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  array  $args the tag cloud arguments
 * @return array        the arguments with the new count text callback if needed
 */
function wct_tag_cloud_args( $args = array() ) {
	if( ! empty( $args['taxonomy'] ) && wct_get_tag() == $args['taxonomy'] ) {
		$args['topic_count_text_callback'] = 'wct_tag_cloud_count_callback';
	}

	return $args;
}

/**
 * Generates a talk tags cloud
 *
 * Used when writing a new talk to allow the author to choose
 * one or more popular talk tags.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  integer $number number of tag to display
 * @param  array   $args   the tag cloud args
 * @return array           associative array containing the number of tags and the content of the cloud.
 */
function wct_generate_tag_cloud( $number = 10, $args = array() ) {
	$r = array( 'number' => $number, 'orderby' => 'count', 'order' => 'DESC' );

	if ( 'private' === wct_default_talk_status() ) {
		$r = array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' );
	}

	$tags = get_terms( wct_get_tag(), apply_filters( 'wct_generate_tag_cloud_args', $r ) );

	if ( empty( $tags ) ) {
		return;
	}

	foreach ( $tags as $key => $tag ) {
		$tags[ $key ]->link = '#';
		$tags[ $key ]->id = $tag->term_id;
	}

	$args = wp_parse_args( $args,
		wct_tag_cloud_args( array( 'taxonomy' => wct_get_tag() ) )
	);

	$retarray = array(
		'number'   => count( $tags ),
		'tagcloud' => wp_generate_tag_cloud( $tags, $args )
	);

	return apply_filters( 'wct_generate_tag_cloud', $retarray );
}

/**
 * Filters WP Editor Buttons depending on plugin's settings.
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  array  $buttons the list of buttons for the editor
 * @return array           the filtered list of buttons to match plugin's needs
 */
function wct_teeny_button_filter( $buttons = array() ) {

	$remove_buttons = array(
		'wp_more',
		'spellchecker',
		'wp_adv',
	);

	if ( ! wct_talk_editor_link() ) {
		$remove_buttons = array_merge( $remove_buttons, array(
			'link',
			'unlink',
		) );
	}

	// Remove unused buttons
	$buttons = array_diff( $buttons, $remove_buttons );

	// Eventually add the image button
	if ( wct_talk_editor_image() ) {
		$buttons = array_diff( $buttons, array( 'fullscreen' ) );
		array_push( $buttons, 'image', 'fullscreen' );
	}

	return $buttons;
}

/**
 * Since WP 4.3 _WP_Editors is now including the format_for_editor filter to sanitize
 * the content to edit. As we were using format_to_edit to sanitize the editor content,
 * it's then sanitized twice and tinymce fails to wysiwyg!
 *
 * So we just need to only apply format_to_edit if WP < 4.3!
 *
 * @since  1.0.0
 *
 * @param  string $text the editor content.
 * @return string the sanitized text or the text without any changes
 */
function wct_format_to_edit( $text = '' ) {
	if ( function_exists( 'format_for_editor' ) ) {
		return $text;
	}

	return format_to_edit( $text );
}

/**
 * Adds wct to global cache groups
 *
 * Mainly used to cach comments about talks count
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 */
function wct_cache_global_group() {
	wp_cache_add_global_groups( array( 'wct' ) );
}

/**
 * Adds a shortcut to plugin's Admin screens using the appearence menus
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @param  WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance
 */
function wct_adminbar_menu( $wp_admin_bar = null ){
	$use_admin_bar = apply_filters( 'wct_adminbar_menu', true );

	if ( empty( $use_admin_bar ) ) {
		return;
	}

	if ( ! empty( $wp_admin_bar ) && wct_user_can( 'edit_talks' ) ) {
		$menu_url = add_query_arg( 'post_type', wct_get_post_type(), admin_url( 'edit.php' ) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'wc_talks',
			'title'  => _x( 'WordCamp Talks', 'Admin bar menu', 'wordcamp-talks' ),
			'href'   => $menu_url,
		) );
	}
}

/**
 * Checks wether signups are allowed
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return bool true if user signups are allowed, false otherwise
 */
function wct_is_signup_allowed() {
	// Default to single site option
	$option = 'users_can_register';

	// Multisite config is using the registration site meta
	if ( is_multisite() ) {
		$option = 'registration';
	}

	$registration_status = get_site_option( $option, 0 );

	// On multisite config, just deal with user signups and avoid blog signups
	$signup_allowed = ( 1 == $registration_status || 'user' == $registration_status );

	return (bool) apply_filters( 'wct_is_signup_allowed', $signup_allowed );
}

/**
 * Checks wether signups are allowed for current blog
 *
 * @package WordCamp Talks
 * @subpackage core/functions
 *
 * @since 1.0.0
 *
 * @return bool true if signups are allowed for current site, false otherwise
 */
function wct_is_signup_allowed_for_current_blog() {
	$signups_allowed = wct_is_signup_allowed();

	if ( ! is_multisite() ) {
		return $signups_allowed;
	}

	return apply_filters( 'wct_is_signup_allowed_for_current_blog', wct_allow_signups() );
}
