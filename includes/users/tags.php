<?php
/**
 * WordCamp Talks Users tags.
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Outputs user's profile nav
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */
function wct_users_the_user_nav() {
	echo wct_users_get_user_nav();
}

	/**
	 * Gets user's profile nav
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_nav() {
		// Get displayed user id.
		$user_id = wct_users_displayed_user_id();

		// If not set, we're not on a user's profile.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get username.
		$username = wct_users_get_displayed_user_username();

		// Get nav items for the user displayed.
		$nav_items = wct_users_get_profile_nav_items( $user_id, $username );

		if ( empty( $nav_items ) ) {
			return;
		}

		$user_nav = '<ul class="user-nav">';

		foreach ( $nav_items as $nav_item ) {
			$class =  ! empty( $nav_item['current'] ) ? ' class="current"' : '';
			$user_nav .= '<li' . $class .'>';
			$user_nav .= '<a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $nav_item['title'] ) . '">' . esc_html( $nav_item['title'] ) . '</a>';
			$user_nav .= '</li>';
		}

		$user_nav .= '</ul>';

		/**
		 * Filter the user nav output
		 *
		 * @param string $user_nav      User nav output
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wct_users_get_user_nav', $user_nav, $user_id, $username );
	}

/**
 * Outputs user's embed profile stats
 *
 * @since 1.0.0
 */
function wct_users_embed_user_stats() {
	echo wct_users_get_embed_user_stats();
}

	/**
	 * Gets user's embed profile stats
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML Output
	 */
	function wct_users_get_embed_user_stats() {
		// Get displayed user id.
		$user_id = wct_users_displayed_user_id();

		// If not set, we're not on a user's profile.
		if ( empty( $user_id ) || ! current_user_can( 'view_other_profiles', $user_id ) ) {
			return;
		}

		// Get username.
		$username = wct_users_get_displayed_user_username();

		/**
		 * Get nav items for the user displayed to build the user stats.
		 *
		 * @since 1.0.0
		 *
		 * @param array $value the nav items that will be used for the embed stats
		 */
		$nav_items = apply_filters( 'wct_users_get_embed_user_stats', wct_users_get_profile_nav_items( $user_id, $username, true ) );

		if ( empty( $nav_items ) ) {
			return;

		// Remove unused nav items on embed profile.
		} else {
			$nav_items = array_diff_key( $nav_items, array( 'profile' => false, 'to_rate' => false ) );
		}

		$user_stats = '<ul class="user-stats">';

		foreach ( $nav_items as $key_nav => $nav_item ) {
			$user_stats .= '<li class=' . sanitize_html_class( $key_nav ) . '>';
			$stat_title  = sprintf( _x( '%s talks', 'embed profile type of stat', 'wordcamp-talks' ), $nav_item['title'] );
			$dashicon    = 'wordcamp-talks-' . esc_attr( $key_nav );

			if ( 'comments' === $key_nav ) {
				$dashicon = 'dashicons-admin-comments';
			}

			$user_stats .= '<div class="stat-label"><a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $stat_title ) . '"><span class="dashicons ' . $dashicon . '"></span><span class="screen-reader-text">' . esc_html( $stat_title ) . '</span></a></div>';
			$user_stats .= '<div class="stat-value"><a href="' . esc_url( $nav_item['url'] ) . '" title="' . esc_attr( $stat_title ) . '">' . wct_users_get_stat_for( $key_nav, $user_id ) . '</a></span>';
			$user_stats .= '</li>';
		}

		$user_stats .= '</ul>';

		/**
		 * Filter the embed stats output
		 *
		 * @since  2.3.0
		 *
		 * @param string $user_stats    User stats output
		 * @param int    $user_id       the user ID
		 * @param string $user_nicename the username
		 */
		return apply_filters( 'wct_users_get_embed_user_stats_output', $user_stats, $user_id, $username );
	}

/**
 * Outputs user's profile avatar
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 */
function wct_users_the_user_profile_avatar() {
	echo wct_users_get_user_profile_avatar();
}

	/**
	 * Gets user's profile avatar
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_profile_avatar() {
		return apply_filters( 'wct_users_get_user_profile_avatar', get_avatar( wct_users_displayed_user_id(), '150' ) );
	}

/**
 * Outputs user's profile display name
 *
 * @since 1.0.0
 */
function wct_users_user_profile_display_name() {
	echo wct_users_get_user_profile_display_name();
}

	/**
	 * Gets user's profile display name
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_user_profile_display_name() {
		return esc_html( apply_filters( 'wct_users_get_user_profile_display_name', wct_users_get_displayed_user_displayname() ) );
	}


/**
 * Outputs user's embed profile avatar
 *
 * @since 1.0.0
 */
