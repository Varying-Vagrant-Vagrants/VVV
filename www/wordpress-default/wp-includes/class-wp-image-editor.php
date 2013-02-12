<?php
/**
 * Base WordPress Image Editor
 *
 * @package WordPress
 * @subpackage Image_Editor
 */

/**
 * Base image editor class from which implementations extend
 *
 * @since 3.5.0
 */
abstract class WP_Image_Editor {
	protected $file = null;
	protected $size = null;
	protected $mime_type = null;
	protected $default_mime_type = 'image/jpeg';
	protected $quality = 90;

	/**
	 * Each instance handles a single file.
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Checks to see if current environment supports the editor chosen.
	 * Must be overridden in a sub-class.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param array $args
	 * @return boolean
	 */
	public static function test( $args = array() ) {
		return false;
	}

	/**
	 * Checks to see if editor supports the mime-type specified.
	 * Must be overridden in a sub-class.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param string $mime_type
	 * @return boolean
	 */
	public static function supports_mime_type( $mime_type ) {
		return false;
	}

	/**
	 * Loads image from $this->file into editor.
	 *
	 * @since 3.5.0
	 * @access protected
	 * @abstract
	 *
	 * @return boolean|WP_Error True if loaded; WP_Error on failure.
	 */
	abstract public function load();

	/**
	 * Saves current image to file.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param string $destfilename
	 * @param string $mime_type
	 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	abstract public function save( $destfilename = null, $mime_type = null );

	/**
	 * Resizes current image.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param int $max_w
	 * @param int $max_h
	 * @param boolean $crop
	 * @return boolean|WP_Error
	 */
	abstract public function resize( $max_w, $max_h, $crop = false );

	/**
	 * Processes current image and saves to disk
	 * multiple sizes from single source.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param array $sizes { {'width'=>int, 'height'=>int, 'crop'=>bool}, ... }
	 * @return array
	 */
	abstract public function multi_resize( $sizes );

