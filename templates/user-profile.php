<?php
/**
 * User profile template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>

<div id="wordcamp-talks">

	<?php wct_user_feedback(); ?>

	<?php do_action( 'wct_user_profile_before_header' ); ?>

	<div class="profile-header">

		<?php do_action( 'wct_user_profile_before_avatar' ); ?>

		<div class="user-avatar">

			<?php wct_users_the_user_profile_avatar(); ?>

		</div>

		<?php do_action( 'wct_user_profile_after_avatar' ); ?>

		<?php wct_users_the_user_profile_description(); ?>

		<div class="clear"></div>

		<?php do_action( 'wct_user_profile_after_description' ); ?>

		<div class="clear"></div>

	</div>

	<?php do_action( 'wct_user_profile_before_nav' ); ?>

	<?php wct_users_the_user_nav(); ?>

	<?php do_action( 'wct_user_profile_after_nav' ); ?>

	<?php if ( wct_is_user_profile_comments() ) : ?>

		<?php wct_template_part( 'user', 'comments' ); ?>

		<?php do_action( 'wct_user_profile_after_comments' ); ?>

	<?php else : ?>

		<?php wct_template_part( 'talk', 'loop' ); ?>

		<?php do_action( 'wct_user_profile_after_loop' ); ?>

	<?php endif; ?>

</div>
