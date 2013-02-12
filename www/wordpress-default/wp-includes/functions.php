<?php
/**
 * Main WordPress API
 *
 * @package WordPress
 */

require( ABSPATH . WPINC . '/option.php' );

/**
 * Converts given date string into a different format.
 *
 * $format should be either a PHP date format string, e.g. 'U' for a Unix
 * timestamp, or 'G' for a Unix timestamp assuming that $date is GMT.
 *
 * If $translate is true then the given date and format string will
 * be passed to date_i18n() for translation.
 *
 * @since 0.71
 *
 * @param string $format Format of the date to return.
 * @param string $date Date string to convert.
 * @param bool $translate Whether the return date should be translated. Default is true.
 * @return string|int Formatted date string, or Unix timestamp.
 */
function mysql2date( $format, $date, $translate = true ) {
	if ( empty( $date ) )
		return false;

	if ( 'G' == $format )
		return strtotime( $date . ' +0000' );

	$i = strtotime( $date );

	if ( 'U' == $format )
		return $i;

	if ( $translate )
		return date_i18n( $format, $i );
	else
		return date( $format, $i );
}

/**
 * Retrieve the current time based on specified type.
 *
 * The 'mysql' type will return the time in the format for MySQL DATETIME field.
 * The 'timestamp' type will return the current timestamp.
 *
 * If $gmt is set to either '1' or 'true', then both types will use GMT time.
 * if $gmt is false, the output is adjusted with the GMT offset in the WordPress option.
 *
 * @since 1.0.0
 *
 * @param string $type Either 'mysql' or 'timestamp'.
 * @param int|bool $gmt Optional. Whether to use GMT timezone. Default is false.
 * @return int|string String if $type is 'gmt', int if $type is 'timestamp'.
 */
function current_time( $type, $gmt = 0 ) {
	switch ( $type ) {
		case 'mysql':
			return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
			break;
		case 'timestamp':
			return ( $gmt ) ? time() : time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			break;
	}
}

/**
 * Retrieve the date in localized format, based on timestamp.
 *
 * If the locale specifies the locale month and weekday, then the locale will
 * take over the format for the date. If it isn't, then the date format string
 * will be used instead.
 *
 * @since 0.71
 *
 * @param string $dateformatstring Format to display the date.
 * @param int $unixtimestamp Optional. Unix timestamp.
 * @param bool $gmt Optional, default is false. Whether to convert to GMT for time.
 * @return string The date, translated if locale specifies it.
 */
function date_i18n( $dateformatstring, $unixtimestamp = false, $gmt = false ) {
	global $wp_locale;
	$i = $unixtimestamp;

	if ( false === $i ) {
		if ( ! $gmt )
			$i = current_time( 'timestamp' );
		else
			$i = time();
		// we should not let date() interfere with our
		// specially computed timestamp
		$gmt = true;
	}

	// store original value for language with untypical grammars
	// see http://core.trac.wordpress.org/ticket/9396
	$req_format = $dateformatstring;

	$datefunc = $gmt? 'gmdate' : 'date';

	if ( ( !empty( $wp_locale->month ) ) && ( !empty( $wp_locale->weekday ) ) ) {
		$datemonth = $wp_locale->get_month( $datefunc( 'm', $i ) );
		$datemonth_abbrev = $wp_locale->get_month_abbrev( $datemonth );
		$dateweekday = $wp_locale->get_weekday( $datefunc( 'w', $i ) );
		$dateweekday_abbrev = $wp_locale->get_weekday_abbrev( $dateweekday );
		$datemeridiem = $wp_locale->get_meridiem( $datefunc( 'a', $i ) );
		$datemeridiem_capital = $wp_locale->get_meridiem( $datefunc( 'A', $i ) );
		$dateformatstring = ' '.$dateformatstring;
		$dateformatstring = preg_replace( "/([^\\\])D/", "\\1" . backslashit( $dateweekday_abbrev ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])F/", "\\1" . backslashit( $datemonth ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])l/", "\\1" . backslashit( $dateweekday ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])M/", "\\1" . backslashit( $datemonth_abbrev ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])a/", "\\1" . backslashit( $datemeridiem ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])A/", "\\1" . backslashit( $datemeridiem_capital ), $dateformatstring );

		$dateformatstring = substr( $dateformatstring, 1, strlen( $dateformatstring ) -1 );
	}
	$timezone_formats = array( 'P', 'I', 'O', 'T', 'Z', 'e' );
	$timezone_formats_re = implode( '|', $timezone_formats );
	if ( preg_match( "/$timezone_formats_re/", $dateformatstring ) ) {
		$timezone_string = get_option( 'timezone_string' );
		if ( $timezone_string ) {
			$timezone_object = timezone_open( $timezone_string );
			$date_object = date_create( null, $timezone_object );
			foreach( $timezone_formats as $timezone_format ) {
				if ( false !== strpos( $dateformatstring, $timezone_format ) ) {
					$formatted = date_format( $date_object, $timezone_format );
					$dateformatstring = ' '.$dateformatstring;
					$dateformatstring = preg_replace( "/([^\\\])$timezone_format/", "\\1" . backslashit( $formatted ), $dateformatstring );
					$dateformatstring = substr( $dateformatstring, 1, strlen( $dateformatstring ) -1 );
				}
			}
		}
	}
	$j = @$datefunc( $dateformatstring, $i );
	// allow plugins to redo this entirely for languages with untypical grammars
	$j = apply_filters('date_i18n', $j, $req_format, $i, $gmt);
	return $j;
}

/**
 * Convert integer number to format based on the locale.
 *
 * @since 2.3.0
 *
 * @param int $number The number to convert based on locale.
 * @param int $decimals Precision of the number of decimal places.
 * @return string Converted number in string format.
 */
function number_format_i18n( $number, $decimals = 0 ) {
	global $wp_locale;
	$formatted = number_format( $number, absint( $decimals ), $wp_locale->number_format['decimal_point'], $wp_locale->number_format['thousands_sep'] );
	return apply_filters( 'number_format_i18n', $formatted );
}

/**
 * Convert number of bytes largest unit bytes will fit into.
 *
 * It is easier to read 1kB than 1024 bytes and 1MB than 1048576 bytes. Converts
 * number of bytes to human readable number by taking the number of that unit
 * that the bytes will go into it. Supports TB value.
 *
 * Please note that integers in PHP are limited to 32 bits, unless they are on
 * 64 bit architecture, then they have 64 bit size. If you need to place the
 * larger size then what PHP integer type will hold, then use a string. It will
 * be converted to a double, which should always have 64 bit length.
 *
 * Technically the correct unit names for powers of 1024 are KiB, MiB etc.
 * @link http://en.wikipedia.org/wiki/Byte
 *
 * @since 2.3.0
 *
 * @param int|string $bytes Number of bytes. Note max integer size for integers.
 * @param int $decimals Precision of number of decimal places. Deprecated.
 * @return bool|string False on failure. Number string on success.
 */
function size_format( $bytes, $decimals = 0 ) {
	$quant = array(
		// ========================= Origin ====
		'TB' => 1099511627776,  // pow( 1024, 4)
		'GB' => 1073741824,     // pow( 1024, 3)
		'MB' => 1048576,        // pow( 1024, 2)
		'kB' => 1024,           // pow( 1024, 1)
		'B ' => 1,              // pow( 1024, 0)
	);
	foreach ( $quant as $unit => $mag )
		if ( doubleval($bytes) >= $mag )
			return number_format_i18n( $bytes / $mag, $decimals ) . ' ' . $unit;

	return false;
}

/**
 * Get the week start and end from the datetime or date string from mysql.
 *
 * @since 0.71
 *
 * @param string $mysqlstring Date or datetime field type from mysql.
 * @param int $start_of_week Optional. Start of the week as an integer.
 * @return array Keys are 'start' and 'end'.
 */
function get_weekstartend( $mysqlstring, $start_of_week = '' ) {
	$my = substr( $mysqlstring, 0, 4 ); // Mysql string Year
	$mm = substr( $mysqlstring, 8, 2 ); // Mysql string Month
	$md = substr( $mysqlstring, 5, 2 ); // Mysql string day
	$day = mktime( 0, 0, 0, $md, $mm, $my ); // The timestamp for mysqlstring day.
	$weekday = date( 'w', $day ); // The day of the week from the timestamp
	if ( !is_numeric($start_of_week) )
		$start_of_week = get_option( 'start_of_week' );

	if ( $weekday < $start_of_week )
		$weekday += 7;

	$start = $day - DAY_IN_SECONDS * ( $weekday - $start_of_week ); // The most recent week start day on or before $day
	$end = $start + 7 * DAY_IN_SECONDS - 1; // $start + 7 days - 1 second
	return compact( 'start', 'end' );
}

/**
 * Unserialize value only if it was serialized.
 *
 * @since 2.0.0
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		return @unserialize( $original );
	return $original;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @since 2.0.5
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( ! is_string( $data ) )
		return false;
	$data = trim( $data );
 	if ( 'N;' == $data )
		return true;
	$length = strlen( $data );
	if ( $length < 4 )
		return false;
	if ( ':' !== $data[1] )
		return false;
	$lastc = $data[$length-1];
	if ( ';' !== $lastc && '}' !== $lastc )
		return false;
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( '"' !== $data[$length-2] )
				return false;
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
	}
	return false;
}

/**
 * Check whether serialized data is of string type.
 *
 * @since 2.0.5
 *
 * @param mixed $data Serialized data
 * @return bool False if not a serialized string, true if it is.
 */
function is_serialized_string( $data ) {
	// if it isn't a string, it isn't a serialized string
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	$length = strlen( $data );
	if ( $length < 4 )
		return false;
	elseif ( ':' !== $data[1] )
		return false;
	elseif ( ';' !== $data[$length-1] )
		return false;
	elseif ( $data[0] !== 's' )
		return false;
	elseif ( '"' !== $data[$length-2] )
		return false;
	else
		return true;
}

/**
 * Serialize data, if needed.
 *
 * @since 2.0.5
 *
 * @param mixed $data Data that might be serialized.
 * @return mixed A scalar data
 */
function maybe_serialize( $data ) {
	if ( is_array( $data ) || is_object( $data ) )
		return serialize( $data );

	// Double serialization is required for backward compatibility.
	// See http://core.trac.wordpress.org/ticket/12930
	if ( is_serialized( $data ) )
		return serialize( $data );

	return $data;
}

/**
 * Retrieve post title from XMLRPC XML.
 *
 * If the title element is not part of the XML, then the default post title from
 * the $post_default_title will be used instead.
 *
 * @package WordPress
 * @subpackage XMLRPC
 * @since 0.71
 *
 * @global string $post_default_title Default XMLRPC post title.
 *
 * @param string $content XMLRPC XML Request content
 * @return string Post title
 */
function xmlrpc_getposttitle( $content ) {
	global $post_default_title;
	if ( preg_match( '/<title>(.+?)<\/title>/is', $content, $matchtitle ) ) {
		$post_title = $matchtitle[1];
	} else {
		$post_title = $post_default_title;
	}
	return $post_title;
}

/**
 * Retrieve the post category or categories from XMLRPC XML.
 *
 * If the category element is not found, then the default post category will be
 * used. The return type then would be what $post_default_category. If the
 * category is found, then it will always be an array.
 *
 * @package WordPress
 * @subpackage XMLRPC
 * @since 0.71
 *
 * @global string $post_default_category Default XMLRPC post category.
 *
 * @param string $content XMLRPC XML Request content
 * @return string|array List of categories or category name.
 */
function xmlrpc_getpostcategory( $content ) {
	global $post_default_category;
	if ( preg_match( '/<category>(.+?)<\/category>/is', $content, $matchcat ) ) {
		$post_category = trim( $matchcat[1], ',' );
		$post_category = explode( ',', $post_category );
	} else {
		$post_category = $post_default_category;
	}
	return $post_category;
}

/**
 * XMLRPC XML content without title and category elements.
 *
 * @package WordPress
 * @subpackage XMLRPC
 * @since 0.71
 *
 * @param string $content XMLRPC XML Request content
 * @return string XMLRPC XML Request content without title and category elements.
 */
function xmlrpc_removepostdata( $content ) {
	$content = preg_replace( '/<title>(.+?)<\/title>/si', '', $content );
	$content = preg_replace( '/<category>(.+?)<\/category>/si', '', $content );
	$content = trim( $content );
	return $content;
}

/**
 * Check content for video and audio links to add as enclosures.
 *
 * Will not add enclosures that have already been added and will
 * remove enclosures that are no longer in the post. This is called as
 * pingbacks and trackbacks.
 *
 * @package WordPress
 * @since 1.5.0
 *
 * @uses $wpdb
 *
 * @param string $content Post Content
 * @param int $post_ID Post ID
 */
