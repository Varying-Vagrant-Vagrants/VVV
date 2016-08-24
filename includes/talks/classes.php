<?php
/**
 * WordCamp Talks classes.
 *
 * @package WordCamp Talks
 * @subpackage talks/classes
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Talk Class ****************************************************************/

if ( ! class_exists( 'WordCamp_Talks_Talk' ) ) :
/**
 * Talk Class.
 *
 * @package WordCamp Talks
 * @subpackage core/classes
 *
 * @since 1.0.0
 */
class WordCamp_Talks_Talk {

	/**
	 * The ID of the talk
	 *
	 * @access  public
	 * @var     integer
	 */
	public $id;

	/**
	 * The name of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $name;

	/**
	 * The ID of the author
	 *
	 * @access  public
	 * @var     integer
	 */
	public $author;

	/**
	 * The title of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $title;

	/**
	 * The content of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $description;

	/**
	 * The status of the talk
	 *
	 * @access  public
	 * @var     string
	 */
	public $status;

	/**
	 * Associative Array containing terms for
	 * the tag and category taxonomies
	 *
	 * @access  public
	 * @var     array
	 */
	public $taxonomies;

	/**
	 * Associative Array meta_key => meta_value
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * Constructor.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|string ID or name of the talk
	 */
	function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			if ( is_numeric( $id ) ) {
				$this->id = $id;
			} else {
				$this->name = $id;
			}
			$this->populate();
		}
	}

	/**
	 * Get an talk
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0)
	 */
	public function populate() {

		if ( empty( $this->id ) ) {
			// Let's try to get an ID thanks to its name.
			if ( ! empty( $this->name ) ) {
				$this->talk = self::get_talk_by_name( $this->name );
			}
		} else {
			$this->talk  = get_post( $this->id );
		}

		$this->id          = $this->talk->ID;
		$this->author      = $this->talk->post_author;
		$this->title       = $this->talk->post_title;
		$this->description = $this->talk->post_content;
		$this->status      = $this->talk->post_status;

		// Build an array of taxonomies
		$this->taxonomies = array();

		// Look in categories
		$categories = wp_get_object_terms( $this->id, wct_get_category(), array( 'fields' => 'ids' ) );

		if ( ! empty( $categories ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wct_get_category() => $categories,
			) );
		}

		// Look in tags
		$tags = wp_get_object_terms( $this->id, wct_get_tag(), array( 'fields' => 'slugs' ) );

		if ( ! empty( $tags ) ) {
			$this->taxonomies = array_merge( $this->taxonomies, array(
				wct_get_tag() => join( ',', $tags )
			) );
		}

		// Build an array of post metas
		$this->metas = array();

		$metas = get_post_custom( $this->id );

		foreach ( $metas as $key => $meta ) {
			if ( false === strpos( $key, '_wc_talks_' ) ) {
				continue;
			}

			$wctalks_key = str_replace( '_wc_talks_', '', $key );

			if ( count( $meta ) == 1 ) {
				$this->metas[ $wctalks_key ] = maybe_unserialize( $meta[0] );
			} else {
				$this->metas[ $wctalks_key ] = array_map( 'maybe_unserialize', $meta );
			}

			$this->metas['keys'][] = $wctalks_key;
		}
	}

	/**
	 * Save an talk.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	public function save() {
		$this->id          = apply_filters_ref_array( 'wct_id_before_save',          array( $this->id,          &$this ) );
		$this->author      = apply_filters_ref_array( 'wct_author_before_save',      array( $this->author,      &$this ) );
		$this->title       = apply_filters_ref_array( 'wct_title_before_save',       array( $this->title,       &$this ) );
		$this->description = apply_filters_ref_array( 'wct_description_before_save', array( $this->description, &$this ) );
		$this->status      = apply_filters_ref_array( 'wct_status_before_save',      array( $this->status,      &$this ) );
		$this->taxonomies  = apply_filters_ref_array( 'wct_taxonomies_before_save',  array( $this->taxonomies,  &$this ) );
		$this->metas       = apply_filters_ref_array( 'wct_metas_before_save',       array( $this->metas,       &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'wct_before_save', array( &$this ) );

		if ( empty( $this->author ) || empty( $this->title ) ) {
			return false;
		}

		if ( empty( $this->status ) ) {
			$this->status = 'publish';
		}

		$post_args = array(
			'post_author'  => $this->author,
			'post_title'   => $this->title,
			'post_type'    => wct_get_post_type(),
			'post_content' => $this->description,
			'post_status'  => $this->status,
			'tax_input'    => $this->taxonomies,
		);

		// Update.
		if ( $this->id ) {
			$post_args = array_merge( array(
				'ID' => $this->id,
			), $post_args );

			$result = wp_update_post( $post_args );
		// Insert.
		} else {
			$result = wp_insert_post( $post_args );
		}

		if ( ! empty( $result ) && ! empty( $this->metas ) ) {

			foreach ( $this->metas as $meta_key => $meta_value ) {
				// Do not update these keys.
				$skip_keys = apply_filters( 'wct_meta_key_skip_save', array( 'keys', 'rates', 'average_rate' ) );
				if ( in_array( $meta_key, $skip_keys ) ) {
					continue;
				}

				if ( empty( $meta_value ) ) {
					wct_talks_delete_meta( $result, $meta_key );
				} else {
					wct_talks_update_meta( $result, $meta_key, $meta_value );
				}
			}
		}

		do_action_ref_array( 'wct_after_save', array( $result, &$this ) );

		return $result;
	}

	/**
	 * The selection query
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args arguments to customize the query
	 * @return array associative array containing talks and total count.
	 */
	public static function get( $args = array() ) {

		$defaults = array(
			'author'     => 0,
			'per_page'   => 10,
			'page'       => 1,
			'search'     => '',
			'exclude'    => '',
			'include'    => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array(),
			'tax_query'  => array(),
		);

		$r = wp_parse_args( $args, $defaults );

		/**
		 * Allow status to be filtered
		 * @see wct_talks_get_status()
		 */
		$talks_status = wct_talks_get_status();

		$query_args = array(
			'post_status'    => $talks_status,
			'post_type'      => 'talks',
			'posts_per_page' => $r['per_page'],
			'paged'          => $r['page'],
			'orderby'        => $r['orderby'],
			'order'          => $r['order'],
			's'              => $r['search'],
		);

		if ( ! empty( $r['author'] ) ) {
			$query_args['author'] = $r['author'];
		}

		if ( ! empty( $r['exclude'] ) ) {
			$query_args['post__not_in'] = wp_parse_id_list( $r['exclude'] );
		}

		if ( ! empty( $r['include'] ) ) {
			$query_args['post__in'] = wp_parse_id_list( $r['include'] );
		}

		if ( 'rates_count' == $r['orderby'] ) {
			$r['meta_query'][] = array(
				'key'     => '_wc_talks_average_rate',
				'compare' => 'EXISTS'
			);
		}

		if ( ! empty( $r['meta_query'] ) ) {
			$query_args['meta_query'] = $r['meta_query'];
		}

		if ( ! empty( $r['tax_query'] ) ) {
			$query_args['tax_query'] = $r['tax_query'];
		}

		// Get the main order
		$main_order = wct_get_global( 'orderby' );

		// Apply the one requested
		wct_set_global( 'orderby', $r['orderby'] );

		$talks = new WP_Query( $query_args );

		// Reset to main order
		wct_set_global( 'orderby', $main_order );

		return array( 'talks' => $talks->posts, 'total' => $talks->found_posts );
	}

	/**
	 * Get an talk using its post name.
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @global $wpdb
	 * @param  string name of the talk
	 * @return WP_Post the talk object
	 */
	public static function get_talk_by_name( $name = '' ) {
		global $wpdb;

		$where = $wpdb->prepare( 'post_name = %s AND post_type = %s', $name, wct_get_post_type() );
		$id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE {$where}" );

		return get_post( $id );
	}
}

