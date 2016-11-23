<?php
/*
Plugin Name: WordCamp Talks
Plugin URI: https://github.com/imath/wordcamp-talks/
Description: A WordCamp Talk Submission System
Version: 1.0.0-alpha
Requires at least: 4.6
Tested up to: 4.6.1
License: GNU/GPL 2
Author: imath
Author URI: http://imathi.eu/
Text Domain: wordcamp-talks
Domain Path: /languages/
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WordCamp_Talks' ) ) :
/**
 * Main plugin's class
 *
 * Sets the needed globalized vars, includes the required
 * files and registers post type stuff.
 *
 * @package WordCamp Talks
 *
 * @since 1.0.0
 */
final class WordCamp_Talks {

	/**
	 * Plugin's main instance
	 * @var object
	 */
	protected static $instance;

	/**
	 * Initialize the plugin
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setups plugin's globals
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		// Version
		$this->version = '1.0.0-alpha';

		// Domain
		$this->domain = 'wordcamp-talks';

		// Base name
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'wct_plugin_basename', plugin_basename( $this->file ) );

		// Path and URL
		$this->plugin_dir = apply_filters( 'wct_plugin_dir_path', plugin_dir_path( $this->file                     ) );
		$this->plugin_url = apply_filters( 'wct_plugin_dir_url',  plugin_dir_url ( $this->file                     ) );
		$this->js_url     = apply_filters( 'wct_js_url',          trailingslashit( $this->plugin_url . 'js'        ) );
		$this->lang_dir   = apply_filters( 'wct_lang_dir',        trailingslashit( $this->plugin_dir . 'languages' ) );

		// Includes
		$this->includes_dir = apply_filters( 'wct_includes_dir_path', trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url = apply_filters( 'wct_includes_dir_url',  trailingslashit( $this->plugin_url . 'includes'  ) );

		// Default templates location (can be overridden from theme or child theme)
		$this->templates_dir = apply_filters( 'wct_templates_dir_path', trailingslashit( $this->plugin_dir . 'templates'  ) );

		// Post types / taxonomies default ids
		$this->post_type = 'talks';
		$this->category  = 'talk_categories';
		$this->tag       = 'talk_tags';

		// Pretty links ?
		$this->pretty_links = get_option( 'permalink_structure' );

		// template globals
		$this->is_talks    = false;
		$this->template_file    = false;
		$this->main_query       = array();
		$this->query_loop       = false;
		$this->per_page         = get_option( 'posts_per_page' );
		$this->is_talks_archive  = false;
		$this->is_category      = false;
		$this->is_tag           = false;
		$this->current_term     = false;
		$this->is_user          = false;
		$this->is_user_rates    = false;
		$this->is_user_comments = false;
		$this->is_action        = false;
		$this->is_new           = false;
		$this->is_edit          = false;
		$this->is_search        = false;
		$this->orderby          = false;
		$this->needs_reset      = false;

		// User globals
		$this->displayed_user   = new WP_User();
		$this->current_user     = new WP_User();
		$this->feedback         = array();
	}

	/**
	 * Includes plugin's needed files
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 *
	 * @uses  is_admin() to check for WordPress Administration
	 */
	private function includes() {
		require( $this->includes_dir . 'core/options.php' );
		require( $this->includes_dir . 'core/functions.php' );
		require( $this->includes_dir . 'core/rewrites.php' );
		require( $this->includes_dir . 'core/classes.php' );
		require( $this->includes_dir . 'core/capabilities.php' );
		require( $this->includes_dir . 'core/upgrade.php' );
		require( $this->includes_dir . 'core/template-functions.php' );
		require( $this->includes_dir . 'core/template-loader.php' );
		require( $this->includes_dir . 'core/widgets.php' );

		require( $this->includes_dir . 'comments/functions.php' );
		require( $this->includes_dir . 'comments/classes.php' );
		require( $this->includes_dir . 'comments/tags.php' );
		require( $this->includes_dir . 'comments/widgets.php' );

		require( $this->includes_dir . 'talks/functions.php' );
		require( $this->includes_dir . 'talks/classes.php' );
		require( $this->includes_dir . 'talks/tags.php' );
		require( $this->includes_dir . 'talks/widgets.php' );

		require( $this->includes_dir . 'users/functions.php' );
		require( $this->includes_dir . 'users/tags.php' );
		require( $this->includes_dir . 'users/widgets.php' );

		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );

		if ( is_admin() ) {
			require( $this->includes_dir . 'admin/admin.php' );
		}

		/**
		 * Add specific functions for the current site
		 */
		if ( file_exists( WP_PLUGIN_DIR . '/wct-functions.php' ) ) {
			require( WP_PLUGIN_DIR . '/wct-functions.php' );
		}

