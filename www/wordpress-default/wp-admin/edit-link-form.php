<?php
/**
 * Edit links form for inclusion in administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( ! empty($link_id) ) {
	$heading = sprintf( __( '<a href="%s">Links</a> / Edit Link' ), 'link-manager.php' );
	$submit_text = __('Update Link');
	$form = '<form name="editlink" id="editlink" method="post" action="link.php">';
	$nonce_action = 'update-bookmark_' . $link_id;
} else {
	$heading = sprintf( __( '<a href="%s">Links</a> / Add New Link' ), 'link-manager.php' );
	$submit_text = __('Add Link');
	$form = '<form name="addlink" id="addlink" method="post" action="link.php">';
	$nonce_action = 'add-bookmark';
}

require_once('./includes/meta-boxes.php');

add_meta_box('linksubmitdiv', __('Save'), 'link_submit_meta_box', null, 'side', 'core');
add_meta_box('linkcategorydiv', __('Categories'), 'link_categories_meta_box', null, 'normal', 'core');
add_meta_box('linktargetdiv', __('Target'), 'link_target_meta_box', null, 'normal', 'core');
add_meta_box('linkxfndiv', __('Link Relationship (XFN)'), 'link_xfn_meta_box', null, 'normal', 'core');
add_meta_box('linkadvanceddiv', __('Advanced'), 'link_advanced_meta_box', null, 'normal', 'core');

do_action('add_meta_boxes', 'link', $link);
do_action('add_meta_boxes_link', $link);

do_action('do_meta_boxes', 'link', 'normal', $link);
do_action('do_meta_boxes', 'link', 'advanced', $link);
do_action('do_meta_boxes', 'link', 'side', $link);

add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' =>
	'<p>' . __( 'You can add or edit links on this screen by entering information in each of the boxes. Only the link&#8217;s web address and name (the text you want to display on your site as the link) are required fields.' ) . '</p>' .
	'<p>' . __( 'The boxes for link name, web address, and description have fixed positions, while the others may be repositioned using drag and drop. You can also hide boxes you don&#8217;t use in the Screen Options tab, or minimize boxes by clicking on the title bar of the box.' ) . '</p>' .
	'<p>' . __( 'XFN stands for <a href="http://gmpg.org/xfn/" target="_blank">XHTML Friends Network</a>, which is optional. WordPress allows the generation of XFN attributes to show how you are related to the authors/owners of the site to which you are linking.' ) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="http://codex.wordpress.org/Links_Add_New_Screen" target="_blank">Documentation on Creating Links</a>' ) . '</p>' .
	'<p>' . __( '<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>'
);

require_once ('admin-header.php');
?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?>  <a href="link-add.php" class="add-new-h2"><?php echo esc_html_x('Add New', 'link'); ?></a></h2>

<?php if ( isset( $_GET['added'] ) ) : ?>
<div id="message" class="updated"><p><?php _e('Link added.'); ?></p></div>
<?php endif; ?>

<?php
if ( !empty($form) )
	echo $form;
if ( !empty($link_added) )
	echo $link_added;

wp_nonce_field( $nonce_action );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

<div id="poststuff">

<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
<div id="post-body-content">
<div id="namediv" class="stuffbox">
<h3><label for="link_name"><?php _ex('Name', 'link name') ?></label></h3>
<div class="inside">
	<input type="text" name="link_name" size="30" value="<?php echo esc_attr($link->link_name); ?>" id="link_name" />
    <p><?php _e('Example: Nifty blogging software'); ?></p>
</div>
</div>

<div id="addressdiv" class="stuffbox">
<h3><label for="link_url"><?php _e('Web Address') ?></label></h3>
<div class="inside">
	<input type="text" name="link_url" size="30" class="code" value="<?php echo esc_attr($link->link_url); ?>" id="link_url" />
    <p><?php _e('Example: <code>http://wordpress.org/</code> &#8212; don&#8217;t forget the <code>http://</code>'); ?></p>
</div>
</div>

<div id="descriptiondiv" class="stuffbox">
<h3><label for="link_description"><?php _e('Description') ?></label></h3>
<div class="inside">
	<input type="text" name="link_description" size="30" value="<?php echo isset($link->link_description) ? esc_attr($link->link_description) : ''; ?>" id="link_description" />
    <p><?php _e('This will be shown when someone hovers over the link in the blogroll, or optionally below the link.'); ?></p>
</div>
</div>
</div><!-- /post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<?php

do_action('submitlink_box');
$side_meta_boxes = do_meta_boxes( 'link', 'side', $link );

?>
</div>
<div id="postbox-container-2" class="postbox-container">
<?php

do_meta_boxes(null, 'normal', $link);

do_meta_boxes(null, 'advanced', $link);

?>
</div>
<?php

if ( $link_id ) : ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="link_id" value="<?php echo (int) $link_id; ?>" />
<input type="hidden" name="order_by" value="<?php echo esc_attr($order_by); ?>" />
<input type="hidden" name="cat_id" value="<?php echo (int) $cat_id ?>" />
<?php else: ?>
<input type="hidden" name="action" value="add" />
<?php endif; ?>

</div>
</div>

</form>
</div>
