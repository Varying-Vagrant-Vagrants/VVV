<?php
/**
 * WordCamp Talks User's comments tags
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Comment Loop **************************************************************/

/**
 * Initialize the user's comments loop.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Arguments for customizing comments retrieved in the loop.
 *     Arguments must be passed as an associative array
 *     @type int 'user_id' to restrict the loop to one user (defaults to displayed user)
 *     @type string 'status' to limit the query to comments having a certain status (defaults to approve)
 *     @type int 'number' Number of results per page.
 *     @type int 'page' the page of results to display.
 * }
 * @return bool         true if comments were found, false otherwise
 */
function wct_comments_has_comments( $args = array() ) {

	$r = wp_parse_args( $args, array(
		'user_id' => wct_users_displayed_user_id(),
		'status'  => 'approve',
		'number'  => wct_talks_per_page(),
		'page'    => 1,
	) );

	// Get the WordCamp Talks
	$comment_query_loop = new WordCamp_Talks_Loop_Comments( array(
		'user_id' => (int) $r['user_id'],
		'status'  => $r['status'],
		'number'  => (int) $r['number'],
		'page'    => (int) $r['page'],
	) );

	// Setup the global query loop
	wct()->comment_query_loop = $comment_query_loop;

	return apply_filters( 'wct_comments_has_comments', $comment_query_loop->has_items(), $comment_query_loop );
}

/**
 * Get the comments returned by the template loop.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 * 
 * @return array List of comments.
 */
function wct_comments_the_comments() {
	return wct()->comment_query_loop->items();
}

/**
 * Get the current comment object in the loop.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 * 
 * @return object The current comment within the loop.
 */
function wct_comments_the_comment() {
	return wct()->comment_query_loop->the_item();
}

/** Loop Output ***************************************************************/
// Mainly inspired by The BuddyPress notifications loop

/**
 * Displays a message if no comments were found
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_no_comment_found() {
	echo wct_comments_get_no_comment_found();
}

	/**
	 * Gets a message if no comments were found
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the message if no comments were found
	 */
	function wct_comments_get_no_comment_found() {
		$output = sprintf(
			__( 'It looks like %s has not commented on any talks yet', 'wordcamp-talks' ),
			wct_users_get_displayed_user_displayname()
		);

		/**
		 * @param  string $output the message if no comments were found
		 */
		return apply_filters( 'wct_comments_get_no_comment_found', $output );
	}

