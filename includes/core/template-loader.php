<?php
/**
 * WordCamp Talks template loader.
 *
 * Most of the job is done in the class WordCamp_Talks_Template_Loader
 * @see  core/classes
 *
 * @package   WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since  1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Buffer a template part to build the content of a page
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @param  string  $slug template slug
 * @param  string  $name template name
 * @param  bool    $echo output or return ?
 * @return string $output html of the buffered template part
 */
function wct_buffer_template_part( $slug, $name = null, $echo = true ) {
	ob_start();

	wct_template_part( $slug, $name );

	// Get the output buffer contents
	$output = ob_get_clean();

	// Echo or return the output buffer contents
	if ( true === $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Add a specific header and footer parts to single talk.
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @param  string $content the content of the talk
 * @return string $new_content the content of the talk
 */
function wct_buffer_single_talk( $content = '' ) {
	$new_content  = '<div id="wordcamp-talks">';

	// Eventually include some feedback messages
	ob_start();

	wct_user_feedback();

	$new_content .= ob_get_clean();

	// add a header
	$new_content .= wct_buffer_template_part( 'talk', 'header', false );

	// keep the content unchanged
	$new_content .= $content;

	// add a footer
	$new_content .= wct_buffer_template_part( 'talk', 'footer', false );

	$new_content .= '</div>';

	return $new_content;
}

/**
 * Load a template part
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @param  string  $slug template slug
 * @param  string  $name template name
 * @param  bool    $load should we load ?
 * @param  bool    $require_once should we load it once only ?
 */
function wct_get_template_part( $slug, $name = null, $load = true, $require_once = true ) {
	$templates = new WordCamp_Talks_Template_Loader;

	return $templates->get_template_part( $slug, $name, $load, $require_once );
}

/**
 * Load a template part as many time as needed.
 *
 * Shortcut for wct_get_template_part() having require once set to false.
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @param  string $slug template slug
 * @param  string $name template name
 * @param  bool   $require_once default to false (for use in loops)
 */
function wct_template_part( $slug, $name = null, $require_once = false ) {
	return wct_get_template_part( $slug, $name, true, $require_once );
}

/**
 * Get the stylesheet to apply by first looking in
 * the theme's wordcamp-talks subdirectory
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @param  string $css the name of the file to load
 * @return string the url to the stylesheet
 */
function wct_get_stylesheet( $css = 'style' ) {
	$style = new WordCamp_Talks_Template_Loader;

	return $style->get_stylesheet( $css );
}

/**
 * Fill up some WordPress globals with dummy data
 *
 * Based on bbPress bbp_theme_compat_reset_post() function
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query
 * @global WP_Post  $post
 * @param  array    $args
 */
function wct_reset_post( $args = array() ) {
	global $wp_query, $post;

	// Switch defaults if post is set
	if ( isset( $wp_query->post ) ) {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => $wp_query->post->ID,
			'post_status'           => $wp_query->post->post_status,
			'post_author'           => $wp_query->post->post_author,
			'post_parent'           => $wp_query->post->post_parent,
			'post_type'             => $wp_query->post->post_type,
			'post_date'             => $wp_query->post->post_date,
			'post_date_gmt'         => $wp_query->post->post_date_gmt,
			'post_modified'         => $wp_query->post->post_modified,
			'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
			'post_content'          => $wp_query->post->post_content,
			'post_title'            => $wp_query->post->post_title,
			'post_excerpt'          => $wp_query->post->post_excerpt,
			'post_content_filtered' => $wp_query->post->post_content_filtered,
			'post_mime_type'        => $wp_query->post->post_mime_type,
			'post_password'         => $wp_query->post->post_password,
			'post_name'             => $wp_query->post->post_name,
			'guid'                  => $wp_query->post->guid,
			'menu_order'            => $wp_query->post->menu_order,
			'pinged'                => $wp_query->post->pinged,
			'to_ping'               => $wp_query->post->to_ping,
			'ping_status'           => $wp_query->post->ping_status,
			'comment_status'        => $wp_query->post->comment_status,
			'comment_count'         => $wp_query->post->comment_count,
			'filter'                => $wp_query->post->filter,

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	} else {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => -9999,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	}

	// Bail if dummy post is empty
	if ( empty( $dummy ) ) {
		return;
	}

	// Set the $post global
	$post = new WP_Post( (object) $dummy );

	// Copy the new post global into the main $wp_query
	$wp_query->post       = $post;
	$wp_query->posts      = array( $post );

	// Prevent comments form from appearing
	$wp_query->post_count = 1;
	$wp_query->is_404     = $dummy['is_404'];
	$wp_query->is_page    = $dummy['is_page'];
	$wp_query->is_single  = $dummy['is_single'];
	$wp_query->is_archive = $dummy['is_archive'];
	$wp_query->is_tax     = $dummy['is_tax'];

	// Clean up the dummy post
	unset( $dummy );

	/**
	 * Force the header back to 200 status if not a deliberate 404
	 */
	if ( ! $wp_query->is_404() ) {
		status_header( 200 );
	}
}

/**
 * Set the template to use, buffers the needed template parts
 * and resets post vars.
 *
 * @package WordCamp Talks
 * @subpackage core/template-loader
 *
 * @since 1.0.0
 *
 * @global $wp_query
 * @param  string $template name of the template to use
 * @return string $template.
 */
function wct_set_template( $template = '' ) {
	global $wp_query;

	if ( wct_get_global( 'is_talks' ) && ! is_404() && ! is_embed() ) {

		// Try to see if the theme has a specific template for WordCamp Talks
		$template = get_query_template( 'wordcamp-talks' );

		if ( empty( $template ) ) {
			// else Try the page template
			$template = get_query_template( 'page', array( 'page.php' ) );
		}

		if (  empty( $template ) ) {
			// finally fall back to the index template
			$template = get_index_template();
		}

		// Define it into plugin's vars
		wct_set_global( 'template_file', $template );

		/**
		 * First get results of the main query if not on a single talk.
		 * and build plugin's main_query var.
		 */
		if( ! wct_is_single_talk() ) {
			wct_set_global( 'main_query', array(
				'talks'      => $wp_query->posts,
				'total'      => $wp_query->found_posts,
				'query_vars' => array(
					'author'     => $wp_query->query_vars['author'],
					'per_page'   => $wp_query->query_vars['posts_per_page'],
					'page'       => ! empty( $wp_query->query_vars['paged'] ) ? $wp_query->query_vars['paged'] : 1,
					'search'     => $wp_query->query_vars['s'],
					'exclude'    => $wp_query->query_vars['post__not_in'],
					'include'    => $wp_query->query_vars['post__in'],
					'orderby'    => ! empty( $wp_query->query_vars['orderby'] ) ? $wp_query->query_vars['orderby'] : 'date',
					'order'      => $wp_query->query_vars['order'],
					'meta_query' => $wp_query->meta_query->queries,
					'tax_query'  => $wp_query->tax_query->queries,
				)
			) );

			// Resetting the 's' query var now we got main query's result.
			set_query_var( 's', '' );

			// Init template args
			$template_args = array(
				'post_title'     => '',
				'comment_status' => 'closed',
				'is_archive'     => true,
				'is_tax'         => false,
				'template_slug'  => 'archive',
				'template_name'  => '',
				'context'        => '',
			);

			// Main plugin's archive page
			if ( is_post_type_archive( wct_get_post_type() ) ) {
				$template_args['context'] = 'archive';
			}

			// Category / tag archive pages
			if ( wct_get_global( 'is_category' ) || wct_get_global( 'is_tag' ) ) {
				$template_args['is_tax']  = true;
				$template_args['context'] = 'taxonomy';
			}

			// User's profile pages
			if ( wct_get_global( 'is_user' ) ) {
				$template_args['template_slug'] = 'user';
				$template_args['template_name'] = 'profile';
				$template_args['context']       = 'user-profile';
			}

			if ( wct_get_global( 'is_action' ) ) {
				$template_args['is_archive']    = false;

				// New talk form
				if ( wct_is_addnew() ) {
					$template_args['template_slug'] = 'talk';
					$template_args['template_name'] = 'form';
					$template_args['context']       = 'new-talk';

				} else if ( wct_is_signup() ) {
					$template_args['template_slug'] = 'signup';
					$template_args['context']       = 'signup';

				// Allow plugins to add custom action
				} else if ( has_filter( 'wct_template_args' ) ) {
					/**
					 * Custom action ?
					 *
					 * @param array $template_args the template arguments used to reset the post
					 */
					$template_args = apply_filters( 'wct_template_args', $template_args );
				}
			}

			// Reset WordPress $post global.
			wct_reset_post( array(
				'ID'             => 0,
				'post_title'     => wct_reset_post_title( $template_args['context'] ),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_type'      => 'talks',
				'post_status'    => 'publish',
				'is_archive'     => $template_args['is_archive'],
				'comment_status' => $template_args['comment_status'],
				'post_password'  => false,
				'is_tax'         => $template_args['is_tax'],
			) );

			/**
			 * Internally used to redirect to BuddyPress member's profile
			 * if needed
			 *
			 * @param  string $context to help choosing the best template to use
			 */
			do_action( 'wct_set_core_template', $template_args['context'], $template_args );

		} else {
			$query_loop = new stdClass();
			$query_loop->talk = $wp_query->post;

			// Should we use a custom template for single talks ?
			$specific_single_template = get_query_template( 'single-wordcamp-talks' );

			if ( ! empty( $specific_single_template ) ) {
				$template = $specific_single_template;
			}

			// Populate the global query loop with current talk
			wct_set_global( 'query_loop', $query_loop );

			// Add the id to globals
			wct_set_global( 'single_talk_id', $wp_query->post->ID );

			// Are we editing an talk ?
			if ( wct_is_edit() ) {

				// Check if the talk is currently being edited by someone else
				$user_is_editing = wct_talks_lock_talk( $query_loop->talk->ID );

				if ( ! empty( $user_is_editing ) ) {
					wct_add_message( array(
						'type'    => 'info',
						'content' => sprintf( __( 'The talk: &#34;%s&#34; is already being edited by another user.', 'wordcamp-talks' ), $query_loop->talk->post_title ),
					) );

					// Redirect the user
					wp_safe_redirect( wct_get_redirect_url() );
					exit();
				}

				// Bail if user can't edit the talk
				if ( ! wct_talks_can_edit( $query_loop->talk ) ) {
					wct_add_message( array(
						'type'    => 'error',
						'content' => __( 'You are not allowed to edit this talk.', 'wordcamp-talks' ),
					) );

					// Redirect the user
					wp_safe_redirect( wct_get_redirect_url() );
					exit();
				}

				// Inform the talk is to display in an edit form
				$query_loop->talk->is_edit = true;

				$template_args = array(
					'template_slug' => 'talk',
					'template_name' => 'form',
					'context'       => 'edit-talk',
				);

				$single_args = array(
					'ID'             => 0,
					'post_title'     => wct_reset_post_title( $template_args['context'] ),
					'post_author'    => 0,
					'post_date'      => 0,
					'post_type'      => 'talks',
					'post_status'    => 'publish',
					'is_archive'     => false,
					'comment_status' => false,
					'post_password'  => false,
				);

			// Or simply viewing one ?
			} else {
				$template_args = array( 'context' => 'single-talk' );
				$single_args = array(
					'is_single'    => true,
				);
			}

			/**
			 * @param array $single_args the single arguments used to reset the post
			 */
			wct_reset_post( apply_filters( 'wct_single_template_args', $single_args ) );

			/**
			 * Internally used to redirect to Buddypress Group's
			 * single talk template if needed
			 *
			 * @param  WP_Post $query_loop->talk the talk to display
			 */
			do_action( 'wct_set_single_template', $query_loop->talk, $template_args );
		}
	}

	/**
	 * No templates matched
	 */
	do_action( 'wct_set_template' );

	return $template;
}

/**
 * Replace the content when in a plugin's front end part
 *
 * @since  1.0.0
 */
class WordCamp_Talks_Core_Screens {
	public function __construct( $template_args = null ) {
		if ( ! empty( $template_args ) ) {
			$this->template_args = $template_args;
		}

		add_filter( 'the_content', array( $this, 'replace_the_content' ), 10, 1 );
	}

	public static function start( $context, $template_args ) {
		$wct = wct();

		if ( empty( $wct->screens ) ) {
			$wct->screens = new self( $template_args );
		}

		return $wct->screens;
	}

	public function replace_the_content( $content ) {
		if ( 'single-talk' === $this->template_args['context'] ) {
			// Do not filter the content inside the document header
			if ( doing_action( 'wp_head' ) ) {
				return $content;
			}

			$content = wct_buffer_single_talk( $content );
		} else {
			$content = wct_buffer_template_part( $this->template_args['template_slug'], $this->template_args['template_name'], false );
		}

		return $content;
	}
}
add_action( 'wct_set_core_template',   array( 'WordCamp_Talks_Core_Screens', 'start' ), 0, 2 );
add_action( 'wct_set_single_template', array( 'WordCamp_Talks_Core_Screens', 'start' ), 0, 2 );