function do_enclose( $content, $post_ID ) {
	global $wpdb;

	//TODO: Tidy this ghetto code up and make the debug code optional
	include_once( ABSPATH . WPINC . '/class-IXR.php' );

	$post_links = array();

	$pung = get_enclosed( $post_ID );

	$ltrs = '\w';
	$gunk = '/#~:.?+=&%@!\-';
	$punc = '.:?\-';
	$any = $ltrs . $gunk . $punc;

	preg_match_all( "{\b http : [$any] +? (?= [$punc] * [^$any] | $)}x", $content, $post_links_temp );

	foreach ( $pung as $link_test ) {
		if ( !in_array( $link_test, $post_links_temp[0] ) ) { // link no longer in post
			$mids = $wpdb->get_col( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'enclosure' AND meta_value LIKE (%s)", $post_ID, like_escape( $link_test ) . '%') );
			foreach ( $mids as $mid )
				delete_metadata_by_mid( 'post', $mid );
		}
	}

	foreach ( (array) $post_links_temp[0] as $link_test ) {
		if ( !in_array( $link_test, $pung ) ) { // If we haven't pung it already
			$test = @parse_url( $link_test );
			if ( false === $test )
				continue;
			if ( isset( $test['query'] ) )
				$post_links[] = $link_test;
			elseif ( isset($test['path']) && ( $test['path'] != '/' ) &&  ($test['path'] != '' ) )
				$post_links[] = $link_test;
		}
	}

	foreach ( (array) $post_links as $url ) {
		if ( $url != '' && !$wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'enclosure' AND meta_value LIKE (%s)", $post_ID, like_escape( $url ) . '%' ) ) ) {

			if ( $headers = wp_get_http_headers( $url) ) {
				$len = isset( $headers['content-length'] ) ? (int) $headers['content-length'] : 0;
				$type = isset( $headers['content-type'] ) ? $headers['content-type'] : '';
				$allowed_types = array( 'video', 'audio' );

				// Check to see if we can figure out the mime type from
				// the extension
				$url_parts = @parse_url( $url );
				if ( false !== $url_parts ) {
					$extension = pathinfo( $url_parts['path'], PATHINFO_EXTENSION );
					if ( !empty( $extension ) ) {
						foreach ( wp_get_mime_types() as $exts => $mime ) {
							if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
								$type = $mime;
								break;
							}
						}
					}
				}

				if ( in_array( substr( $type, 0, strpos( $type, "/" ) ), $allowed_types ) ) {
					add_post_meta( $post_ID, 'enclosure', "$url\n$len\n$mime\n" );
				}
			}
		}
	}
}

/**
 * Perform a HTTP HEAD or GET request.
 *
 * If $file_path is a writable filename, this will do a GET request and write
 * the file to that path.
 *
 * @since 2.5.0
 *
 * @param string $url URL to fetch.
 * @param string|bool $file_path Optional. File path to write request to.
 * @param int $red (private) The number of Redirects followed, Upon 5 being hit, returns false.
 * @return bool|string False on failure and string of headers if HEAD request.
 */
function wp_get_http( $url, $file_path = false, $red = 1 ) {
	@set_time_limit( 60 );

	if ( $red > 5 )
		return false;

	$options = array();
	$options['redirection'] = 5;

	if ( false == $file_path )
		$options['method'] = 'HEAD';
	else
		$options['method'] = 'GET';

	$response = wp_remote_request($url, $options);

	if ( is_wp_error( $response ) )
		return false;

	$headers = wp_remote_retrieve_headers( $response );
	$headers['response'] = wp_remote_retrieve_response_code( $response );

	// WP_HTTP no longer follows redirects for HEAD requests.
	if ( 'HEAD' == $options['method'] && in_array($headers['response'], array(301, 302)) && isset( $headers['location'] ) ) {
		return wp_get_http( $headers['location'], $file_path, ++$red );
	}

	if ( false == $file_path )
		return $headers;

	// GET request - write it to the supplied filename
	$out_fp = fopen($file_path, 'w');
	if ( !$out_fp )
		return $headers;

	fwrite( $out_fp,  wp_remote_retrieve_body( $response ) );
	fclose($out_fp);
	clearstatcache();

	return $headers;
}

/**
 * Retrieve HTTP Headers from URL.
 *
 * @since 1.5.1
 *
 * @param string $url
 * @param bool $deprecated Not Used.
 * @return bool|string False on failure, headers on success.
 */
function wp_get_http_headers( $url, $deprecated = false ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.7' );

	$response = wp_remote_head( $url );

	if ( is_wp_error( $response ) )
		return false;

	return wp_remote_retrieve_headers( $response );
}

/**
 * Whether today is a new day.
 *
 * @since 0.71
 * @uses $day Today
 * @uses $previousday Previous day
 *
 * @return int 1 when new day, 0 if not a new day.
 */
function is_new_day() {
	global $currentday, $previousday;
	if ( $currentday != $previousday )
		return 1;
	else
		return 0;
}

/**
 * Build URL query based on an associative and, or indexed array.
 *
 * This is a convenient function for easily building url queries. It sets the
 * separator to '&' and uses _http_build_query() function.
 *
 * @see _http_build_query() Used to build the query
 * @link http://us2.php.net/manual/en/function.http-build-query.php more on what
 *		http_build_query() does.
 *
 * @since 2.3.0
 *
 * @param array $data URL-encode key/value pairs.
 * @return string URL encoded string
 */
function build_query( $data ) {
	return _http_build_query( $data, null, '&', '', false );
}

// from php.net (modified by Mark Jaquith to behave like the native PHP5 function)
function _http_build_query($data, $prefix=null, $sep=null, $key='', $urlencode=true) {
	$ret = array();

	foreach ( (array) $data as $k => $v ) {
		if ( $urlencode)
			$k = urlencode($k);
		if ( is_int($k) && $prefix != null )
			$k = $prefix.$k;
		if ( !empty($key) )
			$k = $key . '%5B' . $k . '%5D';
		if ( $v === null )
			continue;
		elseif ( $v === FALSE )
			$v = '0';

		if ( is_array($v) || is_object($v) )
			array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
		elseif ( $urlencode )
			array_push($ret, $k.'='.urlencode($v));
		else
			array_push($ret, $k.'='.$v);
	}

	if ( null === $sep )
		$sep = ini_get('arg_separator.output');

	return implode($sep, $ret);
}

/**
 * Retrieve a modified URL query string.
 *
 * You can rebuild the URL and append a new query variable to the URL query by
 * using this function. You can also retrieve the full URL with query data.
 *
 * Adding a single key & value or an associative array. Setting a key value to
 * an empty string removes the key. Omitting oldquery_or_uri uses the $_SERVER
 * value. Additional values provided are expected to be encoded appropriately
 * with urlencode() or rawurlencode().
 *
 * @since 1.5.0
 *
 * @param mixed $param1 Either newkey or an associative_array
 * @param mixed $param2 Either newvalue or oldquery or uri
 * @param mixed $param3 Optional. Old query or uri
 * @return string New URL query string.
 */
function add_query_arg() {
	$ret = '';
	$args = func_get_args();
	if ( is_array( $args[0] ) ) {
		if ( count( $args ) < 2 || false === $args[1] )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = $args[1];
	} else {
		if ( count( $args ) < 3 || false === $args[2] )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = $args[2];
	}

	if ( $frag = strstr( $uri, '#' ) )
		$uri = substr( $uri, 0, -strlen( $frag ) );
	else
		$frag = '';

	if ( 0 === stripos( 'http://', $uri ) ) {
		$protocol = 'http://';
		$uri = substr( $uri, 7 );
	} elseif ( 0 === stripos( 'https://', $uri ) ) {
		$protocol = 'https://';
		$uri = substr( $uri, 8 );
	} else {
		$protocol = '';
	}

	if ( strpos( $uri, '?' ) !== false ) {
		$parts = explode( '?', $uri, 2 );
		if ( 1 == count( $parts ) ) {
			$base = '?';
			$query = $parts[0];
		} else {
			$base = $parts[0] . '?';
			$query = $parts[1];
		}
	} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
		$base = $uri . '?';
		$query = '';
	} else {
		$base = '';
		$query = $uri;
	}

	wp_parse_str( $query, $qs );
	$qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
	if ( is_array( $args[0] ) ) {
		$kayvees = $args[0];
		$qs = array_merge( $qs, $kayvees );
	} else {
		$qs[ $args[0] ] = $args[1];
	}

	foreach ( $qs as $k => $v ) {
		if ( $v === false )
			unset( $qs[$k] );
	}

	$ret = build_query( $qs );
	$ret = trim( $ret, '?' );
	$ret = preg_replace( '#=(&|$)#', '$1', $ret );
	$ret = $protocol . $base . $ret . $frag;
	$ret = rtrim( $ret, '?' );
	return $ret;
}

/**
 * Removes an item or list from the query string.
 *
 * @since 1.5.0
 *
 * @param string|array $key Query key or keys to remove.
 * @param bool $query When false uses the $_SERVER value.
 * @return string New URL query string.
 */
function remove_query_arg( $key, $query=false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( $key as $k )
			$query = add_query_arg( $k, false, $query );
		return $query;
	}
	return add_query_arg( $key, false, $query );
}

/**
 * Walks the array while sanitizing the contents.
 *
 * @since 0.71
 *
 * @param array $array Array to used to walk while sanitizing contents.
 * @return array Sanitized $array.
 */
function add_magic_quotes( $array ) {
	foreach ( (array) $array as $k => $v ) {
		if ( is_array( $v ) ) {
			$array[$k] = add_magic_quotes( $v );
		} else {
			$array[$k] = addslashes( $v );
		}
	}
	return $array;
}

/**
 * HTTP request for URI to retrieve content.
 *
 * @since 1.5.1
 * @uses wp_remote_get()
 *
 * @param string $uri URI/URL of web page to retrieve.
 * @return bool|string HTTP content. False on failure.
 */
function wp_remote_fopen( $uri ) {
	$parsed_url = @parse_url( $uri );

	if ( !$parsed_url || !is_array( $parsed_url ) )
		return false;

	$options = array();
	$options['timeout'] = 10;

	$response = wp_remote_get( $uri, $options );

	if ( is_wp_error( $response ) )
		return false;

	return wp_remote_retrieve_body( $response );
}

/**
 * Set up the WordPress query.
 *
 * @since 2.0.0
 *
 * @param string $query_vars Default WP_Query arguments.
 */
function wp( $query_vars = '' ) {
	global $wp, $wp_query, $wp_the_query;
	$wp->main( $query_vars );

	if ( !isset($wp_the_query) )
		$wp_the_query = $wp_query;
}

/**
 * Retrieve the description for the HTTP status.
 *
 * @since 2.3.0
 *
 * @param int $code HTTP status code.
 * @return string Empty string if not found, or description if found.
 */
function get_status_header_desc( $code ) {
	global $wp_header_to_desc;

	$code = absint( $code );

	if ( !isset( $wp_header_to_desc ) ) {
		$wp_header_to_desc = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended'
		);
	}

	if ( isset( $wp_header_to_desc[$code] ) )
		return $wp_header_to_desc[$code];
	else
		return '';
}

/**
 * Set HTTP status header.
 *
 * @since 2.0.0
 * @uses apply_filters() Calls 'status_header' on status header string, HTTP
 *		HTTP code, HTTP code description, and protocol string as separate
 *		parameters.
 *
 * @param int $header HTTP status code
 * @return unknown
 */
function status_header( $header ) {
	$text = get_status_header_desc( $header );

	if ( empty( $text ) )
		return false;

	$protocol = $_SERVER["SERVER_PROTOCOL"];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
		$protocol = 'HTTP/1.0';
	$status_header = "$protocol $header $text";
	if ( function_exists( 'apply_filters' ) )
		$status_header = apply_filters( 'status_header', $status_header, $header, $text, $protocol );

	return @header( $status_header, true, $header );
}

/**
 * Gets the header information to prevent caching.
 *
 * The several different headers cover the different ways cache prevention is handled
 * by different browsers
 *
 * @since 2.8.0
 *
 * @uses apply_filters()
 * @return array The associative array of header names and field values.
 */
function wp_get_nocache_headers() {
	$headers = array(
		'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
		'Pragma' => 'no-cache',
	);

	if ( function_exists('apply_filters') ) {
		$headers = (array) apply_filters('nocache_headers', $headers);
	}
	$headers['Last-Modified'] = false;
	return $headers;
}

/**
 * Sets the headers to prevent caching for the different browsers.
 *
 * Different browsers support different nocache headers, so several headers must
 * be sent so that all of them get the point that no caching should occur.
 *
 * @since 2.0.0
 * @uses wp_get_nocache_headers()
 */
function nocache_headers() {
	$headers = wp_get_nocache_headers();

	unset( $headers['Last-Modified'] );

	// In PHP 5.3+, make sure we are not sending a Last-Modified header.
	if ( function_exists( 'header_remove' ) ) {
		@header_remove( 'Last-Modified' );
	} else {
		// In PHP 5.2, send an empty Last-Modified header, but only as a
		// last resort to override a header already sent. #WP23021
		foreach ( headers_list() as $header ) {
			if ( 0 === stripos( $header, 'Last-Modified' ) ) {
				$headers['Last-Modified'] = '';
				break;
			}
		}
	}

	foreach( $headers as $name => $field_value )
		@header("{$name}: {$field_value}");
}

/**
 * Set the headers for caching for 10 days with JavaScript content type.
 *
 * @since 2.1.0
 */
function cache_javascript_headers() {
	$expiresOffset = 10 * DAY_IN_SECONDS;
	header( "Content-Type: text/javascript; charset=" . get_bloginfo( 'charset' ) );
	header( "Vary: Accept-Encoding" ); // Handle proxies
	header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expiresOffset ) . " GMT" );
}

/**
 * Retrieve the number of database queries during the WordPress execution.
 *
 * @since 2.0.0
 *
 * @return int Number of database queries
 */
function get_num_queries() {
	global $wpdb;
	return $wpdb->num_queries;
}

/**
 * Whether input is yes or no. Must be 'y' to be true.
 *
 * @since 1.0.0
 *
 * @param string $yn Character string containing either 'y' or 'n'
 * @return bool True if yes, false on anything else
 */
function bool_from_yn( $yn ) {
	return ( strtolower( $yn ) == 'y' );
}

/**
 * Loads the feed template from the use of an action hook.
 *
 * If the feed action does not have a hook, then the function will die with a
 * message telling the visitor that the feed is not valid.
 *
 * It is better to only have one hook for each feed.
 *
 * @since 2.1.0
 * @uses $wp_query Used to tell if the use a comment feed.
 * @uses do_action() Calls 'do_feed_$feed' hook, if a hook exists for the feed.
 */
