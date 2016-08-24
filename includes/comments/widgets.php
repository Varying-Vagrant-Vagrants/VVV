<?php
/**
 * WordCamp Talks Comments Widgets.
 *
 * @package WordCamp Talks
 * @subpackage comments/widgets
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talk_Recent_Comments' ) ) :
/**
 * Recent comment about talks Widget
 *
 * @package WordCamp Talks
 * @subpackage comments/widgets
 *
 * @since 1.0.0
 */
 class WordCamp_Talk_Recent_Comments extends WP_Widget_Recent_Comments {

 	/**
	 * Constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/widgets
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_talks_recent_comments', 'description' => __( 'Latest comments about talks', 'wordcamp-talks' ) );
		WP_Widget::__construct( 'talk-recent-comments', $name = __( 'WordCamp Talks latest comments', 'wordcamp-talks' ), $widget_ops );

		$this->alt_option_name = 'widget_talks_recent_comments';

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'recent_comments_style' ) );
		}
	}

	/**
	 * Register the widget
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/widgets
	 *
	 * @since 1.0.0
	 */
	public static function register_widget() {
		register_widget( 'WordCamp_Talk_Recent_Comments' );
	}

	/**
	 * Override comments query args to only onclude comments about talks
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/widgets
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $comment_args
	 * @return array  the comments query args to display comments about talks
	 */
	public function override_comment_args( $comment_args = array() ) {
		// It's that simple !!
		$comment_args['post_type'] = wct_get_post_type();

		// Now return these args
		return $comment_args;
	}

	/**
	 * @package WordCamp Talks
	 * @subpackage comments/widgets
	 *
	 * @since 1.0.0
	 * 
	 * @param  array $args
	 * @param  array $instance
	 */
	public function widget( $args, $instance ) {
		/**
		 * Add filter so that post type used is talks but before the dummy var
		 * @see WordCamp_Talks_Comments::comments_widget_dummy_var()
		 */
		add_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );

		parent::widget( $args, $instance );

		/**
		 * Once done we need to remove the filter
		 */
		remove_filter( 'widget_comments_args', array( $this, 'override_comment_args' ), 5, 1 );
	}

	/**
	 * Update the preferences for the widget
	 *
	 * @package WordCamp Talks
	 * @subpackage comments/widgets
	 *
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[ 'widget_talks_recent_comments'] ) ) {
			delete_option( 'widget_talks_recent_comments' );
		}

		return $instance;
	}
}

endif;