function wct_users_embed_user_profile_avatar() {
	echo wct_users_get_embed_user_profile_avatar();
}

	/**
	 * Gets user's embed profile avatar
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_embed_user_profile_avatar() {
		return apply_filters( 'wct_users_get_embed_user_profile_avatar', get_avatar( wct_users_displayed_user_id(), '50' ) );
	}

/**
 * Outputs user's embed profile display name
 *
 * @since 1.0.0
 */
function wct_users_embed_user_profile_display_name() {
	echo wct_users_get_embed_user_profile_display_name();
}

	/**
	 * Gets user's embed profile display name
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_embed_user_profile_display_name() {
		return esc_html( apply_filters( 'wct_users_get_embed_user_profile_display_name', wct_users_get_displayed_user_displayname() ) );
	}

/**
 * Outputs user's embed profile link
 *
 * @since 1.0.0
 */
function wct_users_embed_user_profile_link() {
	echo esc_url( wct_users_get_embed_user_profile_link() );
}

	/**
	 * Gets user's embed profile link
	 *
	 * @since 1.0.0
	 */
	function wct_users_get_embed_user_profile_link() {
		$link = wct_users_get_user_profile_url( wct_users_displayed_user_id(), wct_users_get_displayed_user_username() );
		return apply_filters( 'wct_users_get_embed_user_profile_display_name', $link );
	}

/**
 * Does the user's embed profile has a description ?
 *
 * @since 1.0.0
 *
 * @return bool True if the embed profile has a description, False otherwise.
 */
function wct_users_has_embed_description() {
	return (bool) wct_users_get_displayed_user_description();
}

/**
 * Outputs user's embed profile description
 *
 * @since 1.0.0
 */
function wct_users_embed_user_profile_description() {
	echo wct_users_get_embed_user_profile_description();
}

	/**
	 * Get the user's embed profile description
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML Output
	 */
	function wct_users_get_embed_user_profile_description() {
		$description = wct_users_get_displayed_user_description();

		if ( ! empty( $description ) ) {
			$more = ' [&hellip;]';

			if ( current_user_can( 'view_other_profiles', wct_users_displayed_user_id() ) ) {
				$more = ' &hellip; ' . sprintf( '<a href="%1$s" class="wp-embed-more" target="_top">%2$s</a>',
					esc_url( wct_users_get_embed_user_profile_link() ),
					sprintf( esc_html__( "View %s full profile", 'wordcamp-talks' ), '<span class="screen-reader-text">' . sprintf( _x( '%s&#39;s', 'Screen reader text for embed user display name for the more link', 'wordcamp-talks' ), wct_users_get_embed_user_profile_display_name() ) . '</span>' )
				);
			}

			$description = wct_create_excerpt( $description, 20, $more, true );
		}

		return apply_filters( 'wct_users_get_embed_user_profile_description', $description );
	}

/**
 * Append displayed user's rating in talks header when viewing his rates profile
 *
 * @package WordCamp Talks
 * @subpackage users/tags
 *
 * @since 1.0.0
 *
 * @param int $id      the talk ID
 * @param int $user_id the user ID
 */
function wct_users_the_user_talk_rating( $id = 0, $user_id = 0 ) {
	echo wct_users_get_user_talk_rating( $id, $user_id );
}

	/**
	 * Gets displayed user's rating for a given talk
	 *
	 * @package WordCamp Talks
	 * @subpackage users/tags
	 *
	 * @since 1.0.0
	 *
	 * @param int $id      the talk ID
	 * @param int $user_id the user ID
	 */
	function wct_users_get_user_talk_rating( $id = 0, $user_id = 0 ) {
		if ( ! wct_is_user_profile_rates() ) {
			return;
		}

		if ( empty( $id ) ) {
			$query_loop = wct_get_global( 'query_loop' );

			if ( ! empty( $query_loop->talk->ID ) ) {
				$id = $query_loop->talk->ID;
			}
		}

		if ( empty( $user_id ) ) {
			$user_id = wct_users_displayed_user_id();
		}

		if ( empty( $user_id ) || empty( $id ) ) {
			return;
		}

		$user_rating = wct_count_ratings( $id, $user_id );

		if ( empty( $user_rating ) || is_array( $user_rating ) ) {
			return false;
		}

		$username = wct_users_get_displayed_user_username();

		$output = '<a class="user-rating-link" href="' . esc_url( wct_users_get_user_profile_url( $user_id, $username ) ) . '" title="' . esc_attr( $username ) . '">';
		$output .= get_avatar( $user_id, 20 ) . sprintf( _n( 'rated 1 star', 'rated %s stars', $user_rating, 'wordcamp-talks' ), $user_rating ) . '</a>';

		/**
		 * Filter the user talk rating output
		 *
		 * @param string $output        the rating
		 * @param int    $id            the talk ID
		 * @param int    $user_id       the user ID
		 */
		return apply_filters( 'wct_users_get_user_talk_rating', $output, $id, $user_id );
	}