endif;

/** Talks Loop ****************************************************************/

if ( ! class_exists( 'WordCamp_Talks_Loop_Talks' ) ) :
/**
 * Talks loop Class.
 *
 * @package WordCamp Talks
 * @subpackage talk/tags
 *
 * @since 1.0.0
 */
class WordCamp_Talks_Loop_Talks extends WordCamp_Talks_Loop {

	/**
	 * Constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage talk/tags
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args the loop args
	 */
	public function __construct( $args = array() ) {

		if ( ! empty( $args ) && empty( $args['is_widget'] ) ) {
			$paged = get_query_var( 'paged' );

			// Set which pagination page
			if ( ! empty( $paged ) ) {
				$args['page'] = $paged;

			// Checking query string just in case
			} else if ( ! empty( $_GET['paged'] ) ) {
				$args['page'] = absint( $_GET['paged'] );

			// Checking in page args
			} else if ( ! empty( $args['page'] ) ) {
				$args['page'] = absint( $args['page'] );

			// Default to first page
			} else {
				$args['page'] = 1;
			}
		}

		// Only get the talk requested
		if ( ! empty( $args['talk_name'] ) ) {

			$query_loop = wct_get_global( 'query_loop' );

			if ( empty( $query_loop->talk ) ) {
				$talk  = wct_talks_get_talk_by_name( $args['talk_name'] );
			} else {
				$talk  = $query_loop->talk;
			}

			// can't do this too ealy
			$reset_data = array_merge( (array) $talk, array( 'is_page' => true ) );
			wct_reset_post( $reset_data );

			// this needs a "reset postdata"!
			wct_set_global( 'needs_reset', true );

			$talks = array(
				'talks'    => array( $talk ),
				'total'    => 1,
				'get_args' => array(
					'page'     => 1,
					'per_page' => 1,
				),
			);

		// Get the talks
		} else {
			$talks = wct_talks_get_talks( $args );
		}

		if ( ! empty( $talks['get_args'] ) ) {
			foreach ( $talks['get_args'] as $key => $value ) {
				$this->{$key} = $value;
			}
		} else {
			return false;
		}

		$params = array(
			'plugin_prefix'    => 'wct',
			'item_name'        => 'talk',
			'item_name_plural' => 'talks',
			'items'            => $talks['talks'],
			'total_item_count' => $talks['total'],
			'page'             => $this->page,
			'per_page'         => $this->per_page,
		);

		$paginate_args = array();

		// No pretty links
		if ( ! wct_is_pretty_links() ) {
			$paginate_args['base'] = add_query_arg( 'paged', '%#%' );

		} else {

			// Is it the main archive page ?
			if ( wct_is_talks_archive() ) {
				$base = trailingslashit( wct_get_root_url() ) . '%_%';

			// Or the category archive page ?
			} else if ( wct_is_category() ) {
				$base = trailingslashit( wct_get_category_url() ) . '%_%';

			// Or the tag archive page ?
			} else if ( wct_is_tag() ) {
				$base = trailingslashit( wct_get_tag_url() ) . '%_%';

			// Or the displayed user rated talks ?
			} else if ( wct_is_user_profile_rates() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url( 'rates' ) ) . '%_%';

			// Or the displayed user published talks ?
			} else if ( wct_is_user_profile_talks() ) {
				$base = trailingslashit( wct_users_get_displayed_profile_url() ) . '%_%';

			// Or nothing i've planed ?
			} else {

				/**
				 * Create your own pagination base if not handled by the plugin
				 *
				 * @param string empty string
				 */
				$base = apply_filters( 'wct_talks_pagination_base', '' );
			}

			$paginate_args['base']   = $base;
			$paginate_args['format'] = wct_paged_slug() . '/%#%/';
		}

