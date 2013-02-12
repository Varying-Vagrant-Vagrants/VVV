<?php
/**
 * Deprecated functions from WordPress MU and the multisite feature. You shouldn't
 * use these functions and look for the alternatives instead. The functions will be
 * removed in a later version.
 *
 * @package WordPress
 * @subpackage Deprecated
 * @since 3.0.0
 */

/*
 * Deprecated functions come here to die.
 */

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use wp_generate_password()
 * @see wp_generate_password()
 */
function generate_random_password( $len = 8 ) {
	_deprecated_function( __FUNCTION__, '3.0', 'wp_generate_password()' );
	return wp_generate_password( $len );
}

/**
 * Determine if user is a site admin.
 *
 * Plugins should use is_multisite() instead of checking if this function exists
 * to determine if multisite is enabled.
 *
 * This function must reside in a file included only if is_multisite() due to
 * legacy function_exists() checks to determine if multisite is enabled.
 *
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use is_super_admin()
 * @see is_super_admin()
 * @see is_multisite()
 *
 */
function is_site_admin( $user_login = '' ) {
	_deprecated_function( __FUNCTION__, '3.0', 'is_super_admin()' );

	if ( empty( $user_login ) ) {
		$user_id = get_current_user_id();
		if ( !$user_id )
			return false;
	} else {
		$user = get_user_by( 'login', $user_login );
		if ( ! $user->exists() )
			return false;
		$user_id = $user->ID;
	}

	return is_super_admin( $user_id );
}

if ( !function_exists( 'graceful_fail' ) ) :
/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use wp_die()
 * @see wp_die()
 */
function graceful_fail( $message ) {
	_deprecated_function( __FUNCTION__, '3.0', 'wp_die()' );
	$message = apply_filters( 'graceful_fail', $message );
	$message_template = apply_filters( 'graceful_fail_template',
'<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"><head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Error!</title>
<style type="text/css">
img {
	border: 0;
}
body {
line-height: 1.6em; font-family: Georgia, serif; width: 390px; margin: auto;
text-align: center;
}
.message {
	font-size: 22px;
	width: 350px;
	margin: auto;
}
</style>
</head>
<body>
<p class="message">%s</p>
</body>
</html>' );
	die( sprintf( $message_template, $message ) );
}
endif;

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use get_user_by()
 * @see get_user_by()
 */
function get_user_details( $username ) {
	_deprecated_function( __FUNCTION__, '3.0', 'get_user_by()' );
	return get_user_by('login', $username);
}

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use clean_post_cache()
 * @see clean_post_cache()
 */
function clear_global_post_cache( $post_id ) {
	_deprecated_function( __FUNCTION__, '3.0', 'clean_post_cache()' );
}

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use is_main_site()
 * @see is_main_site()
 */
function is_main_blog() {
	_deprecated_function( __FUNCTION__, '3.0', 'is_main_site()' );
	return is_main_site();
}

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated Use is_email()
 * @see is_email()
 */
function validate_email( $email, $check_domain = true) {
	_deprecated_function( __FUNCTION__, '3.0', 'is_email()' );
	return is_email( $email, $check_domain );
}

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated No alternative available. For performance reasons this function is not recommended.
 */
function get_blog_list( $start = 0, $num = 10, $deprecated = '' ) {
	_deprecated_function( __FUNCTION__, '3.0' );

	global $wpdb;
	$blogs = $wpdb->get_results( $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid), ARRAY_A );

	foreach ( (array) $blogs as $details ) {
		$blog_list[ $details['blog_id'] ] = $details;
		$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->get_blog_prefix( $details['blog_id'] ). "posts WHERE post_status='publish' AND post_type='post'" );
	}
	unset( $blogs );
	$blogs = $blog_list;

	if ( false == is_array( $blogs ) )
		return array();

	if ( $num == 'all' )
		return array_slice( $blogs, $start, count( $blogs ) );
	else
		return array_slice( $blogs, $start, $num );
}

/**
 * @since MU
 * @deprecated 3.0.0
 * @deprecated No alternative available. For performance reasons this function is not recommended.
 */
function get_most_active_blogs( $num = 10, $display = true ) {
	_deprecated_function( __FUNCTION__, '3.0' );

	$blogs = get_blog_list( 0, 'all', false ); // $blog_id -> $details
	if ( is_array( $blogs ) ) {
		reset( $blogs );
		foreach ( (array) $blogs as $key => $details ) {
			$most_active[ $details['blog_id'] ] = $details['postcount'];
			$blog_list[ $details['blog_id'] ] = $details; // array_slice() removes keys!!
		}
		arsort( $most_active );
		reset( $most_active );
		foreach ( (array) $most_active as $key => $details )
			$t[ $key ] = $blog_list[ $key ];

		unset( $most_active );
		$most_active = $t;
	}

	if ( $display == true ) {
		if ( is_array( $most_active ) ) {
			reset( $most_active );
			foreach ( (array) $most_active as $key => $details ) {
				$url = esc_url('http://' . $details['domain'] . $details['path']);
				echo '<li>' . $details['postcount'] . " <a href='$url'>$url</a></li>";
			}
		}
	}
	return array_slice( $most_active, 0, $num );
}

/**
 * Redirect a user based on $_GET or $_POST arguments.
 *
 * The function looks for redirect arguments in the following order:
 * 1) $_GET['ref']
 * 2) $_POST['ref']
 * 3) $_SERVER['HTTP_REFERER']
 * 4) $_GET['redirect']
 * 5) $_POST['redirect']
 * 6) $url
 *
 * @since MU
 * @deprecated 3.3.0
 * @deprecated Use wp_redirect()
 * @uses wpmu_admin_redirect_add_updated_param()
 *
 * @param string $url
 */
function wpmu_admin_do_redirect( $url = '' ) {
	_deprecated_function( __FUNCTION__, '3.3' );

	$ref = '';
	if ( isset( $_GET['ref'] ) )
		$ref = $_GET['ref'];
	if ( isset( $_POST['ref'] ) )
		$ref = $_POST['ref'];

	if ( $ref ) {
		$ref = wpmu_admin_redirect_add_updated_param( $ref );
		wp_redirect( $ref );
		exit();
	}
	if ( empty( $_SERVER['HTTP_REFERER'] ) == false ) {
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}

	$url = wpmu_admin_redirect_add_updated_param( $url );
	if ( isset( $_GET['redirect'] ) ) {
		if ( substr( $_GET['redirect'], 0, 2 ) == 's_' )
			$url .= '&action=blogs&s='. esc_html( substr( $_GET['redirect'], 2 ) );
	} elseif ( isset( $_POST['redirect'] ) ) {
		$url = wpmu_admin_redirect_add_updated_param( $_POST['redirect'] );
	}
	wp_redirect( $url );
	exit();
}

/**
 * Adds an 'updated=true' argument to a URL.
 *
 * @since MU
 * @deprecated 3.3.0
 * @deprecated Use add_query_arg()
 *
 * @param string $url
 * @return string
 */
function wpmu_admin_redirect_add_updated_param( $url = '' ) {
	_deprecated_function( __FUNCTION__, '3.3' );

	if ( strpos( $url, 'updated=true' ) === false ) {
		if ( strpos( $url, '?' ) === false )
			return $url . '?updated=true';
		else
			return $url . '&updated=true';
	}
	return $url;
}
