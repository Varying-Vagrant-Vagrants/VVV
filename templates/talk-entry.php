<?php
/**
 * Talk entry template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div class="talk-avatar">
	<?php wct_talks_the_author_avatar(); ?>
</div>
<div class="talk-content">

	<?php do_action( 'wct_talk_entry_before_title' ); ?>

	<div class="talk-title">
		<?php wct_talks_before_talk_title(); ?><a href="<?php wct_talks_the_permalink();?>" title="<?php wct_talks_the_title_attribute(); ?>"><?php wct_talks_the_title(); ?></a>
	</div>

	<?php do_action( 'wct_talk_entry_before_header' ); ?>

	<?php wct_template_part( 'talk', 'header' ); ?>

	<div class="talk-excerpt">
		<?php wct_talks_the_excerpt(); ?>
	</div>

	<?php do_action( 'wct_talk_entry_before_footer' ); ?>

	<?php wct_template_part( 'talk', 'footer' ); ?>

	<?php do_action( 'wct_talk_entry_after_footer' ); ?>
</div>
