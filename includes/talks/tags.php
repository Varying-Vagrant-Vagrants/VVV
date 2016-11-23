<?php
/**
 * WordCamp Talks tags.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Talks Main nav ************************************************************/

/**
 * Displays the Talks Search form
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 * @return string Output for the search form.
 */
function wct_talks_search_form() {
	$placeholder = __( 'Search Talks', 'wordcamp-talks' );
	$search_value = get_query_var( wct_search_rewrite_id() );
	$action = '';
	$hidden = '';

	if ( ! empty( $search_value ) ) {
		$search_value = esc_html( $search_value );
	}

	if ( ! wct_is_pretty_links() ) {
		$hidden = "\n" . '<input type="hidden" name="post_type" value="' . wct_get_post_type() . '"/>';
	} else {
		$action = apply_filters( 'wct_talks_search_form_action_url', wct_get_root_url() );
	}

	$search_form_html = '<form action="' . esc_url( $action ) . '" method="get" id="talks-search-form" class="nav-form">' . $hidden;
	$search_form_html .= '<label><input type="text" name="' . wct_search_rewrite_id() . '" id="talks-search-box" placeholder="'. esc_attr( $placeholder ) .'" value="' . $search_value . '" /></label>';
	$search_form_html .= '<input type="submit" id="talks-search-submit" value="'. esc_attr__( 'Search', 'wordcamp-talks' ) .'" /></form>';

	echo apply_filters( 'wct_talks_search_form', $search_form_html );
}

/**
 * Displays the Orderby form
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string Output for the search form.
 */
function wct_talks_order_form() {
	$order_options = wct_talks_get_order_options();
	$order_value   = get_query_var( 'orderby' );
	$category      = get_query_var( wct_get_category() );
	$tag           = get_query_var( wct_get_tag() );
	$action        = '';
	$hidden        = '';

	if ( ! empty( $order_value ) ) {
		$order_value = esc_html( $order_value );
	} else {
		$order_value = 'date';
	}

	if ( ! wct_is_pretty_links() ) {
		if ( ! empty( $category ) ) {
			$hidden = "\n" . '<input type="hidden" name="' . esc_attr( wct_get_category() ). '" value="' . $category . '"/>';
		} else if ( ! empty( $tag ) ) {
			$hidden = "\n" . '<input type="hidden" name="' . esc_attr( wct_get_tag() ). '" value="' . $tag . '"/>';
		} else {
			$hidden = "\n" . '<input type="hidden" name="post_type" value="' . wct_get_post_type() . '"/>';
		}

	// We need to set the action url
	} else {
		// Viewing tags
		if ( wct_is_tag() ) {
			$action = wct_get_tag_url( $tag );

		// Viewing categgories
		} else if ( wct_is_category() ) {
			$action = wct_get_category_url( $category );

		// Defaults to roout url
		} else {
			$action = wct_get_root_url();
		}

		/**
		 * @param string $action the action form attribute
		 * @param string the current category term slug if set
		 * @param string the current tag term slug if set
		 */
		$action = apply_filters( 'wct_talks_order_form_action_url', $action, $category, $tag );
	}

	$order_form_html = '<form action="' . esc_url( $action ) . '" method="get" id="talks-order-form" class="nav-form">' . $hidden;
	$order_form_html .= '<label><select name="orderby" id="talks-order-box">';

	foreach ( $order_options as $query_var => $label ) {
		$order_form_html .= '<option value="' . esc_attr( $query_var ) . '" ' . selected( $order_value, $query_var, false ) . '>' . esc_html( $label) . '</option>';
	}

	$order_form_html .= '</select></label>';
	$order_form_html .= '<input type="submit" id="talks-order-submit" value="'. esc_attr__( 'Sort', 'wordcamp-talks' ) .'" /></form>';

	echo apply_filters( 'wct_talks_order_form', $order_form_html );
}

/**
 * Displays the current term description if it exists
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string Output for the current term description.
 */
function wct_talks_taxonomy_description() {

	if ( wct_is_category() || wct_is_tag() ) {
		$term = wct_get_current_term();

		if ( ! empty( $term->description ) ) {
			?>
			<p class="talk-term-description"><?php echo esc_html( $term->description ) ; ?></p>
			<?php
		}
	}
}

/** Talk Loop *****************************************************************/

/**
 * Initialize the talks loop.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Arguments for customizing talks retrieved in the loop.
 *     Arguments must be passed as an associative array
 *     @type int 'author' to restrict the loop to one author
 *     @type int 'per_page' Number of results per page.
 *     @type int 'page' the page of results to display.
 *     @type string 'search' to limit the query to talks containing the requested search terms
 *     @type array|string 'exclude' Array or comma separated list of talk IDs to exclude
 *     @type array|string 'include' Array or comma separated list of talk IDs to include
 *     @type string 'orderby' to customize the sorting order type for the talks (default is by date)
 *     @type string 'order' the way results should be sorted : 'DESC' or 'ASC' (default is DESC)
 *     @type array 'meta_query' Limit talks regarding their post meta by passing an array of
 *           meta_query conditions. See {@link WP_Meta_Query->queries} for a
 *           description of the syntax.
 *     @type array 'tax_query' Limit talks regarding their terms by passing an array of
 *           tax_query conditions. See {@link WP_Tax_Query->queries} for a
 *           description of the syntax.
 *     @type string 'talk_name' Limit results by a the post name of the talk.
 *     @type bool 'is_widget' is the query performed inside a widget ?
 * }
 * @return bool         true if talks were found, false otherwise
 */
function wct_talks_has_talks( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = wp_parse_args( $args, array() );
	}

	$template_args = array();

	/**
	 * We have arguments, so let's override the main query
	 */
	if ( ! empty( $args ) ) {
		$search_terms = '';

		if ( isset( $_GET[ wct_search_rewrite_id() ] ) ) {
			$search_terms = stripslashes( $_GET[ wct_search_rewrite_id() ] );
		}

		$r = wp_parse_args( $args, array(
			'author'     => wct_is_user_profile_talks() ? wct_users_displayed_user_id() : '',
			'per_page'   => wct_talks_per_page(),
			'page'       => 1,
			'search'     => '',
			'exclude'    => '',
			'include'    => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array(),
			'tax_query'  => array(),
			'talk_name'  => '',
			'is_widget'  => false
		) );

		$template_args = array(
			'author'     => (int) $r['author'],
			'per_page'   => (int) $r['per_page'],
			'page'       => (int) $r['page'],
			'search'     => $r['search'],
			'exclude'    => $r['exclude'],
			'include'    => $r['include'],
			'orderby'    => $r['orderby'],
			'order'      => $r['order'],
			'meta_query' => (array) $r['meta_query'],
			'tax_query'  => (array) $r['tax_query'],
			'talk_name'  => $r['talk_name'],
			'is_widget'  => (bool) $r['is_widget'],
		);
	}

	// Get the talks
	$query_loop = new WordCamp_Talks_Loop_Talks( $template_args );

	// Setup the global query loop
	wct()->query_loop = $query_loop;

	/**
	 * @param  bool   true if talks were found, false otherwise
	 * @param  object $query_loop the talks loop
	 * @param  array  $template_args arguments used to build the loop
	 * @param  array  $args requested arguments
	 */
	return apply_filters( 'wct_talks_has_talks', $query_loop->has_items(), $query_loop, $template_args, $args );
}

