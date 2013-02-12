<?php
/**
 * Theme Installer List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
class WP_Theme_Install_List_Table extends WP_Themes_List_Table {

	var $features = array();

	function ajax_user_can() {
		return current_user_can( 'install_themes' );
	}

	function prepare_items() {
		include( ABSPATH . 'wp-admin/includes/theme-install.php' );

		global $tabs, $tab, $paged, $type, $theme_field_defaults;
		wp_reset_vars( array( 'tab' ) );

		$search_terms = array();
		$search_string = '';
		if ( ! empty( $_REQUEST['s'] ) ){
			$search_string = strtolower( stripslashes( $_REQUEST['s'] ) );
			$search_terms = array_unique( array_filter( array_map( 'trim', explode( ',', $search_string ) ) ) );
		}

		if ( ! empty( $_REQUEST['features'] ) )
			$this->features = $_REQUEST['features'];

		$paged = $this->get_pagenum();

		$per_page = 36;

		// These are the tabs which are shown on the page,
		$tabs = array();
		$tabs['dashboard'] = __( 'Search' );
		if ( 'search' == $tab )
			$tabs['search']	= __( 'Search Results' );
		$tabs['upload'] = __( 'Upload' );
		$tabs['featured'] = _x( 'Featured','Theme Installer' );
		//$tabs['popular']  = _x( 'Popular','Theme Installer' );
		$tabs['new']      = _x( 'Newest','Theme Installer' );
		$tabs['updated']  = _x( 'Recently Updated','Theme Installer' );

		$nonmenu_tabs = array( 'theme-information' ); // Valid actions to perform which do not have a Menu item.

		$tabs = apply_filters( 'install_themes_tabs', $tabs );
		$nonmenu_tabs = apply_filters( 'install_themes_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And its not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$args = array( 'page' => $paged, 'per_page' => $per_page, 'fields' => $theme_field_defaults );

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? stripslashes( $_REQUEST['type'] ) : 'term';
				switch ( $type ) {
					case 'tag':
						$args['tag'] = array_map( 'sanitize_key', $search_terms );
						break;
					case 'term':
						$args['search'] = $search_string;
						break;
					case 'author':
						$args['author'] = $search_string;
						break;
				}

				if ( ! empty( $this->features ) ) {
					$args['tag'] = $this->features;
					$_REQUEST['s'] = implode( ',', $this->features );
					$_REQUEST['type'] = 'tag';
				}

				add_action( 'install_themes_table_header', 'install_theme_search_form', 10, 0 );
				break;

			case 'featured':
			//case 'popular':
			case 'new':
			case 'updated':
				$args['browse'] = $tab;
				break;

			default:
				$args = false;
		}

		if ( ! $args )
			return;

		$api = themes_api( 'query_themes', $args );

		if ( is_wp_error( $api ) )
			wp_die( $api->get_error_message() . '</p> <p><a href="#" onclick="document.location.reload(); return false;">' . __( 'Try again' ) . '</a>' );

		$this->items = $api->themes;

		$this->set_pagination_args( array(
			'total_items' => $api->info['results'],
			'per_page' => $per_page,
			'infinite_scroll' => true,
		) );
	}

	function no_items() {
		_e( 'No themes match your request.' );
	}

	function get_views() {
		global $tabs, $tab;

		$display_tabs = array();
		foreach ( (array) $tabs as $action => $text ) {
			$class = ( $action == $tab ) ? ' class="current"' : '';
			$href = self_admin_url('theme-install.php?tab=' . $action);
			$display_tabs['theme-install-'.$action] = "<a href='$href'$class>$text</a>";
		}

		return $display_tabs;
	}

	function display() {
		wp_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
?>
		<div class="tablenav top themes">
			<div class="alignleft actions">
				<?php do_action( 'install_themes_table_header' ); ?>
			</div>
			<?php $this->pagination( 'top' ); ?>
			<br class="clear" />
		</div>

		<div id="availablethemes">
			<?php $this->display_rows_or_placeholder(); ?>
		</div>

		<?php
		parent::tablenav( 'bottom' );
	}

	function display_rows() {
		$themes = $this->items;
		foreach ( $themes as $theme ) {
				?>
				<div class="available-theme installable-theme"><?php
					$this->single_row( $theme );
				?></div>
		<?php } // end foreach $theme_names

		$this->theme_installer();
	}

	/*
	 * Prints a theme from the WordPress.org API.
	 *
	 * @param object $theme An object that contains theme data returned by the WordPress.org API.
	 *
	 * Example theme data:
	 *   object(stdClass)[59]
	 *     public 'name' => string 'Magazine Basic' (length=14)
	 *     public 'slug' => string 'magazine-basic' (length=14)
	 *     public 'version' => string '1.1' (length=3)
	 *     public 'author' => string 'tinkerpriest' (length=12)
	 *     public 'preview_url' => string 'http://wp-themes.com/?magazine-basic' (length=36)
	 *     public 'screenshot_url' => string 'http://wp-themes.com/wp-content/themes/magazine-basic/screenshot.png' (length=68)
	 *     public 'rating' => float 80
	 *     public 'num_ratings' => int 1
	 *     public 'homepage' => string 'http://wordpress.org/extend/themes/magazine-basic' (length=49)
	 *     public 'description' => string 'A basic magazine style layout with a fully customizable layout through a backend interface. Designed by <a href="http://bavotasan.com">c.bavota</a> of <a href="http://tinkerpriestmedia.com">Tinker Priest Media</a>.' (length=214)
	 *     public 'download_link' => string 'http://wordpress.org/extend/themes/download/magazine-basic.1.1.zip' (length=66)
	 */
	function single_row( $theme ) {
		global $themes_allowedtags;

		if ( empty( $theme ) )
			return;

		$name   = wp_kses( $theme->name,   $themes_allowedtags );
		$author = wp_kses( $theme->author, $themes_allowedtags );

		$preview_title = sprintf( __('Preview &#8220;%s&#8221;'), $name );
		$preview_url   = add_query_arg( array(
			'tab'   => 'theme-information',
			'theme' => $theme->slug,
		) );

		$actions = array();

		$install_url = add_query_arg( array(
			'action' => 'install-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$status = $this->_get_theme_status( $theme );

		switch ( $status ) {
			default:
			case 'install':
				$actions[] = '<a class="install-now" href="' . esc_url( wp_nonce_url( $install_url, 'install-theme_' . $theme->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Install %s' ), $name ) ) . '">' . __( 'Install Now' ) . '</a>';
				break;
			case 'update_available':
				$actions[] = '<a class="install-now" href="' . esc_url( wp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ), $theme->version ) ) . '">' . __( 'Update' ) . '</a>';
				break;
			case 'newer_installed':
			case 'latest_installed':
				$actions[] = '<span class="install-now" title="' . esc_attr__( 'This theme is already installed and is up to date' ) . '">' . _x( 'Installed', 'theme' ) . '</span>';
				break;
		}

		$actions[] = '<a class="install-theme-preview" href="' . esc_url( $preview_url ) . '" title="' . esc_attr( sprintf( __( 'Preview %s' ), $name ) ) . '">' . __( 'Preview' ) . '</a>';

		$actions = apply_filters( 'theme_install_actions', $actions, $theme );

		?>
		<a class="screenshot install-theme-preview" href="<?php echo esc_url( $preview_url ); ?>" title="<?php echo esc_attr( $preview_title ); ?>">
			<img src='<?php echo esc_url( $theme->screenshot_url ); ?>' width='150' />
		</a>

		<h3><?php echo $name; ?></h3>
		<div class="theme-author"><?php printf( __( 'By %s' ), $author ); ?></div>

		<div class="action-links">
			<ul>
				<?php foreach ( $actions as $action ): ?>
					<li><?php echo $action; ?></li>
				<?php endforeach; ?>
				<li class="hide-if-no-js"><a href="#" class="theme-detail"><?php _e('Details') ?></a></li>
			</ul>
		</div>

		<?php
		$this->install_theme_info( $theme );
	}

	/*
	 * Prints the wrapper for the theme installer.
	 */
	function theme_installer() {
		?>
		<div id="theme-installer" class="wp-full-overlay expanded">
			<div class="wp-full-overlay-sidebar">
				<div class="wp-full-overlay-header">
					<a href="#" class="close-full-overlay"><?php _e( '&larr; Close' ); ?></a>
				</div>
				<div class="wp-full-overlay-sidebar-content">
					<div class="install-theme-info"></div>
				</div>
				<div class="wp-full-overlay-footer">
					<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar'); ?>">
						<span class="collapse-sidebar-label"><?php _e('Collapse'); ?></span>
						<span class="collapse-sidebar-arrow"></span>
					</a>
				</div>
			</div>
			<div class="wp-full-overlay-main"></div>
		</div>
		<?php
	}

	/*
	 * Prints the wrapper for the theme installer with a provided theme's data.
	 * Used to make the theme installer work for no-js.
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 */
	function theme_installer_single( $theme ) {
		?>
		<div id="theme-installer" class="wp-full-overlay single-theme">
			<div class="wp-full-overlay-sidebar">
				<?php $this->install_theme_info( $theme ); ?>
			</div>
			<div class="wp-full-overlay-main">
				<iframe src="<?php echo esc_url( $theme->preview_url ); ?>"></iframe>
			</div>
		</div>
		<?php
	}

	/*
	 * Prints the info for a theme (to be used in the theme installer modal).
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 */
	function install_theme_info( $theme ) {
		global $themes_allowedtags;

		if ( empty( $theme ) )
			return;

		$name   = wp_kses( $theme->name,   $themes_allowedtags );
		$author = wp_kses( $theme->author, $themes_allowedtags );

		$num_ratings = sprintf( _n( '(based on %s rating)', '(based on %s ratings)', $theme->num_ratings ), number_format_i18n( $theme->num_ratings ) );

		$install_url = add_query_arg( array(
			'action' => 'install-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$status = $this->_get_theme_status( $theme );

		?>
		<div class="install-theme-info"><?php
			switch ( $status ) {
				default:
				case 'install':
					echo '<a class="theme-install button-primary" href="' . esc_url( wp_nonce_url( $install_url, 'install-theme_' . $theme->slug ) ) . '">' . __( 'Install' ) . '</a>';
					break;
				case 'update_available':
					echo '<a class="theme-install button-primary" href="' . esc_url( wp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ), $theme->version ) ) . '">' . __( 'Update' ) . '</a>';
					break;
				case 'newer_installed':
				case 'latest_installed':
					echo '<span class="theme-install" title="' . esc_attr__( 'This theme is already installed and is up to date' ) . '">' . _x( 'Installed', 'theme' ) . '</span>';
					break;
			} ?>
			<h3 class="theme-name"><?php echo $name; ?></h3>
			<span class="theme-by"><?php printf( __( 'By %s' ), $author ); ?></span>
			<?php if ( isset( $theme->screenshot_url ) ): ?>
				<img class="theme-screenshot" src="<?php echo esc_url( $theme->screenshot_url ); ?>" />
			<?php endif; ?>
			<div class="theme-details">
				<div class="star-holder" title="<?php echo esc_attr( $num_ratings ); ?>">
					<div class="star-rating" style="width:<?php echo esc_attr( intval( $theme->rating ) . 'px' ); ?>;"></div>
				</div>
				<div class="theme-version">
					<strong><?php _e('Version:') ?> </strong>
					<?php echo wp_kses( $theme->version, $themes_allowedtags ); ?>
				</div>
				<div class="theme-description">
					<?php echo wp_kses( $theme->description, $themes_allowedtags ); ?>
				</div>
			</div>
			<input class="theme-preview-url" type="hidden" value="<?php echo esc_url( $theme->preview_url ); ?>" />
		</div>
		<?php
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @since 3.4
	 * @access private
	 *
	 * @uses $tab Global; current tab within Themes->Install screen
	 * @uses $type Global; type of search.
	 */
	function _js_vars() {
		global $tab, $type;
		parent::_js_vars( compact( 'tab', 'type' ) );
	}

	/**
	 * Check to see if the theme is already installed.
	 *
	 * @since 3.4
	 * @access private
	 *
	 * @param object $theme - A WordPress.org Theme API object.
	 * @return string Theme status.
	 */
	private function _get_theme_status( $theme ) {
		$status = 'install';

		$installed_theme = wp_get_theme( $theme->slug );
		if ( $installed_theme->exists() ) {
			if ( version_compare( $installed_theme->get('Version'), $theme->version, '=' ) )
				$status = 'latest_installed';
			elseif ( version_compare( $installed_theme->get('Version'), $theme->version, '>' ) )
				$status = 'newer_installed';
			else
				$status = 'update_available';
		}

		return $status;
	}
}