function do_feed() {
	global $wp_query;

	$feed = get_query_var( 'feed' );

	// Remove the pad, if present.
	$feed = preg_replace( '/^_+/', '', $feed );

	if ( $feed == '' || $feed == 'feed' )
		$feed = get_default_feed();

	$hook = 'do_feed_' . $feed;
	if ( !has_action($hook) ) {
		$message = sprintf( __( 'ERROR: %s is not a valid feed template.' ), esc_html($feed));
		wp_die( $message, '', array( 'response' => 404 ) );
	}

	do_action( $hook, $wp_query->is_comment_feed );
}

/**
 * Load the RDF RSS 0.91 Feed template.
 *
 * @since 2.1.0
 */
function do_feed_rdf() {
	load_template( ABSPATH . WPINC . '/feed-rdf.php' );
}

/**
 * Load the RSS 1.0 Feed Template.
 *
 * @since 2.1.0
 */
function do_feed_rss() {
	load_template( ABSPATH . WPINC . '/feed-rss.php' );
}

/**
 * Load either the RSS2 comment feed or the RSS2 posts feed.
 *
 * @since 2.1.0
 *
 * @param bool $for_comments True for the comment feed, false for normal feed.
 */
function do_feed_rss2( $for_comments ) {
	if ( $for_comments )
		load_template( ABSPATH . WPINC . '/feed-rss2-comments.php' );
	else
		load_template( ABSPATH . WPINC . '/feed-rss2.php' );
}

/**
 * Load either Atom comment feed or Atom posts feed.
 *
 * @since 2.1.0
 *
 * @param bool $for_comments True for the comment feed, false for normal feed.
 */
function do_feed_atom( $for_comments ) {
	if ($for_comments)
		load_template( ABSPATH . WPINC . '/feed-atom-comments.php');
	else
		load_template( ABSPATH . WPINC . '/feed-atom.php' );
}

/**
 * Display the robots.txt file content.
 *
 * The echo content should be with usage of the permalinks or for creating the
 * robots.txt file.
 *
 * @since 2.1.0
 * @uses do_action() Calls 'do_robotstxt' hook for displaying robots.txt rules.
 */
function do_robots() {
	header( 'Content-Type: text/plain; charset=utf-8' );

	do_action( 'do_robotstxt' );

	$output = "User-agent: *\n";
	$public = get_option( 'blog_public' );
	if ( '0' == $public ) {
		$output .= "Disallow: /\n";
	} else {
		$site_url = parse_url( site_url() );
		$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
		$output .= "Disallow: $path/wp-admin/\n";
		$output .= "Disallow: $path/wp-includes/\n";
	}

	echo apply_filters('robots_txt', $output, $public);
}

/**
 * Test whether blog is already installed.
 *
 * The cache will be checked first. If you have a cache plugin, which saves the
 * cache values, then this will work. If you use the default WordPress cache,
 * and the database goes away, then you might have problems.
 *
 * Checks for the option siteurl for whether WordPress is installed.
 *
 * @since 2.1.0
 * @uses $wpdb
 *
 * @return bool Whether blog is already installed.
 */
function is_blog_installed() {
	global $wpdb;

	// Check cache first. If options table goes away and we have true cached, oh well.
	if ( wp_cache_get( 'is_blog_installed' ) )
		return true;

	$suppress = $wpdb->suppress_errors();
	if ( ! defined( 'WP_INSTALLING' ) ) {
		$alloptions = wp_load_alloptions();
	}
	// If siteurl is not set to autoload, check it specifically
	if ( !isset( $alloptions['siteurl'] ) )
		$installed = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'" );
	else
		$installed = $alloptions['siteurl'];
	$wpdb->suppress_errors( $suppress );

	$installed = !empty( $installed );
	wp_cache_set( 'is_blog_installed', $installed );

	if ( $installed )
		return true;

	// If visiting repair.php, return true and let it take over.
	if ( defined( 'WP_REPAIRING' ) )
		return true;

	$suppress = $wpdb->suppress_errors();

	// Loop over the WP tables. If none exist, then scratch install is allowed.
	// If one or more exist, suggest table repair since we got here because the options
	// table could not be accessed.
	$wp_tables = $wpdb->tables();
	foreach ( $wp_tables as $table ) {
		// The existence of custom user tables shouldn't suggest an insane state or prevent a clean install.
		if ( defined( 'CUSTOM_USER_TABLE' ) && CUSTOM_USER_TABLE == $table )
			continue;
		if ( defined( 'CUSTOM_USER_META_TABLE' ) && CUSTOM_USER_META_TABLE == $table )
			continue;

		if ( ! $wpdb->get_results( "DESCRIBE $table;" ) )
			continue;

		// One or more tables exist. We are insane.

		wp_load_translations_early();

		// Die with a DB error.
		$wpdb->error = sprintf( __( 'One or more database tables are unavailable. The database may need to be <a href="%s">repaired</a>.' ), 'maint/repair.php?referrer=is_blog_installed' );
		dead_db();
	}

	$wpdb->suppress_errors( $suppress );

	wp_cache_set( 'is_blog_installed', false );

	return false;
}

/**
 * Retrieve URL with nonce added to URL query.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @param string $actionurl URL to add nonce action
 * @param string $action Optional. Nonce action name
 * @return string URL with nonce action added.
 */
function wp_nonce_url( $actionurl, $action = -1 ) {
	$actionurl = str_replace( '&amp;', '&', $actionurl );
	return esc_html( add_query_arg( '_wpnonce', wp_create_nonce( $action ), $actionurl ) );
}

/**
 * Retrieve or display nonce hidden field for forms.
 *
 * The nonce field is used to validate that the contents of the form came from
 * the location on the current site and not somewhere else. The nonce does not
 * offer absolute protection, but should protect against most cases. It is very
 * important to use nonce field in forms.
 *
 * The $action and $name are optional, but if you want to have better security,
 * it is strongly suggested to set those two parameters. It is easier to just
 * call the function without any parameters, because validation of the nonce
 * doesn't require any parameters, but since crackers know what the default is
 * it won't be difficult for them to find a way around your nonce and cause
 * damage.
 *
 * The input name will be whatever $name value you gave. The input value will be
 * the nonce creation value.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @param string $action Optional. Action name.
 * @param string $name Optional. Nonce name.
 * @param bool $referer Optional, default true. Whether to set the referer field for validation.
 * @param bool $echo Optional, default true. Whether to display or return hidden form field.
 * @return string Nonce field.
 */
function wp_nonce_field( $action = -1, $name = "_wpnonce", $referer = true , $echo = true ) {
	$name = esc_attr( $name );
	$nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . wp_create_nonce( $action ) . '" />';

	if ( $referer )
		$nonce_field .= wp_referer_field( false );

	if ( $echo )
		echo $nonce_field;

	return $nonce_field;
}

/**
 * Retrieve or display referer hidden field for forms.
 *
 * The referer link is the current Request URI from the server super global. The
 * input name is '_wp_http_referer', in case you wanted to check manually.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @param bool $echo Whether to echo or return the referer field.
 * @return string Referer field.
 */
function wp_referer_field( $echo = true ) {
	$ref = esc_attr( $_SERVER['REQUEST_URI'] );
	$referer_field = '<input type="hidden" name="_wp_http_referer" value="'. $ref . '" />';

	if ( $echo )
		echo $referer_field;
	return $referer_field;
}

/**
 * Retrieve or display original referer hidden field for forms.
 *
 * The input name is '_wp_original_http_referer' and will be either the same
 * value of {@link wp_referer_field()}, if that was posted already or it will
 * be the current page, if it doesn't exist.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @param bool $echo Whether to echo the original http referer
 * @param string $jump_back_to Optional, default is 'current'. Can be 'previous' or page you want to jump back to.
 * @return string Original referer field.
 */
function wp_original_referer_field( $echo = true, $jump_back_to = 'current' ) {
	$jump_back_to = ( 'previous' == $jump_back_to ) ? wp_get_referer() : $_SERVER['REQUEST_URI'];
	$ref = ( wp_get_original_referer() ) ? wp_get_original_referer() : $jump_back_to;
	$orig_referer_field = '<input type="hidden" name="_wp_original_http_referer" value="' . esc_attr( stripslashes( $ref ) ) . '" />';
	if ( $echo )
		echo $orig_referer_field;
	return $orig_referer_field;
}

/**
 * Retrieve referer from '_wp_http_referer' or HTTP referer. If it's the same
 * as the current request URL, will return false.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @return string|bool False on failure. Referer URL on success.
 */
function wp_get_referer() {
	$ref = false;
	if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
		$ref = $_REQUEST['_wp_http_referer'];
	else if ( ! empty( $_SERVER['HTTP_REFERER'] ) )
		$ref = $_SERVER['HTTP_REFERER'];

	if ( $ref && $ref !== $_SERVER['REQUEST_URI'] )
		return $ref;
	return false;
}

/**
 * Retrieve original referer that was posted, if it exists.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @return string|bool False if no original referer or original referer if set.
 */
function wp_get_original_referer() {
	if ( !empty( $_REQUEST['_wp_original_http_referer'] ) )
		return $_REQUEST['_wp_original_http_referer'];
	return false;
}

/**
 * Recursive directory creation based on full path.
 *
 * Will attempt to set permissions on folders.
 *
 * @since 2.0.1
 *
 * @param string $target Full path to attempt to create.
 * @return bool Whether the path was created. True if path already exists.
 */
function wp_mkdir_p( $target ) {
	$wrapper = null;

	// strip the protocol
	if( wp_is_stream( $target ) ) {
		list( $wrapper, $target ) = explode( '://', $target, 2 );
	}

	// from php.net/mkdir user contributed notes
	$target = str_replace( '//', '/', $target );

	// put the wrapper back on the target
	if( $wrapper !== null ) {
		$target = $wrapper . '://' . $target;
	}

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = rtrim($target, '/'); // Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
	if ( empty($target) )
		$target = '/';

	if ( file_exists( $target ) )
		return @is_dir( $target );

	// Attempting to create the directory may clutter up our display.
	if ( @mkdir( $target ) ) {
		$stat = @stat( dirname( $target ) );
		$dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
		@chmod( $target, $dir_perms );
		return true;
	} elseif ( is_dir( dirname( $target ) ) ) {
			return false;
	}

	// If the above failed, attempt to create the parent node, then try again.
	if ( ( $target != '/' ) && ( wp_mkdir_p( dirname( $target ) ) ) )
		return wp_mkdir_p( $target );

	return false;
}

/**
 * Test if a give filesystem path is absolute ('/foo/bar', 'c:\windows').
 *
 * @since 2.5.0
 *
 * @param string $path File path
 * @return bool True if path is absolute, false is not absolute.
 */
function path_is_absolute( $path ) {
	// this is definitive if true but fails if $path does not exist or contains a symbolic link
	if ( realpath($path) == $path )
		return true;

	if ( strlen($path) == 0 || $path[0] == '.' )
		return false;

	// windows allows absolute paths like this
	if ( preg_match('#^[a-zA-Z]:\\\\#', $path) )
		return true;

	// a path starting with / or \ is absolute; anything else is relative
	return ( $path[0] == '/' || $path[0] == '\\' );
}

/**
 * Join two filesystem paths together (e.g. 'give me $path relative to $base').
 *
 * If the $path is absolute, then it the full path is returned.
 *
 * @since 2.5.0
 *
 * @param string $base
 * @param string $path
 * @return string The path with the base or absolute path.
 */
function path_join( $base, $path ) {
	if ( path_is_absolute($path) )
		return $path;

	return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Determines a writable directory for temporary files.
 * Function's preference is the return value of <code>sys_get_temp_dir()</code>,
 * followed by your PHP temporary upload directory, followed by WP_CONTENT_DIR,
 * before finally defaulting to /tmp/
 *
 * In the event that this function does not find a writable location,
 * It may be overridden by the <code>WP_TEMP_DIR</code> constant in
 * your <code>wp-config.php</code> file.
 *
 * @since 2.5.0
 *
 * @return string Writable temporary directory
 */
function get_temp_dir() {
	static $temp;
	if ( defined('WP_TEMP_DIR') )
		return trailingslashit(WP_TEMP_DIR);

	if ( $temp )
		return trailingslashit( rtrim( $temp, '\\' ) );

	$is_win = ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) );

	if ( function_exists('sys_get_temp_dir') ) {
		$temp = sys_get_temp_dir();
		if ( @is_dir( $temp ) && ( $is_win ? win_is_writable( $temp ) : @is_writable( $temp ) ) ) {
			return trailingslashit( rtrim( $temp, '\\' ) );
		}
	}

	$temp = ini_get('upload_tmp_dir');
	if ( is_dir( $temp ) && ( $is_win ? win_is_writable( $temp ) : @is_writable( $temp ) ) )
		return trailingslashit( rtrim( $temp, '\\' ) );

	$temp = WP_CONTENT_DIR . '/';
	if ( is_dir( $temp ) && ( $is_win ? win_is_writable( $temp ) : @is_writable( $temp ) ) )
		return $temp;

	$temp = '/tmp/';
	return $temp;
}

/**
 * Workaround for Windows bug in is_writable() function
 *
 * @since 2.8.0
 *
 * @param string $path
 * @return bool
 */
function win_is_writable( $path ) {
	/* will work in despite of Windows ACLs bug
	 * NOTE: use a trailing slash for folders!!!
	 * see http://bugs.php.net/bug.php?id=27609
	 * see http://bugs.php.net/bug.php?id=30931
	 */

	if ( $path[strlen( $path ) - 1] == '/' ) // recursively return a temporary file path
		return win_is_writable( $path . uniqid( mt_rand() ) . '.tmp');
	else if ( is_dir( $path ) )
		return win_is_writable( $path . '/' . uniqid( mt_rand() ) . '.tmp' );
	// check tmp file for read/write capabilities
	$should_delete_tmp_file = !file_exists( $path );
	$f = @fopen( $path, 'a' );
	if ( $f === false )
		return false;
	fclose( $f );
	if ( $should_delete_tmp_file )
		unlink( $path );
	return true;
}

