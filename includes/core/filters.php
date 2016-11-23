<?php
/**
 * WordCamp Talks Filters.
 *
 * @package WordCamp Talks
 * @subpackage core/filters
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_filter( 'template_include',          'wct_set_template',                 10, 1 );
add_filter( 'wp_title_parts',            'wct_title',                        10, 1 );
add_filter( 'document_title_parts',      'wct_document_title_parts',         10, 1 );
add_filter( 'wp_title',                  'wct_title_adjust',                 20, 3 );
add_filter( 'body_class',                'wct_body_class',                   20, 2 );
add_filter( 'post_class',                'wct_post_class',                   10, 2 );
add_filter( 'map_meta_cap',              'wct_map_meta_caps',                10, 4 );
add_filter( 'widget_tag_cloud_args',     'wct_tag_cloud_args',               10, 1 );
add_filter( 'wp_nav_menu_objects',       'wct_wp_nav',                       10, 2 );
add_filter( 'get_edit_post_link',        'wct_edit_post_link',               10, 2 );
add_filter( 'get_edit_comment_link',     'wct_edit_comment_link',            10, 1 );
add_filter( 'comments_open',             'wct_comments_open',                10, 2 );
add_filter( 'heartbeat_received',        'wct_talks_heartbeat_check_locked', 10, 2 );
add_filter( 'heartbeat_nopriv_received', 'wct_talks_heartbeat_check_locked', 10, 2 );
add_filter( 'mce_external_plugins',      'wct_talks_tiny_mce_plugins',       10, 1 );

// Prefix talk's title in case of private/protected
add_filter( 'private_title_format',   'wct_talks_private_title_prefix',   10, 2 );
add_filter( 'protected_title_format', 'wct_talks_protected_title_prefix', 10, 2 );

// Order by rates count
add_filter( 'posts_clauses', 'wct_set_rates_count_orderby', 10, 2 );

// Sticky Talks
add_filter( 'the_posts', 'wct_talks_stick_talks', 10, 2 );

// Filter comment author urls
add_filter( 'comments_array', 'wct_comments_array',  11, 2 );

// Filter the comment approved for Raters & Blind Raters
add_filter( 'pre_comment_approved', 'wct_users_raters_approved', 10,  2 );

// Eventually add new contact methods
add_filter( 'user_contactmethods', 'wct_users_contactmethods', 10, 1 );

// Filter comment feeds
add_filter( 'comment_feed_limits', 'wct_comment_feed_limits', 10, 2 );

// Formating loop tags
add_filter( 'wct_talks_get_title', 'wptexturize'   );
add_filter( 'wct_talks_get_title', 'convert_chars' );
add_filter( 'wct_talks_get_title', 'trim'          );

add_filter( 'wct_talks_get_title_edit', 'strip_tags', 1 );
add_filter( 'wct_talks_get_title_edit', 'wp_unslash', 5 );

add_filter( 'wct_create_excerpt_text', 'strip_tags',        1 );
add_filter( 'wct_create_excerpt_text', 'force_balance_tags'   );
add_filter( 'wct_create_excerpt_text', 'wptexturize'          );
add_filter( 'wct_create_excerpt_text', 'convert_smilies'      );
add_filter( 'wct_create_excerpt_text', 'convert_chars'        );
add_filter( 'wct_create_excerpt_text', 'wpautop'              );
add_filter( 'wct_create_excerpt_text', 'wp_unslash',        5 );
add_filter( 'wct_create_excerpt_text', 'make_clickable',    9 );

add_filter( 'wct_talks_get_content', 'wptexturize'          );
add_filter( 'wct_talks_get_content', 'convert_smilies'      );
add_filter( 'wct_talks_get_content', 'convert_chars'        );
add_filter( 'wct_talks_get_content', 'wpautop'              );
add_filter( 'wct_talks_get_content', 'wct_do_embed',      8 );
add_filter( 'wct_talks_get_content', 'wp_unslash',        5 );
add_filter( 'wct_talks_get_content', 'make_clickable',    9 );
add_filter( 'wct_talks_get_content', 'force_balance_tags'   );

add_filter( 'wct_talks_get_editor_content', 'wp_unslash'  , 5    );
add_filter( 'wct_talks_get_editor_content', 'wp_kses_post'       );
add_filter( 'wct_talks_get_editor_content', 'wpautop'            );
add_filter( 'wct_talks_get_editor_content', 'wct_format_to_edit' );

add_filter( 'wct_comments_get_comment_excerpt', 'strip_tags',        1 );
add_filter( 'wct_comments_get_comment_excerpt', 'force_balance_tags'   );
add_filter( 'wct_comments_get_comment_excerpt', 'wptexturize'          );
add_filter( 'wct_comments_get_comment_excerpt', 'convert_smilies'      );
add_filter( 'wct_comments_get_comment_excerpt', 'convert_chars'        );
add_filter( 'wct_comments_get_comment_excerpt', 'wpautop'              );
add_filter( 'wct_comments_get_comment_excerpt', 'wp_unslash',        5 );
add_filter( 'wct_comments_get_comment_excerpt', 'make_clickable',    9 );

add_filter( 'wct_users_public_value', 'wct_users_sanitize_public_profile_field', 10, 2 );

add_filter( 'embed_template', 'wct_embed_profile', 10, 1 );
