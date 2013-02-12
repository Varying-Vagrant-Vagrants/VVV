<?php
/**
 * Widgets administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( './admin.php' );

/** WordPress Administration Widgets API */
require_once(ABSPATH . 'wp-admin/includes/widgets.php');

if ( ! current_user_can('edit_theme_options') )
	wp_die( __( 'Cheatin&#8217; uh?' ));

$widgets_access = get_user_setting( 'widgets_access' );
if ( isset($_GET['widgets-access']) ) {
	$widgets_access = 'on' == $_GET['widgets-access'] ? 'on' : 'off';
	set_user_setting( 'widgets_access', $widgets_access );
}

function wp_widgets_access_body_class($classes) {
	return "$classes widgets_access ";
}

if ( 'on' == $widgets_access ) {
	add_filter( 'admin_body_class', 'wp_widgets_access_body_class' );
} else {
	wp_enqueue_script('admin-widgets');

	if ( wp_is_mobile() )
		wp_enqueue_script( 'jquery-touch-punch' );
}

do_action( 'sidebar_admin_setup' );

$title = __( 'Widgets' );
$parent_file = 'themes.php';

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . __('Widgets are independent sections of content that can be placed into any widgetized area provided by your theme (commonly called sidebars). To populate your sidebars/widget areas with individual widgets, drag and drop the title bars into the desired area. By default, only the first widget area is expanded. To populate additional widget areas, click on their title bars to expand them.') . '</p>
	<p>' . __('The Available Widgets section contains all the widgets you can choose from. Once you drag a widget into a sidebar, it will open to allow you to configure its settings. When you are happy with the widget settings, click the Save button and the widget will go live on your site. If you click Delete, it will remove the widget.') . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'removing-reusing',
'title'		=> __('Removing and Reusing'),
'content'	=>
	'<p>' . __('If you want to remove the widget but save its setting for possible future use, just drag it into the Inactive Widgets area. You can add them back anytime from there. This is especially helpful when you switch to a theme with fewer or different widget areas.') . '</p>
	<p>' . __('Widgets may be used multiple times. You can give each widget a title, to display on your site, but it&#8217;s not required.') . '</p>
	<p>' . __('Enabling Accessibility Mode, via Screen Options, allows you to use Add and Edit buttons instead of using drag and drop.') . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'missing-widgets',
'title'		=> __('Missing Widgets'),
'content'	=>
	'<p>' . __('Many themes show some sidebar widgets by default until you edit your sidebars, but they are not automatically displayed in your sidebar management tool. After you make your first widget change, you can re-add the default widgets by adding them from the Available Widgets area.') . '</p>' .
		'<p>' . __('When changing themes, there is often some variation in the number and setup of widget areas/sidebars and sometimes these conflicts make the transition a bit less smooth. If you changed themes and seem to be missing widgets, scroll down on this screen to the Inactive Widgets area, where all of your widgets and their settings will have been saved.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Appearance_Widgets_Screen" target="_blank">Documentation on Widgets</a>') . '</p>' .
	'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

if ( ! current_theme_supports( 'widgets' ) ) {
	wp_die( __( 'The theme you are currently using isn&#8217;t widget-aware, meaning that it has no sidebars that you are able to change. For information on making your theme widget-aware, please <a href="http://codex.wordpress.org/Widgetizing_Themes">follow these instructions</a>.' ) );
}

// These are the widgets grouped by sidebar
$sidebars_widgets = wp_get_sidebars_widgets();

if ( empty( $sidebars_widgets ) )
	$sidebars_widgets = wp_get_widget_defaults();

foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
	if ( 'wp_inactive_widgets' == $sidebar_id )
		continue;

	if ( !isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
		if ( ! empty( $widgets ) ) { // register the inactive_widgets area as sidebar
			register_sidebar(array(
				'name' => __( 'Inactive Sidebar (not used)' ),
				'id' => $sidebar_id,
				'class' => 'inactive-sidebar orphan-sidebar',
				'description' => __( 'This sidebar is no longer available and does not show anywhere on your site. Remove each of the widgets below to fully remove this inactive sidebar.' ),
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '',
				'after_title' => '',
			));
		} else {
			unset( $sidebars_widgets[ $sidebar_id ] );
		}
	}
}