/**
 * Get the Talks returned by the template loop.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return array List of Talks.
 */
function wct_talks_the_talks() {
	return wct()->query_loop->items();
}

/**
 * Get the current Talk object in the loop.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return object The current Talk within the loop.
 */
function wct_talks_the_talk() {
	return wct()->query_loop->the_item();
}

/** Loop Output ***************************************************************/
// Mainly inspired by The BuddyPress notifications loop

/**
 * Displays a message in case no talk was found
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_not_found() {
	echo wct_talks_get_not_found();
}

	/**
	 * Gets a message in case no talk was found
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string the message to output
	 */
	function wct_talks_get_not_found() {
		// general feedback
		$output = esc_html__( 'It looks like no talk has been submitted yet, please sign in or sign up to add yours!', 'wordcamp-talks' );

		if ( wct_is_user_profile() ) {
			/**
			 * This part should probably be improved..
			 */
			if ( ! wct_is_user_profile_rates() && ! wct_is_user_profile_to_rate() ) {
				$output = sprintf(
					__( 'It looks like %s has not submitted any talk yet', 'wordcamp-talks' ),
					wct_users_get_displayed_user_displayname()
				);

			// We're viewing the talk the user rated
			} elseif ( ! wct_is_user_profile_to_rate() ) {
				$output = sprintf(
					__( 'It looks like %s has not rated any talks yet', 'wordcamp-talks' ),
					wct_users_get_displayed_user_displayname()
				);

			// We're viewing the talk the user had to rate, and he rated all
			} else {
				$output = sprintf(
					__( 'Alright sparky, no more talks to rate.. Good job!', 'wordcamp-talks' ),
					wct_users_get_displayed_user_displayname()
				);
			}

		} else if ( wct_is_category() ) {
			$output = __( 'It looks like no talk has been published in this category yet', 'wordcamp-talks' );

		} else if ( wct_is_tag() ) {
			$output = __( 'It looks like no talk has been marked with this tag yet', 'wordcamp-talks' );

		} else if ( wct_is_search() ) {
			$output = __( 'It looks like no talk matches your search terms.', 'wordcamp-talks' );

		} else if ( wct_is_search() ) {
			$output = __( 'It looks like no talk matches your search terms.', 'wordcamp-talks' );

		} else if ( wct_is_orderby( 'rates_count' ) ) {
			$output = __( 'It looks like no talks have been rated yet.', 'wordcamp-talks' );

		} else if ( wct_user_can( 'publish_talks' ) ) {
			$output = sprintf(
				__( 'It looks like no talks has been submitted yet, <a href="%s" title="Submit your talk">add yours</a>', 'wordcamp-talks' ),
				esc_url( wct_get_form_url() )
			);
		}

		/**
		 * @param  string $output the message to output
		 */
		return apply_filters( 'wct_talks_get_not_found', $output );
	}

