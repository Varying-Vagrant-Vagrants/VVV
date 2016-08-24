<?php
/**
 * WordCamp Talks Users Widgets.
 *
 * Collection of Users Widgets, for now
 * only one is available :)
 *
 * @package WordCamp Talks
 * @subpackage users/widgets
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talk_Widget_Top_Contributors' ) ) :
/**
 * List the top contributors
 *
 * @package WordCamp Talks
 * @subpackage users/widgets
 *
 * @since 1.0.0
 */
class WordCamp_Talk_Widget_Top_Contributors extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage users/widgets
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'List the top talk contributors', 'wordcamp-talks' ) );
		parent::__construct( false, $name = __( 'WordCamp Talks Tops', 'wordcamp-talks' ), $widget_ops );

		if ( is_active_widget( false, false, $this->id_base ) && ! is_admin() && ! is_network_admin() ) {
			add_action( 'wct_enqueue_scripts', 'wct_enqueue_style' );
		}
	}

	/**
	 * Register the widget
	 *
	 * @package WordCamp Talks
	 * @subpackage users/widgets
	 *
	 * @since 1.0.0
	 */
	public static function register_widget() {
		register_widget( 'WordCamp_Talk_Widget_Top_Contributors' );
	}

	/**
	 * Display the widget on front end
	 *
	 * @package WordCamp Talks
	 * @subpackage users/widgets
	 *
	 * @since 1.0.0
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Default is 5
		$number = 5;

		// No nav items to show !? Stop!
		if ( ! empty( $instance['number'] ) ) {
			$number = (int) $instance['number'];
		}

		// Ten max.
		if ( $number > 10 ) {
			$number = 10;
		}

		// Default title is nothing
		$title = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		}

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Top contributors.
		$tops = wct_users_talks_count_by_user( $number );


		// Display the Widget output
		if ( ! empty( $tops ) ) : ?>

		<ul class="wordcamp-talks-tops">

			<?php foreach ( $tops as $top_user ) : ?>

				<li>
					<a href="<?php echo esc_url( wct_users_get_user_profile_url( $top_user->post_author, $top_user->user_nicename ) );?>" title="<?php echo esc_attr( $top_user->user_nicename ); ?>">
						<span class="avatar">
							<?php echo get_avatar( $top_user->post_author, 40 ) ;?>
						</span>
						<span class="title">
							<?php echo esc_html( $top_user->user_nicename ); ?>
						</span>
						<span class="count">
							(<?php echo esc_html( $top_user->count_talks );?>)
						</span>
					</a>
				</li>

		<?php endforeach ; ?>

		</ul>
		<?php
		endif;

		echo $args['after_widget'];
	}

	/**
	 * Update widget preferences
	 *
	 * @package WordCamp Talks
	 * @subpackage users/widgets
	 *
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( wp_unslash( $new_instance['title'] ) );
		}

		$max = (int) $new_instance['number'];

		if ( $max > 10 ) {
			$max = 10;
		}

		$instance['number'] = $max;

		return $instance;
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @package WordCamp Talks
	 * @subpackage users/widgets
	 *
	 * @since 1.0.0
	 */
	public function form( $instance = array() ) {
		// Default to nothing
		$title = '';

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		// Number default to 5
		$number = 5;

		if ( ! empty( $instance['number'] ) ) {
			$number = absint( $instance['number'] );
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wordcamp-talks' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of users to show:', 'wordcamp-talks' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>


		<?php
	}
}

endif;
