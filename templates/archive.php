<?php
/**
 * Archive template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>

<div id="wordcamp-talks">

	<?php wct_user_feedback(); ?>

	<?php do_action( 'wct_before_archive_main_nav' ); ?>

	<ul id="talks-main-nav">
		<li><?php wct_talks_search_form(); ?></li>
		<li class="last"><?php wct_talks_order_form(); ?></li>
	</ul>

	<?php do_action( 'wct_after_archive_main_nav' ); ?>

	<?php wct_template_part( 'talk', 'loop' ); ?>

</div>