/**
 * Output the pagination count for the current Talks loop.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_pagination_count() {
	echo wct_talks_get_pagination_count();
}
	/**
	 * Return the pagination count for the current Talks loop.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML for the pagination count.
	 */
	function wct_talks_get_pagination_count() {
		$query_loop = wct()->query_loop;
		$start_num  = intval( ( $query_loop->page - 1 ) * $query_loop->per_page ) + 1;
		$from_num   = number_format_i18n( $start_num );
		$to_num     = number_format_i18n( ( $start_num + ( $query_loop->per_page - 1 ) > $query_loop->total_talk_count ) ? $query_loop->total_talk_count : $start_num + ( $query_loop->per_page - 1 ) );
		$total      = number_format_i18n( $query_loop->total_talk_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s talks)', 'Viewing %1$s to %2$s (of %3$s talks)', $total, 'wordcamp-talks' ), $from_num, $to_num, $total );

		/**
		 * @param  string $pag the pagination count to output
		 */
		return apply_filters( 'wct_talks_get_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current Talks loop.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_pagination_links() {
	echo wct_talks_get_pagination_links();
}

	/**
	 * Return the pagination links for the current Rendez Vous loop.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output for the pagination links.
	 */
	function wct_talks_get_pagination_links() {
		/**
		 * @param  string the pagination links to output
		 */
		return apply_filters( 'wct_talks_get_pagination_links', wct()->query_loop->pag_links );
	}

/**
 * Output the ID of the talk currently being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_id() {
	echo wct_talks_get_id();
}

	/**
	 * Return the ID of the Talk currently being iterated on.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return int ID of the current Talk.
	 */
	function wct_talks_get_id() {
		/**
		 * @param  int the talk ID to output
		 */
		return apply_filters( 'wct_talks_get_id', wct()->query_loop->talk->ID );
	}

/**
 * Get the Talk author ID.
 *
 * @since 1.0.0
 *
 * @return  int The Talk author ID
 */
function wct_talks_get_author_id() {
	$talk   = wct()->query_loop->talk;

	$author_ID = 0;

	if ( ! empty( $talk->post_author ) ) {
		$author_ID = (int) $talk->post_author;
	}

	return $author_ID;
}

/**
 * Checks if the Talk being iterated on is sticky
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return bool True if the Talk being iterating on is sticky, false otherwise
 */
function wct_talks_is_sticky_talk() {
	$query_loop = wct()->query_loop;
	$talk = $query_loop->talk;

	if ( ! wct_is_talks_archive() || wct_get_global( 'orderby' ) || wct_is_search() ) {
		return;
	}

	if ( empty( $query_loop->page ) || ( ! empty( $query_loop->page ) && 1 < $query_loop->page ) ) {
		return;
	}

	// Bail if sticky is disabled
	if ( ! wct_is_sticky_enabled() ) {
		return;
	}

	if ( ! empty( $talk->is_sticky ) ) {
		return true;
	} else {
		return wct_talks_is_sticky( $talk->ID );
	}
}

/**
 * Output the row classes of the Talk being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_classes() {
	echo wct_talks_get_classes();
}

	/**
	 * Gets the row classes for the Talk being iterated on
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output the row class attribute
	 */
	function wct_talks_get_classes() {
		$classes = array( 'talk' );

		if ( wct_talks_is_sticky_talk() ) {
			$classes[] = 'sticky-talk';
		}

		if ( ! wct_user_can( 'view_other_profiles', wct_talks_get_author_id() ) ) {
			$classes[] = 'no-avatar';
		}

		/**
		 * @param  array $classes the talk row classes
		 */
		$classes = apply_filters( 'wct_talks_get_classes', $classes );

		return 'class="' . join( ' ', $classes ) . '"';
	}

/**
 * Output the author avatar of the Talk being iterated on.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_author_avatar() {
	echo wct_talks_get_author_avatar();
}

	/**
	 * Gets the author avatar
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output the author's avatar
	 */
	function wct_talks_get_author_avatar() {
		$talk   = wct()->query_loop->talk;
		$author = $talk->post_author;
		$avatar = get_avatar( $author );
		$avatar_link = '<a href="' . esc_url( wct_users_get_user_profile_url( $author ) ) . '" title="' . esc_attr__( 'User&#39;s profile', 'wordcamp-talks' ) . '">' . $avatar . '</a>';

		/**
		 * @param  string  $avatar_link the avatar output
		 * @param  int     $author the author ID
		 * @param  string  $avatar the avatar
		 * @param  WP_Post $talk the talk object
		 */
		return apply_filters( 'wct_talks_get_author_avatar', $avatar_link, $author, $avatar, $talk );
	}

/**
 * Prefix talk title.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_before_talk_title() {
	echo wct_talks_get_before_talk_title();
}

	/**
	 * Gets the talk title prefix
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output the talk title prefix
	 */
	function wct_talks_get_before_talk_title() {
		$output = '';

		if ( wct_talks_is_sticky_talk() ) {
			$output = '<span class="sticky-talk"></span> ';
		}

		/**
		 * @param  string  $output the avatar output
		 * @param  int     the talk ID
		 */
		return apply_filters( 'wct_talks_get_before_talk_title', $output, wct()->query_loop->talk->ID );
	}

/**
 * Displays talk title.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_title() {
	echo wct_talks_get_title();
}

	/**
	 * Gets the title of the talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output the title of the talk
	 */
	function wct_talks_get_title() {
		$talk = wct()->query_loop->talk;
		$title = get_the_title( $talk );

		/**
		 * @param  string  $title the title to output
		 * @param  WP_Post $talk the talk object
		 */
		return apply_filters( 'wct_talks_get_title', $title, $talk );
	}

/**
 * Displays talk permalink.
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_permalink() {
	echo wct_talks_get_permalink();
}

	/**
	 * Gets the permalink of the talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output the permalink to the talk
	 */
	function wct_talks_get_permalink() {
		$talk = wct()->query_loop->talk;
		$permalink = wct_talks_get_talk_permalink( $talk );

		/**
		 * @param  string  the permalink url
		 * @param  WP_Post $talk the talk object
		 */
		return apply_filters( 'wct_talks_get_permalink', esc_url( $permalink ), $talk );
	}

/**
 * Adds to talk's permalink an attribute containg the talk's title
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_title_attribute() {
	echo wct_talks_get_title_attribute();
}

	/**
	 * Gets the title attribute of the talk's permalink
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string output of the attribute
	 */
	function wct_talks_get_title_attribute() {
		$talk = wct()->query_loop->talk;
		$title = '';

		if ( ! empty( $talk->post_password ) ) {
			$title = _x( 'Protected:', 'talk permalink title protected attribute', 'wordcamp-talks' ) . ' ';
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status ) {
			$title = _x( 'Private:', 'talk permalink title private attribute', 'wordcamp-talks' ) . ' ';
		}

		$title .= $talk->post_title;

		/**
		 * @param  string  the title to output
		 * @param  string  the db title
		 * @param  WP_Post $talk the talk object
		 */
		return apply_filters( 'wct_talks_get_title_attribute', esc_attr( $title ), $talk->post_title, $talk );
	}

/**
 * Displays the number of comments about an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_comment_number() {
	echo wct_talks_get_comment_number();
}

	/**
	 * Gets the title attribute of the talk's permalink
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  int $id the talk ID
	 * @return int the comments number
	 */
	function wct_talks_get_comment_number( $id = 0 ) {
		if ( empty( $id ) ) {
			$id = wct()->query_loop->talk->ID;
		}

		return get_comments_number( $id );
	}

/**
 * Displays the comment link of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param  mixed $zero       false or the text to show when talk got no comments
 * @param  mixed $one        false or the text to show when talk got one comment
 * @param  mixed $more       false or the text to show when talk got more than one comment
 * @param  string $css_class the name of the css classes to use
 * @param  mixed $none       false or the text to show when no talk comment link
 */
function wct_talks_the_talk_comment_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ) {
	echo wct_talks_get_talk_comment_link( $zero, $one, $more, $css_class, $none );
}

	/**
	 * Gets the comment link of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $zero       false or the text to show when talk got no comments
	 * @param  mixed $one        false or the text to show when talk got one comment
	 * @param  mixed $more       false or the text to show when talk got more than one comment
	 * @param  string $css_class the name of the css classes to use
	 * @param  mixed $none       false or the text to show when no talk comment link
	 * @return string             output for the comment link
	 */
	function wct_talks_get_talk_comment_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ) {
		$output = '';
		$talk = wct()->query_loop->talk;

		if ( false === $zero ) {
			$zero = __( 'No Comments', 'wordcamp-talks' );
		}
		if ( false === $one ) {
			$one = __( '1 Comment', 'wordcamp-talks' );
		}
		if ( false === $more ) {
			$more = __( '% Comments', 'wordcamp-talks' );
		}
		if ( false === $none ) {
			$none = sprintf( '<span class="%1$s">%2$s</span>', $css_class, esc_html__( 'Comments Off', 'wordcamp-talks' ) );
		}

		if ( ! wct_user_can( 'comment_talks', $talk->ID ) ) {
			return $none;
		}

		$number = wct_talks_get_comment_number( $talk->ID );
		$title = '';

		if ( post_password_required( $talk->ID ) ) {
			$title = _x( 'Comments are protected.', 'talk protected comments message', 'wordcamp-talks' );
			$output .= '<span class="talk-comments-protected">' . $title . '</span>';
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status && ! wct_user_can( 'read_talk', $talk->ID ) ) {
			$title = _x( 'Comments are private.', 'talk private comments message', 'wordcamp-talks' );
			$output .= '<span class="talk-comments-private">' . $title . '</span>';
		} else if ( ! comments_open( $talk->ID ) ) {
			$output .= '<span' . ( ( ! empty( $css_class ) ) ? ' class="' . esc_attr( $css_class ) . '"' : '') . '>' . $none . '</span>';
		} else {
			$comment_link = ( 0 == $number ) ? wct_talks_get_talk_permalink( $talk ) . '#respond' : wct_talks_get_talk_comments_link( $talk );
			$output .= '<a href="' . esc_url( $comment_link ) . '"';

			if ( ! empty( $css_class ) ) {
				$output .= ' class="' . $css_class . '" ';
			}

			$title = esc_attr( strip_tags( $talk->post_title ) );

			$output .= ' title="' . esc_attr( sprintf( __('Comment on %s', 'wordcamp-talks'), $title ) ) . '">';

			$comment_number_output = '';

			if ( $number > 1 ) {
				$comment_number_output = str_replace( '%', number_format_i18n( $number ), $more );
			} elseif ( $number == 0 ) {
				$comment_number_output = $zero;
			} else { // must be one
				$comment_number_output = $one;
			}

			/**
			 * Filter the comments count for display just like WordPress does
			 * in get_comments_number_text()
			 *
			 * @param  string  $comment_number_output
			 * @param  int     $number
			 */
			$comment_number_output = apply_filters( 'comments_number', $comment_number_output, $number );

			$output .= $comment_number_output . '</a>';
		}

		/**
		 * @param  string  $output the comment link to output
		 * @param  int     the talk ID
		 * @param  string  $title the title attribute
		 * @param  int     $number amount of comments about the talk
		 */
		return apply_filters( 'wct_talks_get_talk_comment_link', $output, $talk->ID, $title, $number );
	}

