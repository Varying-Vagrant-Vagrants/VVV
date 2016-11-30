<?php
/**
 * WordCamp Talks Settings.
 *
 * Administration / Settings
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The settings sections
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return array the setting sections
 */
function wct_get_settings_sections() {
	$settings_sections =  array(
		'wc_talks_settings_core' => array(
			'title'    => __( 'Main Settings', 'wordcamp-talks' ),
			'callback' => 'wct_settings_core_section_callback',
			'page'     => 'wc_talks',
		),
	);

	if ( wct_is_pretty_links() ) {
		$settings_sections['wc_talks_settings_rewrite'] = array(
			'title'    => __( 'Pretty Links', 'wordcamp-talks' ),
			'callback' => 'wct_settings_rewrite_section_callback',
			'page'     => 'wc_talks',
		);
	}

	if ( is_multisite() ) {
		$settings_sections['wc_talks_settings_multisite'] = array(
			'title'    => __( 'Network users settings', 'wordcamp-talks' ),
			'callback' => 'wct_settings_multisite_section_callback',
			'page'     => 'wc_talks',
		);
	}

	/**
	 * @param array $settings_sections the setting sections
	 */
	return (array) apply_filters( 'wct_get_settings_sections', $settings_sections );
}

/**
 * The different fields for setting sections
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return array the settings fields
 */
