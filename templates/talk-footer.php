<?php
/**
 * Talk footer template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div class="talk-footer">
	<?php do_action( 'wct_before_talk_footer' ) ;?>

	<p class="talk-meta"><?php wct_talks_the_talk_footer(); ?></p>

	<?php do_action( 'wct_after_talk_footer' ) ;?>

	<?php if ( is_single() ) wct_talks_bottom_navigation() ;?>
</div>
