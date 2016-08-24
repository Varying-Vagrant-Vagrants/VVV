<?php
/**
 * Talk plugin template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div id="wordcamp-talks">

	<?php do_action( 'wct_talks_before_plugin_content' ); ?>

	<?php wct_user_feedback(); ?>

	<?php do_action( 'wct_talks_plugin_content' ); ?>

</div>