/**
 * Displays the average rating of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_average_rating() {
	echo wct_talks_get_average_rating();
}

	/**
	 * Gets the average rating of a talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  int $id the talk ID
	 * @return string  output for the average rating
	 */
	function wct_talks_get_average_rating( $id = 0 ) {
		if ( empty( $id ) ) {
			$id = wct()->query_loop->talk->ID;
		}

		$rating = get_post_meta( $id, '_wc_talks_average_rate', true );

		if ( ! empty( $rating ) && is_numeric( $rating ) ) {
			$rating = number_format_i18n( $rating, 1 );
		}

		return $rating;
	}

/**
 * Displays the rating link of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param  mixed $zero       false or the text to show when talk got no rates
 * @param  mixed $more       false or the text to show when talk got one or more rates
 * @param  string $css_class the name of the css classes to use
 */
function wct_talks_the_rating_link( $zero = false, $more = false, $css_class = '' ) {
	// Bail if ratings are disabled
	if ( wct_is_rating_disabled() || ! wct_user_can( 'rate_talks' ) ) {
		return false;
	}

	if ( wct_is_single_talk() ) {
		echo '<div id="rate" data-talk="' . wct()->query_loop->talk->ID . '"></div><div class="rating-info"></div>';
	} else {
		echo wct_talks_get_rating_link( $zero, $more, $css_class );
	}
}

	/**
	 * Gets the rating link of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $zero       false or the text to show when talk got no rates
	 * @param  mixed $more       false or the text to show when talk got one or more rates
	 * @param  string $css_class the name of the css classes to use
	 * @return string             output for the rating link
	 */
	function wct_talks_get_rating_link( $zero = false, $more = false, $css_class = '' ) {
		$output = '';
		$talk = wct()->query_loop->talk;

		// Simply dont display votes if password protected or private.
		if ( post_password_required( $talk->ID ) ) {
			return $output;
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status && ! wct_user_can( 'read_talk', $talk->ID ) ) {
			return $output;
		}

		if ( false === $zero ) {
			$zero = __( 'Not rated yet', 'wordcamp-talks' );
		}
		if ( false === $more ) {
			$more = __( 'Average rating: %', 'wordcamp-talks' );
		}

		$average = wct_talks_get_average_rating( $talk->ID );

		$rating_link = wct_talks_get_talk_permalink( $talk ) . '#rate';

		$title = esc_attr( strip_tags( $talk->post_title ) );
		$title = sprintf( __('Rate %s', 'wordcamp-talks'), $title );

		if ( ! is_user_logged_in() ) {
			$rating_link = wp_login_url( $rating_link );
			$title = _x( 'Please, log in to rate.', 'talk rating not logged in message', 'wordcamp-talks' );
		}

		$output .= '<a href="' . esc_url( $rating_link ) . '"';

		if ( ! empty( $css_class ) ) {
			if ( empty( $average ) ) {
				$css_class .= ' empty';
			}
			$output .= ' class="' . $css_class . '" ';
		}

		$output .= ' title="' . esc_attr( $title ) . '">';

		if ( ! empty( $average  ) ) {
			$output .= str_replace( '%', $average, $more );
		} else {
			$output .= $zero;
		}

		$output .= '</a>';

		/**
		 * @param  string  $output the rating link to output
		 * @param  int     the talk ID
		 * @param  string  $title the title attribute
		 * @param  string  $average the average rating of an talk
		 */
		return apply_filters( 'wct_talks_get_rating_link', $output, $talk->ID, $title, $average );
	}

/**
 * Displays the excerpt of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_excerpt() {
	echo wct_talks_get_excerpt();
}

	/**
	 * Gets the excerpt of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @global  WP_Post $post the current post
	 * @return string  output for the excerpt
	 */
	function wct_talks_get_excerpt() {
		global $post;
		$reset_post = $post;
		$talk = wct()->query_loop->talk;

		// Password protected
		if ( post_password_required( $talk ) ) {
			$excerpt = __( 'This talk is password protected, you will need it to view its content.', 'wordcamp-talks' );

		// Private
		} else if ( ! empty( $talk->post_status ) && 'private' == $talk->post_status && ! wct_user_can( 'read_talk', $talk->ID ) ) {
			$excerpt = __( 'This talk is private, you cannot view its content.', 'wordcamp-talks' );

		// Public
		} else {
			$excerpt = strip_shortcodes( $talk->post_excerpt );
		}

		if ( empty( $excerpt ) ) {
			// This is temporary!
			$post = $talk;

			$excerpt = wct_create_excerpt( $talk->post_content, 20 );

			// Reset the post
			$post = $reset_post;
		} else {
			/**
			 * @param  string  $excerpt the excerpt to output
			 * @param  WP_Post $talk the talk object
			 */
			$excerpt = apply_filters( 'wct_create_excerpt_text', $excerpt, $talk );
		}

		return $excerpt;
	}

