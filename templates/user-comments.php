<?php
/**
 * User comments template
 *
 * @package WordCamp Talks
 * @subpackage templates
 *
 * @since 1.0.0
 */
?>
<?php if ( wct_comments_has_comments( wct_comments_query_args() ) ) : ?>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="talk-count-top">

			<?php wct_comments_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="talk-pag-top">

			<?php wct_comments_pagination_links(); ?>

		</div>

	</div>

	<ul class="talk-comments-list">

	<?php while ( wct_comments_the_comments() ) : wct_comments_the_comment() ; ?>

		<li id="talk-comment-<?php wct_comments_the_comment_id(); ?>">
			<div class="talk-comment-avatar">
				<?php wct_comments_the_comment_author_avatar(); ?>
			</div>
			<div class="talk-comment-content">

				<?php do_action( 'wct_talk_comment_before_title' ); ?>

				<div class="talk-comment-title">
					<?php wct_comments_before_comment_title(); ?> <a href="<?php wct_comments_the_comment_permalink();?>" title="<?php wct_comments_the_comment_title_attribute(); ?>"><?php wct_comments_the_comment_title(); ?></a>
				</div>

				<?php do_action( 'wct_talk_comment_before_excerpt' ); ?>

				<div class="talk-comment-excerpt">
					<?php wct_comments_the_comment_excerpt(); ?>
				</div>

				<?php do_action( 'wct_talk_comment_before_footer' ); ?>

				<div class="talk-comment-footer">
					<p class="talk-comment-meta"><?php wct_comments_the_comment_footer(); ?></p>

					<?php do_action( 'wct_comment_footer' ) ;?>
				</div>
			</div>
		</li>

	<?php endwhile ; ?>

	</ul>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="talk-count-bottom">

			<?php wct_comments_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="talk-pag-bottom">

			<?php wct_comments_pagination_links(); ?>

		</div>

	</div>

	<?php wct_maybe_reset_postdata() ;?>

<?php else : ?>

<div class="message info">
	<p><?php wct_comments_no_comment_found() ;?></p>
</div>

<?php endif ;?>
