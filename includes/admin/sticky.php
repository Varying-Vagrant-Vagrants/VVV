<?php
/**
 * WordCamp Talks Sticky Administration.
 *
 * @package WordCamp Talks
 * @subpackage admin/sticky
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Admin_Sticky' ) ) :
/**
 * Sticky Talks Administration class
 *
 * Unlike regular Posts, WordPress doesn't support natively
 * the sticky feature for other post types.
 * @see  https://core.trac.wordpress.org/ticket/12702
 *
 * The goal of this class is to add a custom metabox to allow
 * talks to be sticked to the top of the talks post type archive
 * page (not the front page of the blog)
 * On front end, in talks/functions you'll find the wct_talks_stick_talks()
 * function that is extending the WP_Query in order to prepend the talks sticked to top
 * of the post type archive page.
 *
 * @package WordCamp Talks
 * @subpackage admin/sticky
 *
 * @since 1.0.0
 */
class WordCamp_Talks_Admin_Sticky {

	/** Variables *************************************************************/

	/**
	 * @access  private
	 * @var string The talks post type identifier
	 */
	private $post_type = '';

	/**
	 * The constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->hooks();
	}

	/**
	 * Let's start the class
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		if ( ! is_admin() ) {
			return;
		}

		$wct_admin = wct()->admin;

		if ( empty( $wct_admin->sticky ) ) {
			$wct_admin->sticky = new self;
		}

		return $wct_admin->sticky;
	}

	/**
	 * Setups the post type global
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->post_type = wct_get_post_type();
	}

	/**
	 * Setups the action and filters to hook to
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		/** Actions *******************************************************************/

		// Sticky metabox
		add_action( 'wct_save_metaboxes', array( $this, 'sticky_metabox_save' ), 10, 3 );

		// Remove trashed post from stickies
		add_action( 'wp_trash_post', array( $this, 'unstick_talk' ), 10, 1 );

		/** Filters *******************************************************************/

		// Sticky metabox
		add_filter( 'wct_admin_get_meta_boxes', array( $this, 'sticky_metabox' ),  10, 1 );

		// Adds the sticky states to the talk
		add_filter( 'display_post_states', array( $this, 'talk_states' ), 10, 2 );

		// Filter the WP_List_Table views to include a sticky one.
		add_filter( "wct_admin_edit_talks_views", array( $this, 'talk_views' ), 10, 1 );

		// Add sticky updated messages
		add_filter( 'wct_admin_updated_messages', array( $this, 'updated_messages' ), 10, 1 );