/**
 * Displays the content of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_content() {
	echo wct_talks_get_content();
}

	/**
	 * Gets the content of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Post $post
	 * @return string  output for the excerpt
	 */
	function wct_talks_get_content() {
		global $post;
		$reset_post = $post;

		// Temporarly set the post to be the talk so that embeds works!
		$post = wct()->query_loop->talk;

		// Password protected
		if ( post_password_required( $post ) ) {
			$content = __( 'This talk is password protected, you will need it to view its content.', 'wordcamp-talks' );

		// Private
		} else if ( ! empty( $post->post_status ) && 'private' == $post->post_status && ! wct_user_can( 'read_talk', $post->ID ) ) {
			$content = __( 'This talk is private, you cannot view its content.', 'wordcamp-talks' );

		// Public
		} else {
			$content = $post->post_content;
		}

		/**
		 * @param  string  $content the content to output
		 * @param  WP_Post $post the talk object
		 */
		$content = apply_filters( 'wct_talks_get_content', $content, $post );

		// Reset the post.
		$post = $reset_post;

		/**
		 * shortcode_unautop filter fails in groups ??
		 * So we're manually executing the shortcodes
		 * before returning the content.
		 */
		return do_shortcode( $content );
	}

/**
 * Displays the term list links
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param   integer $id       the talk ID
 * @param   string  $taxonomy the taxonomy of the terms
 * @param   string  $before   the string to display before
 * @param   string  $sep      the separator for the term list
 * @param   string  $after    the string to display after
 * @return  string the term list links
 */
function wct_talks_get_the_term_list( $id = 0, $taxonomy = '', $before = '', $sep = ', ', $after = '' ) {
	// Bail if no talk ID or taxonomy identifier
	if ( empty( $id ) || empty( $taxonomy ) ) {
		return false;
	}

	/**
	 * @param  string  the term list
	 * @param  int $id the talk ID
	 * @param  string $taxonomy the taxonomy identifier
	 */
	return apply_filters( 'wct_talks_get_the_term_list', get_the_term_list( $id, $taxonomy, $before, $sep, $after ), $id, $taxonomy );
}

/**
 * Displays a custom field in single talk's view
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param  string $display_meta the meta field single output
 * @param  object $meta_object  the meta object
 * @param  string $context      the display context (single/form/admin)
 */
function wct_meta_single_display( $display_meta = '', $meta_object = null, $context = '' ) {
	echo wct_get_meta_single_display( $display_meta, $meta_object, $context );
}

	/**
	 * Gets the custom field output for single talk's view
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  string $display_meta the meta field single output
	 * @param  object $meta_object  the meta object
	 * @param  string $context      the display context (single/form/admin)
	 * @return string               HTML Output
	 */
	function wct_get_meta_single_display( $display_meta = '', $meta_object = null, $context = '' ) {
		// Bail if no field name.
		if ( empty( $meta_object->field_name ) ) {
			return;
		}

		$output = '';

		if ( 'single' != $context ) {
			return;
		}

		$output  = '<p><strong>' . esc_html( $meta_object->label ) . '</strong> ';
		$output .= esc_html( $meta_object->field_value ) . '</p>';

		/**
		 * @param  string $output       the meta field single output
		 * @param  object $meta_object  the meta object
		 * @param  string $context      the display context (single/form/admin)
		 */
		return apply_filters( 'wct_get_meta_single_display', $output, $meta_object, $context );
	}

/**
 * Displays the footer of an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_talk_footer() {
	echo wct_talks_get_talk_footer();
}

	/**
	 * Gets the footer of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string  output for the footer
	 */
	function wct_talks_get_talk_footer() {
		$talk = wct()->query_loop->talk;

		$date = apply_filters( 'get_the_date', mysql2date( get_option( 'date_format' ), $talk->post_date ) );
		$placeholders = array( 'date' => $date );

		$category_list = wct_talks_get_the_term_list( $talk->ID, wct_get_category() );
		$tag_list      = wct_talks_get_the_term_list( $talk->ID, wct_get_tag() );

		// Translators: 1 is category, 2 is tag and 3 is the date.
		$retarray = array(
			'utility_text' => _x( 'This talk was posted on %3$s.', 'default talk footer utility text', 'wordcamp-talks' ),
		);

		if ( ! empty( $category_list ) ) {
			// Translators: 1 is category, 2 is tag and 3 is the date.
			$retarray['utility_text'] = _x( 'This talk was posted in %1$s on %3$s.', 'talk attached to at least one category footer utility text', 'wordcamp-talks' );
			$placeholders['category'] = $category_list;
		}

		if ( ! empty( $tag_list ) ) {
			// Translators: 1 is category, 2 is tag and 3 is the date.
			$retarray['utility_text'] = _x( 'This talk was tagged %2$s on %3$s.', 'talk attached to at least one tag footer utility text', 'wordcamp-talks' );
			$placeholders['tag'] = $tag_list;

			if ( ! empty( $category_list ) ) {
				// Translators: 1 is category, 2 is tag and 3 is the date.
				$retarray['utility_text'] =  _x( 'This talk was posted in %1$s and tagged %2$s on %3$s.', 'talk attached to at least one tag and one category footer utility text', 'wordcamp-talks' );
			}
		}

		if ( wct_is_single_talk() ) {
			if ( ! wct_user_can( 'view_other_profiles', $talk->post_author ) ) {
				$user_link = __( 'hidden name', 'wordcamp-talks' );
			} else {
				$user = wct_users_get_user_data( 'id', $talk->post_author );
				$user_link = '<a class="talk-author" href="' . esc_url( wct_users_get_user_profile_url( $talk->post_author, $user->user_nicename ) ) . '" title="' . esc_attr( $user->display_name ) . '">';
				$user_link .= get_avatar( $talk->post_author, 20 ) . esc_html( $user->display_name ) . '</a>';
			}

			// Translators: 1 is category, 2 is tag, 3 is the date and 4 is author.
			$retarray['utility_text']  = _x( 'This talk was posted on %3$s by %4$s.', 'default single talk footer utility text', 'wordcamp-talks' );
			$placeholders['user_link'] = $user_link;

			if ( ! empty( $category_list ) ) {
				// Translators: 1 is category, 2 is tag, 3 is the date and 4 is author.
				$retarray['utility_text'] = _x( 'This talk was posted in %1$s on %3$s by %4$s.', 'single talk attached to at least one category footer utility text', 'wordcamp-talks' );
			}

			if ( ! empty( $tag_list ) ) {
				// Translators: 1 is category, 2 is tag, 3 is the date and 4 is author.
				$retarray['utility_text'] = _x( 'This talk was tagged %2$s on %3$s by %4$s.', 'single talk attached to at least one tag footer utility text', 'wordcamp-talks' );

				if ( ! empty( $category_list ) ) {
					// Translators: 1 is category, 2 is tag, 3 is the date and 4 is author.
					$retarray['utility_text'] =  _x( 'This talk was posted in %1$s and tagged %2$s on %3$s by %4$s.', 'single talk attached to at least one tag and one category footer utility text', 'wordcamp-talks' );
				}
			}

			// Print placeholders
			$retarray['utility_text'] = sprintf(
				$retarray['utility_text'],
				$category_list,
				$tag_list,
				$date,
				$user_link
			);

		} else {
			// Print placeholders
			$retarray['utility_text'] = sprintf(
				$retarray['utility_text'],
				$category_list,
				$tag_list,
				$date
			);
		}

		// Init edit url
		$edit_url = '';

		// Super admin will use the Administration screens
		if ( wct_user_can( 'wct_talks_admin' ) ) {
			$edit_url = get_edit_post_link( $talk->ID );

		// The author will use the front end edit form
		} else if ( wct_talks_can_edit( $talk ) ) {
			$edit_url = wct_get_form_url( wct_edit_slug(), $talk->post_name );
		}

		if ( ! empty( $edit_url ) ) {
			$edit_class = 'edit-talk';
			$edit_title = __( 'Edit Talk', 'wordcamp-talks' );

			if ( 'talks' !== $talk->post_type ) {
				$post_type_labels = get_post_type_labels( get_post_type_object( $talk->post_type ) );
				if ( ! empty( $post_type_labels->singular_name ) ) {
					$edit_class = 'edit-' . strtolower( $post_type_labels->singular_name );
					$edit_title = $post_type_labels->edit_item;
				}
			}

			$retarray['edit'] = '<a class="' . sanitize_html_class( $edit_class ) . '" href="' . esc_url( $edit_url ) . '" title="' . esc_attr( $edit_title ) . '">' . esc_html( $edit_title ) . '</a>';
		}

		/**
		 * Filter here to edit the talk footer utility text
		 *
		 * @since 1.0.0
		 *
		 * @param  string  the footer to output
		 * @param  array   $retarray the parts of the footer organized in an associative array
		 * @param  WP_Post $talk the talk object
		 * @param  array   $placeholders the placeholders for the footer utility text
		 */
		return apply_filters( 'wct_talks_get_talk_footer', join( ' ', $retarray ), $retarray, $talk, $placeholders );
	}

