<?php
/**
 * Contains the user's profile embed template.
 *
 * @package WordCamp Talks
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! headers_sent() ) {
	header( 'X-WP-embed: true' );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php echo wp_get_document_title(); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php
	/**
	 * Print scripts or data in the embed template <head> tag.
	 *
	 * @since 1.0.0
	 */
	do_action( 'embed_head' );
	?>
</head>
<body <?php body_class(); ?>>
	<div id="wordcamp-talks" class="wp-embed">
		<div class="profile-header">
			<div class="user-avatar">
				<?php wct_users_embed_user_profile_avatar(); ?>
			</div>

			<div class="user-display-name">
				<p class="wp-embed-heading">
					<?php if ( current_user_can( 'view_other_profiles', wct_users_displayed_user_id() ) ) : ?>

						<a href="<?php wct_users_embed_user_profile_link(); ?>">
							<?php wct_users_embed_user_profile_display_name(); ?>
						</a>

					<?php else : ?>

						<?php wct_users_embed_user_profile_display_name(); ?>

					<?php endif; ?>
				</p>
			</div>
		</div>

		<?php if ( wct_users_has_embed_description() ) : ?>

			<div class="wp-embed-excerpt">
				<p><?php wct_users_embed_user_profile_description(); ?></p>
			</div>

		<?php endif ; ?>

		<div class="profile-footer">

			<?php wct_users_embed_user_stats() ;?>

			<div class="wp-embed-meta">
				<?php
				/**
				 * Print additional meta content in the embed template.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wct_embed_content_meta' );
				?>
			</div>
		</div>
	</div>

<?php
/**
 * Print scripts or data before the closing body tag in the embed template.
 *
 * @since 1.0.0
 */
do_action( 'embed_footer' ); ?>
</body>
</html>
