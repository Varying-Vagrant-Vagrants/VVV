<?php
/**
 * WordCamp Talks template functions.
 *
 * @package   WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Check the main WordPress query to match WordCamp Talks conditions
 * Eventually Override query vars and set global template conditions / vars
 *
 * This the key function of the plugin, it is definining the templates
 * to load and is setting the displayed user.
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param WP_Query $posts_query The WP_Query instance
 */
function wct_parse_query( $posts_query = null ) {
	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() ) {
		return;
	}

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) ) {
		return;
	}

	// Handle the specific queries in the plugin's Admin screens
	if ( wct_is_admin() ) {

		// Display sticky talks if requested
		if ( wct_is_sticky_enabled() && ! empty( $_GET['sticky_talks'] ) ) {
			$posts_query->set( 'post__in', wct_talks_get_stickies() );
		}

		// Build meta_query if orderby rates is set
		if ( ! wct_is_rating_disabled() && ! empty( $_GET['orderby'] ) && 'rates_count' == $_GET['orderby'] ) {
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'EXISTS'
				)
			) );

			// Set the orderby talk var
			wct_set_global( 'orderby', 'rates_count' );
		}

		// Build a meta query to filter by workflow state
		if ( ! empty( $_REQUEST['workflow_states'] ) ) {
			$admin_meta_query = array();

			if ( 'pending' == $_REQUEST['workflow_states'] ) {
				$admin_meta_query = array(
					'key'     => '_wc_talks_workflow_state',
					'compare' => 'NOT EXISTS'
				);
			} else {
				$admin_meta_query = array(
					'key'     => '_wc_talks_workflow_state',
					'compare' => '=',
					'value'   => $_REQUEST['workflow_states']
				);
			}

			$posts_query->set( 'meta_query', array( $admin_meta_query ) );
		}

		do_action( 'wct_admin_request', $posts_query );

		return;
	}

	// Bail if else where in admin
	if ( is_admin() ) {
		return;
	}

	// Talks post type for a later use
	$talk_post_type = wct_get_post_type();

	/** User's profile ************************************************************/

	// Are we requesting the user-profile template ?
	$user       = $posts_query->get( wct_user_rewrite_id() );
	$embed_page = wct_is_embed_profile();

	if ( ! empty( $user ) ) {

		if ( ! is_numeric( $user ) ) {
			// Get user by his username
			$user = wct_users_get_user_data( 'slug', $user );
		} else {
			// Get user by his id
			$user = wct_users_get_user_data( 'id', $user );
		}

		// No user id: no profile!
		if ( empty( $user->ID ) || true === apply_filters( 'wct_users_is_spammy', is_multisite() && is_user_spammy( $user ), $user ) ) {
			$posts_query->set_404();

			// Make sure the WordPress Embed Template will be used
			if ( ( 'true' === get_query_var( 'embed' ) || true === get_query_var( 'embed' ) ) ) {
				$posts_query->is_embed = true;
				$posts_query->set( 'p', -1 );
			}

			return;
		}

		// Set the displayed user id
		wct_set_global( 'is_user', absint( $user->ID ) );

		// Make sure the post_type is set to talks.
		$posts_query->set( 'post_type', $talk_post_type );

		if ( wct_user_can( 'rate_talks' ) ) {
			// Are we requesting user rates
			$user_rates    = $posts_query->get( wct_user_rates_rewrite_id() );

			// Are we requesting user's ideas to rate?
			$user_to_rate  = $posts_query->get( wct_user_to_rate_rewrite_id() );
		}

		if ( wct_user_can( 'comment_talks' ) ) {
			// Or user comments ?
			$user_comments = $posts_query->get( wct_user_comments_rewrite_id() );
		}

		if ( ! empty( $user_rates ) && ! wct_is_rating_disabled() ) {
			// We are viewing user's rates
			wct_set_global( 'is_user_rates', true );

			// Define the Meta Query to get his rates
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_rates',
					'value'   => ';i:' . $user->ID .';',
					'compare' => 'LIKE'
				)
			) );

		} else if ( ! empty( $user_to_rate ) && ! wct_is_user_to_rate_disabled() ) {
			// We are viewing user's ideas to rate
			wct_set_global( 'is_user_to_rate', true );

			// Define the Meta Query to get the not rated yet talks
			$posts_query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => '_wc_talks_rates',
					'value'   => ';i:' . $user->ID .';',
					'compare' => 'NOT LIKE'
				),
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'NOT EXISTS'
				)
			) );

			// We need to see all ideas, not only the one of the current displayed user
			$posts_query->set( 'author', 0 );

		} else if ( ! empty( $user_comments ) ) {
			// We are viewing user's comments
			wct_set_global( 'is_user_comments', true );

			/**
			 * Make sure no result.
			 * Query will be built later in user comments loop
			 */
			$posts_query->set( 'p', -1 );

		} else {
			if ( ( 'true' === get_query_var( 'embed' ) || true === get_query_var( 'embed' ) ) ) {
				$posts_query->is_embed = true;
				$posts_query->set( 'p', -1 );

				if ( $embed_page ) {
					wct_set_global( 'is_user_embed', true );
				} else {
					$posts_query->set_404();
					return;
				}
			}

			// Default to the talks the user submitted
			$posts_query->set( 'author', $user->ID  );
		}

		// No stickies on user's profile
		$posts_query->set( 'ignore_sticky_posts', true );

		// Set the displayed user.
		wct_set_global( 'displayed_user', $user );

		if ( wct_user_can( 'view_other_profiles' ) ) {
			// Make sure no 404
			$posts_query->is_404  = false;
		} else {
			// Make sure it is a 404!
			$posts_query->is_404  = true;
			return;
		}
	}

	/** Actions (New Talk) ********************************************************/

	$action = $posts_query->get( wct_action_rewrite_id() );

	if ( ! empty( $action ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define a global to inform we're dealing with an action
		wct_set_global( 'is_action', true );

		// Is the new talk form requested ?
		if ( wct_addnew_slug() == $action ) {
			// Yes so set the corresponding var
			wct_set_global( 'is_new', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		// Edit action ?
		} else if ( wct_edit_slug() == $action ) {
			// Yes so set the corresponding var
			wct_set_global( 'is_edit', true );

		// Signup support
		} else if ( wct_signup_slug() == $action && wct_is_signup_allowed_for_current_blog() ) {
			// Set the signup global var
			wct_set_global( 'is_signup', true );

			/**
			 * Make sure no result.
			 * We are not querying any content, but creating one
			 */
			$posts_query->set( 'p', -1 );

		} else if ( has_action( 'wct_custom_action' ) ) {
			/**
			 * Allow plugins to other custom talk actions
			 *
			 * @param string   $action      The requested action
			 * @param WP_Query $posts_query The WP_Query instance
			 */
			do_action( 'wct_custom_action', $action, $posts_query );
		} else {
			$posts_query->set_404();
			return;
		}
	}

	/** Talks by category *********************************************************/

	$category = $posts_query->get( wct_get_category() );

	if ( ! empty( $category ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the current category
		wct_set_global( 'is_category', $category );
	}

	/** Talks by tag **************************************************************/

	$tag = $posts_query->get( wct_get_tag() );

	if ( ! empty( $tag ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the current tag
		wct_set_global( 'is_tag', $tag );
	}


	/** Searching talks ***********************************************************/

	$search = $posts_query->get( wct_search_rewrite_id() );

	if ( ! empty( $search ) ) {
		// Make sure the post type is set to talks
		$posts_query->set( 'post_type', $talk_post_type );

		// Define the query as a search one
		$posts_query->set( 'is_search', true );

		/**
		 * Temporarly set the 's' parameter of WP Query
		 * This will be reset while building talks main_query args
		 * @see wct_set_template()
		 */
		$posts_query->set( 's', $search );

		// Set the search conditionnal var
		wct_set_global( 'is_search', true );
	}

	/** Changing order ************************************************************/

	// Here we're using built-in var
	$orderby = $posts_query->get( 'orderby' );

	// Make sure we are ordering talks
	if ( ! empty( $orderby ) && $talk_post_type == $posts_query->get( 'post_type' ) ) {

		if ( ! wct_is_rating_disabled() && 'rates_count' == $orderby ) {
			/**
			 * It's an order by rates request, set the meta query to achieve this.
			 * Here we're not ordering yet, we simply make sure to get talks that
			 * have been rated.
			 * Order will happen thanks to wct_set_rates_count_orderby()
			 * filter.
			 */
			$posts_query->set( 'meta_query', array(
				array(
					'key'     => '_wc_talks_average_rate',
					'compare' => 'EXISTS'
				)
			) );
		}

		// Set the order by var
		wct_set_global( 'orderby', $orderby );
	}

	// Set the talk archive var if viewing talks archive
	if ( $posts_query->is_post_type_archive() ) {
		wct_set_global( 'is_talks_archive', true );
	}

	// Finally if post_type is talks, then we're in a plugin's area.
	if ( $talk_post_type === $posts_query->get( 'post_type' ) ) {
		wct_set_global( 'is_talks', true );

		// Reset the pagination
		if ( -1 !== $posts_query->get( 'p' ) ) {
			$posts_query->set( 'posts_per_page', wct_talks_per_page() );
		}
	}
}

/**
 * Loads the plugin's stylesheet
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 */
function wct_enqueue_style() {
	$style_deps = apply_filters( 'wct_style_deps', array( 'dashicons' ) );
	wp_enqueue_style( 'wc-talks-style', wct_get_stylesheet(), $style_deps, wct_get_version() );

	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	if ( wct_is_user_profile() && wct_is_embed_profile() ) {
		wp_enqueue_style( 'wc-talks-sharing-profile', includes_url( "css/wp-embed-template{$min}.css" ), array(), wct_get_version() );
	}
}

/**
 * Loads the embed stylesheet to be used inside embed templates
 *
 * @since 1.0.0
 */
function wct_enqueue_embed_style() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$min = '';
	}

	wp_enqueue_style( 'wc-talks-embed-style', wct_get_stylesheet( "embed-style{$min}" ), array(), wct_get_version() );
}

/** Conditional template tags *************************************************/

/**
 * Is this a plugin's Administration screen?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if displaying a plugin's admin screen, false otherwise
 */
function wct_is_admin() {
	$retval = false;

	// using this as is_admin() can be true in case of AJAX
	if ( ! function_exists( 'get_current_screen' ) ) {
		return $retval;
	}

	// Get current screen
	$current_screen = get_current_screen();

	// Make sure the current screen post type is step and is the talks one
	if ( ! empty( $current_screen->post_type ) && wct_get_post_type() == $current_screen->post_type ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this Plugin's front end area?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing a plugin's front end page, false otherwise
 */
function wct_is_talks() {
	return (bool) wct_get_global( 'is_talks' );
}

/**
 * Is this the new talk form ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if on the addnew form, false otherwise
 */
function wct_is_addnew() {
	return (bool) wct_get_global( 'is_new' );
}

/**
 * Is this the edit talk form ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if on the edit form, false otherwise
 */
function wct_is_edit() {
	return (bool) wct_get_global( 'is_edit' );
}

/**
 * Is this the signup form ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if on the edit form, false otherwise
 */
function wct_is_signup() {
	return (bool) wct_get_global( 'is_signup' );
}

/**
 * Are we viewing a single talk ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if on a single talk template, false otherwise
 */
function wct_is_single_talk() {
	return (bool) apply_filters( 'wct_is_single_talk', is_singular( wct_get_post_type() ) );
}

/**
 * Current ID for the talk being viewed
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return int the current talk ID
 */
function wct_get_single_talk_id() {
	return (int) apply_filters( 'wct_get_single_talk_id', wct_get_global( 'single_talk_id' ) );
}

/**
 * Are we viewing talks archive ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if on talks archive, false otherwise
 */
function wct_is_talks_archive() {
	$retval = false;

	if ( is_post_type_archive( wct_get_post_type() ) || wct_get_global( 'is_talks_archive' ) ) {
		$retval = true;
	}

	return apply_filters( 'wct_is_talks_archive', $retval );
}

/**
 * Are we viewing talks by category ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing talks categorized in a sepecific term, false otherwise.
 */
function wct_is_category() {
	$retval = false;

	if ( is_tax( wct_get_category() ) || wct_get_global( 'is_category' ) ) {
		$retval = true;
	}

	return apply_filters( 'wct_is_category', $retval );
}

/**
 * Are we viewing talks by tag ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 * @return bool true if viewing talks tagged with a sepecific term, false otherwise.
 */
function wct_is_tag() {
	$retval = false;

	if ( is_tax( wct_get_tag() ) || wct_get_global( 'is_tag' ) ) {
		$retval = true;
	}

	return apply_filters( 'wct_is_tag', $retval );
}

/**
 * Get / Set the current term being viewed
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 * @return object $current_term
 */
function wct_get_current_term() {
	$current_term = wct_get_global( 'current_term' );

	if ( empty( $current_term ) ) {
		$current_term = get_queried_object();
	}

	wct_set_global( 'current_term', $current_term );

	return apply_filters( 'wct_get_current_term', $current_term );
}

/**
 * Get the current term name
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return string the term name
 */
function wct_get_term_name() {
	$term = wct_get_current_term();

	return apply_filters( 'wct_get_term_name', $term->name );
}

/**
 * Are we searching talks ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if an talk search is performed, otherwise false
 */
function wct_is_search() {
	$retval = false;

	if ( get_query_var( wct_search_rewrite_id() ) || wct_get_global( 'is_search' ) ) {
		$retval = true;
	}

	return apply_filters( 'wct_is_search', $retval );
}

/**
 * Has the order changed to the type being checked
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param  string $type the order to check
 * @return bool true if the order has changed from default one, false otherwise
 */
function wct_is_orderby( $type = '' ) {
	$retval = false;

	$orderby = wct_get_global( 'orderby' );

	if ( empty( $orderby ) ) {
		$orderby = get_query_var( 'orderby' );
	}

	if ( ! empty( $orderby ) && $orderby == $type ) {
		$retval = true;
	}

	return apply_filters( 'wct_is_orderby', $retval, $type );
}

/**
 * Are viewing a user's profile ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true a user's profile is being viewed, false otherwise
 */
function wct_is_user_profile() {
	return (bool) apply_filters( 'wct_is_user_profile', wct_get_global( 'is_user' ) );
}

/**
 * Are we viewing comments in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing user's profile comments, false otherwise
 */
function wct_is_user_profile_comments() {
	return (bool) apply_filters( 'wct_is_user_profile_comments', wct_get_global( 'is_user_comments' ) );
}

/**
 * Are we viewing rates in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing user's profile rates, false otherwise
 */
function wct_is_user_profile_rates() {
	return (bool) apply_filters( 'wct_is_user_profile_rates', wct_get_global( 'is_user_rates' ) );
}

/**
 * Are we viewing the "to rate" area of the user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing user's profile to rate, false otherwise.
 */
function wct_is_user_profile_to_rate() {
	return (bool) apply_filters( 'wct_is_user_profile_to_rate', wct_get_global( 'is_user_to_rate' ) );
}

/**
 * Are we viewing talks in user's profile
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if viewing talks in the user's profile, false otherwise
 */
function wct_is_user_profile_talks() {
	return (bool) ( ! wct_is_user_profile_comments() && ! wct_is_user_profile_rates() && ! wct_is_user_profile_to_rate() );
}

/**
 * Is this self profile ?
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @return bool true if current user is viewing his profile, false otherwise
 */
function wct_is_current_user_profile() {
	$current_user      = wct_get_global( 'current_user' );
	$displayed_user_id = wct_get_global( 'is_user' );

	if( empty( $current_user->ID ) ) {
		return false;
	}

	$is_user_profile = ( $current_user->ID == $displayed_user_id );

	/**
	 * @param  bool $is_user_profile whether the user is viewing his profile or not
	 */
	return (bool) apply_filters( 'wct_is_current_user_profile', $is_user_profile );
}

/**
 * Reset the page (post) title depending on the context
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param string $context the context to build the title for
 * @return string the post title
 */
function wct_reset_post_title( $context = '' ) {
	$post_title = wct_archive_title();

	switch( $context ) {
		case 'archive' :
			if ( wct_user_can( 'publish_talks' ) ) {
				$post_title =  '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
				$post_title .= ' <a href="' . esc_url( wct_get_form_url() ) .'" class="button wpis-title-button">' . esc_html__( 'Add new', 'wordcamp-talks' ) . '</a>';
			}
			break;

		case 'taxonomy' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . wct_get_term_name();
			break;

		case 'user-profile':
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . sprintf( esc_html__( '%s&#39;s profile', 'wordcamp-talks' ), wct_users_get_displayed_user_displayname() );
			break;

		case 'new-talk' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'New Talk', 'wordcamp-talks' );
			break;

		case 'edit-talk' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'Edit Talk', 'wordcamp-talks' );
			break;

		case 'signup' :
			$post_title = '<a href="' . esc_url( wct_get_root_url() ) . '">' . $post_title . '</a>';
			$post_title .= '<span class="talk-title-sep"></span>' . __( 'Create an account', 'wordcamp-talks' );
			break;
	}

	/**
	 * @param  string $post_title the title for the template
	 * @param  string $context the context
	 */
	return apply_filters( 'wct_reset_post_title', $post_title, $context );
}

/**
 * Filters the <title> content
 *
 * Inspired by bbPress's bbp_title()
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param array $title the title parts
 * @return string the page title meta tag
 */
function wct_title( $title_array = array() ) {
	if ( ! wct_is_talks() ) {
		return $title_array;
	}

	$new_title = array();

	if ( wct_is_addnew() ) {
		$new_title[] = esc_attr__( 'New talk', 'wordcamp-talks' );
	} elseif ( wct_is_edit() ) {
		$new_title[] = esc_attr__( 'Edit talk', 'wordcamp-talks' );
	} elseif ( wct_is_user_profile() ) {
		$new_title[] = sprintf( esc_html__( '%s&#39;s profile', 'wordcamp-talks' ), wct_users_get_displayed_user_displayname() );
	} elseif ( wct_is_single_talk() ) {
		$new_title[] = single_post_title( '', false );
	} elseif ( is_tax() ) {
		$term = wct_get_current_term();
		if ( $term ) {
			$tax = get_taxonomy( $term->taxonomy );

			// Catch the term for later use
			wct_set_global( 'current_term', $term );

			$new_title[] = single_term_title( '', false );
			$new_title[] = $tax->labels->name;
		}
	} elseif ( wct_is_signup() ) {
		$new_title[] = esc_html__( 'Create an account', 'wordcamp-talks' );
	} else {
		$new_title[] = esc_html__( 'Talks', 'wordcamp-talks' );
	}

	// Compare new title with original title
	if ( empty( $new_title ) ) {
		return $title_array;
	}

	$title_array = array_diff( $title_array, $new_title );
	$new_title_array = array_merge( $title_array, $new_title );

	/**
	 * @param  string $new_title the filtered title
	 * @param  string $sep
	 * @param  string $seplocation
	 * @param  string $title the original title meta tag
	 */
	return apply_filters( 'wct_title', $new_title_array, $title_array, $new_title );
}

/**
 * Set the document title for plugin's front end pages
 *
 * @since  1.0.0
 *
 * @param  array  $document_title The WordPress Document title
 * @return array                  The Document title
 */
function wct_document_title_parts( $document_title = array() ) {
	if ( ! wct_is_talks() ) {
		return $document_title;
	}

	$new_document_title = $document_title;

	// Reset the document title if needed
	if ( ! wct_is_single_talk() ) {
		$title = (array) wct_title();

		// On user's profile, add some piece of info
		if ( wct_is_user_profile() && count( $title ) === 1 ) {
			// Seeing comments of the user
			if ( wct_is_user_profile_comments() ) {
				$title[] = __( 'Talk Comments', 'wordcamp-talks' );

				// Get the pagination page
				if ( get_query_var( wct_cpage_rewrite_id() ) ) {
					$cpage = get_query_var( wct_cpage_rewrite_id() );

				} elseif ( ! empty( $_GET[ wct_cpage_rewrite_id() ] ) ) {
					$cpage = $_GET[ wct_cpage_rewrite_id() ];
				}

				if ( ! empty( $cpage ) ) {
					$title['page'] = sprintf( __( 'Page %s', 'wordcamp-talks' ), (int) $cpage );
				}

			// Seeing Ratings for the user
			} elseif( wct_is_user_profile_rates() ) {
				$title[] = __( 'Talk Ratings', 'wordcamp-talks' );

			// Seeing The root profile
			} else {
				$title[] = __( 'Talks', 'wordcamp-talks' );
			}
		}

		// Get WordPress Separator
		$sep = apply_filters( 'document_title_separator', '-' );

		$new_document_title['title'] = implode( " $sep ", array_filter( $title ) );;
	}

	// Set the site name if not already set.
	if ( ! isset( $new_document_title['site'] ) ) {
		$new_document_title['site'] = get_bloginfo( 'name', 'display' );
	}

	// Unset tagline for Plugin's front end Pages
	if ( isset( $new_document_title['tagline'] ) ) {
		unset( $new_document_title['tagline'] );
	}

	return apply_filters( 'wct_document_title_parts', $new_document_title, $document_title );
}

/**
 * Remove the site description from title.
 * @todo we should make sure $wp_query->is_home is false in a future release
 *
 * @since 1.0.0
 *
 * @param  string $new_title the filtered title
 * @param  string $sep
 * @param  string $seplocation
 */
function wct_title_adjust( $title = '', $sep = '&raquo;', $seplocation = '' ) {
	if ( ! wct_is_talks() ) {
		return $title;
	}

	$site_description = get_bloginfo( 'description', 'display' );
	if ( ! empty( $sep ) ) {
		$site_description = ' ' . $sep . ' ' . $site_description;
	}

	$new_title = str_replace( $site_description, '', $title );

	return apply_filters( 'wct_title_adjust', $new_title, $title, $sep, $seplocation );
}

/**
 * Output a body class if in a plugin's front end area.
 *
 * Inspired by bbPress's bbp_body_class()
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param  array $wp_classes
 * @param  array $custom_classes
 * @return array the new Body Classes
 */
function wct_body_class( $wp_classes, $custom_classes = false ) {

	$talks_classes = array();

	/** Plugin's area *********************************************************/

	if ( wct_is_talks() ) {
		$talks_classes[] = 'talks';
	}

	// Force Twentyseventeen to display the one column style
	if ( 'twentyseventeen' === get_template() ) {
		$wp_classes = array_diff( $wp_classes, array( 'has-sidebar', 'page-two-column', 'blog' ) );
		$talks_classes[] = 'page-one-column';
	}

	/** Clean up **************************************************************/

	// Merge WP classes with Plugin's classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $talks_classes, (array) $wp_classes ) );

	/**
	 * @param array $classes returned classes.
	 * @param array $wc_talks_classes specific classes to the plugin.
	 * @param array $wp_classes regular WordPress classes.
	 * @param array $custom_classes.
	 */
	return apply_filters( 'wct_body_class', $classes, $talks_classes, $wp_classes, $custom_classes );
}

/**
 * Adds a 'type-page' class as the page template is the the most commonly targetted
 * as the root template.
 *
 * NB: TwentySixteen needs this to display the content on full available width
 *
 * @since  1.0.0
 *
 * @param  $wp_classes
 * @param  $theme_class
 * @return array Plugin's Post Classes
 */
function wct_post_class( $wp_classes, $theme_class ) {
	if ( wct_is_talks() ) {
		$classes = array_unique( array_merge( array( 'type-page' ), (array) $wp_classes ) );
	} else {
		$classes = $wp_classes;
	}

	return apply_filters( 'wct_post_class', $classes, $wp_classes, $theme_class );
}

/**
 * Reset postdata if needed
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 */
function wct_maybe_reset_postdata() {
	if ( wct_get_global( 'needs_reset' ) ) {
		wp_reset_postdata();

		do_action( 'wct_maybe_reset_postdata' );
	}
}

/**
 * Filters nav menus looking for the root page to eventually make it current if not the
 * case although it's a plugin's front end area.
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param  array  $sorted_menu_items list of menu items of the wp_nav menu
 * @param  array  $args
 * @return array  the menu items with specific classes if needed
 */
function wct_wp_nav( $sorted_menu_items = array(), $args = array() ) {

	if ( ! wct_is_talks() ) {
		return $sorted_menu_items;
	}

	foreach ( $sorted_menu_items as $key => $menu ) {

		if( wct_get_root_url() != $menu->url ){
			// maybe unset parent page if not the talks root
			if ( in_array( 'current_page_parent', $menu->classes ) ) {
				$sorted_menu_items[$key]->classes = array_diff( $menu->classes, array( 'current_page_parent' ) );
			}
		} else {
			if ( ! in_array( 'current-menu-item', $menu->classes ) ) {
				$sorted_menu_items[$key]->classes = array_merge( $menu->classes, array( 'current-menu-item' ) );
			}
		}
	}

	return apply_filters( 'wct_wp_nav', $sorted_menu_items );
}

/**
 * Filters edit post link to avoid its display when needed
 *
 * @package WordCamp Talks
 * @subpackage core/template-functions
 *
 * @since 1.0.0
 *
 * @param  string $edit_link the link to edit the post
 * @return mixed false if needed, original edit link otherwise
 */
function wct_edit_post_link( $edit_link = '', $post_id = 0 ) {
	/**
	 * using the capability check prevents edit link to display in case current user is the
	 * author of the talk and don't have the minimal capability to open the talk in WordPress
	 * Administration edit screen
	 */
	if ( wct_is_talks() && ( 0 === $post_id || ! wct_user_can( 'edit_talks' ) ) ) {
		/**
		 * @param  bool   false to be sure the edit link won't show
		 * @param  string $edit_link
		 * @param  int    $post_id
		 */
		return apply_filters( 'wct_edit_post_link', false, $edit_link, $post_id );
	}

	return $edit_link;
}

/**
 * Use the Embed Profile template when an Embed profile is requested
 *
 * @since 1.0.0
 *
 * @param  string $template The WordPress Embed template
 * @return string           The appropriate template to use
 */
function wct_embed_profile( $template = '' ) {
	if ( ! wct_get_global( 'is_user_embed' ) || ! wct_get_global( 'is_user' ) ) {
		return $template;
	}

	return wct_get_template_part( 'embed', 'profile', false );
}

/**
 * Adds oEmbed discovery links in the website <head> for the Talk user's profile root page.
 *
 * @since 1.0.0
 */
function wct_oembed_add_discovery_links() {
	if ( ! wct_is_user_profile_talks() || ! wct_is_embed_profile() ) {
		return;
	}

	$user_link = wct_users_get_user_profile_url( wct_users_displayed_user_id(), '', true );
	$output = '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( $user_link ) ) . '" />' . "\n";

	if ( class_exists( 'SimpleXMLElement' ) ) {
		$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( $user_link, 'xml' ) ) . '" />' . "\n";
	}

	/**
	 * Filter the oEmbed discovery links HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output HTML of the discovery links.
	 */
	echo apply_filters( 'wct_users_oembed_add_discovery_links', $output );
}
