<?php
/**
 * WordCamp Talks Comments functions.
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Set/Get comment(s) ********************************************************/

/**
 * Builds the talk comments object
 *
 * Adds usefull datas to check if the comments is about an talk
 * - post type
 * - post author
 * - post title
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  int  $comment_id the comment ID
 * @return array the list of comments matching arguments
 */
function wct_comments_get_comment( $comment_id = 0 ) {
	// Bail if comment id is not set
	if ( empty( $comment_id ) ) {
		return false;
	}

	$comment = get_comment( $comment_id );

	// Make sur the comment exist
	if ( empty( $comment ) ) {
		return false;
	}

	// Get and append post type if an talk one
	$post = get_post( $comment->comment_post_ID );

	if ( wct_get_post_type() == $post->post_type ) {
		$comment->comment_post_type   = $post->post_type;
		$comment->comment_post_author = $post->post_author;
		$comment->comment_post_title  = $post->post_title;
	}

	/**
	 * @param  object  $comment the comment object
	 * @param  WP_Post $post    the post the comment is linked to
	 */
	return apply_filters( 'wct_comments_get_comment', $comment, $post );
}

/**
 * Gets comments matching arguments
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  array  $args the arguments of the comments query
 * @return array        the list of comments matching arguments
 */
function wct_comments_get_comments( $args = array() ) {
	$comments_args = wp_parse_args( $args, array(
		'post_type'   => wct_get_post_type(),
		'post_status' => 'publish',
		'status'      => 'approve',
		'number'      => false,
		'offset'      => false,
		'fields'      => false,
		'post_id'     => 0
	) );

	return get_comments( $comments_args );
}

/**
 * Clean the talk's comment count cache
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  int     $comment_id the comment ID
 * @param  string  $status     its status
 */
function wct_comments_clean_count_cache( $comment_id = 0, $status = '' ) {
	if ( 'wp_insert_comment' === current_action() && is_a( $status, 'WP_Comment' ) ) {
		$status = $status->comment_approved;

		// Bail if the comment is not approved
		if ( 1 !== (int) $status ) {
			return;
		}
	}

	// Bail if no comment id or the status is delete
	if ( empty( $comment_id ) || ( ! empty( $status ) && 'delete' == $status ) ) {
		return;
	}

	$comment = wct_comments_get_comment( $comment_id );

	// Make sure the comment has been made on an talk post type
	if ( empty( $comment->comment_post_type ) || wct_get_post_type() != $comment->comment_post_type ) {
		return;
	}

	// Clean global talk comment count cache if needed.
	if ( wct_is_comments_disjoined() ) {
		wp_cache_delete( "talk_comment_count_0", 'wct' );
	}

	// Clean user count cache
	if ( ! empty( $comment->user_id ) ) {
		wp_cache_delete( "talk_comment_count_{$comment->user_id}", 'wct' );
	}
}

/**
 * Retrieve total comments about talks for blog or user.
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param int $user_id Optional. User ID.
 * @return object Comment stats.
 */
function wct_comments_count_comments( $user_id = 0 ) {

	$user_id = (int) $user_id;

	$count = wp_cache_get( "talk_comment_count_{$user_id}", 'wct' );

	if ( false !== $count ) {
		return $count;
	}

	// Counting for one user
	if ( ! empty( $user_id ) ) {
		$stats = WordCamp_Talks_Comments::count_user_comments( $user_id );

	// Counting for comments on talks
	} else {
		$stats = WordCamp_Talks_Comments::count_talks_comments();
	}

	wp_cache_set( "talk_comment_count_{$user_id}", $stats, 'wct' );

	return $stats;
}

/** Comments urls *************************************************************/

/**
 * Builds the talk's comment permalink
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  integer $comment_id the comment ID
 * @return string              the comment link
 */