/**
 * Displays a bottom nav on single template
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string the bottom nav output
 */
function wct_talks_bottom_navigation() {
	?>
	<ul class="talk-nav-single">
		<li class="talk-nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'wordcamp-talks' ) . '</span> %title' ); ?></li>
		<li class="talk-nav-all"><span class="meta-nav">&uarr;</span> <a href="<?php echo esc_url( wct_get_root_url() );?>" title="<?php esc_attr_e( 'All Talks', 'wordcamp-talks') ;?>"><?php esc_html_e( 'All Talks', 'wordcamp-talks') ;?></a></li>
		<li class="talk-nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'wordcamp-talks' ) . '</span>' ); ?></li>
	</ul>
	<?php
}

/** Talk Form *****************************************************************/

/**
 * Displays a message to not logged in users
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string the not logged in message output
 */
function wct_talks_not_loggedin() {
	$output = esc_html__( 'You are not allowed to submit talks', 'wordcamp-talks' );

	if ( ! is_user_logged_in() ) {

		if ( wct_is_signup_allowed_for_current_blog() ) {
			$output = sprintf(
				__( 'Please <a href="%s" title="Log in">log in</a> or <a href="%s" title="Sign up">register</a> to this site to submit an talk.', 'wordcamp-talks' ),
				esc_url( wp_login_url( wct_get_form_url() ) ),
				esc_url( wct_users_get_signup_url() )
			);
		} else {
			$output = sprintf(
				__( 'Please <a href="%s" title="Log in">log in</a> to this site to submit an talk.', 'wordcamp-talks' ),
				esc_url( wp_login_url( wct_get_form_url() ) )
			);
		}

		// Check for a custom message..
		$custom_message = wct_login_message();

		if ( ! empty( $custom_message ) ) {
			$output = $custom_message;
		}

	}

	/**
	 * @param  string $output the message to output
	 */
	echo apply_filters( 'wct_talks_not_loggedin', $output );
}

/**
 * Displays the field to edit the talk title
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string output for the talk title field
 */
function wct_talks_the_title_edit() {
	?>
	<label for="_wct_the_title"><?php esc_html_e( 'Title', 'wordcamp-talks' );?> <span class="required">*</span></label>
	<input type="text" id="_wct_the_title" name="wct[_the_title]" value="<?php wct_talks_get_title_edit();?>"/>
	<?php
}

	/**
	 * Gets the value of the title field of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string  output for the title field
	 */
	function wct_talks_get_title_edit() {
		$wct = wct();

		// Did the user submitted a title ?
		if ( ! empty( $_POST['wct']['_the_title'] ) ) {
			$edit_title = $_POST['wct']['_the_title'];

		// Are we editing an talk ?
		} else if ( ! empty( $wct->query_loop->talk->post_title ) ) {
			$edit_title = $wct->query_loop->talk->post_title;

		// Fallback to empty
		} else {
			$edit_title = '';
		}

		/**
		 * @param  string $edit_title the title field
		 */
		echo apply_filters( 'wct_talks_get_title_edit', esc_attr( $edit_title ) );
	}

/**
 * Displays the field to edit the talk content
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string output for the talk content field
 */
function wct_talks_the_editor() {
	$args = array(
		'textarea_name' => 'wct[_the_content]',
		'wpautop'       => true,
		'media_buttons' => false,
		'editor_class'  => 'wc-talks-tinymce',
		'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
		'teeny'         => false,
		'dfw'           => false,
		'tinymce'       => true,
		'quicktags'     => false
	);

	// Temporarly filter the editor
	add_filter( 'mce_buttons', 'wct_teeny_button_filter', 10, 1 );
	?>

	<label for="wct_the_content"><?php esc_html_e( 'Description', 'wordcamp-talks' ) ;?> <span class="required">*</span></label>

	<?php
	do_action( 'wct_media_buttons' );
	wp_editor( wct_talks_get_editor_content(), 'wct_the_content', $args );

	remove_filter( 'mce_buttons', 'wct_teeny_button_filter', 10, 1 );
}

	/**
	 * Gets the value of the content field of an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string  output for the content field
	 */
	function wct_talks_get_editor_content() {
		$wct = wct();

		// Did the user submitted a content ?
		if ( ! empty( $_POST['wct']['_the_content'] ) ) {
			$edit_content = $_POST['wct']['_the_content'];

		// Are we editing an talk ?
		} else if ( ! empty( $wct->query_loop->talk->post_content ) ) {
			$edit_content = do_shortcode( $wct->query_loop->talk->post_content );

		// Fallback to empty
		} else {
			$edit_content = '';
		}

		/**
		 * @param  string $edit_content the content field
		 */
		return apply_filters( 'wct_talks_get_editor_content', $edit_content );
	}