function wct_users_the_signup_fields() {
	echo wct_users_get_signup_fields();
}

	function wct_users_get_signup_fields() {
		$output = '';

		foreach ( (array) wct_user_get_fields() as $key => $label ) {
			// reset
			$sanitized = array(
				'key'   => sanitize_key( $key ),
				'label' => esc_html( $label ),
				'value' => '',
			);

			if ( ! empty( $_POST['wct_signup'][ $sanitized['key'] ] ) ) {
				$sanitized['value'] = apply_filters( "wct_users_get_signup_field_{$key}", $_POST['wct_signup'][ $sanitized['key'] ] );
			}

			$required = apply_filters( 'wct_users_is_signup_field_required', false, $key );
			$required_output = false;

			if ( ! empty( $required ) || in_array( $key, array( 'user_login', 'user_email' ) ) ) {
				$required_output = '<span class="required">*</span>';
			}

			$output .= '<label for="_wct_signup_' . esc_attr( $sanitized['key'] ) . '">' . esc_html( $sanitized['label'] ) . ' ' . $required_output . '</label>';

			if ( 'description' !== $sanitized['key'] ) {
				$output .= '<input type="text" id="_wct_signup_' . esc_attr( $sanitized['key'] ) . '" name="wct_signup[' . esc_attr( $sanitized['key'] ) . ']" value="' . esc_attr( $sanitized['value'] ) . '"/>';
			} else {
				$output .= '<textarea id="_wct_signup_' . esc_attr( $sanitized['key'] ) . '" name="wct_signup[' . esc_attr( $sanitized['key'] ) . ']">' . esc_textarea( $sanitized['value'] ) . '</textarea>';
			}

			$output .= apply_filters( 'wct_users_after_signup_field', '', $sanitized );
		}

		return apply_filters( 'wct_users_get_signup_fields', $output );
	}

function wct_users_the_signup_submit() {
	$wct = wct();

	wp_nonce_field( 'wct_signup' );

	do_action( 'wct_users_the_signup_submit' ); ?>

	<input type="reset" value="<?php esc_attr_e( 'Reset', 'wordcamp-talks' ) ;?>"/>
	<input type="submit" value="<?php esc_attr_e( 'Sign-up', 'wordcamp-talks' ) ;?>" name="wct_signup[signup]"/>
	<?php
}

/**
 * Displays the Sharing button inside a user's profile
 *
 * So that WordPress can build the sharing dialog and embed codes
 * we need to temporarly set the Utility page as the current post
 * for the displayed user's profile.
 * Then, we intercept the permalink and the title of the utility page
 * using filters and we override them with the ones of the displayed
 * user profile. Tada!
 *
 * @since 1.0.0
 *
 * @global WP_Post $post
 * @return bool False in case the Embed profile is disabled
 */
function wct_users_embed_content_meta() {
	global $post;

	$reset_post = get_post( wct_is_embed_profile() );

	if ( ( ! empty( $post->ID ) && ! empty( $reset_post->ID ) && (int) $post->ID === (int) $reset_post->ID ) || empty( $reset_post->ID ) ) {
		return false;
	}

	if ( ! empty( $reset_post->ID ) ) {
		// Globalize the post
		wct_set_global( 'embed_reset_post', $post );

		// Reset it to our utility page
		$post = $reset_post;

		// Globalize the user
		wct_set_global( 'embed_user_data', wct_users_get_user_data( 'id', wct_users_displayed_user_id() ) );

		// Make sure the post link will be the one of the user's displayed profile
		add_filter( 'post_type_link', 'wct_users_oembed_link',  10, 2 );
		add_filter( 'the_title',      'wct_users_oembed_title', 10, 2 );

		add_action( 'embed_footer', 'wct_users_embed_content_reset_post', 40 );

		// Add WordPress Sharing Button
		print_embed_sharing_button();
	}
}