		// Is this a search ?
		if ( wct_get_global( 'is_search' ) ) {
			$paginate_args['add_args'] = array( wct_search_rewrite_id() => $_GET[ wct_search_rewrite_id() ] );
		}

		// Do we have a specific order to use ?
		$orderby = wct_get_global( 'orderby' );

		if ( ! empty( $orderby ) && 'date' != $orderby ) {
			$merge = array();

			if ( ! empty( $paginate_args['add_args'] ) ) {
				$merge = $paginate_args['add_args'];
			}
			$paginate_args['add_args'] = array_merge( $merge, array( 'orderby' => $orderby ) );
		}

		/**
		 * Use this filter to override the pagination
		 *
		 * @param array $paginate_args the pagination arguments
		 */
		parent::start( $params, apply_filters( 'wct_talks_pagination_args', $paginate_args ) );
	}
}

endif;

if ( ! class_exists( 'WordCamp_Talk_Metas' ) ) :
/**
 * Talk metas Class.
 *
 * Tries to ease the process of managing custom fields for talks
 * @see  wct_talks_register_meta() talks/functions to
 * register new talk metas.
 *
 * @package WordCamp Talks
 * @subpackage talk/tags
 *
 * @since 1.0.0
 */
class WordCamp_Talk_Metas {

	/**
	 * List of meta objects
	 *
	 * @access  public
	 * @var     array
	 */
	public $metas;

