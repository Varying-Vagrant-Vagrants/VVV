<?php
/**
 * WordCamp Talks Comments Administration.
 *
 * Comments Administration class
 *
 * @package WordCamp Talks
 * @subpackage admin/comments
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Admin_Comments' ) ) :
/**
 * Comments Administration class
 *
 * The goal of the class is to adapt the Comments
 * Administration interface so that comments about talks
 * are disjoined and included in the main plugin's menu
 *
 * @package WordCamp Talks
 * @subpackage admin/comments
 *
 * @since 1.0.0
 *
 * @see  comments/class WordCamp_Talks_Comments for the disjoin methods
 */
class WordCamp_Talks_Admin_Comments {

	/** Variables *****************************************************************/

	/**
	 * @access  private
	 * @var string the talks post type
	 */
	private $post_type = '';

	/**
	 * @access  public
	 * @var object talk comments stats
	 */
	public $talk_comment_count;

	/**
	 * The constuctor
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Starts the class
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wct_admin = wct()->admin;

		if ( empty( $wct_admin->comments ) ) {
			$wct_admin->comments = new self;
		}

		return $wct_admin->comments;
	}

	/**
	 * Sets some globals
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->post_type          = wct_get_post_type();
		$this->talk_comment_count = false;
	}

	/**
	 * Sets up the hooks to extend the plugin's Administration
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Add a bubble to plugin's parent menu if some talk comments are pending
		add_action( 'wct_admin_head',  array( $this, 'admin_head' ), 10 );

		// Check the post type if actions were made clicking on a moderation link from an email
		add_action( 'load-edit-comments.php', array( $this, 'maybe_force_post_type' ) );

		// Load some script to also disjoin bubbles
		add_action( 'admin_footer-edit-comments.php', array( $this, 'disjoin_post_bubbles' ) );
		add_action( 'admin_footer-edit.php',          array( $this, 'disjoin_post_bubbles' ) );

		/** Filters *******************************************************************/

		// Add a comment submenu to plugin's menu.
		add_filter( 'wct_admin_menus', array( $this, 'comments_menu' ), 10, 1 );