/**
 * Output the pagination count for the current comments loop.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_pagination_count() {
	echo wct_comments_get_pagination_count();
}

	/**
	 * Return the pagination count for the current comments loop.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string HTML for the pagination count.
	 */
	function wct_comments_get_pagination_count() {
		$query_loop = wct()->comment_query_loop;
		$start_num  = intval( ( $query_loop->page - 1 ) * $query_loop->per_page ) + 1;
		$from_num   = number_format_i18n( $start_num );
		$to_num     = number_format_i18n( ( $start_num + ( $query_loop->per_page - 1 ) > $query_loop->total_comment_count ) ? $query_loop->total_comment_count : $start_num + ( $query_loop->number - 1 ) );
		$total      = number_format_i18n( $query_loop->total_comment_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s comments)', 'Viewing %1$s to %2$s (of %3$s comments)', $total, 'wordcamp-talks' ), $from_num, $to_num, $total );

		/**
		 * @param  string $pag the pagination count to output
		 */
		return apply_filters( 'wct_comments_get_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current comments loop.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_pagination_links() {
	echo wct_comments_get_pagination_links();
}

	/**
	 * Return the pagination links for the current comments loop.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string HTML for the pagination links.
	 */
	function wct_comments_get_pagination_links() {
		/**
		 * @param  string the pagination links to output
		 */
		return apply_filters( 'wct_comments_get_pagination_links', wct()->comment_query_loop->pag_links );
	}

/**
 * Output the ID of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_id() {
	echo wct_comments_get_comment_id();
}

	/**
	 * Return the ID of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return int ID of the current comment.
	 */
	function wct_comments_get_comment_id() {
		/**
		 * @param  int the comment ID to output
		 */
		return apply_filters( 'wct_comments_get_comment_id', wct()->comment_query_loop->comment->comment_ID );
	}

/**
 * Output the avatar of the author of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_author_avatar() {
	echo wct_comments_get_comment_author_avatar();
}

	/**
	 * Return the avatar of the author of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the avatar.
	 */
	function wct_comments_get_comment_author_avatar() {
		$author = wct()->comment_query_loop->comment->user_id;
		$avatar = get_avatar( $author );
		$avatar_link = '<a href="' . esc_url( wct_users_get_user_profile_url( $author ) ) . '" title="' . esc_attr__( 'User&#39;s profile', 'wordcamp-talks' ) . '">' . $avatar . '</a>';

		/**
		 * @param  string  $avatar_link the avatar output
		 * @param  int     $author the author ID
		 * @param  string  $avatar the avatar
		 */
		return apply_filters( 'wct_comments_get_comment_author_avatar', $avatar_link, $author, $avatar );
	}

/**
 * Output the mention to add before the title of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_before_comment_title() {
	echo wct_comments_get_before_comment_title();
}

	/**
	 * Return the mention to add before the title of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the mention to prefix the title with.
	 */
	function wct_comments_get_before_comment_title() {
		/**
		 * @param  string  the mention output
		 */
		return apply_filters( 'wct_comments_get_before_comment_title', esc_html__( 'In reply to:', 'wordcamp-talks' ) );
	}

/**
 * Output the permalink of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_permalink() {
	echo wct_comments_get_comment_permalink();
}

	/**
	 * Return the permalink of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the comment's permalink.
	 */
	function wct_comments_get_comment_permalink() {
		$comment = wct()->comment_query_loop->comment;
		$comment_link = wct_comments_get_comment_link( $comment );

		/**
		 * @param  string  $comment_link the comment link
		 * @param  object  $comment the comment object
		 */
		return apply_filters( 'wct_comments_get_comment_permalink', esc_url( $comment_link ), $comment );
	}

/**
 * Output the title attribute of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_title_attribute() {
	echo wct_comments_get_comment_title_attribute();
}

	/**
	 * Return the title attribute of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the title attribute.
	 */
	function wct_comments_get_comment_title_attribute() {
		$comment = wct()->comment_query_loop->comment;
		$title = '';

		$talk = $comment->comment_post_ID;

		if ( ! empty( $comment->talk ) ) {
			$talk = $comment->talk;
		}

		$talk = get_post( $talk );

		if ( ! empty( $talk->post_password ) ) {
			$title = _x( 'Protected:', 'talk permalink title protected attribute', 'wordcamp-talks' ) . ' ';
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status ) {
			$title = _x( 'Private:', 'talk permalink title private attribute', 'wordcamp-talks' ) . ' ';
		}

		$title .= $talk->post_title;

		/**
		 * @param  string   $title the title attribute
		 * @param  WP_Post  $talk the talk object
		 * @param  object   $comment the comment object
		 */
		return apply_filters( 'wct_comments_get_comment_title_attribute', esc_attr( $title ), $talk, $comment );
	}

/**
 * Output the title of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_title() {
	echo wct_comments_get_comment_title();
}

	/**
	 * Return the title of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the title.
	 */
	function wct_comments_get_comment_title() {
		$comment = wct()->comment_query_loop->comment;

		/**
		 * When the talk has a private status, we're applying a dashicon to a span
		 * So we need to only allow this tag when sanitizing the output
		 */
		if ( isset( $comment->post_status ) && 'publish' !== $comment->post_status ) {
			$title = wp_kses( get_the_title( $comment->comment_post_ID ), array( 'span' => array( 'class' => array() ) ) );
		} else {
			$title = esc_html( get_the_title( $comment->comment_post_ID ) );
		}

		/**
		 * @param  string   the title of the talk, the comment is linked to
		 * @param  object   $comment the comment object
		 */
		return apply_filters( 'wct_comments_get_comment_title', $title, $comment );
	}

/**
 * Output the excerpt of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_excerpt() {
	echo wct_comments_get_comment_excerpt();
}

	/**
	 * Return the excerpt of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the excerpt.
	 */
	function wct_comments_get_comment_excerpt() {
		$comment = wct()->comment_query_loop->comment;
		$title = '';

		$talk = $comment->comment_post_ID;

		if ( ! empty( $comment->talk ) ) {
			$talk = $comment->talk;
		}

		$talk = get_post( $talk );

		if ( post_password_required( $talk ) ) {
			$excerpt = __( 'The talk the comment was posted on is password protected: you will need the password to view its content.', 'wordcamp-talks' );

		// Private
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status && ! wct_user_can( 'read_talk', $talk->ID ) ) {
			$excerpt = __( 'The talk the comment was posted on is private: you cannot view its content.', 'wordcamp-talks' );

		// Public
		} else {
			$excerpt = get_comment_excerpt( wct()->comment_query_loop->comment->comment_ID );
		}

		/**
		 * @param  string   $excerpt the comment excerpt
		 */
		return apply_filters( 'wct_comments_get_comment_excerpt', $excerpt );
	}

/**
 * Output the footer of the comment currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage comments/tags
 *
 * @since 1.0.0
 */
function wct_comments_the_comment_footer() {
	echo wct_comments_get_comment_footer();
}

	/**
	 * Return the footer of the comment currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/tags
	 *
	 * @since 1.0.0
	 * 
	 * @return string the footer.
	 */
	function wct_comments_get_comment_footer() {
		$posted_on = sprintf( esc_html__( 'This comment was posted on %s', 'wordcamp-talks' ), get_comment_date( '', wct()->comment_query_loop->comment->comment_ID ) );

		/**
		 * @param  string   $posted_on the comment footer
		 * @param  object   the comment object
		 */
		return apply_filters( 'wct_comments_get_comment_footer', $posted_on, wct()->comment_query_loop->comment );
	}