// register the inactive_widgets area as sidebar
register_sidebar(array(
	'name' => __('Inactive Widgets'),
	'id' => 'wp_inactive_widgets',
	'class' => 'inactive-sidebar',
	'description' => __( 'Drag widgets here to remove them from the sidebar but keep their settings.' ),
	'before_widget' => '',
	'after_widget' => '',
	'before_title' => '',
	'after_title' => '',
));

retrieve_widgets();

// We're saving a widget without js
if ( isset($_POST['savewidget']) || isset($_POST['removewidget']) ) {
	$widget_id = $_POST['widget-id'];
	check_admin_referer("save-delete-widget-$widget_id");

	$number = isset($_POST['multi_number']) ? (int) $_POST['multi_number'] : '';
	if ( $number ) {
		foreach ( $_POST as $key => $val ) {
			if ( is_array($val) && preg_match('/__i__|%i%/', key($val)) ) {
				$_POST[$key] = array( $number => array_shift($val) );
				break;
			}
		}
	}

	$sidebar_id = $_POST['sidebar'];
	$position = isset($_POST[$sidebar_id . '_position']) ? (int) $_POST[$sidebar_id . '_position'] - 1 : 0;

	$id_base = $_POST['id_base'];
	$sidebar = isset($sidebars_widgets[$sidebar_id]) ? $sidebars_widgets[$sidebar_id] : array();

	// delete
	if ( isset($_POST['removewidget']) && $_POST['removewidget'] ) {

		if ( !in_array($widget_id, $sidebar, true) ) {
			wp_redirect( admin_url('widgets.php?error=0') );
			exit;
		}

		$sidebar = array_diff( $sidebar, array($widget_id) );
		$_POST = array('sidebar' => $sidebar_id, 'widget-' . $id_base => array(), 'the-widget-id' => $widget_id, 'delete_widget' => '1');
	}

	$_POST['widget-id'] = $sidebar;

	foreach ( (array) $wp_registered_widget_updates as $name => $control ) {
		if ( $name != $id_base || !is_callable($control['callback']) )
			continue;

		ob_start();
			call_user_func_array( $control['callback'], $control['params'] );
		ob_end_clean();

		break;
	}

	$sidebars_widgets[$sidebar_id] = $sidebar;

	// remove old position
	if ( !isset($_POST['delete_widget']) ) {
		foreach ( $sidebars_widgets as $key => $sb ) {
			if ( is_array($sb) )
				$sidebars_widgets[$key] = array_diff( $sb, array($widget_id) );
		}
		array_splice( $sidebars_widgets[$sidebar_id], $position, 0, $widget_id );
	}

	wp_set_sidebars_widgets($sidebars_widgets);
	wp_redirect( admin_url('widgets.php?message=0') );
	exit;
}