/**
 * Get an array containing the current upload directory's path and url.
 *
 * Checks the 'upload_path' option, which should be from the web root folder,
 * and if it isn't empty it will be used. If it is empty, then the path will be
 * 'WP_CONTENT_DIR/uploads'. If the 'UPLOADS' constant is defined, then it will
 * override the 'upload_path' option and 'WP_CONTENT_DIR/uploads' path.
 *
 * The upload URL path is set either by the 'upload_url_path' option or by using
 * the 'WP_CONTENT_URL' constant and appending '/uploads' to the path.
 *
 * If the 'uploads_use_yearmonth_folders' is set to true (checkbox if checked in
 * the administration settings panel), then the time will be used. The format
 * will be year first and then month.
 *
 * If the path couldn't be created, then an error will be returned with the key
 * 'error' containing the error message. The error suggests that the parent
 * directory is not writable by the server.
 *
 * On success, the returned array will have many indices:
 * 'path' - base directory and sub directory or full path to upload directory.
 * 'url' - base url and sub directory or absolute URL to upload directory.
 * 'subdir' - sub directory if uploads use year/month folders option is on.
 * 'basedir' - path without subdir.
 * 'baseurl' - URL path without subdir.
 * 'error' - set to false.
 *
 * @since 2.0.0
 * @uses apply_filters() Calls 'upload_dir' on returned array.
 *
 * @param string $time Optional. Time formatted in 'yyyy/mm'.
 * @return array See above for description.
 */
function wp_upload_dir( $time = null ) {
	$siteurl = get_option( 'siteurl' );
	$upload_path = trim( get_option( 'upload_path' ) );

	if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {
		$dir = WP_CONTENT_DIR . '/uploads';
	} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
		// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
		$dir = path_join( ABSPATH, $upload_path );
	} else {
		$dir = $upload_path;
	}

	if ( !$url = get_option( 'upload_url_path' ) ) {
		if ( empty($upload_path) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) )
			$url = WP_CONTENT_URL . '/uploads';
		else
			$url = trailingslashit( $siteurl ) . $upload_path;
	}

	// Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
	// We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
	if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {
		$dir = ABSPATH . UPLOADS;
		$url = trailingslashit( $siteurl ) . UPLOADS;
	}

	// If multisite (and if not the main site in a post-MU network)
	if ( is_multisite() && ! ( is_main_site() && defined( 'MULTISITE' ) ) ) {

		if ( ! get_site_option( 'ms_files_rewriting' ) ) {
			// If ms-files rewriting is disabled (networks created post-3.5), it is fairly straightforward:
			// Append sites/%d if we're not on the main site (for post-MU networks). (The extra directory
			// prevents a four-digit ID from conflicting with a year-based directory for the main site.
			// But if a MU-era network has disabled ms-files rewriting manually, they don't need the extra
			// directory, as they never had wp-content/uploads for the main site.)

			if ( defined( 'MULTISITE' ) )
				$ms_dir = '/sites/' . get_current_blog_id();
			else
				$ms_dir = '/' . get_current_blog_id();

			$dir .= $ms_dir;
			$url .= $ms_dir;

		} elseif ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
			// Handle the old-form ms-files.php rewriting if the network still has that enabled.
			// When ms-files rewriting is enabled, then we only listen to UPLOADS when:
			//   1) we are not on the main site in a post-MU network,
			//      as wp-content/uploads is used there, and
			//   2) we are not switched, as ms_upload_constants() hardcodes
			//      these constants to reflect the original blog ID.
			//
			// Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
			// (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
			// as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
			// rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)

			if ( defined( 'BLOGUPLOADDIR' ) )
				$dir = untrailingslashit( BLOGUPLOADDIR );
			else
				$dir = ABSPATH . UPLOADS;
			$url = trailingslashit( $siteurl ) . 'files';
		}
	}

	$basedir = $dir;
	$baseurl = $url;

	$subdir = '';
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		if ( !$time )
			$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$subdir = "/$y/$m";
	}

	$dir .= $subdir;
	$url .= $subdir;

	$uploads = apply_filters( 'upload_dir',
		array(
			'path'    => $dir,
			'url'     => $url,
			'subdir'  => $subdir,
			'basedir' => $basedir,
			'baseurl' => $baseurl,
			'error'   => false,
		) );

	// Make sure we have an uploads dir
	if ( ! wp_mkdir_p( $uploads['path'] ) ) {
		if ( 0 === strpos( $uploads['basedir'], ABSPATH ) )
			$error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
		else
			$error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];

		$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $error_path );
		$uploads['error'] = $message;
	}

	return $uploads;
}

/**
 * Get a filename that is sanitized and unique for the given directory.
 *
 * If the filename is not unique, then a number will be added to the filename
 * before the extension, and will continue adding numbers until the filename is
 * unique.
 *
 * The callback is passed three parameters, the first one is the directory, the
 * second is the filename, and the third is the extension.
 *
 * @since 2.5.0
 *
 * @param string $dir
 * @param string $filename
 * @param mixed $unique_filename_callback Callback.
 * @return string New filename, if given wasn't unique.
 */
function wp_unique_filename( $dir, $filename, $unique_filename_callback = null ) {
	// sanitize the file name before we begin processing
	$filename = sanitize_file_name($filename);

	// separate the filename into a name and extension
	$info = pathinfo($filename);
	$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
	$name = basename($filename, $ext);

	// edge case: if file is named '.ext', treat as an empty name
	if ( $name === $ext )
		$name = '';

	// Increment the file number until we have a unique file to save in $dir. Use callback if supplied.
	if ( $unique_filename_callback && is_callable( $unique_filename_callback ) ) {
		$filename = call_user_func( $unique_filename_callback, $dir, $name, $ext );
	} else {
		$number = '';

		// change '.ext' to lower case
		if ( $ext && strtolower($ext) != $ext ) {
			$ext2 = strtolower($ext);
			$filename2 = preg_replace( '|' . preg_quote($ext) . '$|', $ext2, $filename );

			// check for both lower and upper case extension or image sub-sizes may be overwritten
			while ( file_exists($dir . "/$filename") || file_exists($dir . "/$filename2") ) {
				$new_number = $number + 1;
				$filename = str_replace( "$number$ext", "$new_number$ext", $filename );
				$filename2 = str_replace( "$number$ext2", "$new_number$ext2", $filename2 );
				$number = $new_number;
			}
			return $filename2;
		}

		while ( file_exists( $dir . "/$filename" ) ) {
			if ( '' == "$number$ext" )
				$filename = $filename . ++$number . $ext;
			else
				$filename = str_replace( "$number$ext", ++$number . $ext, $filename );
		}
	}

	return $filename;
}

/**
 * Create a file in the upload folder with given content.
 *
 * If there is an error, then the key 'error' will exist with the error message.
 * If success, then the key 'file' will have the unique file path, the 'url' key
 * will have the link to the new file. and the 'error' key will be set to false.
 *
 * This function will not move an uploaded file to the upload folder. It will
 * create a new file with the content in $bits parameter. If you move the upload
 * file, read the content of the uploaded file, and then you can give the
 * filename and content to this function, which will add it to the upload
 * folder.
 *
 * The permissions will be set on the new file automatically by this function.
 *
 * @since 2.0.0
 *
 * @param string $name
 * @param null $deprecated Never used. Set to null.
 * @param mixed $bits File content
 * @param string $time Optional. Time formatted in 'yyyy/mm'.
 * @return array
 */
function wp_upload_bits( $name, $deprecated, $bits, $time = null ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.0' );

	if ( empty( $name ) )
		return array( 'error' => __( 'Empty filename' ) );

	$wp_filetype = wp_check_filetype( $name );
	if ( ! $wp_filetype['ext'] && ! current_user_can( 'unfiltered_upload' ) )
		return array( 'error' => __( 'Invalid file type' ) );

	$upload = wp_upload_dir( $time );

	if ( $upload['error'] !== false )
		return $upload;

	$upload_bits_error = apply_filters( 'wp_upload_bits', array( 'name' => $name, 'bits' => $bits, 'time' => $time ) );
	if ( !is_array( $upload_bits_error ) ) {
		$upload[ 'error' ] = $upload_bits_error;
		return $upload;
	}

	$filename = wp_unique_filename( $upload['path'], $name );

	$new_file = $upload['path'] . "/$filename";
	if ( ! wp_mkdir_p( dirname( $new_file ) ) ) {
		if ( 0 === strpos( $upload['basedir'], ABSPATH ) )
			$error_path = str_replace( ABSPATH, '', $upload['basedir'] ) . $upload['subdir'];
		else
			$error_path = basename( $upload['basedir'] ) . $upload['subdir'];

		$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $error_path );
		return array( 'error' => $message );
	}

	$ifp = @ fopen( $new_file, 'wb' );
	if ( ! $ifp )
		return array( 'error' => sprintf( __( 'Could not write file %s' ), $new_file ) );

	@fwrite( $ifp, $bits );
	fclose( $ifp );
	clearstatcache();

	// Set correct file permissions
	$stat = @ stat( dirname( $new_file ) );
	$perms = $stat['mode'] & 0007777;
	$perms = $perms & 0000666;
	@ chmod( $new_file, $perms );
	clearstatcache();

	// Compute the URL
	$url = $upload['url'] . "/$filename";

	return array( 'file' => $new_file, 'url' => $url, 'error' => false );
}

/**
 * Retrieve the file type based on the extension name.
 *
 * @package WordPress
 * @since 2.5.0
 * @uses apply_filters() Calls 'ext2type' hook on default supported types.
 *
 * @param string $ext The extension to search.
 * @return string|null The file type, example: audio, video, document, spreadsheet, etc. Null if not found.
 */
function wp_ext2type( $ext ) {
	$ext2type = apply_filters( 'ext2type', array(
		'audio'       => array( 'aac', 'ac3',  'aif',  'aiff', 'm3a',  'm4a',   'm4b',  'mka',  'mp1',  'mp2',  'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma' ),
		'video'       => array( 'asf', 'avi',  'divx', 'dv',   'flv',  'm4v',   'mkv',  'mov',  'mp4',  'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt',  'rm', 'vob', 'wmv' ),
		'document'    => array( 'doc', 'docx', 'docm', 'dotm', 'odt',  'pages', 'pdf',  'rtf',  'wp',   'wpd' ),
		'spreadsheet' => array( 'numbers',     'ods',  'xls',  'xlsx', 'xlsm',  'xlsb' ),
		'interactive' => array( 'swf', 'key',  'ppt',  'pptx', 'pptm', 'pps',   'ppsx', 'ppsm', 'sldx', 'sldm', 'odp' ),
		'text'        => array( 'asc', 'csv',  'tsv',  'txt' ),
		'archive'     => array( 'bz2', 'cab',  'dmg',  'gz',   'rar',  'sea',   'sit',  'sqx',  'tar',  'tgz',  'zip', '7z' ),
		'code'        => array( 'css', 'htm',  'html', 'php',  'js' ),
	));
	foreach ( $ext2type as $type => $exts )
		if ( in_array( $ext, $exts ) )
			return $type;
}

/**
 * Retrieve the file type from the file name.
 *
 * You can optionally define the mime array, if needed.
 *
 * @since 2.0.4
 *
 * @param string $filename File name or path.
 * @param array $mimes Optional. Key is the file extension with value as the mime type.
 * @return array Values with extension first and mime type.
 */
function wp_check_filetype( $filename, $mimes = null ) {
	if ( empty($mimes) )
		$mimes = get_allowed_mime_types();
	$type = false;
	$ext = false;

	foreach ( $mimes as $ext_preg => $mime_match ) {
		$ext_preg = '!\.(' . $ext_preg . ')$!i';
		if ( preg_match( $ext_preg, $filename, $ext_matches ) ) {
			$type = $mime_match;
			$ext = $ext_matches[1];
			break;
		}
	}

	return compact( 'ext', 'type' );
}

/**
 * Attempt to determine the real file type of a file.
 * If unable to, the file name extension will be used to determine type.
 *
 * If it's determined that the extension does not match the file's real type,
 * then the "proper_filename" value will be set with a proper filename and extension.
 *
 * Currently this function only supports validating images known to getimagesize().
 *
 * @since 3.0.0
 *
 * @param string $file Full path to the image.
 * @param string $filename The filename of the image (may differ from $file due to $file being in a tmp directory)
 * @param array $mimes Optional. Key is the file extension with value as the mime type.
 * @return array Values for the extension, MIME, and either a corrected filename or false if original $filename is valid
 */
function wp_check_filetype_and_ext( $file, $filename, $mimes = null ) {

	$proper_filename = false;

	// Do basic extension validation and MIME mapping
	$wp_filetype = wp_check_filetype( $filename, $mimes );
	extract( $wp_filetype );

	// We can't do any further validation without a file to work with
	if ( ! file_exists( $file ) )
		return compact( 'ext', 'type', 'proper_filename' );

	// We're able to validate images using GD
	if ( $type && 0 === strpos( $type, 'image/' ) && function_exists('getimagesize') ) {

		// Attempt to figure out what type of image it actually is
		$imgstats = @getimagesize( $file );

		// If getimagesize() knows what kind of image it really is and if the real MIME doesn't match the claimed MIME
		if ( !empty($imgstats['mime']) && $imgstats['mime'] != $type ) {
			// This is a simplified array of MIMEs that getimagesize() can detect and their extensions
			// You shouldn't need to use this filter, but it's here just in case
			$mime_to_ext = apply_filters( 'getimagesize_mimes_to_exts', array(
				'image/jpeg' => 'jpg',
				'image/png'  => 'png',
				'image/gif'  => 'gif',
				'image/bmp'  => 'bmp',
				'image/tiff' => 'tif',
			) );

			// Replace whatever is after the last period in the filename with the correct extension
			if ( ! empty( $mime_to_ext[ $imgstats['mime'] ] ) ) {
				$filename_parts = explode( '.', $filename );
				array_pop( $filename_parts );
				$filename_parts[] = $mime_to_ext[ $imgstats['mime'] ];
				$new_filename = implode( '.', $filename_parts );

				if ( $new_filename != $filename )
					$proper_filename = $new_filename; // Mark that it changed

				// Redefine the extension / MIME
				$wp_filetype = wp_check_filetype( $new_filename, $mimes );
				extract( $wp_filetype );
			}
		}
	}

	// Let plugins try and validate other types of files
	// Should return an array in the style of array( 'ext' => $ext, 'type' => $type, 'proper_filename' => $proper_filename )
	return apply_filters( 'wp_check_filetype_and_ext', compact( 'ext', 'type', 'proper_filename' ), $file, $filename, $mimes );
}