/**
 * Displays the list of inserted images to let the user
 * choose the one he wishes to use as the Talk Featured image
 *
 * @since 1.0.0
 *
 * @return string HTML Output
 */
function wct_talks_the_images_list() {
	if ( ! wct_featured_images_allowed() || ! current_theme_supports( 'post-thumbnails' ) ) {
		return;
	}

	$selected       = false;
	$content        = '';
	$srcs           = array();
	$wct = wct();
	$class          = ' class="hidden"';

	// There was an error eg: missing title
	if( ! empty( $_POST['wct']['_the_content'] ) ) {
		$content = wp_unslash( $_POST['wct']['_the_content'] );

		// Did the user selected a featured image ?
		if ( ! empty( $_POST['wct']['_the_thumbnail'] ) ) {
			$selected = (array) $_POST['wct']['_the_thumbnail'];
			$selected = reset( $selected );
		}

	// Are we editing an talk ?
	} else if ( ! empty( $wct->query_loop->talk->post_content ) ) {
		$talk    = $wct->query_loop->talk;
		$content = $talk->post_content;

		// Try to get the current featured image
		$selected = (int) get_post_thumbnail_id( $talk );

		if ( ! empty( $selected ) ) {
			$original_url = get_post_meta( $selected, '_wc_talks_original_src', true );

			if ( empty( $original_url ) ) {
				$original_url = wp_get_attachment_url( $selected );
			}

			$srcs = array( $original_url => $selected );
		}

		/**
		 * Get all talk attachments (those who have an _wc_talks_original_url meta)
		 *
		 * We need to do this in case the featured image was edited and for some reason the
		 * user deleted one or more images from the content
		 */
		$srcs = array_replace( $srcs, WordCamp_Talks_Talks_Thumbnail::get_talk_attachments( $talk->ID ) );
	}

	// Find image into the content
	if ( ! empty( $content ) ) {
		$class = '';

		if ( false !== stripos( $content, 'src=' ) ) {
			preg_match_all( '#src=(["\'])([^"\']+)\1#i', $content, $img_srcs );
			if ( ! empty( $img_srcs[2] ) ) {
				// Avoid duplicates
				$content_srcs = array_unique( $img_srcs[2] );

				// Create a non numeric keys array
				$content_srcs = array_combine( $content_srcs, $content_srcs );

				/**
				 * Make sure to use attachment ids if some were found earlier
				 */
				$srcs = array_replace( $content_srcs, $srcs );
			}
		}
	}

	// Can be an attachment ID
	if ( ! empty( $selected ) ) {
		if ( is_numeric( $selected ) ) {
			$selected = (int) $selected;

		// Or an url
		} else {
			$selected = esc_url( $selected );
		}
	}
	?>
	<div id="talk-images-list"<?php echo $class; ?>>
		<label><?php esc_html_e( 'Select the featured image for your talk.', 'wordcamp-talks' );?></label>
		<?php if ( ! empty( $srcs ) ) : ?>
			<ul>
			<?php foreach ( $srcs as $ksrc => $src )  : ?>
				<li>
					<img src="<?php echo esc_url( $ksrc ) ;?>"/>

					<?php if ( is_numeric( $src ) ) {
						$thumbnail = (int) $src;
					} else {
						$thumbnail = esc_url( $src );
					};?>

					<div class="cb-container">
						<input type="checkbox" name="wct[_the_thumbnail][<?php echo esc_url_raw( $ksrc ) ;?>]" value="<?php echo $thumbnail ;?>" <?php checked( $selected, $thumbnail ); ?>/>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif ;?>
	</div>
	<?php
}

/**
 * Checks if the category taxonomy has terms
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return bool true if category has terms, false otherwise
 */
function wct_talks_has_terms() {
	// Allow hiding cats
	$pre_has_terms = apply_filters( 'wct_talks_pre_has_terms', true );

	if ( empty( $pre_has_terms ) ) {
		return false;
	}

	// Allow category listing override
	$args = apply_filters( 'wct_talks_get_terms_args', array() );

	// Get all terms matching args
	$terms = wct_talks_get_terms( wct_get_category(), $args );

	if ( empty( $terms ) ) {
		return false;
	}

	// Catch terms
	wct_set_global( 'edit_form_terms', $terms );

	// Inform we have categories
	return true;
}

/**
 * Displays the checkboxes to select categories
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_category_edit() {
	if ( ! taxonomy_exists( wct_get_category() ) || ! wct_talks_has_terms() ) {
		return;
	}
	?>
	<label><?php esc_html_e( 'Categories', 'wordcamp-talks' );?></label>
	<?php wct_talks_get_category_edit();
}

	/**
	 * Builds a checkboxes list of categories
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string  output for the list of categories
	 */
	function wct_talks_get_category_edit() {
		$wct = wct();

		// Did the user submitted categories ?
		if ( ! empty( $_POST['wct']['_the_category'] ) ) {
			$edit_categories = (array) $_POST['wct']['_the_category'];

		// Are we editing an talk ?
		} else if ( ! empty( $wct->query_loop->talk->ID ) ) {
			$edit_categories = (array) wp_get_object_terms( $wct->query_loop->talk->ID, wct_get_category(), array( 'fields' => 'ids' ) );

		// Default to en empty array
		} else {
			$edit_categories = array();
		}

		$terms = wct_get_global( 'edit_form_terms' );

		// Default output
		$output = esc_html__( 'No categories are available.', 'wordcamp-talks' );

		if ( empty( $terms ) ) {
			/**
			 * @param  string $output the output when no categories
			 */
			echo apply_filters( 'wct_talks_get_category_edit_none', $output );
			return;
		}

		$output = '<ul class="category-list">';

		foreach ( $terms as $term ) {
			$output .= '<li><label for="_wct_the_category_' . esc_attr( $term->term_id ) . '">';
			$output .= '<input type="checkbox" name="wct[_the_category][]" id="_wct_the_category_' . esc_attr( $term->term_id ) . '" value="' . esc_attr( $term->term_id ) . '" ' . checked( true, in_array( $term->term_id, $edit_categories  ), false ) . '/>';
			$output .= ' ' . esc_html( $term->name ) . '</label></li>';

		}

		$output .= '</ul>';

		/**
		 * @param  string $output the output when has categories
		 * @param  array  $edit_categories selected term ids
		 * @param  array  $terms available terms for the category taxonomy
		 */
		echo apply_filters( 'wct_talks_get_category_edit', $output, $edit_categories, $terms );
	}


