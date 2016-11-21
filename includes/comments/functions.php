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
	 * Avoid notices when deleting a user/post if BuddyPress Activity & Blogs are active
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
	 * Used internally in BuddyPress parts
	 *
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
		if ( empty( $comment->user_id ) ) {
			continue;
		}

		$comments[ $key ]->comment_author_url = esc_url( wct_users_get_user_profile_url( $comment->user_id ) );
	}

	return $comments;
}
