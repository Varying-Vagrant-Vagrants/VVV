<?php
/**
 * Talk form template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div id="wordcamp-talks">

	<?php do_action( 'wct_talks_before_form' ); ?>

	<?php wct_user_feedback(); ?>

	<?php if ( wct_user_can( 'publish_talks' ) ) : ?>

		<form class="standard-form" id="wordcamp-talks-form" action="" method="post">

			<?php wct_talks_the_title_edit(); ?>

			<?php wct_talks_the_editor(); ?>

			<?php wct_talks_the_images_list() ;?>

			<div class="category-list">

				<?php wct_talks_the_category_edit(); ?>

			</div>

			<div class="tag-list">

				<?php wct_talks_the_tags_edit() ;?>

			</div>

			<?php do_action( 'wct_talks_the_talk_meta_edit' ); ?>

			<div class="submit">

				<?php wct_talks_the_form_submit() ;?>

			</div>

		</form>

	<?php else: ?>

		<div class="message info">
			<p><?php wct_talks_not_loggedin(); ?></p>
		</div>

	<?php endif; ?>

	<?php do_action( 'wct_talks_after_form' ); ?>

</div>