function wct_get_settings_fields() {
	$setting_fields = array(
		/** Core Section **************************************************************/

		'wc_talks_settings_core' => array(

			// Post Type Archive page title
			'_wc_talks_archive_title' => array(
				'title'             => __( 'WordCamp Talks archive page', 'wordcamp-talks' ),
				'callback'          => 'wct_archive_title_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Closing date for the call for speakers.
			'_wc_talks_closing_date' => array(
				'title'             => __( 'Closing date.', 'wordcamp-talks' ),
				'callback'          => 'wct_closing_date_setting_callback',
				'sanitize_callback' => 'wct_sanitize_closing_date',
				'args'              => array()
			),

			// Default post type status
			'_wc_talks_submit_status' => array(
				'title'             => __( 'New talks status', 'wordcamp-talks' ),
				'callback'          => 'wct_submit_status_setting_callback',
				'sanitize_callback' => 'wct_sanitize_status',
				'args'              => array()
			),

			// Can we add images to content ?
			'_wc_talks_editor_image' => array(
				'title'             => __( 'Images', 'wordcamp-talks' ),
				'callback'          => 'wct_editor_image_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Can we add featured images to the talk ?
			'_wc_talks_featured_images' => array(
				'title'             => __( 'Featured images', 'wordcamp-talks' ),
				'callback'          => 'wct_editor_featured_images_setting_callback',
				'sanitize_callback' => 'wct_editor_featured_images_sanitize',
				'args'              => array()
			),

			// Can we add links to content ?
			'_wc_talks_editor_link' => array(
				'title'             => __( 'Links', 'wordcamp-talks' ),
				'callback'          => 'wct_editor_link_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Is there a specific message to show if Pending is default status ?
			'_wc_talks_moderation_message' => array(
				'title'             => __( 'Moderation message', 'wordcamp-talks' ),
				'callback'          => 'wct_moderation_message_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Is there a specific message to show to not logged in users ?
			'_wc_talks_login_message' => array(
				'title'             => __( 'Not logged in message', 'wordcamp-talks' ),
				'callback'          => 'wct_login_message_setting_callback',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Customize the hint list
			'_wc_talks_hint_list' => array(
				'title'             => __( 'Rating stars hover captions', 'wordcamp-talks' ),
				'callback'          => 'wct_hint_list_setting_callback',
				'sanitize_callback' => 'wct_sanitize_list',
				'args'              => array()
			),

			// Are user's talks to rate profile area enabled ?
			'_wc_talks_to_rate_disabled' => array(
				'title'             => __( 'Disable the &quot;To rate&quot; tab for the user\'s profile', 'wordcamp-talks' ),
				'callback'          => 'wct_to_rate_profile_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Disable stickies ?
			'_wc_talks_sticky_talks' => array(
				'title'             => __( 'Sticky talks', 'wordcamp-talks' ),
				'callback'          => 'wct_sticky_talks_setting_callback',
				'sanitize_callback' => 'wct_sticky_sanitize',
				'args'              => array()
			),

			// Disable comments disjoin ?
			'_wc_talks_disjoin_comments' => array(
				'title'             => __( 'Talk comments', 'wordcamp-talks' ),
				'callback'          => 'wct_disjoin_comments_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Disable comments in talks post type
			'_wc_talks_allow_comments' => array(
				'title'             => __( 'Comments', 'wordcamp-talks' ),
				'callback'          => 'wct_allow_comments_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Private fields (not shown on front-end)
			'_wc_talks_private_fields_list' => array(
				'title'             => __( 'Private user profile fields', 'wordcamp-talks' ),
				'callback'          => 'wct_fields_list_setting_callback',
				'sanitize_callback' => 'wct_sanitize_user_fields_list',
				'args'              => array( 'type' => 'private' )
			),

			// Public fields (shown on front-end)
			'_wc_talks_public_fields_list' => array(
				'title'             => __( 'Public user profile fields', 'wordcamp-talks' ),
				'callback'          => 'wct_fields_list_setting_callback',
				'sanitize_callback' => 'wct_sanitize_user_fields_list',
				'args'              => array( 'type' => 'public' )
			),

			// Signup fields (shown into the signup form)
			'_wc_talks_signup_fields' => array(
				'title'             => __( 'Fields to add to the signup form.', 'wordcamp-talks' ),
				'callback'          => 'wct_signup_fields_setting_callback',
				'sanitize_callback' => 'wct_sanitize_list',
				'args'              => array()
			),

			// Signup fields (shown into the signup form)
			'_wc_talks_autolog_enabled' => array(
				'title'             => __( 'Signups Autolog', 'wordcamp-talks' ),
				'callback'          => 'wct_autolog_signups_fields_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),

			// Are users profiles embeddable ?
			'_wc_talks_embed_profile' => array(
				'title'             => __( 'Embed Profile', 'wordcamp-talks' ),
				'callback'          => 'wct_embed_profile_setting_callback',
				'sanitize_callback' => 'wct_sanitize_embed_profile',
				'args'              => array()
			),
		)
	);

	if ( wct_is_pretty_links() ) {
		/** Rewrite Section ***********************************************************/
		$setting_fields['wc_talks_settings_rewrite'] = array(

			// Root slug
			'_wc_talks_root_slug' => array(
				'title'             => __( 'WordCamp Talks root slug', 'wordcamp-talks' ),
				'callback'          => 'wct_root_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Single talk slug
			'_wc_talks_talk_slug' => array(
				'title'             => __( 'Single talk slug', 'wordcamp-talks' ),
				'callback'          => 'wct_talk_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Talk Categories slug
			'_wc_talks_category_slug' => array(
				'title'             => __( 'Category slug', 'wordcamp-talks' ),
				'callback'          => 'wct_category_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Talk Tags slug
			'_wc_talks_tag_slug' => array(
				'title'             => __( 'Tag slug', 'wordcamp-talks' ),
				'callback'          => 'wct_tag_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// User slug
			'_wc_talks_user_slug' => array(
				'title'             => __( 'User slug', 'wordcamp-talks' ),
				'callback'          => 'wct_user_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// User comments slug
			'_wc_talks_user_comments_slug' => array(
				'title'             => __( 'User comments slug', 'wordcamp-talks' ),
				'callback'          => 'wct_user_comments_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Comments page slug
			'_wc_talks_cpage_slug' => array(
				'title'             => __( 'User comments paging slug', 'wordcamp-talks' ),
				'callback'          => 'wct_cpage_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_comments_page_slug',
				'args'              => array()
			),

			// User rates slug
			'_wc_talks_user_rates_slug' => array(
				'title'             => __( 'User ratings slug', 'wordcamp-talks' ),
				'callback'          => 'wct_user_rates_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// User rates slug
			'_wc_talks_user_to_rate_slug' => array(
				'title'             => __( 'User &quot;to rate&quot; slug', 'wordcamp-talks' ),
				'callback'          => 'wct_user_to_rate_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Signup slug
			'_wc_talks_signup_slug' => array(
				'title'             => __( 'Sign-up slug', 'wordcamp-talks' ),
				'callback'          => 'wct_signup_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Action slug (so far 1 action is available > add )
			'_wc_talks_action_slug' => array(
				'title'             => __( 'Action slug', 'wordcamp-talks' ),
				'callback'          => 'wct_action_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Add new slug
			'_wc_talks_addnew_slug' => array(
				'title'             => __( 'New form slug', 'wordcamp-talks' ),
				'callback'          => 'wct_addnew_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),

			// Edit slug
			'_wc_talks_edit_slug' => array(
				'title'             => __( 'Edit form slug', 'wordcamp-talks' ),
				'callback'          => 'wct_edit_slug_setting_callback',
				'sanitize_callback' => 'wct_sanitize_slug',
				'args'              => array()
			),
		);
	}

	/**
	 * Disable some settings if ratings are disabled.
	 */
	if ( wct_is_rating_disabled() ) {
		unset(
			$setting_fields['wc_talks_settings_core']['_wc_talks_hint_list'],
			$setting_fields['wc_talks_settings_core']['_wc_talks_to_rate_disabled'],
			$setting_fields['wc_talks_settings_rewrite']['_wc_talks_user_rates_slug'],
			$setting_fields['wc_talks_settings_rewrite']['_wc_talks_user_to_rate_slug']
		);
	} elseif ( wct_is_user_to_rate_disabled( 0, false ) ) {
		unset( $setting_fields['wc_talks_settings_rewrite']['_wc_talks_user_to_rate_slug'] );
	}

	if ( ! wct_is_signup_allowed_for_current_blog() ) {
		unset(
			$setting_fields['wc_talks_settings_core']['_wc_talks_signup_fields'],
			$setting_fields['wc_talks_settings_core']['_wc_talks_autolog_enabled'],
			$setting_fields['wc_talks_settings_rewrite']['_wc_talks_signup_slug']
		);
	}

	if ( is_multisite() ) {
		/** Multisite Section *********************************************************/
		$setting_fields['wc_talks_settings_multisite'] = array();

		if ( wct_is_signup_allowed() ) {
			$setting_fields['wc_talks_settings_multisite']['_wc_talks_allow_signups'] = array(
				'title'             => __( 'Sign-ups', 'wordcamp-talks' ),
				'callback'          => 'wct_allow_signups_setting_callback',
				'sanitize_callback' => 'absint',
				'args'              => array()
			);
		}

		$setting_fields['wc_talks_settings_multisite']['_wc_talks_user_new_talk_set_role'] = array(
			'title'             => __( 'Default role for network users', 'wordcamp-talks' ),
			'callback'          => 'wct_get_user_default_role_setting_callback',
			'sanitize_callback' => 'absint',
			'args'              => array()
		);
	}

	/**
	 * @param array $setting_fields the setting fields
	 */
	return (array) apply_filters( 'wct_get_settings_fields', $setting_fields );
}


/**
 * Gives the setting fields for section
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $section_id
 * @return array  the fields for the requested section
 */
function wct_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = wct_get_settings_fields();
	$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

	/**
	 * @param array $retval      the setting fields
	 * @param string $section_id the section id
	 */
	return (array) apply_filters( 'wct_get_settings_fields_for_section', $retval, $section_id );
}

/**
 * Disable a settings field if its value rely on another setting field value
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $function function to get the option value
 * @param  string $option   the option value
 * @return string HTML output
 */
function wct_setting_disabled( $function = '', $option = '', $operator = '=' ) {
	if ( empty( $function ) || empty( $option ) || ! function_exists( $function ) ) {
		return;
	}

	$compare = call_user_func( $function );

	if ( '!=' === $operator ) {
		disabled( $compare !== $option );
		return;
	}

	disabled( $compare === $option );
}

/**
 * Disable a settings field if another option is set
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $option_key the option key
 * @return string HTML output
 */
function wct_setting_disabled_option( $option = '' ) {
	if( ! get_option( $option, false ) ) {
		return;
	}

	disabled( true );
}

/**
 * Checks for rewrite conflicts, displays a warning if any
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $slug the plugin's root slug
 * @return string HTML output
 */
function wct_root_slug_conflict_check( $slug = 'talks' ) {
	// Initialize attention
	$attention = array();

	/**
	 * For pages and posts, problem can occur if the permalink setting is set to
	 * '/%postname%/' In that case a post will be listed in post archive pages but the
	 * single post may arrive on the main Archive page.
	 */
	if ( '/%postname%/' == wct()->pretty_links ) {
		// Check for posts having a post name == root slug
		$post = get_posts( array( 'name' => $slug, 'post_type' => array( 'post', 'page' ) ) );

		if ( ! empty( $post ) ) {
			$post = $post[0];
			$conflict = sprintf( _x( 'this %s', 'wc_talks settings root slug conflict', 'wordcamp-talks' ), $post->post_type );
			$attention[] = '<strong><a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . $conflict . '</strong>';
		}
	}

	/**
	 * We need to check for bbPress forum's root prefix, if called the same way than
	 * the root prefix of wc_talks, then forums archive won't be reachable.
	 */
	if ( function_exists( 'bbp_get_root_slug' ) && $slug == bbp_get_root_slug() ) {
		$conflict = _x( 'bbPress forum root slug', 'bbPress possible conflict', 'wordcamp-talks' );
		$attention[] = '<strong><a href="' . esc_url( add_query_arg( array( 'page' => 'bbpress' ), admin_url( 'options-general.php' ) ) ) .'">' . $conflict . '</strong>';
	}

	/**
	 * Finally, in case of a multisite config, we need to check if a child blog is called
	 * the same way than the wc_talks root slug
	 */
	if ( is_multisite() ) {
		$blog_id         = (int) get_id_from_blogname( $slug );
		$current_blog_id = (int) get_current_blog_id();
		$current_site    = get_current_site();

		if ( ! empty( $blog_id ) && $blog_id != $current_blog_id && $current_site->blog_id == $current_blog_id ) {
			$conflict = _x( 'child blog slug', 'Child blog possible conflict', 'wordcamp-talks' );

			$blog_url = get_home_url( $blog_id, '/' );

			if ( is_super_admin() ) {
				$blog_url = add_query_arg( array( 'id' => $blog_id ), network_admin_url( 'site-info.php' ) );
			}

			$attention[] = '<strong><a href="' . esc_url( $blog_url ) .'">' . $conflict . '</strong>';
		}
	}
	/**
	 * Other plugins can come in there to draw attention ;)
	 *
	 * @param array  $attention list of slug conflicts
	 * @param string $slug      the plugin's root slug
	 */
	$attention = apply_filters( 'wct_root_slug_conflict_check', $attention, $slug );

	// Display warnings if needed
	if ( ! empty( $attention ) ) {
		?>

		<span class="attention"><?php printf( esc_html__( 'Possible conflict with: %s', 'wordcamp-talks' ), join( ', ', $attention ) ) ;?></span>

		<?php
	}
}

/** Core settings callbacks ***************************************************/

/**
 * Some text to introduce the core settings section
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_settings_core_section_callback() {
	?>

	<p><?php _e( 'Customize WordCamp Talks features', 'wordcamp-talks' ); ?></p>
	<p class="description"><?php printf( esc_html__( 'Url of WordCamp Talks&#39;s main page: %s', 'wordcamp-talks' ), '<code>' . wct_get_root_url() .'</code>' ) ;?></p>

	<?php
}

/**
 * Archive page title callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_archive_title_setting_callback() {
	?>

	<input name="_wc_talks_archive_title" id="_wc_talks_archive_title" type="text" class="regular-text code" value="<?php echo esc_attr( wct_archive_title() ); ?>" />

	<?php
}

/**
 * Callback function for Talks submission closing date
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_closing_date_setting_callback() {
	$closing = wct_get_closing_date();
	?>
	<input name="_wc_talks_closing_date" id="_wc_talks_closing_date" type="text" class="regular-text code" placeholder="YYYY-MM-DD HH:II" value="<?php echo esc_attr( $closing ); ?>" />
	<p class="description"><?php esc_html_e( 'Date when the call for speakers will end.', 'wordcamp-talks' ); ?></p>
	<?php
}

/**
 * Submit Status callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_submit_status_setting_callback() {
	$current_status = wct_default_talk_status();
	$stati          = array_diff_key( get_post_stati( array( 'show_in_admin_all_list' => true ), 'objects' ), array(
		'draft'  => false,
		'future' => false,
	) );
	?>
	<select name="_wc_talks_submit_status" id="_wc_talks_submit_status">

		<?php foreach ( $stati as $status ) : ?>

			<option value="<?php echo esc_attr( $status->name ); ?>" <?php selected( $current_status, $status->name );?>><?php echo esc_html( $status->label );?></option>

		<?php endforeach; ?>

	</select>
	<p class="description"><?php esc_html_e( 'The default status for all talks. Depending on this setting, the moderation message setting will be available', 'wordcamp-talks' ); ?></p>
	<?php
}

/**
 * WP Editor's image button callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_editor_image_setting_callback() {
	?>

	<input name="_wc_talks_editor_image" id="_wc_talks_editor_image" type="checkbox" value="1" <?php checked( wct_talk_editor_image() ); ?> />
	<label for="_wc_talks_editor_image"><?php esc_html_e( 'Allow users to add images to their talks', 'wordcamp-talks' ); ?></label>
	<p class="description"><?php esc_html_e( 'Depending on this setting, the featured images setting will be available', 'wordcamp-talks' ); ?></p>

	<?php
}

/**
 * WP Editor's Featured images callback
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_editor_featured_images_setting_callback() {
	?>

	<input name="_wc_talks_featured_images" id="_wc_talks_featured_images" type="checkbox" value="1" <?php checked( wct_featured_images_allowed() ); ?> <?php disabled( wct_talk_editor_image(), false ); ?>/>
	<label for="_wc_talks_featured_images"><?php esc_html_e( 'If users can add images, you can allow them to choose the featured image for their talks', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * WP Editor's link button callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_editor_link_setting_callback() {
	?>

	<input name="_wc_talks_editor_link" id="_wc_talks_editor_link" type="checkbox" value="1" <?php checked( wct_talk_editor_link() ); ?> />
	<label for="_wc_talks_editor_link"><?php esc_html_e( 'Allow users to add links to their talks', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Custom moderation message callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_moderation_message_setting_callback() {
	?>

	<label for="_wc_talks_moderation_message"><?php esc_html_e( 'In cases where &#34;Pending&#34; is the status for all talks, you can customize the moderation message', 'wordcamp-talks' ); ?></label>
	<textarea name="_wc_talks_moderation_message" id="_wc_talks_moderation_message" rows="10" cols="50" class="large-text code" <?php wct_setting_disabled( 'wct_default_talk_status', 'pending', '!=' ); ?>><?php echo esc_textarea( wct_moderation_message() );?></textarea>

	<?php
}

/**
 * Custom login message callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_login_message_setting_callback() {
	?>

	<label for="_wc_talks_login_message"><?php esc_html_e( 'You can customize the message shown to not logged in users on the new talk form', 'wordcamp-talks' ); ?></label>
	<textarea name="_wc_talks_login_message" id="_wc_talks_login_message" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( wct_login_message() );?></textarea>

	<?php
}

/**
 * List of captions for the rating stars
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_hint_list_setting_callback() {
	$hintlist = wct_get_hint_list();
	$csv_hinlist = join( ',', $hintlist );
	?>

	<label for="_wc_talks_hint_list"><?php esc_html_e( 'You can customize the hover captions used for stars by using a comma separated list of captions', 'wordcamp-talks' ); ?></label>
	<input name="_wc_talks_hint_list" id="_wc_talks_hint_list" type="text" class="large-text code" value="<?php echo esc_attr( $csv_hinlist ); ?>" />

	<?php
}

/**
 * User's Profile "To Rate" tab disabling callback
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_to_rate_profile_setting_callback() {
	?>

	<input name="_wc_talks_to_rate_disabled" id="_wc_talks_to_rate_disabled" type="checkbox" value="1" <?php checked( wct_is_user_to_rate_disabled() ); ?> />
	<label for="_wc_talks_to_rate_disabled"><?php esc_html_e( '&quot;To rate&quot; user\'s profile tab.', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Sticky talks callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_sticky_talks_setting_callback() {
	?>

	<input name="_wc_talks_sticky_talks" id="_wc_talks_sticky_talks" type="checkbox" value="1" <?php checked( wct_is_sticky_enabled() ); ?> />
	<label for="_wc_talks_sticky_talks"><?php esc_html_e( 'Allow talks to be made &#34;sticky&#34; (they will stay at the top of WordCamp Talks first page)', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Disjoin talk comments callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_disjoin_comments_setting_callback() {
	?>

	<input name="_wc_talks_disjoin_comments" id="_wc_talks_disjoin_comments" type="checkbox" value="1" <?php checked( wct_is_comments_disjoined() ); ?> />
	<label for="_wc_talks_disjoin_comments"><?php esc_html_e( 'Separate comments made on talks from the other post types.', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Global "opened" comments callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_allow_comments_setting_callback() {
	?>

	<input name="_wc_talks_allow_comments" id="_wc_talks_allow_comments" type="checkbox" value="1" <?php checked( wct_is_comments_allowed() ); ?> />
	<label for="_wc_talks_allow_comments"><?php esc_html_e( 'Allow users to add comments on talks', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * List of labels for the user's profile fields
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  array  $args  Whether to get private or public fields.
 * @return string        HTML output.
 */
function wct_fields_list_setting_callback( $args = array() ) {
	if ( empty( $args['type'] ) ) {
		return;
	}

	if ( 'public' === $args['type'] ) {
		$label_list = wct_user_public_fields_list();
		$option     = '_wc_talks_public_fields_list';
	} else {
		$label_list = wct_user_private_fields_list();
		$option     = '_wc_talks_private_fields_list';
	}

	$csv_list   = join( ',', $label_list );
	?>

	<label for="<?php echo esc_attr( $option ); ?>"><?php printf( esc_html__( 'Adding a comma separated list of fields label will generate new %s contact informations for the user.', 'wordcamp-talks' ), $args['type'] ); ?></label>
	<input name="<?php echo esc_attr( $option ); ?>" id="<?php echo esc_attr( $option ); ?>" type="text" class="large-text code" value="<?php echo esc_attr( $csv_list ); ?>" />

	<?php
}

/**
 * List of field keys to include in the signup form.
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output.
 */
function wct_signup_fields_setting_callback() {
	$fields = wct_users_get_all_contact_methods();
	$signup = array_flip( wct_user_signup_fields() );
	?>
	<ul>
		<?php foreach ( $fields as $field_key => $field_name ):?>

			<li style="display:inline-block;width:45%;margin-right:1em">
				<label for="wct-signup-field-cb-<?php echo esc_attr( $field_key ); ?>">
					<input type="checkbox" class="checkbox" id="wct-signup-field-cb-<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $field_key ); ?>" name="_wc_talks_signup_fields[]" <?php checked( isset( $signup[ $field_key ] ) ); ?>>
					<?php echo esc_html( $field_name ); ?>
				</label>
			</li>

		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Signups autolog callback
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_autolog_signups_fields_setting_callback() {
	?>

	<input name="_wc_talks_autolog_enabled" id="_wc_talks_autolog_enabled" type="checkbox" value="1" <?php checked( (bool) wct_user_autolog_after_signup() ); ?> />
	<label for="_wc_talks_autolog_enabled"><?php esc_html_e( 'Automagically log in just signed up users.', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Embed User Profiles callback
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_embed_profile_setting_callback() {
	?>

	<input name="_wc_talks_embed_profile" id="_wc_talks_embed_profile" type="checkbox" value="1" <?php checked( (bool) wct_is_embed_profile() ); ?> />
	<label for="_wc_talks_embed_profile"><?php esc_html_e( 'Allow users profiles to be embed', 'wordcamp-talks' ); ?></label>

	<?php
}

/** Rewrite settings callbacks ************************************************/

/**
 * Some text to introduce the rewrite settings section
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_settings_rewrite_section_callback() {
	?>

	<p><?php esc_html_e( 'Customize the slugs of WordCamp Talks urls', 'wordcamp-talks' ); ?></p>

	<?php
}

/**
 * Root slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_root_slug_setting_callback() {
	?>

	<input name="_wc_talks_root_slug" id="_wc_talks_root_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_root_slug() ); ?>" />

	<?php
	wct_root_slug_conflict_check( wct_root_slug() );
}

/**
 * Talk slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_talk_slug_setting_callback() {
	?>

	<input name="_wc_talks_talk_slug" id="_wc_talks_talk_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_get_talk_slug() ); ?>" />

	<?php
}

/**
 * Category slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_category_slug_setting_callback() {
	?>

	<input name="_wc_talks_category_slug" id="_wc_talks_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_get_category_slug() ); ?>" />

	<?php
}

/**
 * Tag slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_tag_slug_setting_callback() {
	?>

	<input name="_wc_talks_tag_slug" id="_wc_talks_tag_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_get_tag_slug() ); ?>" />

	<?php
}

/**
 * User slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_user_slug_setting_callback() {
	?>

	<input name="_wc_talks_user_slug" id="_wc_talks_user_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_get_user_slug() ); ?>" />

	<?php
}

/**
 * User comments slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_user_comments_slug_setting_callback() {
	?>

	<input name="_wc_talks_user_comments_slug" id="_wc_talks_user_comments_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_user_comments_slug() ); ?>" />

	<?php
}

/**
 * User comments pagination slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_cpage_slug_setting_callback() {
	?>

	<input name="_wc_talks_cpage_slug" id="_wc_talks_cpage_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_cpage_slug() ); ?>" />
	<p class="description"><?php printf( esc_html__( '&#39;%s&#39; slug cannot be used here.', 'wordcamp-talks' ), wct_paged_slug() ); ?></p>

	<?php
}

/**
 * User rates slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_user_rates_slug_setting_callback() {
	?>

	<input name="_wc_talks_user_rates_slug" id="_wc_talks_user_rates_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_user_rates_slug() ); ?>" />

	<?php
}

/**
 * User's "To Rate" lug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_user_to_rate_slug_setting_callback() {
	?>

	<input name="_wc_talks_user_to_rate_slug" id="_wc_talks_user_to_rate_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_user_to_rate_slug() ); ?>" />

	<?php
}

/**
 * Signup slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_signup_slug_setting_callback() {
	?>

	<input name="_wc_talks_signup_slug" id="_wc_talks_signup_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_signup_slug() ); ?>" />

	<?php
}

/**
 * Action slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_action_slug_setting_callback() {
	?>

	<input name="_wc_talks_action_slug" id="_wc_talks_action_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_get_action_slug() ); ?>" />

	<?php
}

/**
 * New talk slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_addnew_slug_setting_callback() {
	?>

	<input name="_wc_talks_addnew_slug" id="_wc_talks_addnew_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_addnew_slug() ); ?>" />

	<?php
}

/**
 * Edit talk slug of the plugin
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_edit_slug_setting_callback() {
	?>

	<input name="_wc_talks_edit_slug" id="_wc_talks_edit_slug" type="text" class="regular-text code" value="<?php echo esc_attr( wct_edit_slug() ); ?>" />

	<?php
}

/**
 * Some text to introduce the multisite settings section
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_settings_multisite_section_callback() {
	?>

	<p><?php esc_html_e( 'Define your preferences about network users', 'wordcamp-talks' ); ?></p>

	<?php
}

/**
 * Does the blog is allowing us to manage signups?
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_allow_signups_setting_callback() {
	?>

	<input name="_wc_talks_allow_signups" id="_wc_talks_allow_signups" type="checkbox" value="1" <?php checked( wct_allow_signups() ); ?> />
	<label for="_wc_talks_allow_signups"><?php esc_html_e( 'Allow WordCamp Talks to manage signups for your site', 'wordcamp-talks' ); ?></label>

	<?php
}

/**
 * Default role for users posting an talk on this site callback
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @return string HTML output
 */
function wct_get_user_default_role_setting_callback() {
	?>

	<input name="_wc_talks_user_new_talk_set_role" id="_wc_talks_user_new_talk_set_role" type="checkbox" value="1" <?php checked( wct_get_user_default_role() ); ?> />
	<label for="_wc_talks_user_new_talk_set_role"><?php esc_html_e( 'Automatically set this site&#39;s default role for users posting a new talk and having no role on this site.', 'wordcamp-talks' ); ?></label>

	<?php
}

/** Custom sanitization *******************************************************/

/**
 * 'Sanitize' the date
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $option
 * @return string closing date
 */
function wct_sanitize_closing_date( $option = '' ) {
	if ( empty( $option ) ) {
		delete_option( '_wc_talks_closing_date' );
	}

	$now    = strtotime( date_i18n( 'Y-m-d H:i' ) );
	$option = strtotime( $option );

	if ( $option <= $now ) {
		return wct_get_closing_date( true );

	} else {
		return $option;
	}
}

/**
 * Sanitize the status setting
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $option the value choosed by the admin
 * @return string         the sanitized value
 */
function wct_sanitize_status( $option = '' ) {
	/**
	 * @param string $option the sanitized option
	 */
	return apply_filters( 'wct_sanitize_status', sanitize_key( $option ) );
}

/**
 * Sanitize list
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $option the comma separated values choosed by the admin
 * @return string         the sanitized value
 */
function wct_sanitize_list( $option = '' ) {
	if ( is_array( $option ) ) {
		$items = $option;
	} else {
		$items = explode( ',', wp_unslash( $option ) );
	}

	if ( ! is_array( $items ) ) {
		return false;
	}

	$items = array_map( 'sanitize_text_field', $items );

	/**
	 * @param array $items the sanitized items
	 */
	return apply_filters( 'wct_sanitize_list', $items );
}

/**
 * Sanitize the user profile fields
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $option the comma separated values choosed by the admin
 * @return string         the sanitized value
 */
function wct_sanitize_user_fields_list( $option = '' ) {
	if ( is_array( $option ) ) {
		$labels = $option;
	} else {
		$labels = explode( ',', wp_unslash( $option ) );
	}

	if ( ! is_array( $labels ) ) {
		return false;
	}

	$labels = array_map( 'sanitize_text_field', $labels );
	$keys   = array();

	foreach ( $labels as $label ) {
		$keys[] = 'wct_' . sanitize_key( $label );
	}

	$fields = array_combine( $keys, $labels );

	/**
	 * @param array $fields the sanitized fields
	 */
	return apply_filters( 'wct_sanitize_user_fields_list', $fields );
}

/**
 * Make sure sticky talks are removed if the sticky setting is disabled
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  int $option the sticky setting
 * @return int         the new sticky setting
 */
function wct_sticky_sanitize( $option = 0 ) {
	if ( empty( $option ) ) {
		delete_option( 'sticky_talks' );
	}

	return absint( $option );
}

/**
 * Sanitize the featured image option.
 *
 * @since 1.0.0
 *
 * @param  int $option the featured image setting
 * @return int         the new featured image setting
 */
function wct_editor_featured_images_sanitize( $option = 0 ) {
	// People need to insert image before selecting a featured one.
	if ( ! wct_talk_editor_image() ) {
		return 0;
	}

	return absint( $option );
}

/**
 * Create the Utility page for embed profile if needed
 *
 * @since 1.0.0
 *
 * @param  int $option the embed profile setting
 * @return int         the new embed profile setting
 */
function wct_sanitize_embed_profile( $option = 0 ) {
	$utility_page_id = wct_is_embed_profile();

	if ( $utility_page_id ) {
		$utility_page = get_post( $utility_page_id );
	}

	if ( isset( $utility_page->post_type ) && 'wct_utility' !== $utility_page->post_type ) {
		$utility_page = null;
	}

	if ( ! empty( $option ) ) {
		if ( empty( $utility_page->ID ) ) {
			$option = wp_insert_post( array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
				'post_title'     => 'wc_talks_user_page',
				'post_type'      => 'wct_utility',
			) );
		} else {
			$option = $utility_page->ID;
		}

	} elseif ( ! empty( $utility_page->ID ) ) {
		wp_delete_post( $utility_page->ID, true );
	}

	return absint( $option );
}

/**
 * Sanitize permalink slugs when saving the settings page.
 *
 * Inspired by bbPress's bbp_sanitize_slug() function
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $slug the slug choosed by the admin
 * @return string the sanitized slug
 */
function wct_sanitize_slug( $slug = '' ) {
	// Remove accents
	$value = remove_accents( $slug );

	// Put every character in lowercase
	$value = strtolower( $value );

	// Don't allow multiple slashes in a row
	$value = preg_replace( '#/+#', '/', str_replace( '#', '', $value ) );

	// Strip out unsafe or unusable chars
	$value = esc_url_raw( $value );

	// esc_url_raw() adds a scheme via esc_url(), so let's remove it
	$value = str_replace( 'http://', '', $value );

	// Trim off first and last slashes.
	//
	// We already prevent double slashing elsewhere, but let's prevent
	// accidental poisoning of options values where we can.
	$value = ltrim( $value, '/' );
	$value = rtrim( $value, '/' );

	/**
	 * @param string $value the sanitized slug
	 * @param string $slug  the slug choosed by the admin
	 */
	return apply_filters( 'wct_sanitize_slug', $value, $slug );
}

/**
 * Sanitize the user comments pagination slug.
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 *
 * @param  string $slug the slug choosed by the admin
 * @return string the sanitized slug
 */
function wct_sanitize_comments_page_slug( $slug = '' ) {
	if ( $slug == wct_paged_slug() ) {
		return 'cpage';
	}

	return wct_sanitize_slug( $slug );
}

/**
 * Displays the settings page
 *
 * @package WordCamp Talks
 * @subpackage admin/settings
 *
 * @since 1.0.0
 */
function wct_settings() {
	?>
	<div class="wrap">

		<h2><?php esc_html_e( 'WordCamp Talks Settings', 'wordcamp-talks' ) ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'wc_talks' ); ?>

			<?php do_settings_sections( 'wc_talks' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wordcamp-talks' ); ?>" />
			</p>
		</form>
	</div>
	<?php
}