function wct_comments_get_comment_link( $comment_id = 0 ) {
	if ( empty( $comment_id ) ) {
		return false;
	}

	$comment = get_comment( $comment_id );

	/**
	 * Check if the Talk still exists.
	 */
	if ( empty( $comment->comment_post_ID ) ) {
		$comment_link = false;
	} else {
		$comment_link = get_comment_link( $comment );
	}

	/**
	 * @param  string $comment_link the comment permalink
	 * @param  int    $comment_id   the comment ID
	 */
	return apply_filters( 'wct_comments_get_comment_link', $comment_link, $comment_id );
}

/**
 * Make sure the comment edit link about talks post type will
 * open the plugin's Comments Submenu once cliked on.
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  string $location the comment edit link
 * @return string           the new comment edit link if about an talk, unchanged otherwise
 */
function wct_edit_comment_link( $location = '' ) {
	if ( empty( $location ) ) {
		return $location;
	}

	// Too bad WordPres is not sending the comment object or ID in the filter :(
	if ( ! preg_match( '/[&|&amp;]c=(\d+)/', $location, $matches ) ) {
		return $location;
	}

	if ( empty( $matches[1] ) ) {
		return $location;
	}

	$comment_id = absint( $matches[1] );
	$comment    = wct_comments_get_comment( $comment_id );

	if ( empty( $comment->comment_post_type ) || wct_get_post_type() != $comment->comment_post_type ) {
		return $location;
	}

	$new_location = add_query_arg( 'post_type', wct_get_post_type(), $location );

	/**
	 * @param  string $new_location the new comment edit link
	 * @param  string $location     the original comment edit link
	 * @param  object $comment      the talk's comment object
	 */
	return apply_filters( 'wct_edit_comment_link', $new_location, $location, $comment );
}

/** Template functions ********************************************************/

/**
 * Builds the loop query arguments for user comments
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  string $type is this a single talk ?
 * @return array        the loop args
 */
function wct_comments_query_args() {
	/**
	 * Use this filter to overide loop args
	 * @see wct_comments_has_comments() for the list of available ones
	 *
	 * @param  array by default an empty array
	 */
	return apply_filters( 'wct_comments_query_args', array() );
}

/**
 * Should we display the comments form ?
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  bool $open   true if comments are opened, false otherwise
 * @param  int $talk_id the ID of the talk
 * @return bool          true if comments are opened, false otherwise
 */
function wct_comments_open( $open = true, $talk_id = 0 ) {
	if ( ! wct_is_talks() ) {
		return $open;
	}

	if ( $open !== wct_is_comments_allowed() ) {
		$open = false;
	} else {
		$open = wct_user_can( 'comment_talks' );
	}

	/**
	 * @param  bool $open true if comments are opened, false otherwise
	 * @param  int $talk_id the ID of the talk
	 */
	return apply_filters( 'wct_comments_open', $open, $talk_id );
}

/**
 * Replace or Add the user's profile link to the comment authors
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  array   $comments the list of comments in an array
 * @param  int     $talk_id  the ID of the talk
 * @return array             the list of comments, author links replaced by the plugin's profile if needed
 */
function wct_comments_array( $comments = array(), $talk_id = 0 ) {
	// Only filter comments arry if on a single talk
	if ( ! wct_is_single_talk() || empty( $comments ) || empty( $talk_id ) ) {
		return $comments;
	}

	// If the user can't comment
	if ( ! wct_user_can( 'comment_talks' ) ) {
		return array();
	}

	foreach (  $comments as $key => $comment ) {
		if ( empty( $comment->user_id ) || ! wct_user_can( 'view_other_profiles', $comment->user_id ) ) {
			continue;
		}

		$comments[ $key ]->comment_author_url = esc_url( wct_users_get_user_profile_url( $comment->user_id ) );
	}

	return $comments;
}

/**
 * Filter the comments query in case of a private call for speakers
 * (when the default talk status is private).
 *
 * @since 1.0.0
 *
 * @param  array $comment_query_args The Comments loop query arguments.
 * @return array                     The Comments loop query arguments.
 */