	/**
	 * The constructor
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->do_metas();
	}

	/**
	 * Starts the class
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		$wct = wct();

		if ( empty( $wct->talk_metas ) ) {
			$wct->talk_metas = new self;
		}

		return $wct->talk_metas;
	}

	/**
	 * Checks if talk metas are registered and hooks to some key actions/filters
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 */
	private function do_metas() {
		$this->metas = wct_get_global( 'wc_talks_metas' );

		if ( empty( $this->metas ) || ! is_array( $this->metas ) ) {
			return;
		}

		/** Admin *********************************************************************/
		add_filter( 'wct_admin_get_meta_boxes', array( $this, 'register_metabox' ), 10, 1 );
		add_action( 'wct_save_metaboxes',       array( $this, 'save_metabox' ),     10, 3 );

		/** Front *********************************************************************/
		add_action( 'wct_talks_the_talk_meta_edit', array( $this, 'front_output'  ) );
		add_action( 'wct_before_talk_footer',       array( $this, 'single_output' ) );
	}

	/**
	 * Registers a new metabox for custom fields
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $metaboxes the metabox list
	 * @return array            the new list
	 */
	public function register_metabox( $metaboxes = array() ) {
		$metas_metabox = array(
			'wc_talks_metas' => array(
				'id'            => 'wct_metas_box',
				'title'         => __( 'Custom fields', 'wordcamp-talks' ),
				'callback'      => array( $this, 'do_metabox' ),
				'context'       => 'advanced',
				'priority'      => 'high'
		) );

		return array_merge( $metaboxes, $metas_metabox );
	}

	/**
	 * Outputs the fields in the Custom Field Talk metabox
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Post $talk the talk object
	 * @return string       HTML output
	 */
	public function do_metabox( $talk = null ) {
		if ( empty( $talk->ID ) || ! is_array( $this->metas ) ) {
			esc_html_e( 'No custom fields available', 'wordcamp-talks' );
			return;
		}

		$meta_list = array_keys( $this->metas );
		?>
		<div id="wc-talks_list_metas">
			<ul>
			<?php foreach ( $this->metas as $meta_object ) :?>
				<li id="wc-talks-meta-<?php echo esc_attr( $meta_object->meta_key );?>"><?php $this->display_meta( $talk->ID, $meta_object, 'admin' );?></li>
			<?php endforeach;?>
			</ul>

			<input type="hidden" value="<?php echo join( ',', $meta_list );?>" name="wct[meta_keys]"/>
		</div>
		<?php
		wp_nonce_field( 'admin-wc-talks-metas', '_admin_wc_talks_metas' );
	}