/**
 * Displays the tag editor for an talk
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 */
function wct_talks_the_tags_edit() {
	if ( ! taxonomy_exists( wct_get_tag() ) ) {
		return;
	}
	?>
	<label for="_wct_the_tags"><?php esc_html_e( 'Tags', 'wordcamp-talks' );?></label>
	<p class="description"><?php esc_html_e( 'Type your tag, then hit the return or space key to add it','wordcamp-talks' ); ?></p>
	<div id="_wct_the_tags"><?php wct_talks_get_tags();?></div>
	<?php wct_talks_the_tag_cloud();
}

	/**
	 * Builds a checkboxes list of categories
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @return string  output for the list of tags
	 */
	function wct_talks_get_tags() {
		$wct = wct();

		// Did the user submitted tags ?
		if ( ! empty( $_POST['wct']['_the_tags'] ) ) {
			$edit_tags = (array) $_POST['wct']['_the_tags'];

		// Are we editing tags ?
		} else if ( ! empty( $wct->query_loop->talk->ID ) ) {
			$edit_tags = (array) wp_get_object_terms( $wct->query_loop->talk->ID, wct_get_tag(), array( 'fields' => 'names' ) );

		// Default to an empty array
		} else {
			$edit_tags = array();
		}

		// Sanitize tags
		$edit_tags = array_map( 'esc_html', $edit_tags );

		/**
		 * @param  string the tags list output
		 * @param  array  $edit_tags selected term slugs
		 */
		echo apply_filters( 'wct_talks_get_tags', join( ', ', $edit_tags ), $edit_tags );
	}

/**
 * Displays a tag cloud to show the most used one
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param  int $number the number of tags to display
 * @return string output for the tag cloud
 */
function wct_talks_the_tag_cloud( $number = 10 ) {
	$tag_cloud = wct_generate_tag_cloud();

	if ( empty( $tag_cloud ) ) {
		return;
	}

	if ( $tag_cloud['number'] != $number  ) {
		$number = $tag_cloud['number'];
	}

	$number = number_format_i18n( $number );
	?>
	<div id="wct_most_used_tags">
		<p class="description"><?php printf( _n( 'Choose the most used tag', 'Choose from the %d most used tags', $number, 'wordcamp-talks' ), $number ) ;?></p>
		<div class="tag-items">
			<?php echo $tag_cloud['tagcloud'] ;?>
		</div>
	</div>
	<?php
}

/**
 * Displays a meta field for form/admin views
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @param  string $display_meta the meta field single output
 * @param  object $meta_object  the meta object
 * @param  string $context      the display context (single/form/admin)
 */
function wct_meta_admin_display( $display_meta = '', $meta_object = null, $context = '' ) {
	echo wct_get_meta_admin_display( $display_meta, $meta_object, $context );
}

	/**
	 * Gets the custom field output for form/admin talk's view
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  string $display_meta the meta field single output
	 * @param  object $meta_object  the meta object
	 * @param  string $context      the display context (single/form/admin)
	 * @return string               HTML Output
	 */
	function wct_get_meta_admin_display( $display_meta = '', $meta_object = null, $context = '' ) {
		if ( empty( $meta_object->field_name ) ) {
			return;
		}

		$output = '';

		if ( 'admin' == $context ) {
			$output  = '<p><strong class="label">' . esc_html( $meta_object->label ) . '</strong> ';
			$output .= '<input type="text" name="' . esc_attr( $meta_object->field_name ) . '" value="' . esc_attr( $meta_object->field_value ) . '"/></p>';
		} else if ( 'form' == $context ) {
			$output  = '<p><label for="_wct_' . $meta_object->meta_key . '">' . esc_html( $meta_object->label ) . '</label>';
			$output .= '<input type="text" id="_wct_' . $meta_object->meta_key . '" name="' . esc_attr( $meta_object->field_name ) . '" value="' . esc_attr( $meta_object->field_value ) . '"/></p>';
		}

		/**
		 * @param  string $output       the meta field admin/form output
		 * @param  object $meta_object  the meta object
		 * @param  string $context      the display context (single/form/admin)
		 */
		return apply_filters( 'wct_get_meta_admin_display', $output, $meta_object, $context );
	}

/**
 * Displays the form submit/reset buttons
 *
 * @package WordCamp Talks
 * @subpackage talks/tags
 *
 * @since 1.0.0
 *
 * @return string output for submit/reset buttons
 */
function wct_talks_the_form_submit() {
	$wct = wct();

	wp_nonce_field( 'wct_save' );

	do_action( 'wct_talks_the_form_submit' ); ?>

	<?php if ( wct_is_addnew() ) : ?>

		<input type="reset" value="<?php esc_attr_e( 'Reset', 'wordcamp-talks' ) ;?>"/>
		<input type="submit" value="<?php esc_attr_e( 'Submit', 'wordcamp-talks' ) ;?>" name="wct[save]"/>

	<?php elseif( wct_is_edit() && ! empty( $wct->query_loop->talk->ID ) ) : ?>

		<input type="hidden" value="<?php echo esc_attr( $wct->query_loop->talk->ID ) ;?>" name="wct[_the_id]"/>
		<input type="submit" value="<?php esc_attr_e( 'Update', 'wordcamp-talks' ) ;?>" name="wct[save]"/>

	<?php endif ; ?>

	<?php
}

/**
 * If BuddyDrive is activated, then use it to allow files
 * to be added to talks !
 *
 * @since  1.0.0
 */
function wct_buddydrive_button() {
	if ( function_exists( 'buddydrive_editor' ) ) {
		buddydrive_editor();
	}
}
add_action( 'wct_media_buttons', 'wct_buddydrive_button' );

/**
 * Output the Talk Ratings if needed into the Embedded talk
 *
 * @since  1.0.0
 *
 * @return string HTML output
 */
function wct_talks_embed_meta() {
	$talk = get_post();

	if ( ! isset( $talk->post_type ) || wct_get_post_type() !== $talk->post_type || wct_is_rating_disabled() ) {
		return;
	}

	// Get the Average Rate
	$average_rate = wct_talks_get_average_rating( $talk->ID );

	if ( ! $average_rate ) {
		return;
	}

	// Get rating link
	$rating_link = wct_talks_get_talk_permalink( $talk ) . '#rate';
	?>
	<div class="wc-talks-embed-ratings">
		<a href="<?php echo esc_url( $rating_link ); ?>" target="_top">
			<span class="dashicons wc-talks-star-filled"></span>
			<?php printf(
				esc_html__( '%1$sAverage Rating:%2$s%3$s', 'wordcamp-talks' ),
				'<span class="screen-reader-text">',
				'</span>',
				$average_rate
			); ?>
		</a>
	</div>
	<?php
}
