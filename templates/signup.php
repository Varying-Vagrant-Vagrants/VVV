<?php
/**
 * Signup template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div id="wordcamp-talks">

	<?php do_action( 'wct_signup_before_content' ); ?>

	<?php wct_user_feedback(); ?>

	<form class="standard-form" id="wordcamp-talks-form" action="" method="post">

		<?php do_action( 'wct_signup_custom_field_before' ); ?>

		<?php wct_users_the_signup_fields() ; ?>

		<?php do_action( 'wct_signup_custom_field_after' ); ?>

		<div class="submit">

			<?php wct_users_the_signup_submit() ;?>

		</div>

	</form>

	<?php do_action( 'wct_signup_after_content' ); ?>

</div>