/**
 * Retrieve list of mime types and file extensions.
 *
 * @since 3.5.0
 *
 * @uses apply_filters() Calls 'mime_types' on returned array. This filter should
 * be used to add types, not remove them. To remove types use the upload_mimes filter.
 *
 * @return array Array of mime types keyed by the file extension regex corresponding to those types.
 */
function wp_get_mime_types() {
	// Accepted MIME types are set here as PCRE unless provided.
	return apply_filters( 'mime_types', array(
	// Image formats
	'jpg|jpeg|jpe' => 'image/jpeg',
	'gif' => 'image/gif',
	'png' => 'image/png',
	'bmp' => 'image/bmp',
	'tif|tiff' => 'image/tiff',
	'ico' => 'image/x-icon',
	// Video formats
	'asf|asx|wax|wmv|wmx' => 'video/asf',
	'avi' => 'video/avi',
	'divx' => 'video/divx',
	'flv' => 'video/x-flv',
	'mov|qt' => 'video/quicktime',
	'mpeg|mpg|mpe' => 'video/mpeg',
	'mp4|m4v' => 'video/mp4',
	'ogv' => 'video/ogg',
	'mkv' => 'video/x-matroska',
	// Text formats
	'txt|asc|c|cc|h' => 'text/plain',
	'csv' => 'text/csv',
	'tsv' => 'text/tab-separated-values',
	'ics' => 'text/calendar',
	'rtx' => 'text/richtext',
	'css' => 'text/css',
	'htm|html' => 'text/html',
	// Audio formats
	'mp3|m4a|m4b' => 'audio/mpeg',
	'ra|ram' => 'audio/x-realaudio',
	'wav' => 'audio/wav',
	'ogg|oga' => 'audio/ogg',
	'mid|midi' => 'audio/midi',
	'wma' => 'audio/wma',
	'mka' => 'audio/x-matroska',
	// Misc application formats
	'rtf' => 'application/rtf',
	'js' => 'application/javascript',
	'pdf' => 'application/pdf',
	'swf' => 'application/x-shockwave-flash',
	'class' => 'application/java',
	'tar' => 'application/x-tar',
	'zip' => 'application/zip',
	'gz|gzip' => 'application/x-gzip',
	'rar' => 'application/rar',
	'7z' => 'application/x-7z-compressed',
	'exe' => 'application/x-msdownload',
	// MS Office formats
	'doc' => 'application/msword',
	'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
	'wri' => 'application/vnd.ms-write',
	'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
	'mdb' => 'application/vnd.ms-access',
	'mpp' => 'application/vnd.ms-project',
	'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
	'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
	'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
	'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
	'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
	'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
	'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
	'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
	'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
	'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
	'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
	'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
	'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
	'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
	'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
	'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
	'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
	// OpenOffice formats
	'odt' => 'application/vnd.oasis.opendocument.text',
	'odp' => 'application/vnd.oasis.opendocument.presentation',
	'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	'odg' => 'application/vnd.oasis.opendocument.graphics',
	'odc' => 'application/vnd.oasis.opendocument.chart',
	'odb' => 'application/vnd.oasis.opendocument.database',
	'odf' => 'application/vnd.oasis.opendocument.formula',
	// WordPerfect formats
	'wp|wpd' => 'application/wordperfect',
	) );
}
/**
 * Retrieve list of allowed mime types and file extensions.
 *
 * @since 2.8.6
 *
 * @uses apply_filters() Calls 'upload_mimes' on returned array
 * @uses wp_get_upload_mime_types() to fetch the list of mime types
 *
 * @return array Array of mime types keyed by the file extension regex corresponding to those types.
 */
function get_allowed_mime_types() {
	return apply_filters( 'upload_mimes', wp_get_mime_types() );
}

/**
 * Display "Are You Sure" message to confirm the action being taken.
 *
 * If the action has the nonce explain message, then it will be displayed along
 * with the "Are you sure?" message.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @param string $action The nonce action.
 */
function wp_nonce_ays( $action ) {
	$title = __( 'WordPress Failure Notice' );
	if ( 'log-out' == $action ) {
		$html = sprintf( __( 'You are attempting to log out of %s' ), get_bloginfo( 'name' ) ) . '</p><p>';
		$html .= sprintf( __( "Do you really want to <a href='%s'>log out</a>?"), wp_logout_url() );
	} else {
		$html = __( 'Are you sure you want to do this?' );
		if ( wp_get_referer() )
			$html .= "</p><p><a href='" . esc_url( remove_query_arg( 'updated', wp_get_referer() ) ) . "'>" . __( 'Please try again.' ) . "</a>";
	}

	wp_die( $html, $title, array('response' => 403) );
}

/**
 * Kill WordPress execution and display HTML message with error message.
 *
 * This function complements the die() PHP function. The difference is that
 * HTML will be displayed to the user. It is recommended to use this function
 * only, when the execution should not continue any further. It is not
 * recommended to call this function very often and try to handle as many errors
 * as possible silently.
 *
 * @since 2.0.4
 *
 * @param string $message Error message.
 * @param string $title Error title.
 * @param string|array $args Optional arguments to control behavior.
 */
function wp_die( $message = '', $title = '', $args = array() ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		$function = apply_filters( 'wp_die_ajax_handler', '_ajax_wp_die_handler' );
	elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		$function = apply_filters( 'wp_die_xmlrpc_handler', '_xmlrpc_wp_die_handler' );
	else
		$function = apply_filters( 'wp_die_handler', '_default_wp_die_handler' );

	call_user_func( $function, $message, $title, $args );
}

/**
 * Kill WordPress execution and display HTML message with error message.
 *
 * This is the default handler for wp_die if you want a custom one for your
 * site then you can overload using the wp_die_handler filter in wp_die
 *
 * @since 3.0.0
 * @access private
 *
 * @param string $message Error message.
 * @param string $title Error title.
 * @param string|array $args Optional arguments to control behavior.
 */
function _default_wp_die_handler( $message, $title = '', $args = array() ) {
	$defaults = array( 'response' => 500 );
	$r = wp_parse_args($args, $defaults);

	$have_gettext = function_exists('__');

	if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
		if ( empty( $title ) ) {
			$error_data = $message->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['title'] ) )
				$title = $error_data['title'];
		}
		$errors = $message->get_error_messages();
		switch ( count( $errors ) ) :
		case 0 :
			$message = '';
			break;
		case 1 :
			$message = "<p>{$errors[0]}</p>";
			break;
		default :
			$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
			break;
		endswitch;
	} elseif ( is_string( $message ) ) {
		$message = "<p>$message</p>";
	}

	if ( isset( $r['back_link'] ) && $r['back_link'] ) {
		$back_text = $have_gettext? __('&laquo; Back') : '&laquo; Back';
		$message .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
	}

	if ( ! did_action( 'admin_head' ) ) :
		if ( !headers_sent() ) {
			status_header( $r['response'] );
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
		}

		if ( empty($title) )
			$title = $have_gettext ? __('WordPress &rsaquo; Error') : 'WordPress &rsaquo; Error';

		$text_direction = 'ltr';
		if ( isset($r['text_direction']) && 'rtl' == $r['text_direction'] )
			$text_direction = 'rtl';
		elseif ( function_exists( 'is_rtl' ) && is_rtl() )
			$text_direction = 'rtl';