	/**
	 * Displays an talk's meta
	 *
	 * Used for forms (admin or front) and single outputs
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  int     $talk_id     the ID of the talk
	 * @param  object  $meta_object the meta object to send to callback function
	 * @param  string  $context     the context (admin/single/form)
	 * @return string               HTML Output
	 */
	public function display_meta( $talk_id = 0, $meta_object = null, $context = 'form' ) {
		// bail if no meta key
		if ( empty( $meta_object->meta_key ) ) {
			return;
		}

		$meta_object->field_name  = 'wct[_the_metas]['. $meta_object->meta_key .']';
		$meta_object->field_value = false;
		$meta_object->talk_id     = $talk_id;
		$display_meta             = '';

		if ( empty( $meta_object->label ) ) {
			$meta_object->label = ucfirst( str_replace( '_', ' ', $meta_object->meta_key ) );
		}

		if ( ! empty( $talk_id ) ) {
			$meta_object->field_value = wct_talks_get_meta( $talk_id, $meta_object->meta_key );
		}

		if ( empty( $meta_object->form ) ) {
			$meta_object->form = $meta_object->admin;
		}

		if ( 'single' == $context && empty( $meta_object->field_value ) ) {
			return;
		}

		if ( ! is_callable( $meta_object->{$context} ) ) {
			return;
		}

		// We apply the callback as an action
		add_action( 'wct_talks_meta_display', $meta_object->{$context}, 10, 3 );

		// Generate the output for the meta object
		do_action( 'wct_talks_meta_display', $display_meta, $meta_object, $context );

		// Remove the action for other metas
		remove_action( 'wct_talks_meta_display', $meta_object->{$context}, 10, 3 );
	}

	/**
	 * Saves the custom fields when edited from the admin screens (edit/post new)
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  int      $id     the talk ID
	 * @param  WP_Post  $talk   the talk object
	 * @param  bool     $update whether it's an update or not
	 * @return int         		the ID of the talk
	 */
	public function save_metabox( $id = 0, $talk = null, $update = false ) {
		// Bail if no meta to save
		if ( empty( $_POST['wct']['meta_keys'] ) )  {
			return $id;
		}

		check_admin_referer( 'admin-wc-talks-metas', '_admin_wc_talks_metas' );

		$the_metas = array();
		if ( ! empty( $_POST['wct']['_the_metas'] ) ) {
			$the_metas = $_POST['wct']['_the_metas'];
		}

		$meta_keys = explode( ',', $_POST['wct']['meta_keys'] );
		$meta_keys = array_map( 'sanitize_key', (array) $meta_keys );

		foreach ( $meta_keys as $meta_key ) {
			if ( empty( $the_metas[ $meta_key ] ) && wct_talks_get_meta( $id, $meta_key ) ) {
				wct_talks_delete_meta( $id, $meta_key );
			} else if ( ! empty( $the_metas[ $meta_key ] ) ) {
				wct_talks_update_meta( $id, $meta_key, $the_metas[ $meta_key ] );
			}
		}

		return $id;
	}

	/**
	 * Displays metas for form/single display
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 *
	 * @param  string $context the context (single/form)
	 * @return string          HTML Output
	 */
	public function front_output( $context = '' ) {
		if ( empty( $this->metas ) ) {
			return;
		}

		if ( empty( $context ) ) {
			$context = 'form';
		}

		$wct = wct();

		$talk_id = 0;

		if ( ! empty( $wct->query_loop->talk->ID ) ) {
			$talk_id = $wct->query_loop->talk->ID;
		}

		foreach ( $this->metas as $meta_object ) {
			$this->display_meta( $talk_id, $meta_object, $context );
		}
	}

	/**
	 * Displays metas for single display
	 *
	 * @package WordCamp Talks
	 * @subpackage talks/classes
	 *
	 * @since 1.0.0
	 * 
	 * @return string          HTML Output
	 */
	public function single_output() {
		if ( ! wct_is_single_talk() ) {
			return;
		}

		return $this->front_output( 'single' );
	}
}

endif;

if ( ! class_exists( 'WordCamp_Talks_Talks_Thumbnail' ) ) :
/**
 * Class to side upload Talk Thumbnails.
 *
 * @since 1.0.0
 */
class WordCamp_Talks_Talks_Thumbnail {
	protected static $instance = null;

	/**
	 * Set the class
	 *
	 * @param string $src the link to the image to side upload
	 * @param int    $post_id the ID of the post set the featured image for
	 */
	function __construct( $src, $post_id ) {
		// Set vars
		$this->src          = $src;
		$this->post_id      = $post_id;
		$this->thumbnail_id = 0;

		// Process
		$this->includes();
		$this->upload();
	}