		// Help tabs
		add_filter( 'wct_get_help_tabs', array( $this, 'sticky_help_tabs' ), 10, 1 );
	}

	/**
	 * Adds a sticky metabox to the plugin's metaboxes
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $metaboxes the plugin's metabox list
	 * @return array            the new list
	 */
	public function sticky_metabox( $metaboxes = array() ) {
		$sticky_metabox = array(
			'sticky' => array(
				'id'            => 'wct_sticky_box',
				'title'         => __( 'Sticky', 'wordcamp-talks' ),
				'callback'      => array( 'WordCamp_Talks_Admin_Sticky', 'sticky_do_metabox' ),
				'context'       => 'side',
				'priority'      => 'high'
		) );

		return array_merge( $metaboxes, $sticky_metabox );
	}

	/**
	 * Displays the sticky metabox
	 *
	 * It also checks the status of the talk to eventually
	 * remove the talk from stickies if the status is not
	 * 'publish'
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Post $talk the talk object
	 * @return string HTML output
	 */
	public static function sticky_do_metabox( $talk = null ) {
		$id = $talk->ID;

		if ( wct_talks_admin_no_sticky( $talk ) ) {

			self::unstick_talk( $id );

			esc_html_e( 'This talk cannot be sticky', 'wordcamp-talks' );
		} else {

			$is_sticky = wct_talks_is_sticky( $id );
			?>

			<p>
				<label class="screen-reader-text" for="wct_sticky"><?php esc_html_e( 'Select whether or not to make the talk sticky.', 'wordcamp-talks' ); ?></label>
				<input type="checkbox" name="wct_sticky" id="wct_sticky" value="1" <?php checked( true, $is_sticky ) ;?>/> <strong class="label"><?php esc_html_e( 'Mark as sticky', 'wordcamp-talks' ); ?></strong>
			</p>

			<?php
			wp_nonce_field( 'wct_sticky_metabox_save', 'wct_sticky_metabox' );

			/**
			 * @param  int  $id the talk ID
			 * @param  bool $is_sticky true if the talk is sticky, false otherwise
			 */
			do_action( 'wct_do_sticky_metabox', $id, $is_sticky );
		}
	}

	/**
	 * Saves the sticky preference for the talk
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  int      $id     the talk ID
	 * @param  WP_Post  $talk   the talk object
	 * @param  bool     $update whether it's an update or not
	 * @return int          the talk ID
	 */
	public function sticky_metabox_save( $id = 0, $talk = null, $update = false ) {
		$updated_message = false;

		// Private post or password protected talks cant be sticky
		if ( 'private' == $talk->post_status || ! empty( $talk->post_password ) ) {
			// Eventually add a message
			if ( ! empty( $_POST['wct_sticky'] ) ) {
				wct_set_global( 'feedback', array( 'updated_message' => 14 ) );
			}

			return $id;
		}

		// Nonce check
		if ( ! empty( $_POST['wct_sticky_metabox'] ) && check_admin_referer( 'wct_sticky_metabox_save', 'wct_sticky_metabox' ) ) {

			$sticky_talks = wct_talks_get_stickies();
			$updated_stickies = $sticky_talks;

			// The talk is no more sticky
			if ( empty( $_POST['wct_sticky'] ) && in_array( $id, $sticky_talks ) ) {
				$updated_stickies = array_diff( $updated_stickies, array( $id ) );
				$updated_message = 15;
			}

			// The talk is to mark as sticky
			if ( ! empty( $_POST['wct_sticky'] ) && ! in_array( $id, $sticky_talks ) ) {
				$updated_stickies = array_merge( $updated_stickies, array( $id ) );
				$updated_message = 16;
			}

			if ( $sticky_talks != $updated_stickies ) {
				update_option( 'sticky_talks', $updated_stickies );
			}
		}

		if ( ! empty( $updated_message ) ) {
			wct_set_global( 'feedback', array( 'updated_message' => $updated_message ) );
		}

		return $id;
	}

	/**
	 * Unstick an talk
	 *
	 * If the post status is not publish or if the talk was trashed: unstick!
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  int $id the talk ID
	 */
	public static function unstick_talk( $id = 0 ) {
		if ( empty( $id ) ) {
			return false;
		}

		$stickies = wct_talks_get_stickies();

		if ( ! wct_talks_is_sticky( $id, $stickies ) ) {
			return;
		}

		$stickies = array_diff( $stickies, array( $id ) );

		// Update the sticky talks
		update_option( 'sticky_talks', $stickies );
	}

	/**
	 * Adds sticky updated messages to plugin's updated messages
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $messages list of plugin's Updated messages
	 * @return array            new list
	 */
	public function updated_messages( $messages = array() ) {
		$messages[14] = $messages[1] . '<br/>' . esc_html__( 'Private or password protected talks cannot be marked as sticky', 'wordcamp-talks' );
		$messages[15] = $messages[1] . '<br/>' . esc_html__( 'Talk successfully removed from stickies', 'wordcamp-talks' );
		$messages[16] = $messages[1] . '<br/>' . esc_html__( 'Talk successfully added to stickies', 'wordcamp-talks' );

		return $messages;
	}

	/**
	 * Adds a sticky state after the talk title in WP_List_Table
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $talk_states  the available talk states
	 * @param  WP_Post $talk         the talk object
	 * @return array                 the new talk states
	 */
	public function talk_states( $talk_states = array(), $talk = null ) {
		if ( $talk->post_type != $this->post_type ) {
			return $talk_states;
		}

		if ( wct_talks_is_sticky( $talk->ID ) ) {
			$talk_states['sticky'] = esc_html_x( 'Sticky', 'talk list table row state', 'wordcamp-talks' );
		}

		return $talk_states;
	}

	/**
	 * Add a sticky view to existing talk views (WP_List_Table)
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $views the available talk views
	 * @return array         the new views
	 */
	public function talk_views( $views = array() ) {
		$stickies = wct_talks_get_stickies();
		$count_stickies = count( $stickies );

		if ( ! empty( $stickies ) ) {
			$sticky_url = add_query_arg(
				array(
					'post_type'    => $this->post_type,
					'sticky_talks' => 1,
				),
				admin_url( 'edit.php' )
			);

			$class = '';
			if ( ! empty( $_GET['sticky_talks'] ) ) {
				$class = 'class="current"';
			}

			$sticky_link = '<a href="' . esc_url( $sticky_url ) .'"' . $class . '>' . sprintf(
				_nx( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', $count_stickies, 'admin talks sticky view', 'wordcamp-talks' ),
				number_format_i18n( $count_stickies )
				) . '</a>';

			$sticky_view = array(
				'sticky_talks' => $sticky_link
			);

			foreach ( $views as $key => $view ) {
				// Make sure current class is removed for the other views
				// if viewing stickies
				if ( ! empty( $class ) ) {
					$views[ $key ] = str_replace( $class, '', $view );
				}

				// Make sure the trash view is last
				if ( 'trash' == $key ) {
					$sticky_view[ $key ] = $view;
					unset( $views[ $key ] );
				}
			}

			$views = array_merge( $views, $sticky_view );
		}

		return $views;
	}

	/**
	 * Adds the Sticky help tabs
	 *
	 * @package WordCamp Talks
	 * @subpackage admin/sticky
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $help_tabs the list of help tabs
	 * @return array            the new list of help tabs
	 */
	public function sticky_help_tabs( $help_tabs = array() ) {
		if ( ! empty( $help_tabs['talks']['add_help_tab'] ) ) {
			$talks_help_tabs = wp_list_pluck( $help_tabs['talks']['add_help_tab'], 'id' );
			$talks_overview = array_search( 'talks-overview', $talks_help_tabs );

			if ( isset( $help_tabs['talks']['add_help_tab'][ $talks_overview ]['content'] ) ) {
				$help_tabs['talks']['add_help_tab'][ $talks_overview ]['content'][] = esc_html__( 'The Sticky metabox allows you to stick a published talk (not password protected) to the top of the front talks archive first page.', 'wordcamp-talks' );
			}
		}

		return $help_tabs;
	}
}

endif;

add_action( 'wct_loaded', array( 'WordCamp_Talks_Admin_Sticky', 'start' ), 7 );