// Output the widget form without js
if ( isset($_GET['editwidget']) && $_GET['editwidget'] ) {
	$widget_id = $_GET['editwidget'];

	if ( isset($_GET['addnew']) ) {
		// Default to the first sidebar
		$sidebar = array_shift( $keys = array_keys($wp_registered_sidebars) );

		if ( isset($_GET['base']) && isset($_GET['num']) ) { // multi-widget
			// Copy minimal info from an existing instance of this widget to a new instance
			foreach ( $wp_registered_widget_controls as $control ) {
				if ( $_GET['base'] === $control['id_base'] ) {
					$control_callback = $control['callback'];
					$multi_number = (int) $_GET['num'];
					$control['params'][0]['number'] = -1;
					$widget_id = $control['id'] = $control['id_base'] . '-' . $multi_number;
					$wp_registered_widget_controls[$control['id']] = $control;
					break;
				}
			}
		}
	}

	if ( isset($wp_registered_widget_controls[$widget_id]) && !isset($control) ) {
		$control = $wp_registered_widget_controls[$widget_id];
		$control_callback = $control['callback'];
	} elseif ( !isset($wp_registered_widget_controls[$widget_id]) && isset($wp_registered_widgets[$widget_id]) ) {
		$name = esc_html( strip_tags($wp_registered_widgets[$widget_id]['name']) );
	}

	if ( !isset($name) )
		$name = esc_html( strip_tags($control['name']) );

	if ( !isset($sidebar) )
		$sidebar = isset($_GET['sidebar']) ? $_GET['sidebar'] : 'wp_inactive_widgets';

	if ( !isset($multi_number) )
		$multi_number = isset($control['params'][0]['number']) ? $control['params'][0]['number'] : '';

	$id_base = isset($control['id_base']) ? $control['id_base'] : $control['id'];

	// show the widget form
	$width = ' style="width:' . max($control['width'], 350) . 'px"';
	$key = isset($_GET['key']) ? (int) $_GET['key'] : 0;

	require_once( './admin-header.php' ); ?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( $title ); ?></h2>
	<div class="editwidget"<?php echo $width; ?>>
	<h3><?php printf( __( 'Widget %s' ), $name ); ?></h3>

	<form action="widgets.php" method="post">
	<div class="widget-inside">
<?php
	if ( is_callable( $control_callback ) )
		call_user_func_array( $control_callback, $control['params'] );
	else
		echo '<p>' . __('There are no options for this widget.') . "</p>\n"; ?>
	</div>

	<p class="describe"><?php _e('Select both the sidebar for this widget and the position of the widget in that sidebar.'); ?></p>
	<div class="widget-position">
	<table class="widefat"><thead><tr><th><?php _e('Sidebar'); ?></th><th><?php _e('Position'); ?></th></tr></thead><tbody>
<?php
	foreach ( $wp_registered_sidebars as $sbname => $sbvalue ) {
		echo "\t\t<tr><td><label><input type='radio' name='sidebar' value='" . esc_attr($sbname) . "'" . checked( $sbname, $sidebar, false ) . " /> $sbvalue[name]</label></td><td>";
		if ( 'wp_inactive_widgets' == $sbname || 'orphaned_widgets' == substr( $sbname, 0, 16 ) ) {
			echo '&nbsp;';
		} else {
			if ( !isset($sidebars_widgets[$sbname]) || !is_array($sidebars_widgets[$sbname]) ) {
				$j = 1;
				$sidebars_widgets[$sbname] = array();
			} else {
				$j = count($sidebars_widgets[$sbname]);
				if ( isset($_GET['addnew']) || !in_array($widget_id, $sidebars_widgets[$sbname], true) )
					$j++;
			}
			$selected = '';
			echo "\t\t<select name='{$sbname}_position'>\n";
			echo "\t\t<option value=''>" . __('&mdash; Select &mdash;') . "</option>\n";
			for ( $i = 1; $i <= $j; $i++ ) {
				if ( in_array($widget_id, $sidebars_widgets[$sbname], true) )
					$selected = selected( $i, $key + 1, false );
				echo "\t\t<option value='$i'$selected> $i </option>\n";
			}
			echo "\t\t</select>\n";
		}
		echo "</td></tr>\n";
	} ?>
	</tbody></table>
	</div>

	<div class="widget-control-actions">
<?php
	if ( isset($_GET['addnew']) ) { ?>
	<a href="widgets.php" class="button alignleft"><?php _e('Cancel'); ?></a>
<?php
	} else {
		submit_button( __( 'Delete' ), 'button alignleft', 'removewidget', false );
	}
	submit_button( __( 'Save Widget' ), 'button-primary alignright', 'savewidget', false ); ?>
	<input type="hidden" name="widget-id" class="widget-id" value="<?php echo esc_attr($widget_id); ?>" />
	<input type="hidden" name="id_base" class="id_base" value="<?php echo esc_attr($id_base); ?>" />
	<input type="hidden" name="multi_number" class="multi_number" value="<?php echo esc_attr($multi_number); ?>" />
<?php	wp_nonce_field("save-delete-widget-$widget_id"); ?>
	<br class="clear" />
	</div>
	</form>
	</div>
	</div>
<?php
	require_once( './admin-footer.php' );
	exit;
}