function wct_comments_template_query_args( $comment_query_args = array() ) {
	if ( ! wct_is_talks() || 'private' !== wct_default_talk_status() ) {
		return $comment_query_args;
	}

	// This case should never happened as the talk is private.
	if ( ! is_user_logged_in() ) {
		$comment_query_args['type__not_in'] = array( 'comment' );

	// if the user can't view any talk comments, only show him the ones he posted.
	} elseif ( ! wct_user_can( 'view_talk_comments' ) ) {
		$comment_query_args['user_id'] = get_current_user_id();
	}

	return $comment_query_args;
}

/**
 * Filter the comments count in case of a private call for speakers
 * (when the default talk status is private).
 *
 * @since 1.0.0
 *
 * @param  int $count   The comment count.
 * @param  int $post_id The current Post ID.
 * @return int          The comment count.
 */
function wct_edit_comments_number( $count = 0, $post_id = 0 ) {
	if ( empty( $count ) || empty( $post_id ) ) {
		return $count;
	}

	$post_type = get_post_type( $post_id );

	if ( wct_get_post_type() !== $post_type ) {
		return $count;
	}

	if ( 'private' !== wct_default_talk_status() || wct_user_can( 'view_talk_comments' ) ) {
		return $count;
	}

	// When listing comments on a single post, we only fetch the current user comments.
	if ( ! empty( $GLOBALS['wp_query']->comments ) ) {
		return $GLOBALS['wp_query']->comment_count;
	}

	// Otherwise there are no comments the user can see.
	return 0;
}

/**
 * Edit the comment reply link for blind raters, as they can only view their own comments.
 *
 * @since  1.0.0
 *
 * @param  string     $reply_link HTML output for the reply link.
 * @param  array      $args       An array of arguments overriding the defaults.
 * @param  WP_Comment $comment    The object of the comment being replied.
 * @param  WP_Post    $post       The WP_Post object.
 * @return string                 HTML output for the reply link.
 */
function wct_comment_reply_link( $reply_link = '', $args = array(), $comment = null, $post = null ) {
	if ( empty( $comment->user_id ) ) {
		return $reply_link;
	}

	$post_type = get_post_type( $post );

	if ( wct_get_post_type() !== $post_type ) {
		return $reply_link;
	}

	if ( 'private' !== wct_default_talk_status() || user_can( $comment->user_id, 'view_talk_comments' ) ) {
		return $reply_link;
	}

	$blind_rater_reply_link = '';

	if ( (int) $comment->user_id !== wct_users_current_user_id() ) {
		$blind_rater_reply_link = sprintf(
			esc_html__( '%1$s%2$s Blind raters can only view their own comments.%3$s' ),
			$args['before'] . '<span class="comment-reply-link">',
			'<span class="dashicons dashicons-hidden"></span>',
			'</span>' . $args['after']
		);
	}

	/**
	 * Filter here if you want to edit the comment reply link for blind raters.
	 *
	 * @since  1.0.0
	 *
	 * @param  string     $blind_rater_reply_link HTML output for the reply link of the blind rater.
	 * @param  string     $reply_link             Original HTML output for the reply link.
	 * @param  array      $args                   An array of arguments overriding the defaults.
	 * @param  WP_Comment $comment                The object of the comment being replied.
	 * @param  WP_Post    $post                   The WP_Post object.
	 */
	return apply_filters( 'wct_comment_reply_link', $blind_rater_reply_link, $reply_link, $args, $comment, $post );
}

/**
 * Make sure user can see comment feeds.
 *
 * @package WordCamp Talks
 * @subpackage comments/functions
 *
 * @since 1.0.0
 *
 * @param  string   $limit    the limit args of the WP_Query comment subquery.
 * @param  WP_Query $wp_query WordPress main query.
 * @return string             the limit args of the WP_Query comment subquery.
 */
function wct_comment_feed_limits( $limit = '', $wp_query = null ) {
	// Force the comments query to return nothing
	if ( ! empty( $wp_query->query['post_type'] ) && wct_get_post_type() === $wp_query->query['post_type'] && ! wct_user_can( 'comment_talks' ) ) {
		$limit = 'LIMIT 0';
	}

	return $limit;
}
