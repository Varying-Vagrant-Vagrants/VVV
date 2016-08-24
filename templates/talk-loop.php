<?php
/**
 * Talks loop template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>

<?php if ( wct_talks_has_talks( wct_talks_query_args() ) ) : ?>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="talk-count-top">

			<?php wct_talks_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="talk-pag-top">

			<?php wct_talks_pagination_links(); ?>

		</div>

	</div>

	<ul class="talk-list">

	<?php while ( wct_talks_the_talks() ) : wct_talks_the_talk(); ?>

		<li id="talk-<?php wct_talks_the_id(); ?>" <?php wct_talks_the_classes(); ?>>
			<?php wct_template_part( 'talk', 'entry' ); ?>
		</li>

	<?php endwhile ; ?>

	</ul>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="talk-count-bottom">

			<?php wct_talks_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="talk-pag-bottom">

			<?php wct_talks_pagination_links(); ?>

		</div>

	</div>

	<?php wct_maybe_reset_postdata(); ?>

<?php else : ?>

<div class="message info">
	<p><?php wct_talks_not_found(); ?></p>
</div>

<?php endif ;?>