?>
<!DOCTYPE html>
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title ?></title>
	<style type="text/css">
		html {
			background: #f9f9f9;
		}
		body {
			background: #fff;
			color: #333;
			font-family: sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			-webkit-border-radius: 3px;
			border-radius: 3px;
			border: 1px solid #dfdfdf;
			max-width: 700px;
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font: 24px Georgia, "Times New Roman", Times, serif;
			margin: 30px 0 0 0;
			padding: 0;
			padding-bottom: 7px;
		}
		#error-page {
			margin-top: 50px;
		}
		#error-page p {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		#error-page code {
			font-family: Consolas, Monaco, monospace;
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #21759B;
			text-decoration: none;
		}
		a:hover {
			color: #D54E21;
		}
		.button {
			display: inline-block;
			text-decoration: none;
			font-size: 14px;
			line-height: 23px;
			height: 24px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			border-width: 1px;
			border-style: solid;
			-webkit-border-radius: 3px;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;
			background: #f3f3f3;
			background-image: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#f4f4f4));
			background-image: -webkit-linear-gradient(top, #fefefe, #f4f4f4);
			background-image:    -moz-linear-gradient(top, #fefefe, #f4f4f4);
			background-image:      -o-linear-gradient(top, #fefefe, #f4f4f4);
			background-image:   linear-gradient(to bottom, #fefefe, #f4f4f4);
			border-color: #bbb;
		 	color: #333;
			text-shadow: 0 1px 0 #fff;
		}

		.button.button-large {
			height: 29px;
			line-height: 28px;
			padding: 0 12px;
		}

		.button:hover,
		.button:focus {
			background: #f3f3f3;
			background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#f3f3f3));
			background-image: -webkit-linear-gradient(top, #fff, #f3f3f3);
			background-image:    -moz-linear-gradient(top, #fff, #f3f3f3);
			background-image:     -ms-linear-gradient(top, #fff, #f3f3f3);
			background-image:      -o-linear-gradient(top, #fff, #f3f3f3);
			background-image:   linear-gradient(to bottom, #fff, #f3f3f3);
			border-color: #999;
			color: #222;
		}

		.button:focus  {
			-webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
			box-shadow: 1px 1px 1px rgba(0,0,0,.2);
		}

		.button:active {
			outline: none;
			background: #eee;
			background-image: -webkit-gradient(linear, left top, left bottom, from(#f4f4f4), to(#fefefe));
			background-image: -webkit-linear-gradient(top, #f4f4f4, #fefefe);
			background-image:    -moz-linear-gradient(top, #f4f4f4, #fefefe);
			background-image:     -ms-linear-gradient(top, #f4f4f4, #fefefe);
			background-image:      -o-linear-gradient(top, #f4f4f4, #fefefe);
			background-image:   linear-gradient(to bottom, #f4f4f4, #fefefe);
			border-color: #999;
			color: #333;
			text-shadow: 0 -1px 0 #fff;
			-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
		 	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
		}

		<?php if ( 'rtl' == $text_direction ) : ?>
		body { font-family: Tahoma, Arial; }
		<?php endif; ?>
	</style>
</head>
<body id="error-page">
<?php endif; // ! did_action( 'admin_head' ) ?>
	<?php echo $message; ?>
</body>
</html>
<?php
	die();
}

/**
 * Kill WordPress execution and display XML message with error message.
 *
 * This is the handler for wp_die when processing XMLRPC requests.
 *
 * @since 3.2.0
 * @access private
 *
 * @param string $message Error message.
 * @param string $title Error title.
 * @param string|array $args Optional arguments to control behavior.
 */
function _xmlrpc_wp_die_handler( $message, $title = '', $args = array() ) {
	global $wp_xmlrpc_server;
	$defaults = array( 'response' => 500 );

	$r = wp_parse_args($args, $defaults);

	if ( $wp_xmlrpc_server ) {
		$error = new IXR_Error( $r['response'] , $message);
		$wp_xmlrpc_server->output( $error->getXml() );
	}
	die();
}

/**
 * Kill WordPress ajax execution.
 *
 * This is the handler for wp_die when processing Ajax requests.
 *
 * @since 3.4.0
 * @access private
 *
 * @param string $message Optional. Response to print.
 */
function _ajax_wp_die_handler( $message = '' ) {
	if ( is_scalar( $message ) )
		die( (string) $message );
	die( '0' );
}

/**
 * Kill WordPress execution.
 *
 * This is the handler for wp_die when processing APP requests.
 *
 * @since 3.4.0
 * @access private
 *
 * @param string $message Optional. Response to print.
 */
function _scalar_wp_die_handler( $message = '' ) {
	if ( is_scalar( $message ) )
		die( (string) $message );
	die();
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @since 3.5.0
 *
 * @param mixed $response Variable (usually an array or object) to encode as JSON, then print and die.
 */
function wp_send_json( $response ) {
	@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	echo json_encode( $response );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		wp_die();
	else
		die;
}

/**
 * Send a JSON response back to an Ajax request, indicating success.
 *
 * @since 3.5.0
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
function wp_send_json_success( $data = null ) {
	$response = array( 'success' => true );

	if ( isset( $data ) )
		$response['data'] = $data;

	wp_send_json( $response );
}

/**
 * Send a JSON response back to an Ajax request, indicating failure.
 *
 * @since 3.5.0
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
function wp_send_json_error( $data = null ) {
	$response = array( 'success' => false );

	if ( isset( $data ) )
		$response['data'] = $data;

	wp_send_json( $response );
}

/**
 * Retrieve the WordPress home page URL.
 *
 * If the constant named 'WP_HOME' exists, then it will be used and returned by
 * the function. This can be used to counter the redirection on your local
 * development environment.
 *
 * @access private
 * @package WordPress
 * @since 2.2.0
 *
 * @param string $url URL for the home location
 * @return string Homepage location.
 */
function _config_wp_home( $url = '' ) {
	if ( defined( 'WP_HOME' ) )
		return untrailingslashit( WP_HOME );
	return $url;
}

/**
 * Retrieve the WordPress site URL.
 *
 * If the constant named 'WP_SITEURL' is defined, then the value in that
 * constant will always be returned. This can be used for debugging a site on
 * your localhost while not having to change the database to your URL.
 *
 * @access private
 * @package WordPress
 * @since 2.2.0
 *
 * @param string $url URL to set the WordPress site location.
 * @return string The WordPress Site URL
 */
function _config_wp_siteurl( $url = '' ) {
	if ( defined( 'WP_SITEURL' ) )
		return untrailingslashit( WP_SITEURL );
	return $url;
}

/**
 * Set the localized direction for MCE plugin.
 *
 * Will only set the direction to 'rtl', if the WordPress locale has the text
 * direction set to 'rtl'.
 *
 * Fills in the 'directionality', 'plugins', and 'theme_advanced_button1' array
 * keys. These keys are then returned in the $input array.
 *
 * @access private
 * @package WordPress
 * @subpackage MCE
 * @since 2.1.0
 *
 * @param array $input MCE plugin array.
 * @return array Direction set for 'rtl', if needed by locale.
 */
function _mce_set_direction( $input ) {
	if ( is_rtl() ) {
		$input['directionality'] = 'rtl';
		$input['plugins'] .= ',directionality';
		$input['theme_advanced_buttons1'] .= ',ltr';
	}

	return $input;
}

/**
 * Convert smiley code to the icon graphic file equivalent.
 *
 * You can turn off smilies, by going to the write setting screen and unchecking
 * the box, or by setting 'use_smilies' option to false or removing the option.
 *
 * Plugins may override the default smiley list by setting the $wpsmiliestrans
 * to an array, with the key the code the blogger types in and the value the
 * image file.
 *
 * The $wp_smiliessearch global is for the regular expression and is set each
 * time the function is called.
 *
 * The full list of smilies can be found in the function and won't be listed in
 * the description. Probably should create a Codex page for it, so that it is
 * available.
 *
 * @global array $wpsmiliestrans
 * @global array $wp_smiliessearch
 * @since 2.2.0
 */
function smilies_init() {
	global $wpsmiliestrans, $wp_smiliessearch;

	// don't bother setting up smilies if they are disabled
	if ( !get_option( 'use_smilies' ) )
		return;

	if ( !isset( $wpsmiliestrans ) ) {
		$wpsmiliestrans = array(
		':mrgreen:' => 'icon_mrgreen.gif',
		':neutral:' => 'icon_neutral.gif',
		':twisted:' => 'icon_twisted.gif',
		  ':arrow:' => 'icon_arrow.gif',
		  ':shock:' => 'icon_eek.gif',
		  ':smile:' => 'icon_smile.gif',
		    ':???:' => 'icon_confused.gif',
		   ':cool:' => 'icon_cool.gif',
		   ':evil:' => 'icon_evil.gif',
		   ':grin:' => 'icon_biggrin.gif',
		   ':idea:' => 'icon_idea.gif',
		   ':oops:' => 'icon_redface.gif',
		   ':razz:' => 'icon_razz.gif',
		   ':roll:' => 'icon_rolleyes.gif',
		   ':wink:' => 'icon_wink.gif',
		    ':cry:' => 'icon_cry.gif',
		    ':eek:' => 'icon_surprised.gif',
		    ':lol:' => 'icon_lol.gif',
		    ':mad:' => 'icon_mad.gif',
		    ':sad:' => 'icon_sad.gif',
		      '8-)' => 'icon_cool.gif',
		      '8-O' => 'icon_eek.gif',
		      ':-(' => 'icon_sad.gif',
		      ':-)' => 'icon_smile.gif',
		      ':-?' => 'icon_confused.gif',
		      ':-D' => 'icon_biggrin.gif',
		      ':-P' => 'icon_razz.gif',
		      ':-o' => 'icon_surprised.gif',
		      ':-x' => 'icon_mad.gif',
		      ':-|' => 'icon_neutral.gif',
		      ';-)' => 'icon_wink.gif',
		// This one transformation breaks regular text with frequency.
		//     '8)' => 'icon_cool.gif',
		       '8O' => 'icon_eek.gif',
		       ':(' => 'icon_sad.gif',
		       ':)' => 'icon_smile.gif',
		       ':?' => 'icon_confused.gif',
		       ':D' => 'icon_biggrin.gif',
		       ':P' => 'icon_razz.gif',
		       ':o' => 'icon_surprised.gif',
		       ':x' => 'icon_mad.gif',
		       ':|' => 'icon_neutral.gif',
		       ';)' => 'icon_wink.gif',
		      ':!:' => 'icon_exclaim.gif',
		      ':?:' => 'icon_question.gif',
		);
	}

	if (count($wpsmiliestrans) == 0) {
		return;
	}

	/*
	 * NOTE: we sort the smilies in reverse key order. This is to make sure
	 * we match the longest possible smilie (:???: vs :?) as the regular
	 * expression used below is first-match
	 */
	krsort($wpsmiliestrans);

	$wp_smiliessearch = '/(?:\s|^)';

	$subchar = '';
	foreach ( (array) $wpsmiliestrans as $smiley => $img ) {
		$firstchar = substr($smiley, 0, 1);
		$rest = substr($smiley, 1);

		// new subpattern?
		if ($firstchar != $subchar) {
			if ($subchar != '') {
				$wp_smiliessearch .= ')|(?:\s|^)';
			}
			$subchar = $firstchar;
			$wp_smiliessearch .= preg_quote($firstchar, '/') . '(?:';
		} else {
			$wp_smiliessearch .= '|';
		}
		$wp_smiliessearch .= preg_quote($rest, '/');
	}

	$wp_smiliessearch .= ')(?:\s|$)/m';
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for both string or array
 * to be merged into another array.
 *
 * @since 2.2.0
 *
 * @param string|array $args Value to merge with $defaults
 * @param array $defaults Array that serves as the defaults.
 * @return array Merged user defined values with defaults.
 */
function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

/**
 * Clean up an array, comma- or space-separated list of IDs.
 *
 * @since 3.0.0
 *
 * @param array|string $list
 * @return array Sanitized array of IDs
 */
function wp_parse_id_list( $list ) {
	if ( !is_array($list) )
		$list = preg_split('/[\s,]+/', $list);

	return array_unique(array_map('absint', $list));
}

/**
 * Extract a slice of an array, given a list of keys.
 *
 * @since 3.1.0
 *
 * @param array $array The original array
 * @param array $keys The list of keys
 * @return array The array slice
 */
function wp_array_slice_assoc( $array, $keys ) {
	$slice = array();
	foreach ( $keys as $key )
		if ( isset( $array[ $key ] ) )
			$slice[ $key ] = $array[ $key ];

	return $slice;
}

/**
 * Filters a list of objects, based on a set of key => value arguments.
 *
 * @since 3.0.0
 *
 * @param array $list An array of objects to filter
 * @param array $args An array of key => value arguments to match against each object
 * @param string $operator The logical operation to perform. 'or' means only one element
 *	from the array needs to match; 'and' means all elements must match. The default is 'and'.
 * @param bool|string $field A field from the object to place instead of the entire object
 * @return array A list of objects or object fields
 */
function wp_filter_object_list( $list, $args = array(), $operator = 'and', $field = false ) {
	if ( ! is_array( $list ) )
		return array();

	$list = wp_list_filter( $list, $args, $operator );

	if ( $field )
		$list = wp_list_pluck( $list, $field );

	return $list;
}

/**
 * Filters a list of objects, based on a set of key => value arguments.
 *
 * @since 3.1.0
 *
 * @param array $list An array of objects to filter
 * @param array $args An array of key => value arguments to match against each object
 * @param string $operator The logical operation to perform:
 *    'AND' means all elements from the array must match;
 *    'OR' means only one element needs to match;
 *    'NOT' means no elements may match.
 *   The default is 'AND'.
 * @return array
 */
function wp_list_filter( $list, $args = array(), $operator = 'AND' ) {
	if ( ! is_array( $list ) )
		return array();

	if ( empty( $args ) )
		return $list;

	$operator = strtoupper( $operator );
	$count = count( $args );
	$filtered = array();

	foreach ( $list as $key => $obj ) {
		$to_match = (array) $obj;

		$matched = 0;
		foreach ( $args as $m_key => $m_value ) {
			if ( array_key_exists( $m_key, $to_match ) && $m_value == $to_match[ $m_key ] )
				$matched++;
		}

		if ( ( 'AND' == $operator && $matched == $count )
		  || ( 'OR' == $operator && $matched > 0 )
		  || ( 'NOT' == $operator && 0 == $matched ) ) {
			$filtered[$key] = $obj;
		}
	}

	return $filtered;
}

/**
 * Pluck a certain field out of each object in a list.
 *
 * @since 3.1.0
 *
 * @param array $list A list of objects or arrays
 * @param int|string $field A field from the object to place instead of the entire object
 * @return array
 */
function wp_list_pluck( $list, $field ) {
	foreach ( $list as $key => $value ) {
		if ( is_object( $value ) )
			$list[ $key ] = $value->$field;
		else
			$list[ $key ] = $value[ $field ];
	}

	return $list;
}

/**
 * Determines if Widgets library should be loaded.
 *
 * Checks to make sure that the widgets library hasn't already been loaded. If
 * it hasn't, then it will load the widgets library and run an action hook.
 *
 * @since 2.2.0
 * @uses add_action() Calls '_admin_menu' hook with 'wp_widgets_add_menu' value.
 */
function wp_maybe_load_widgets() {
	if ( ! apply_filters('load_default_widgets', true) )
		return;
	require_once( ABSPATH . WPINC . '/default-widgets.php' );
	add_action( '_admin_menu', 'wp_widgets_add_menu' );
}

/**
 * Append the Widgets menu to the themes main menu.
 *
 * @since 2.2.0
 * @uses $submenu The administration submenu list.
 */
function wp_widgets_add_menu() {
	global $submenu;

	if ( ! current_theme_supports( 'widgets' ) )
		return;

	$submenu['themes.php'][7] = array( __( 'Widgets' ), 'edit_theme_options', 'widgets.php' );
	ksort( $submenu['themes.php'], SORT_NUMERIC );
}

/**
 * Flush all output buffers for PHP 5.2.
 *
 * Make sure all output buffers are flushed before our singletons our destroyed.
 *
 * @since 2.2.0
 */
function wp_ob_end_flush_all() {
	$levels = ob_get_level();
	for ($i=0; $i<$levels; $i++)
		ob_end_flush();
}

/**
 * Load custom DB error or display WordPress DB error.
 *
 * If a file exists in the wp-content directory named db-error.php, then it will
 * be loaded instead of displaying the WordPress DB error. If it is not found,
 * then the WordPress DB error will be displayed instead.
 *
 * The WordPress DB error sets the HTTP status header to 500 to try to prevent
 * search engines from caching the message. Custom DB messages should do the
 * same.
 *
 * This function was backported to the the WordPress 2.3.2, but originally was
 * added in WordPress 2.5.0.
 *
 * @since 2.3.2
 * @uses $wpdb
 */
function dead_db() {
	global $wpdb;

	// Load custom DB error template, if present.
	if ( file_exists( WP_CONTENT_DIR . '/db-error.php' ) ) {
		require_once( WP_CONTENT_DIR . '/db-error.php' );
		die();
	}

	// If installing or in the admin, provide the verbose message.
	if ( defined('WP_INSTALLING') || defined('WP_ADMIN') )
		wp_die($wpdb->error);

	// Otherwise, be terse.
	status_header( 500 );
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );

	wp_load_translations_early();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php _e( 'Database Error' ); ?></title>

</head>
<body>
	<h1><?php _e( 'Error establishing a database connection' ); ?></h1>
</body>
</html>
<?php
	die();
}

/**
 * Converts value to nonnegative integer.
 *
 * @since 2.5.0
 *
 * @param mixed $maybeint Data you wish to have converted to a nonnegative integer
 * @return int An nonnegative integer
 */
function absint( $maybeint ) {
	return abs( intval( $maybeint ) );
}

/**
 * Determines if the blog can be accessed over SSL.
 *
 * Determines if blog can be accessed over SSL by using cURL to access the site
 * using the https in the siteurl. Requires cURL extension to work correctly.
 *
 * @since 2.5.0
 *
 * @param string $url
 * @return bool Whether SSL access is available
 */
function url_is_accessable_via_ssl($url)
{
	if ( in_array( 'curl', get_loaded_extensions() ) ) {
		$ssl = set_url_scheme( $url, 'https' );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $ssl);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		if ($status == 200 || $status == 401) {
			return true;
		}
	}
	return false;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @package WordPress
 * @subpackage Debug
 * @since 2.5.0
 * @access private
 *
 * @uses do_action() Calls 'deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 */
function _deprecated_function( $function, $version, $replacement = null ) {

	do_action( 'deprecated_function_run', $function, $replacement, $version );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
		if ( ! is_null($replacement) )
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $function, $version, $replacement ) );
		else
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version ) );
	}
}

/**
 * Marks a file as deprecated and informs when it has been used.
 *
 * There is a hook deprecated_file_included that will be called that can be used
 * to get the backtrace up to what file and function included the deprecated
 * file.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every file that is deprecated.
 *
 * @package WordPress
 * @subpackage Debug
 * @since 2.5.0
 * @access private
 *
 * @uses do_action() Calls 'deprecated_file_included' and passes the file name, what to use instead,
 *   the version in which the file was deprecated, and any message regarding the change.
 * @uses apply_filters() Calls 'deprecated_file_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $file The file that was included
 * @param string $version The version of WordPress that deprecated the file
 * @param string $replacement Optional. The file that should have been included based on ABSPATH
 * @param string $message Optional. A message regarding the change
 */
function _deprecated_file( $file, $version, $replacement = null, $message = '' ) {

	do_action( 'deprecated_file_included', $file, $replacement, $version, $message );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'deprecated_file_trigger_error', true ) ) {
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) )
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $file, $version, $replacement ) . $message );
		else
			trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $file, $version ) . $message );
	}
}
/**
 * Marks a function argument as deprecated and informs when it has been used.
 *
 * This function is to be used whenever a deprecated function argument is used.
 * Before this function is called, the argument must be checked for whether it was
 * used by comparing it to its default value or evaluating whether it is empty.
 * For example:
 * <code>
 * if ( !empty($deprecated) )
 * 	_deprecated_argument( __FUNCTION__, '3.0' );
 * </code>
 *
 * There is a hook deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function used the deprecated
 * argument.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @package WordPress
 * @subpackage Debug
 * @since 3.0.0
 * @access private
 *
 * @uses do_action() Calls 'deprecated_argument_run' and passes the function name, a message on the change,
 *   and the version in which the argument was deprecated.
 * @uses apply_filters() Calls 'deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the argument used
 * @param string $message Optional. A message regarding the change.
 */
function _deprecated_argument( $function, $version, $message = null ) {

	do_action( 'deprecated_argument_run', $function, $message, $version );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'deprecated_argument_trigger_error', true ) ) {
		if ( ! is_null( $message ) )
			trigger_error( sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s'), $function, $version, $message ) );
		else
			trigger_error( sprintf( __('%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version ) );
	}
}

/**
 * Marks something as being incorrectly called.
 *
 * There is a hook doing_it_wrong_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @package WordPress
 * @subpackage Debug
 * @since 3.1.0
 * @access private
 *
 * @uses do_action() Calls 'doing_it_wrong_run' and passes the function arguments.
 * @uses apply_filters() Calls 'doing_it_wrong_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called.
 * @param string $message A message explaining what has been done incorrectly.
 * @param string $version The version of WordPress where the message was added.
 */
function _doing_it_wrong( $function, $message, $version ) {

	do_action( 'doing_it_wrong_run', $function, $message, $version );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {
		$version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s.)' ), $version );
		$message .= ' ' . __( 'Please see <a href="http://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a> for more information.' );
		trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s' ), $function, $message, $version ) );
	}
}

/**
 * Is the server running earlier than 1.5.0 version of lighttpd?
 *
 * @since 2.5.0
 *
 * @return bool Whether the server is running lighttpd < 1.5.0
 */
function is_lighttpd_before_150() {
	$server_parts = explode( '/', isset( $_SERVER['SERVER_SOFTWARE'] )? $_SERVER['SERVER_SOFTWARE'] : '' );
	$server_parts[1] = isset( $server_parts[1] )? $server_parts[1] : '';
	return  'lighttpd' == $server_parts[0] && -1 == version_compare( $server_parts[1], '1.5.0' );
}

/**
 * Does the specified module exist in the Apache config?
 *
 * @since 2.5.0
 *
 * @param string $mod e.g. mod_rewrite
 * @param bool $default The default return value if the module is not found
 * @return bool
 */
function apache_mod_loaded($mod, $default = false) {
	global $is_apache;

	if ( !$is_apache )
		return false;

	if ( function_exists('apache_get_modules') ) {
		$mods = apache_get_modules();
		if ( in_array($mod, $mods) )
			return true;
	} elseif ( function_exists('phpinfo') ) {
			ob_start();
			phpinfo(8);
			$phpinfo = ob_get_clean();
			if ( false !== strpos($phpinfo, $mod) )
				return true;
	}
	return $default;
}

/**
 * Check if IIS 7 supports pretty permalinks.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function iis7_supports_permalinks() {
	global $is_iis7;

	$supports_permalinks = false;
	if ( $is_iis7 ) {
		/* First we check if the DOMDocument class exists. If it does not exist,
		 * which is the case for PHP 4.X, then we cannot easily update the xml configuration file,
		 * hence we just bail out and tell user that pretty permalinks cannot be used.
		 * This is not a big issue because PHP 4.X is going to be deprecated and for IIS it
		 * is recommended to use PHP 5.X NTS.
		 * Next we check if the URL Rewrite Module 1.1 is loaded and enabled for the web site. When
		 * URL Rewrite 1.1 is loaded it always sets a server variable called 'IIS_UrlRewriteModule'.
		 * Lastly we make sure that PHP is running via FastCGI. This is important because if it runs
		 * via ISAPI then pretty permalinks will not work.
		 */
		$supports_permalinks = class_exists('DOMDocument') && isset($_SERVER['IIS_UrlRewriteModule']) && ( php_sapi_name() == 'cgi-fcgi' );
	}

	return apply_filters('iis7_supports_permalinks', $supports_permalinks);
}

/**
 * File validates against allowed set of defined rules.
 *
 * A return value of '1' means that the $file contains either '..' or './'. A
 * return value of '2' means that the $file contains ':' after the first
 * character. A return value of '3' means that the file is not in the allowed
 * files list.
 *
 * @since 1.2.0
 *
 * @param string $file File path.
 * @param array $allowed_files List of allowed files.
 * @return int 0 means nothing is wrong, greater than 0 means something was wrong.
 */
function validate_file( $file, $allowed_files = '' ) {
	if ( false !== strpos( $file, '..' ) )
		return 1;

	if ( false !== strpos( $file, './' ) )
		return 1;

	if ( ! empty( $allowed_files ) && ! in_array( $file, $allowed_files ) )
		return 3;

	if (':' == substr( $file, 1, 1 ) )
		return 2;

	return 0;
}

/**
 * Determine if SSL is used.
 *
 * @since 2.6.0
 *
 * @return bool True if SSL, false if not used.
 */
function is_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}

/**
 * Whether SSL login should be forced.
 *
 * @since 2.6.0
 *
 * @param string|bool $force Optional.
 * @return bool True if forced, false if not forced.
 */
function force_ssl_login( $force = null ) {
	static $forced = false;

	if ( !is_null( $force ) ) {
		$old_forced = $forced;
		$forced = $force;
		return $old_forced;
	}

	return $forced;
}

/**
 * Whether to force SSL used for the Administration Screens.
 *
 * @since 2.6.0
 *
 * @param string|bool $force
 * @return bool True if forced, false if not forced.
 */
function force_ssl_admin( $force = null ) {
	static $forced = false;

	if ( !is_null( $force ) ) {
		$old_forced = $forced;
		$forced = $force;
		return $old_forced;
	}

	return $forced;
}

/**
 * Guess the URL for the site.
 *
 * Will remove wp-admin links to retrieve only return URLs not in the wp-admin
 * directory.
 *
 * @since 2.6.0
 *
 * @return string
 */
function wp_guess_url() {
	if ( defined('WP_SITEURL') && '' != WP_SITEURL ) {
		$url = WP_SITEURL;
	} else {
		$schema = is_ssl() ? 'https://' : 'http://'; // set_url_scheme() is not defined yet
		$url = preg_replace( '#/(wp-admin/.*|wp-login.php)#i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	}

	return rtrim($url, '/');
}

/**
 * Temporarily suspend cache additions.
 *
 * Stops more data being added to the cache, but still allows cache retrieval.
 * This is useful for actions, such as imports, when a lot of data would otherwise
 * be almost uselessly added to the cache.
 *
 * Suspension lasts for a single page load at most. Remember to call this
 * function again if you wish to re-enable cache adds earlier.
 *
 * @since 3.3.0
 *
 * @param bool $suspend Optional. Suspends additions if true, re-enables them if false.
 * @return bool The current suspend setting
 */
function wp_suspend_cache_addition( $suspend = null ) {
	static $_suspend = false;

	if ( is_bool( $suspend ) )
		$_suspend = $suspend;

	return $_suspend;
}

/**
 * Suspend cache invalidation.
 *
 * Turns cache invalidation on and off. Useful during imports where you don't wont to do invalidations
 * every time a post is inserted. Callers must be sure that what they are doing won't lead to an inconsistent
 * cache when invalidation is suspended.
 *
 * @since 2.7.0
 *
 * @param bool $suspend Whether to suspend or enable cache invalidation
 * @return bool The current suspend setting
 */
function wp_suspend_cache_invalidation($suspend = true) {
	global $_wp_suspend_cache_invalidation;

	$current_suspend = $_wp_suspend_cache_invalidation;
	$_wp_suspend_cache_invalidation = $suspend;
	return $current_suspend;
}

/**
 * Is main site?
 *
 *
 * @since 3.0.0
 * @package WordPress
 *
 * @param int $blog_id optional blog id to test (default current blog)
 * @return bool True if not multisite or $blog_id is main site
 */
function is_main_site( $blog_id = '' ) {
	global $current_site;

	if ( ! is_multisite() )
		return true;

	if ( ! $blog_id )
		$blog_id = get_current_blog_id();

	return $blog_id == $current_site->blog_id;
}

/**
 * Whether global terms are enabled.
 *
 *
 * @since 3.0.0
 * @package WordPress
 *
 * @return bool True if multisite and global terms enabled
 */
function global_terms_enabled() {
	if ( ! is_multisite() )
		return false;

	static $global_terms = null;
	if ( is_null( $global_terms ) ) {
		$filter = apply_filters( 'global_terms_enabled', null );
		if ( ! is_null( $filter ) )
			$global_terms = (bool) $filter;
		else
			$global_terms = (bool) get_site_option( 'global_terms_enabled', false );
	}
	return $global_terms;
}

/**
 * gmt_offset modification for smart timezone handling.
 *
 * Overrides the gmt_offset option if we have a timezone_string available.
 *
 * @since 2.8.0
 *
 * @return float|bool
 */
function wp_timezone_override_offset() {
	if ( !$timezone_string = get_option( 'timezone_string' ) ) {
		return false;
	}

	$timezone_object = timezone_open( $timezone_string );
	$datetime_object = date_create();
	if ( false === $timezone_object || false === $datetime_object ) {
		return false;
	}
	return round( timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS, 2 );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.9.0
 *
 * @param unknown_type $a
 * @param unknown_type $b
 * @return int
 */
function _wp_timezone_choice_usort_callback( $a, $b ) {
	// Don't use translated versions of Etc
	if ( 'Etc' === $a['continent'] && 'Etc' === $b['continent'] ) {
		// Make the order of these more like the old dropdown
		if ( 'GMT+' === substr( $a['city'], 0, 4 ) && 'GMT+' === substr( $b['city'], 0, 4 ) ) {
			return -1 * ( strnatcasecmp( $a['city'], $b['city'] ) );
		}
		if ( 'UTC' === $a['city'] ) {
			if ( 'GMT+' === substr( $b['city'], 0, 4 ) ) {
				return 1;
			}
			return -1;
		}
		if ( 'UTC' === $b['city'] ) {
			if ( 'GMT+' === substr( $a['city'], 0, 4 ) ) {
				return -1;
			}
			return 1;
		}
		return strnatcasecmp( $a['city'], $b['city'] );
	}
	if ( $a['t_continent'] == $b['t_continent'] ) {
		if ( $a['t_city'] == $b['t_city'] ) {
			return strnatcasecmp( $a['t_subcity'], $b['t_subcity'] );
		}
		return strnatcasecmp( $a['t_city'], $b['t_city'] );
	} else {
		// Force Etc to the bottom of the list
		if ( 'Etc' === $a['continent'] ) {
			return 1;
		}
		if ( 'Etc' === $b['continent'] ) {
			return -1;
		}
		return strnatcasecmp( $a['t_continent'], $b['t_continent'] );
	}
}

/**
 * Gives a nicely formatted list of timezone strings. // temporary! Not in final
 *
 * @since 2.9.0
 *
 * @param string $selected_zone Selected Zone
 * @return string
 */
function wp_timezone_choice( $selected_zone ) {
	static $mo_loaded = false;

	$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

	// Load translations for continents and cities
	if ( !$mo_loaded ) {
		$locale = get_locale();
		$mofile = WP_LANG_DIR . '/continents-cities-' . $locale . '.mo';
		load_textdomain( 'continents-cities', $mofile );
		$mo_loaded = true;
	}

	$zonen = array();
	foreach ( timezone_identifiers_list() as $zone ) {
		$zone = explode( '/', $zone );
		if ( !in_array( $zone[0], $continents ) ) {
			continue;
		}

		// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
		$exists = array(
			0 => ( isset( $zone[0] ) && $zone[0] ),
			1 => ( isset( $zone[1] ) && $zone[1] ),
			2 => ( isset( $zone[2] ) && $zone[2] ),
		);
		$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
		$exists[4] = ( $exists[1] && $exists[3] );
		$exists[5] = ( $exists[2] && $exists[3] );

		$zonen[] = array(
			'continent'   => ( $exists[0] ? $zone[0] : '' ),
			'city'        => ( $exists[1] ? $zone[1] : '' ),
			'subcity'     => ( $exists[2] ? $zone[2] : '' ),
			't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
			't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
			't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' )
		);
	}
	usort( $zonen, '_wp_timezone_choice_usort_callback' );

	$structure = array();

	if ( empty( $selected_zone ) ) {
		$structure[] = '<option selected="selected" value="">' . __( 'Select a city' ) . '</option>';
	}

	foreach ( $zonen as $key => $zone ) {
		// Build value in an array to join later
		$value = array( $zone['continent'] );

		if ( empty( $zone['city'] ) ) {
			// It's at the continent level (generally won't happen)
			$display = $zone['t_continent'];
		} else {
			// It's inside a continent group

			// Continent optgroup
			if ( !isset( $zonen[$key - 1] ) || $zonen[$key - 1]['continent'] !== $zone['continent'] ) {
				$label = $zone['t_continent'];
				$structure[] = '<optgroup label="'. esc_attr( $label ) .'">';
			}

			// Add the city to the value
			$value[] = $zone['city'];

			$display = $zone['t_city'];
			if ( !empty( $zone['subcity'] ) ) {
				// Add the subcity to the value
				$value[] = $zone['subcity'];
				$display .= ' - ' . $zone['t_subcity'];
			}
		}

		// Build the value
		$value = join( '/', $value );
		$selected = '';
		if ( $value === $selected_zone ) {
			$selected = 'selected="selected" ';
		}
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( $value ) . '">' . esc_html( $display ) . "</option>";

		// Close continent optgroup
		if ( !empty( $zone['city'] ) && ( !isset($zonen[$key + 1]) || (isset( $zonen[$key + 1] ) && $zonen[$key + 1]['continent'] !== $zone['continent']) ) ) {
			$structure[] = '</optgroup>';
		}
	}

	// Do UTC
	$structure[] = '<optgroup label="'. esc_attr__( 'UTC' ) .'">';
	$selected = '';
	if ( 'UTC' === $selected_zone )
		$selected = 'selected="selected" ';
	$structure[] = '<option ' . $selected . 'value="' . esc_attr( 'UTC' ) . '">' . __('UTC') . '</option>';
	$structure[] = '</optgroup>';

	// Do manual UTC offsets
	$structure[] = '<optgroup label="'. esc_attr__( 'Manual Offsets' ) .'">';
	$offset_range = array (-12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
		0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14);
	foreach ( $offset_range as $offset ) {
		if ( 0 <= $offset )
			$offset_name = '+' . $offset;
		else
			$offset_name = (string) $offset;

		$offset_value = $offset_name;
		$offset_name = str_replace(array('.25','.5','.75'), array(':15',':30',':45'), $offset_name);
		$offset_name = 'UTC' . $offset_name;
		$offset_value = 'UTC' . $offset_value;
		$selected = '';
		if ( $offset_value === $selected_zone )
			$selected = 'selected="selected" ';
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( $offset_value ) . '">' . esc_html( $offset_name ) . "</option>";

	}
	$structure[] = '</optgroup>';

	return join( "\n", $structure );
}

/**
 * Strip close comment and close php tags from file headers used by WP.
 * See http://core.trac.wordpress.org/ticket/8497
 *
 * @since 2.8.0
 *
 * @param string $str
 * @return string
 */
function _cleanup_header_comment($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}

/**
 * Permanently deletes posts, pages, attachments, and comments which have been in the trash for EMPTY_TRASH_DAYS.
 *
 * @since 2.9.0
 */
function wp_scheduled_delete() {
	global $wpdb;

	$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );

	$posts_to_delete = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_trash_meta_time' AND meta_value < '%d'", $delete_timestamp), ARRAY_A);

	foreach ( (array) $posts_to_delete as $post ) {
		$post_id = (int) $post['post_id'];
		if ( !$post_id )
			continue;

		$del_post = get_post($post_id);

		if ( !$del_post || 'trash' != $del_post->post_status ) {
			delete_post_meta($post_id, '_wp_trash_meta_status');
			delete_post_meta($post_id, '_wp_trash_meta_time');
		} else {
			wp_delete_post($post_id);
		}
	}

	$comments_to_delete = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = '_wp_trash_meta_time' AND meta_value < '%d'", $delete_timestamp), ARRAY_A);

	foreach ( (array) $comments_to_delete as $comment ) {
		$comment_id = (int) $comment['comment_id'];
		if ( !$comment_id )
			continue;

		$del_comment = get_comment($comment_id);

		if ( !$del_comment || 'trash' != $del_comment->comment_approved ) {
			delete_comment_meta($comment_id, '_wp_trash_meta_time');
			delete_comment_meta($comment_id, '_wp_trash_meta_status');
		} else {
			wp_delete_comment($comment_id);
		}
	}
}

/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8kiB of a file, such as a plugin or theme.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8kiB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 * @see http://codex.wordpress.org/File_Header
 *
 * @since 2.9.0
 * @param string $file Path to the file
 * @param array $default_headers List of headers, in the format array('HeaderKey' => 'Header Name')
 * @param string $context If specified adds filter hook "extra_{$context}_headers"
 */
function get_file_data( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
		$all_headers = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
			$all_headers[ $field ] = _cleanup_header_comment( $match[1] );
		else
			$all_headers[ $field ] = '';
	}

	return $all_headers;
}

/**
 * Used internally to tidy up the search terms.
 *
 * @access private
 * @since 2.9.0
 *
 * @param string $t
 * @return string
 */
function _search_terms_tidy($t) {
	return trim($t, "\"'\n\r ");
}

/**
 * Returns true.
 *
 * Useful for returning true to filters easily.
 *
 * @since 3.0.0
 * @see __return_false()
 * @return bool true
 */
function __return_true() {
	return true;
}

/**
 * Returns false.
 *
 * Useful for returning false to filters easily.
 *
 * @since 3.0.0
 * @see __return_true()
 * @return bool false
 */
function __return_false() {
	return false;
}

/**
 * Returns 0.
 *
 * Useful for returning 0 to filters easily.
 *
 * @since 3.0.0
 * @see __return_zero()
 * @return int 0
 */
function __return_zero() {
	return 0;
}

/**
 * Returns an empty array.
 *
 * Useful for returning an empty array to filters easily.
 *
 * @since 3.0.0
 * @see __return_zero()
 * @return array Empty array
 */
function __return_empty_array() {
	return array();
}

/**
 * Returns null.
 *
 * Useful for returning null to filters easily.
 *
 * @since 3.4.0
 * @return null
 */
function __return_null() {
	return null;
}

/**
 * Send a HTTP header to disable content type sniffing in browsers which support it.
 *
 * @link http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
 * @link http://src.chromium.org/viewvc/chrome?view=rev&revision=6985
 *
 * @since 3.0.0
 * @return none
 */
function send_nosniff_header() {
	@header( 'X-Content-Type-Options: nosniff' );
}

/**
 * Returns a MySQL expression for selecting the week number based on the start_of_week option.
 *
 * @internal
 * @since 3.0.0
 * @param string $column
 * @return string
 */
function _wp_mysql_week( $column ) {
	switch ( $start_of_week = (int) get_option( 'start_of_week' ) ) {
	default :
	case 0 :
		return "WEEK( $column, 0 )";
	case 1 :
		return "WEEK( $column, 1 )";
	case 2 :
	case 3 :
	case 4 :
	case 5 :
	case 6 :
		return "WEEK( DATE_SUB( $column, INTERVAL $start_of_week DAY ), 0 )";
	}
}

/**
 * Finds hierarchy loops using a callback function that maps object IDs to parent IDs.
 *
 * @since 3.1.0
 * @access private
 *
 * @param callback $callback function that accepts ( ID, $callback_args ) and outputs parent_ID
 * @param int $start The ID to start the loop check at
 * @param int $start_parent the parent_ID of $start to use instead of calling $callback( $start ). Use null to always use $callback
 * @param array $callback_args optional additional arguments to send to $callback
 * @return array IDs of all members of loop
 */
function wp_find_hierarchy_loop( $callback, $start, $start_parent, $callback_args = array() ) {
	$override = is_null( $start_parent ) ? array() : array( $start => $start_parent );

	if ( !$arbitrary_loop_member = wp_find_hierarchy_loop_tortoise_hare( $callback, $start, $override, $callback_args ) )
		return array();

	return wp_find_hierarchy_loop_tortoise_hare( $callback, $arbitrary_loop_member, $override, $callback_args, true );
}

/**
 * Uses the "The Tortoise and the Hare" algorithm to detect loops.
 *
 * For every step of the algorithm, the hare takes two steps and the tortoise one.
 * If the hare ever laps the tortoise, there must be a loop.
 *
 * @since 3.1.0
 * @access private
 *
 * @param callback $callback function that accepts ( ID, callback_arg, ... ) and outputs parent_ID
 * @param int $start The ID to start the loop check at
 * @param array $override an array of ( ID => parent_ID, ... ) to use instead of $callback
 * @param array $callback_args optional additional arguments to send to $callback
 * @param bool $_return_loop Return loop members or just detect presence of loop?
 *             Only set to true if you already know the given $start is part of a loop
 *             (otherwise the returned array might include branches)
 * @return mixed scalar ID of some arbitrary member of the loop, or array of IDs of all members of loop if $_return_loop
 */
function wp_find_hierarchy_loop_tortoise_hare( $callback, $start, $override = array(), $callback_args = array(), $_return_loop = false ) {
	$tortoise = $hare = $evanescent_hare = $start;
	$return = array();

	// Set evanescent_hare to one past hare
	// Increment hare two steps
	while (
		$tortoise
	&&
		( $evanescent_hare = isset( $override[$hare] ) ? $override[$hare] : call_user_func_array( $callback, array_merge( array( $hare ), $callback_args ) ) )
	&&
		( $hare = isset( $override[$evanescent_hare] ) ? $override[$evanescent_hare] : call_user_func_array( $callback, array_merge( array( $evanescent_hare ), $callback_args ) ) )
	) {
		if ( $_return_loop )
			$return[$tortoise] = $return[$evanescent_hare] = $return[$hare] = true;

		// tortoise got lapped - must be a loop
		if ( $tortoise == $evanescent_hare || $tortoise == $hare )
			return $_return_loop ? $return : $tortoise;

		// Increment tortoise by one step
		$tortoise = isset( $override[$tortoise] ) ? $override[$tortoise] : call_user_func_array( $callback, array_merge( array( $tortoise ), $callback_args ) );
	}

	return false;
}

/**
 * Send a HTTP header to limit rendering of pages to same origin iframes.
 *
 * @link https://developer.mozilla.org/en/the_x-frame-options_response_header
 *
 * @since 3.1.3
 * @return none
 */
function send_frame_options_header() {
	@header( 'X-Frame-Options: SAMEORIGIN' );
}

/**
 * Retrieve a list of protocols to allow in HTML attributes.
 *
 * @since 3.3.0
 * @see wp_kses()
 * @see esc_url()
 *
 * @return array Array of allowed protocols
 */
function wp_allowed_protocols() {
	static $protocols;

	if ( empty( $protocols ) ) {
		$protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp' );
		$protocols = apply_filters( 'kses_allowed_protocols', $protocols );
	}

	return $protocols;
}

/**
 * Return a comma separated string of functions that have been called to get to the current point in code.
 *
 * @link http://core.trac.wordpress.org/ticket/19589
 * @since 3.4
 *
 * @param string $ignore_class A class to ignore all function calls within - useful when you want to just give info about the callee
 * @param int $skip_frames A number of stack frames to skip - useful for unwinding back to the source of the issue
 * @param bool $pretty Whether or not you want a comma separated string or raw array returned
 * @return string|array Either a string containing a reversed comma separated trace or an array of individual calls.
 */
function wp_debug_backtrace_summary( $ignore_class = null, $skip_frames = 0, $pretty = true ) {
	if ( version_compare( PHP_VERSION, '5.2.5', '>=' ) )
		$trace = debug_backtrace( false );
	else
		$trace = debug_backtrace();

	$caller = array();
	$check_class = ! is_null( $ignore_class );
	$skip_frames++; // skip this function

	foreach ( $trace as $call ) {
		if ( $skip_frames > 0 ) {
			$skip_frames--;
		} elseif ( isset( $call['class'] ) ) {
			if ( $check_class && $ignore_class == $call['class'] )
				continue; // Filter out calls

			$caller[] = "{$call['class']}{$call['type']}{$call['function']}";
		} else {
			if ( in_array( $call['function'], array( 'do_action', 'apply_filters' ) ) ) {
				$caller[] = "{$call['function']}('{$call['args'][0]}')";
			} elseif ( in_array( $call['function'], array( 'include', 'include_once', 'require', 'require_once' ) ) ) {
				$caller[] = $call['function'] . "('" . str_replace( array( WP_CONTENT_DIR, ABSPATH ) , '', $call['args'][0] ) . "')";
			} else {
				$caller[] = $call['function'];
			}
		}
	}
	if ( $pretty )
		return join( ', ', array_reverse( $caller ) );
	else
		return $caller;
}

/**
 * Retrieve ids that are not already present in the cache
 *
 * @since 3.4.0
 *
 * @param array $object_ids ID list
 * @param string $cache_key The cache bucket to check against
 *
 * @return array
 */
function _get_non_cached_ids( $object_ids, $cache_key ) {
	$clean = array();
	foreach ( $object_ids as $id ) {
		$id = (int) $id;
		if ( !wp_cache_get( $id, $cache_key ) ) {
			$clean[] = $id;
		}
	}

	return $clean;
}

/**
 * Test if the current device has the capability to upload files.
 *
 * @since 3.4.0
 * @access private
 *
 * @return bool true|false
 */
function _device_can_upload() {
	if ( ! wp_is_mobile() )
		return true;

	$ua = $_SERVER['HTTP_USER_AGENT'];

	if ( strpos($ua, 'iPhone') !== false
		|| strpos($ua, 'iPad') !== false
		|| strpos($ua, 'iPod') !== false ) {
			return preg_match( '#OS ([\d_]+) like Mac OS X#', $ua, $version ) && version_compare( $version[1], '6', '>=' );
	}

	return true;
}

/**
 * Test if a given path is a stream URL
 *
 * @param string $path The resource path or URL
 * @return bool True if the path is a stream URL
 */
function wp_is_stream( $path ) {
	$wrappers = stream_get_wrappers();
	$wrappers_re = '(' . join('|', $wrappers) . ')';

	return preg_match( "!^$wrappers_re://!", $path ) === 1;
}

/**
 * Test if the supplied date is valid for the Gregorian calendar
 *
 * @since 3.5.0
 *
 * @return bool true|false
 */
function wp_checkdate( $month, $day, $year, $source_date ) {
	return apply_filters( 'wp_checkdate', checkdate( $month, $day, $year ), $source_date );
}