/**
 * Make sure to play nice with WordPress by resetting the post global the way it was
 * before overriding it with our utility page.
 *
 * @since 1.0.0
 *
 * @global WP_Post $post
 */
function wct_users_embed_content_reset_post() {
	global $post;

	// Reset post the way it was.
	$post = wct_get_global( 'embed_reset_post' );

	// Reset the embed user & post
	wct_set_global( 'embed_user_data', null );
	wct_set_global( 'embed_reset_post', null );

	// Stop filtering...
	remove_filter( 'post_type_link', 'wct_users_oembed_link',  10, 2 );
	remove_filter( 'the_title',      'wct_users_oembed_title', 10, 2 );
}

/**
 * Edit/Share profile buttons.
 *
 * Displays the edit profile URL for Admins/self profiles.
 * Displays the sharing dialog box on user's profile so
 * that people can easily get the embed code.
 *
 * @since 1.0.0
 */
function wct_users_buttons() {
	// Edit button for self profiles.
	if ( wct_is_current_user_profile() || is_super_admin() ) {
		$url = wct_users_get_user_profile_edit_url();

		if ( $url ) {
			?>
			<div class="wct-edit">
				<a href="<?php echo esc_url( $url ); ?>" class="button" aria-label="<?php esc_attr_e( 'Edit profile', 'wordcamp-talks' ); ?>">
					<span class="dashicons dashicons-edit"></span>
				</a>
			</div>
			<?php
		}
	}

	// Embed profile button.
	if ( wct_is_embed_profile() ) {
		wct_users_embed_content_meta();

		// Print the sharing dialog
		print_embed_sharing_dialog();

		// Reset the post
		wct_users_embed_content_reset_post();
	}
}

/**
 * Set the public profile fields for the user's info template on front-end
 *
 * @since  1.0.0
 *
 * @return array The list of field keys.
 */
function wct_users_public_profile_infos() {
	/**
	 * Filter here if you need to edit the public profile infos list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $value An associative array keyed by field IDs containing field labels.
	 */
	$public_labels = (array) apply_filters( 'wct_users_public_profile_infos', array_merge( array(
		'user_description' => __( 'Biographical Info', 'wordcamp-talks' ),
		'user_url'         => __( 'Website', 'wordcamp-talks' ),
	), wct_users_contactmethods( array(), 'public' ) ) );

	wct_set_global( 'public_profile_labels', $public_labels );

	return array_keys( $public_labels );
}

/**
 * Check if a field's key has a corresponding value for the user.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 * @return bool         True if the user has filled the field. False otherwise.
 */
function wct_users_public_profile_has_info(  $info = '' ) {
	if ( empty( $info ) ) {
		return false;
	}

	return ! empty( wct()->displayed_user->{$info} );
}

/**
 * While Iterating fields, count the empty ones.
 *
 * @since  1.0.0
 */
function wct_users_public_empty_info() {
	$empty_info = (int) wct_get_global( 'empty_info' );

	wct_set_global( 'empty_info', $empty_info + 1 );
}

/**
 * Displays the field label.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 */
function wct_users_public_profile_label( $info = '' ) {
	if ( empty( $info ) ) {
		return;
	}

	$labels = wct_get_global( 'public_profile_labels' );

	if ( ! isset( $labels[ $info ] ) ) {
		return;
	}

	echo esc_html( apply_filters( 'wct_users_public_label', $labels[ $info ], $info ) );
}

/**
 * Displays the field value.
 *
 * @since  1.0.0
 *
 * @param  string $info The field key.
 */
function wct_users_public_profile_value( $info = '' ) {
	if ( empty( $info ) ) {
		return;
	}

	echo apply_filters( 'wct_users_public_value', wct()->displayed_user->{$info}, $info );
}

/**
 * Check if no fields were filled by the user.
 *
 * @since  1.0.0
 *
 * @return bool True if the user didn't filled any fields. False otherwise.
 */
function wct_users_public_empty_profile() {
	$empty_info = (int) wct_get_global( 'empty_info' );
	$labels     = wct_get_global( 'public_profile_labels' );

	if ( $empty_info && $empty_info === count( $labels ) ) {
		$message = __( 'This user has not filled any public profile informations.', 'wordcamp-talks' );

		if ( wct_is_current_user_profile() ) {
			$message = __( 'You have not filled any public profile informations. You can edit your profile to add some.', 'wordcamp-talks' );
		}

		wct_set_global( 'feedback', array(
			'type'    => 'info',
			'content' => $message,
		) );

		return true;
	}

	return false;
}