$messages = array(
	__('Changes saved.')
);

$errors = array(
	__('Error while saving.'),
	__('Error in displaying the widget settings form.')
);

require_once( './admin-header.php' ); ?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<?php if ( isset($_GET['message']) && isset($messages[$_GET['message']]) ) { ?>
<div id="message" class="updated"><p><?php echo $messages[$_GET['message']]; ?></p></div>
<?php } ?>
<?php if ( isset($_GET['error']) && isset($errors[$_GET['error']]) ) { ?>
<div id="message" class="error"><p><?php echo $errors[$_GET['error']]; ?></p></div>
<?php } ?>

<?php do_action( 'widgets_admin_page' ); ?>

<div class="widget-liquid-left">
<div id="widgets-left">
	<div id="available-widgets" class="widgets-holder-wrap">
		<div class="sidebar-name">
		<div class="sidebar-name-arrow"><br /></div>
		<h3><?php _e('Available Widgets'); ?> <span id="removing-widget"><?php _ex('Deactivate', 'removing-widget'); ?> <span></span></span></h3></div>
		<div class="widget-holder">
		<p class="description"><?php _e('Drag widgets from here to a sidebar on the right to activate them. Drag widgets back here to deactivate them and delete their settings.'); ?></p>
		<div id="widget-list">
		<?php wp_list_widgets(); ?>
		</div>
		<br class='clear' />
		</div>
		<br class="clear" />
	</div>

<?php
foreach ( $wp_registered_sidebars as $sidebar => $registered_sidebar ) {
	if ( false !== strpos( $registered_sidebar['class'], 'inactive-sidebar' ) || 'orphaned_widgets' == substr( $sidebar, 0, 16 ) ) {
		$wrap_class = 'widgets-holder-wrap';
		if ( !empty( $registered_sidebar['class'] ) )
			$wrap_class .= ' ' . $registered_sidebar['class'];

?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"><br /></div>
				<h3><?php echo esc_html( $registered_sidebar['name'] ); ?>
					<span class="spinner"></span>
				</h3>
			</div>
			<div class="widget-holder inactive">
				<?php wp_list_widget_controls( $registered_sidebar['id'] ); ?>
				<div class="clear"></div>
			</div>
		</div>
<?php
	}
}
?>

</div>
</div>

<div class="widget-liquid-right">
<div id="widgets-right">
<?php
$i = 0;
foreach ( $wp_registered_sidebars as $sidebar => $registered_sidebar ) {
	if ( false !== strpos( $registered_sidebar['class'], 'inactive-sidebar' ) || 'orphaned_widgets' == substr( $sidebar, 0, 16 ) )
		continue;

	$wrap_class = 'widgets-holder-wrap';
	if ( !empty( $registered_sidebar['class'] ) )
		$wrap_class .= ' sidebar-' . $registered_sidebar['class'];

	if ( $i )
		$wrap_class .= ' closed'; ?>

	<div class="<?php echo esc_attr( $wrap_class ); ?>">
	<div class="sidebar-name">
	<div class="sidebar-name-arrow"><br /></div>
	<h3><?php echo esc_html( $registered_sidebar['name'] ); ?>
	<span class="spinner"></span></h3></div>
	<?php wp_list_widget_controls( $sidebar ); // Show the control forms for each of the widgets in this sidebar ?>
	</div>
<?php
	$i++;
} ?>
</div>
</div>
<form action="" method="post">
<?php wp_nonce_field( 'save-sidebar-widgets', '_wpnonce_widgets', false ); ?>
</form>
<br class="clear" />
</div>

<?php
do_action( 'sidebar_admin_page' );
require_once( './admin-footer.php' );