	/**
	 * Crops Image.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param string|int $src The source file or Attachment ID.
	 * @param int $src_x The start x position to crop from.
	 * @param int $src_y The start y position to crop from.
	 * @param int $src_w The width to crop.
	 * @param int $src_h The height to crop.
	 * @param int $dst_w Optional. The destination width.
	 * @param int $dst_h Optional. The destination height.
	 * @param boolean $src_abs Optional. If the source crop points are absolute.
	 * @return boolean|WP_Error
	 */
	abstract public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false );

	/**
	 * Rotates current image counter-clockwise by $angle.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param float $angle
	 * @return boolean|WP_Error
	 */
	abstract public function rotate( $angle );

	/**
	 * Flips current image.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param boolean $horz Horizontal Flip
	 * @param boolean $vert Vertical Flip
	 * @return boolean|WP_Error
	 */
	abstract public function flip( $horz, $vert );

	/**
	 * Streams current image to browser.
	 *
	 * @since 3.5.0
	 * @access public
	 * @abstract
	 *
	 * @param string $mime_type
	 * @return boolean|WP_Error
	 */
	abstract public function stream( $mime_type = null );

	/**
	 * Gets dimensions of image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @return array {'width'=>int, 'height'=>int}
	 */
	public function get_size() {
		return $this->size;
	}

	/**
	 * Sets current image size.
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param int $width
	 * @param int $height
	 */
	protected function update_size( $width = null, $height = null ) {
		$this->size = array(
			'width' => (int) $width,
			'height' => (int) $height
		);
		return true;
	}

	/**
	 * Sets Image Compression quality on a 1-100% scale.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $quality Compression Quality. Range: [1,100]
	 * @return boolean
	 */
	public function set_quality( $quality ) {
		$this->quality = apply_filters( 'wp_editor_set_quality', $quality );

		return ( (bool) $this->quality );
	}

	/**
	 * Returns preferred mime-type and extension based on provided
	 * file's extension and mime, or current file's extension and mime.
	 *
	 * Will default to $this->default_mime_type if requested is not supported.
	 *
	 * Provides corrected filename only if filename is provided.
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param string $filename
	 * @param string $mime_type
	 * @return array { filename|null, extension, mime-type }
	 */
	protected function get_output_format( $filename = null, $mime_type = null ) {
		$new_ext = $file_ext = null;
		$file_mime = null;

		// By default, assume specified type takes priority
		if ( $mime_type ) {
			$new_ext = $this->get_extension( $mime_type );
		}

		if ( $filename ) {
			$file_ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			$file_mime = $this->get_mime_type( $file_ext );
		}
		else {
			// If no file specified, grab editor's current extension and mime-type.
			$file_ext = strtolower( pathinfo( $this->file, PATHINFO_EXTENSION ) );
			$file_mime = $this->mime_type;
		}

		// Check to see if specified mime-type is the same as type implied by
		// file extension.  If so, prefer extension from file.
		if ( ! $mime_type || ( $file_mime == $mime_type ) ) {
			$mime_type = $file_mime;
			$new_ext = $file_ext;
		}

		// Double-check that the mime-type selected is supported by the editor.
		// If not, choose a default instead.
		if ( ! $this->supports_mime_type( $mime_type ) ) {
			$mime_type = apply_filters( 'image_editor_default_mime_type', $this->default_mime_type );
			$new_ext = $this->get_extension( $mime_type );
		}

		if ( $filename ) {
			$ext = '';
			$info = pathinfo( $filename );
			$dir  = $info['dirname'];

			if( isset( $info['extension'] ) )
				$ext = $info['extension'];

			$filename = trailingslashit( $dir ) . wp_basename( $filename, ".$ext" ) . ".{$new_ext}";
		}

		return array( $filename, $new_ext, $mime_type );
	}

	/**
	 * Builds an output filename based on current file, and adding proper suffix
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $suffix
	 * @param string $dest_path
	 * @param string $extension
	 * @return string filename
	 */
	public function generate_filename( $suffix = null, $dest_path = null, $extension = null ) {
		// $suffix will be appended to the destination filename, just before the extension
		if ( ! $suffix )
			$suffix = $this->get_suffix();

		$info = pathinfo( $this->file );
		$dir  = $info['dirname'];
		$ext  = $info['extension'];

		$name = wp_basename( $this->file, ".$ext" );
		$new_ext = strtolower( $extension ? $extension : $ext );

		if ( ! is_null( $dest_path ) && $_dest_path = realpath( $dest_path ) )
			$dir = $_dest_path;

		return trailingslashit( $dir ) . "{$name}-{$suffix}.{$new_ext}";
	}

	/**
	 * Builds and returns proper suffix for file based on height and width.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @return string suffix
	 */
	public function get_suffix() {
		if ( ! $this->get_size() )
			return false;

		return "{$this->size['width']}x{$this->size['height']}";
	}

	/**
	 * Either calls editor's save function or handles file as a stream.
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param string|stream $filename
	 * @param callable $function
	 * @param array $arguments
	 * @return boolean
	 */
	protected function make_image( $filename, $function, $arguments ) {
		$dst_file = $filename;

		if ( $stream = wp_is_stream( $filename ) ) {
			$filename = null;
			ob_start();
		}

		$result = call_user_func_array( $function, $arguments );

		if ( $result && $stream ) {
			$contents = ob_get_contents();

			$fp = fopen( $dst_file, 'w' );

			if ( ! $fp )
				return false;

			fwrite( $fp, $contents );
			fclose( $fp );
		}

		if ( $stream ) {
			ob_end_clean();
		}

		return $result;
	}

	/**
	 * Returns first matched mime-type from extension,
	 * as mapped from wp_get_mime_types()
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param string $extension
	 * @return string|boolean
	 */
	protected static function get_mime_type( $extension = null ) {
		if ( ! $extension )
			return false;

		$mime_types = wp_get_mime_types();
		$extensions = array_keys( $mime_types );

		foreach( $extensions as $_extension ) {
			if ( preg_match( "/{$extension}/i", $_extension ) ) {
				return $mime_types[$_extension];
			}
		}

		return false;
	}

	/**
	 * Returns first matched extension from Mime-type,
	 * as mapped from wp_get_mime_types()
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param string $mime_type
	 * @return string|boolean
	 */
	protected static function get_extension( $mime_type = null ) {
		$extensions = explode( '|', array_search( $mime_type, wp_get_mime_types() ) );

		if ( empty( $extensions[0] ) )
			return false;

		return $extensions[0];
	}
}