		/**
		 * On multisite configs, load current blog's specific functions
		 */
		if ( is_multisite() && file_exists( WP_PLUGIN_DIR . '/wct-' . get_current_blog_id() . '- functions.php' ) ) {
			require( WP_PLUGIN_DIR . '/wct-' . get_current_blog_id() . '- functions.php' );
		}
	}

	/**
	 * Setups some hooks to register post type stuff, scripts, set
	 * the current user.
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_action() to perform custom actions at key points
	 */
	private function setup_hooks() {
		// Main hooks
		add_action( 'wct_loaded',              array( $this, 'load_textdomain'     ), 0 );
		add_action( 'wct_register_post_types', array( $this, 'register_post_type'  )    );
		add_action( 'wct_register_taxonomies', array( $this, 'register_taxonomies' )    );
		add_action( 'wct_setup_current_user',  array( $this, 'setup_current_user'  )    );
		add_action( 'wct_enqueue_scripts',     array( $this, 'enqueue_scripts'     ), 1 );
	}

	/**
	 * Registers the Talks post type
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {
		//register the Talks post-type
		register_post_type(
			wct_get_post_type(),
			array_merge(
				wct_post_type_register_labels(),
				wct_post_type_register_args()
			)
		);

		// Register a private utility post type
		register_post_type(
			'wct_utility',
			array(
				'label'              => 'wct_utility',
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'show_in_nav_menus'  => false,
				'query_var'          => false,
				'rewrite'            => false,
				'has_archive'        => false,
				'hierarchical'       => true,
			)
		);
	}

	/**
	 * Registers the Talks taxonomies
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	public function register_taxonomies() {

		// Register the category taxonomy
		register_taxonomy(
			wct_get_category(),
			wct_get_post_type(),
			array_merge(
				wct_category_register_labels(),
				wct_category_register_args()
			)
		);

		// Register the tag taxonomy
		register_taxonomy(
			wct_get_tag(),
			wct_get_post_type(),
			array_merge(
				wct_tag_register_labels(),
				wct_tag_register_args()
			)
		);
	}

	/**
	 * Setups the loggedin user
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	public function setup_current_user() {
		$this->current_user = wp_get_current_user();
	}

	/**
	 * Setups a globalized var for a later use
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 *
	 * @param string $var   The key to access to the globalized var
	 * @param mixed  $value The value of the globalized var
	 */
	public function set_global( $var = '', $value = null ) {
		if ( empty( $var ) || empty( $value ) ) {
			return false;
		}

		$this->{$var} = $value;
	}

	/**
	 * Gets a globalized var
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 *
	 * @param  string $var the key to access to the globalized var
	 * @return mixed       the value of the globalized var
	 */
	public function get_global( $var = '' ) {
		if ( empty( $var ) || empty( $this->{$var} ) ) {
			return false;
		}

		return $this->{$var};
	}

	/**
	 * Registers external javascript libraries to be linked later
	 * using the wp_enqueue_script() function, & adds the plugin's stylesheet
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! wct_is_talks() ) {
			return;
		}

		// Register jquery Raty
		wp_register_script( 'jquery-raty', wct_get_js_script( 'jquery.raty' ), array( 'jquery' ), '2.7.0.imath', true );

		// Register tagging
		wp_register_script( 'tagging', wct_get_js_script( 'tagging' ), array( 'jquery' ), '1.3.1', true );

		wct_enqueue_style();
	}

	/**
	 * Loads the translation files
	 *
	 * @package WordCamp Talks
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		// Use regular locale
		if ( ! function_exists( 'get_user_locale' ) ) {
			// Look in global /wp-content/languages/plugins/
			load_plugin_textdomain( $this->domain );

		// Use user locale instead
		} else {
			/**
			 * Filter here to edit this plugin locale.
			 *
			 * @since 1.0.0
			 *
			 * @param string $value  The locale.
			 * @param string $domain The plugin domain.
			 */
			$locale = apply_filters( 'wordcamp_talks_locale', get_user_locale(), $this->domain );

			if ( empty( $locale ) ) {
				$mofile = $this->domain . '.mo';
			} else {
				$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );
			}

			/**
			 * Filter here to use another dir than the regular plugin lang dir
			 *
			 * @since 1.0.0
			 *
			 * @param string $value  Absolute path to the mo file.
			 * @param string $mofile The mofile file name.
			 * @param string $locale The current locale.
			 */
			$mofile_dir = apply_filters( 'wordcamp_talks_lang_dir', $this->lang_dir . $mofile, $mofile, $locale );

			// Try to see if a GlotPress generated language is available first.
			if ( ! load_textdomain( $this->domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
				load_textdomain( $this->domain, $mofile_dir );
			}
		}
	}
}

endif;

/**
 * Plugin's Bootstrap Function
 *
 * @package WordCamp Talks
 *
 * @since 1.0.0
 */
function wct() {
	return WordCamp_Talks::start();
}
add_action( 'plugins_loaded', 'wct' );
