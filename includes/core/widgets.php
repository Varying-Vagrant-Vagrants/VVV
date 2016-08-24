<?php
/**
 * WordCamp Talks Widgets.
 *
 * Core Widgets
 *
 * @package WordCamp Talks
 * @subpackage core/widgets
 */


// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks_Navig' ) ) :
/**
 * Navigation Menu widget class
 *
 * @package WordCamp Talks
 * @subpackage core/widgets
 *
 * @since 1.0.0
 */
 class WordCamp_Talks_Navig extends WP_Widget {

 	/**
	 * Constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'Add WordCamp Talks&#39;s nav to your sidebar.', 'wordcamp-talks' ) );
		parent::__construct( false, $name = __( 'WordCamp Talks Nav', 'wordcamp-talks' ), $widget_ops );

		// We need to wait for the talks post type to be registered
		add_action( 'wct_init', array( $this, 'set_available_nav_items' ) );
	}

	/**
	 * Register the widget
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 *
	 * @since 1.0.0
	 */
	public static function register_widget() {
		register_widget( 'WordCamp_Talks_Navig' );
	}

	/**
	 * Setup available nav items
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 */
	public function set_available_nav_items() {
		// construct nav
		$this->nav_items_available = array(
			'talk_archive' => array(
				'url'  => wct_get_root_url(),
				'name' => wct_archive_title()
			),
			'addnew'       => array(
				'url'  => wct_get_form_url(),
				'name' => __( 'New talk', 'wordcamp-talks' )
			)
		);

		if ( is_user_logged_in() ) {
			$this->nav_items_available['current_user_profile'] = array(
				'url'  => wct_users_get_logged_in_profile_url(),
				'name' => __( 'My profile', 'wordcamp-talks' )
			);
		}

		/**
		 * @param array the available nav items
		 * @param string the widget's id base
		 */
		$this->nav_items_available = apply_filters( 'wct_widget_nav_items', $this->nav_items_available, $this->id_base );
	}

	/**
	 * Display the widget on front end
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 *
	 * @since 1.0.0
	 */
	public function widget( $args = array(), $instance = array() ) {
		// Default to all items
		$nav_items = array( 'talk_archive', 'addnew', 'current_user_profile' );

		if ( ! empty( $instance['nav_items'] ) ) {
			$nav_items = (array) $instance['nav_items'];
		}

		// No nav items to show !? Stop!
		if ( empty( $nav_items ) ) {
			return;
		}

		// Get selected Nav items
		$nav_items = array_intersect_key( $this->nav_items_available, array_flip( $nav_items ) );

		// Default to nothing
		$title = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		}

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Display the Nav
		?>
		<div class="menu-wc-talks-container">

			<ul class="menu">

				<?php foreach ( $nav_items as $key_nav => $nav_item ) :
					$current = '';

					if ( function_exists( 'wct_is_' . $key_nav ) &&  call_user_func( 'wct_is_' . $key_nav ) ) {
						$current = ' current-menu-item';
					}
				?>

				<li class="menu-item menu-item-type-post_type<?php echo $current;?>">

					<a href="<?php echo esc_url( $nav_item['url'] );?>" title="<?php echo esc_attr( $nav_item['name'] );?>"><?php echo esc_html( $nav_item['name'] ); ?></a>

				</li>

				<?php endforeach; ?>

			</ul>

		</div>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Update widget preferences
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 *
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( wp_unslash( $new_instance['title'] ) );
		}

		$instance['nav_items'] = (array) $new_instance['nav_items'];

		return $instance;
	}

	/**
	 * Display the form in Widgets Administration
	 *
	 * @package WordCamp Talks
	 * @subpackage core/widgets
	 *
	 * @since 1.0.0
	 */
	public function form( $instance ) {
		// Default to nothing
		$title = '';

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		// Default to all nav items
		$nav_items = array( 'talk_archive', 'addnew', 'current_user_profile' );

		if ( ! empty( $instance['nav_items'] ) && is_array( $instance['nav_items'] ) ) {
			$nav_items = $instance['nav_items'];
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'wordcamp-talks' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>

			<?php foreach ( $this->nav_items_available as $key_item => $item ) : ?>

				<input class="checkbox" type="checkbox" <?php checked( in_array( $key_item, $nav_items), true) ?> id="<?php echo $this->get_field_id( 'nav_items' ) . '-' . $key_item; ?>" name="<?php echo $this->get_field_name( 'nav_items' ); ?>[]" value="<?php echo esc_attr( $key_item );?>" />
				<label for="<?php echo $this->get_field_id( 'nav_items' ) . '-' . $key_item; ?>"><?php echo esc_html( $item['name'] ); ?></label><br />

			<?php endforeach; ?>

		</p>

		<?php
	}
}

endif;