	/**
	 * Get the required files
	 */
	private function includes() {
		require_once( ABSPATH . 'wp-admin/includes/file.php'  );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
	}

	/**
	 * Side upload the image and set it as the post thumbnail
	 */
	private function upload() {
		// Not an image ?
		if ( ! preg_match( '/[^\?]+\.(?:jpe?g|jpe|gif|png)(?:\?|$)/i', $this->src ) ) {
			$this->result = new WP_Error( 'not_an_image', __( 'This image file type is not supported.', 'wordcamp-talks' ) );

		// We can proceed
		} else {
			// First there can be a chance the src is already saved as an attachment
			$thumbnail_id = self::get_existing_attachment( $this->src );

			if ( ! empty( $thumbnail_id ) ) {
				$this->result = set_post_thumbnail( $this->post_id, $thumbnail_id );

			// Otherwise, we need to save it
			} else {
				// Temporarly filter the attachment url to set the Thumbnail ID
				add_filter( 'wp_get_attachment_url', array( $this, 'intercept_id' ), 10, 2 );

				$this->new_src = media_sideload_image( $this->src, $this->post_id, null, 'src' );

				remove_filter( 'wp_get_attachment_url', array( $this, 'intercept_id' ), 10, 2 );

				if ( ! is_wp_error( $this->new_src ) && isset( $this->thumbnail_id ) ) {
					$this->result = set_post_thumbnail( $this->post_id, $this->thumbnail_id );
					update_post_meta( $this->thumbnail_id, '_wc_talks_original_src', esc_url_raw( $this->src ) );
				} else {
					$this->result = new WP_Error( 'sideload_failed' );
				}
			}
		}
	}

	/**
	 * Intercept the Attachment ID.
	 *
	 * @param string $url the       link to the attachment just created
	 * @param int    $attachment_id the ID of attachment
	 */
	public function intercept_id( $url = '', $attachment_id = 0 ) {
		if ( ! empty( $attachment_id ) ) {
			$this->thumbnail_id = $attachment_id;
		}

		return $url;
	}

	/**
	 * Check if a featured image has already been uploaded and use it
	 *
	 * @param  string $src original image src
	 * @return int the attachment id containing the featured image
	 */
	public static function get_existing_attachment( $src = '' ) {
		global $wpdb;

		if ( empty( $src ) ) {
			return false;
		}

		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wc_talks_original_src' AND meta_value = %s", esc_url_raw( $src ) ) );
	}

	/**
	 * Temporarly include the meta value to WP_Query post fields
	 *
	 * @param  string $fields comma separated list of db fields
	 * @return string comma separated list of db fields + the meta value one
	 */
	public static function original_src_field( $fields = '' ) {
		global $wpdb;

		$qf   = explode( ',', $fields );
		$qf   = array_map( 'trim', $qf );
		$qf[] = $wpdb->postmeta . '.meta_value';

		return join( ', ', $qf );
	}

	/**
	 * Get all existing attachments having an '_wc_talks_original_src' meta key
	 *
	 * @param int $talk_id the ID of talk
	 */
	public static function get_talk_attachments( $talk_id = 0 ) {
		global $wpdb;

		$talk_id = (int) $talk_id;

		if ( empty( $talk_id ) ) {
			return array();
		}

		add_filter( 'posts_fields', array( __CLASS__, 'original_src_field' ), 10, 1 );

		$attachment_ids = new WP_Query( array(
			'post_parent' => $talk_id,
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_wc_talks_original_src',
			'fields'      => 'id=>parent',
		) );

		remove_filter( 'posts_fields', array( __CLASS__, 'original_src_field' ), 10, 1 );

		return wp_list_pluck( $attachment_ids->posts, 'ID', 'meta_value' );
	}

	/**
	 * Starting point.
	 *
	 * @param string $src the link to the image to side upload
	 * @param int    $post_id the ID of the post set the featured image for
	 */
	public static function start( $src = '', $post_id = 0 ) {
		if ( empty( $src ) || empty( $post_id ) ) {
			return new WP_Error( 'missing_argument' );
		}

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $src, $post_id );
		}

		return self::$instance;
	}
}

endif;
