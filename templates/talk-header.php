<?php
/**
 * Talk header template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<div class="talk-header">
	<?php wct_talks_the_talk_comment_link( __( 'Leave a reply', 'wordcamp-talks' ), __( '1 Reply', 'wordcamp-talks' ), __( '% Replies', 'wordcamp-talks' ), 'talk-comment-link icon' );?>
	<?php wct_talks_the_rating_link( __( 'Rate the talk', 'wordcamp-talks' ), __( 'Average rating: %', 'wordcamp-talks' ), 'talk-rating-link icon' ); ?>

	<?php do_action( 'wct_talk_header' ) ;?>
</div>