		// Adjust comment views (count) and comment row actions
		add_filter( 'comment_status_links', array( $this, 'adjust_comment_status_links' ), 10, 1 );
		add_filter( 'comment_row_actions',  array( $this, 'adjust_row_actions' ),          10, 2 );
	}

	/**
	 * Adds a bubble to menu title to show how many comments are pending
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $menu_title the text for the menu
	 * @param  int     $count      the number of comments
	 * @return string              the title menu output
	 */
	public function bubbled_menu( $menu_title = '', $count = 0 ) {
		return sprintf(
			_x( '%1$s %2$s', 'wordcamp-talks admin menu bubble', 'wordcamp-talks' ),
			$menu_title,
			"<span class='awaiting-mod count-" . esc_attr( $count ) . "'><span class='pending-count-talk'>" . number_format_i18n( $count ) . "</span></span>"
		);
	}

	/**
	 * Creates a comments submenu for the plugin's menu
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $menus list of menu items to add
	 * @return array         the new menu items
	 */
	public function comments_menu( $menus = array() ) {
		// Comments menu title
		$comments_menu_title = esc_html__( 'Comments', 'wordcamp-talks' );

		$this->talk_comment_count = wct_get_global( 'talk_comment_count' );

		if ( empty( $this->talk_comment_count ) ) {
			$this->talk_comment_count = wct_comments_count_comments();
		}

		$comments_menu_title = $this->bubbled_menu( $comments_menu_title . ' ', $this->talk_comment_count->moderated );

		$menus[0] = array(
			'type'          => 'comments',
			'parent_slug'   => wct()->admin->parent_slug,
			'page_title'    => esc_html__( 'Comments', 'wordcamp-talks' ),
			'menu_title'    => $comments_menu_title,
			'capability'    => 'edit_posts', // Unfortunately We cannot use 'edit_talks' here see edit-comments.php.
			'slug'          => add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' ),
			'function'      => '',
			'alt_screen_id' => 'edit-comments.php',
			'actions'       => array(
				'admin_head-%page%' => array( $this, 'comments_menu_highlight' )
			),
		);

		return $menus;
	}

	/**
	 * Adds a bubble to plugin's menu title and make sure it's the highlighted parent
	 * when talk comments screens are displayed
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @global $menu
	 * @global $submenu
	 * @global $parent_file
	 * @global $submenu_file
	 */
	public function admin_head() {
 		global $menu, $submenu, $parent_file, $submenu_file;

 		$menu_title = _x( 'Talks', 'Main Plugin menu', 'wordcamp-talks' );

 		// Eventually add a bubble in plugin's Menu
 		foreach ( $menu as $position => $data ) {
 			if ( strpos( $data[0], $menu_title ) !== false ) {
				$menu[ $position ][0] = $this->bubbled_menu( $menu_title . ' ', $this->talk_comment_count->moderated );
 			}
		}

		if ( $this->post_type == get_current_screen()->post_type && 'comment' == get_current_screen()->id ) {
			$parent_file  = add_query_arg( 'post_type', $this->post_type, 'edit.php' );
			$submenu_file = add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' );
		}
	}

	/**
	 * Make sure the comments plugin's submenu is the highlighted submenu
	 * if its content is displayed
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @global $submenu_file
	 * @uses   add_query_arg() to build menu item slugs
	 */
	public function comments_menu_highlight() {
		global $submenu_file;

		if( ! wct_is_admin() ) {
			return;
		}

		$submenu_file = add_query_arg( 'post_type', $this->post_type, 'edit-comments.php' );
	}

	/**
	 * Replaces the comment count by the talk comments count in the screen views when
	 * managing comments about talks
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $status_links list of WP Liste Comments Table views
	 * @return array                list of views with a new count if needed
	 */
	public function adjust_comment_status_links( $status_links = array() ) {
		// Bail if not in Talk Comments area
		if ( ! wct_is_admin() ) {
			return $status_links;
		}

		foreach ( $status_links as $key => $link ) {

			if ( isset( $this->talk_comment_count->{$key} ) ) {
				$prefix = $key;

				if ( 'moderated' == $key ) {
					$prefix = 'pending';
				}

				$link = preg_replace(
					'/<span class=\"' . $prefix . '-count\">\d<\/span>/',
					'<span class="' . $prefix . '-count">' . $this->talk_comment_count->{$key} . '</span>',
					$link
				);
			}

			$link = preg_replace( '/\?/', '?post_type=' . $this->post_type . '&', $link );

			if ( preg_match( '/class=\"pending-count\"/', $link ) ) {
				$link = preg_replace( '/class=\"pending-count\"/', 'class="pending-count-talk"', $link );
			}

			$status_links[$key] = $link;
		}

		return $status_links;
	}

	/**
	 * Adds a post_type query var to the edit action link
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $actions the list of row actions
	 * @param  object $comment the comment object
	 * @return array           the list of row actions
	 */
	public function adjust_row_actions( $actions = array(), $comment = null ) {
		// Default is unknown...
		$post_type = '';

		if ( ! empty( $comment->post_type ) ) {
			$post_type = $comment->post_type;

		// Ajax Listing comments in the edit talk screen will
		// fail in getting the post type.
		} else {
			$post_type = get_post_type( get_the_ID() );
		}

		// Bail if not the talks post type
		if ( $this->post_type != $post_type ) {
			return $actions;
		}

		if ( ! empty( $actions['edit'] ) ) {
			// get the url
			preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', $actions['edit'], $matches );

			// and add the post type query var to it.
			if ( ! empty( $matches[1] ) ) {
				$actions['edit'] = str_replace( $matches[1], $matches[1] . '&amp;post_type=' . $this->post_type, $actions['edit'] );
			}
		}

		return $actions;
	}

	/**
	 * Sets the post type attribute of the screen when the comments
	 * was made on an talk
	 *
	 * When clicking on a moderation link within a moderation email, the post type
	 * is not set, as a result, the highlighted menu is not the good one. This make
	 * sure the typenow global and the post type attribute of the screen are set
	 * to the talks post type if needed.
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @global $typenow
	 */
	function maybe_force_post_type() {
		global $typenow;

		if ( empty( $_GET['post_type'] ) ) {

			$get_keys = array_keys( $_GET );
			$did_keys = array( 'approved', 'trashed', 'spammed' );

			$match_keys = array_intersect( $get_keys, $did_keys);

			if ( ! $match_keys ) {
				return;
			}

			if ( ! in_array( 'p', $get_keys ) ) {
				return;
			}

			$post_type = get_post_type( absint( $_GET['p'] ) );

			if ( empty( $post_type ) ) {
				return;
			}

			$typenow = $post_type;
			get_current_screen()->post_type = $post_type;
		}
	}

	/**
	 * Disjoin comment count bubbles
	 *
	 * The goal here is to make sure the ajax bubbles count update
	 * are dissociated between posts and talks
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/comments
	 *
	 * @since 1.0.0
	 *
	 * @return string JS output
	 */
	public function disjoin_post_bubbles() {
		if ( ! wct_is_admin() ) {
			return;
		}
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		( function( $ ) {

			<?php if ( 'edit-comments' == get_current_screen()->id ) :?>

				// Neutralize post bubbles
				$( 'span.pending-count' ).each( function() {
					original = $( this ).prop( 'class' );
					$( this ).prop( 'class', original.replace( 'pending-count', 'pending-count-post' ) )
				} );

				// Activate talk bubbles
				$( 'span.pending-count-talk' ).each( function() {
					original = $( this ).prop( 'class' );
					$( this ).prop( 'class', original.replace( 'pending-count-talk', 'pending-count' ) )
				} );

			<?php endif; ?>

			// As WP_List_Table->comments_bubble() function is protected and no filter... last option is JS
			$( '.post-com-count' ).each( function() {
				original = $( this ).prop( 'href' );
				$( this ).prop( 'href', original + '&post_type=<?php wct_post_type(); ?>' );
			} );

		} )(jQuery);
		/* ]]> */
		</script>
		<?php
	}
}

endif;

add_action( 'wct_loaded', array( 'WordCamp_Talks_Admin_Comments', 'start' ), 6 );
