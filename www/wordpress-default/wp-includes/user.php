<?php
/**
 * WordPress User API
 *
 * @package WordPress
 */

/**
 * Authenticate user with remember capability.
 *
 * The credentials is an array that has 'user_login', 'user_password', and
 * 'remember' indices. If the credentials is not given, then the log in form
 * will be assumed and used if set.
 *
 * The various authentication cookies will be set by this function and will be
 * set for a longer period depending on if the 'remember' credential is set to
 * true.
 *
 * @since 2.5.0
 *
 * @param array $credentials Optional. User info in order to sign on.
 * @param bool $secure_cookie Optional. Whether to use secure cookie.
 * @return object Either WP_Error on failure, or WP_User on success.
 */
function wp_signon( $credentials = '', $secure_cookie = '' ) {
	if ( empty($credentials) ) {
		if ( ! empty($_POST['log']) )
			$credentials['user_login'] = $_POST['log'];
		if ( ! empty($_POST['pwd']) )
			$credentials['user_password'] = $_POST['pwd'];
		if ( ! empty($_POST['rememberme']) )
			$credentials['remember'] = $_POST['rememberme'];
	}

	if ( !empty($credentials['remember']) )
		$credentials['remember'] = true;
	else
		$credentials['remember'] = false;

	// TODO do we deprecate the wp_authentication action?
	do_action_ref_array('wp_authenticate', array(&$credentials['user_login'], &$credentials['user_password']));

	if ( '' === $secure_cookie )
		$secure_cookie = is_ssl();

	$secure_cookie = apply_filters('secure_signon_cookie', $secure_cookie, $credentials);

	global $auth_secure_cookie; // XXX ugly hack to pass this to wp_authenticate_cookie
	$auth_secure_cookie = $secure_cookie;

	add_filter('authenticate', 'wp_authenticate_cookie', 30, 3);

	$user = wp_authenticate($credentials['user_login'], $credentials['user_password']);

	if ( is_wp_error($user) ) {
		if ( $user->get_error_codes() == array('empty_username', 'empty_password') ) {
			$user = new WP_Error('', '');
		}

		return $user;
	}

	wp_set_auth_cookie($user->ID, $credentials['remember'], $secure_cookie);
	do_action('wp_login', $user->user_login, $user);
	return $user;
}

/**
 * Authenticate the user using the username and password.
 */
add_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
function wp_authenticate_username_password($user, $username, $password) {
	if ( is_a($user, 'WP_User') ) { return $user; }

	if ( empty($username) || empty($password) ) {
		$error = new WP_Error();

		if ( empty($username) )
			$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

		if ( empty($password) )
			$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

		return $error;
	}

	$user = get_user_by('login', $username);

	if ( !$user )
		return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), wp_lostpassword_url()));

	if ( is_multisite() ) {
		// Is user marked as spam?
		if ( 1 == $user->spam)
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));

		// Is a user's blog marked as spam?
		if ( !is_super_admin( $user->ID ) && isset($user->primary_blog) ) {
			$details = get_blog_details( $user->primary_blog );
			if ( is_object( $details ) && $details->spam == 1 )
				return new WP_Error('blog_suspended', __('Site Suspended.'));
		}
	}

	$user = apply_filters('wp_authenticate_user', $user, $password);
	if ( is_wp_error($user) )
		return $user;

	if ( !wp_check_password($password, $user->user_pass, $user->ID) )
		return new WP_Error( 'incorrect_password', sprintf( __( '<strong>ERROR</strong>: The password you entered for the username <strong>%1$s</strong> is incorrect. <a href="%2$s" title="Password Lost and Found">Lost your password</a>?' ),
		$username, wp_lostpassword_url() ) );

	return $user;
}

/**
 * Authenticate the user using the WordPress auth cookie.
 */
function wp_authenticate_cookie($user, $username, $password) {
	if ( is_a($user, 'WP_User') ) { return $user; }

	if ( empty($username) && empty($password) ) {
		$user_id = wp_validate_auth_cookie();
		if ( $user_id )
			return new WP_User($user_id);

		global $auth_secure_cookie;

		if ( $auth_secure_cookie )
			$auth_cookie = SECURE_AUTH_COOKIE;
		else
			$auth_cookie = AUTH_COOKIE;

		if ( !empty($_COOKIE[$auth_cookie]) )
			return new WP_Error('expired_session', __('Please log in again.'));

		// If the cookie is not set, be silent.
	}

	return $user;
}

/**
 * Number of posts user has written.
 *
 * @since 3.0.0
 * @uses $wpdb WordPress database object for queries.
 *
 * @param int $userid User ID.
 * @return int Amount of posts user has written.
 */
function count_user_posts($userid) {
	global $wpdb;

	$where = get_posts_by_author_sql('post', true, $userid);

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

	return apply_filters('get_usernumposts', $count, $userid);
}

/**
 * Number of posts written by a list of users.
 *
 * @since 3.0.0
 *
 * @param array $users Array of user IDs.
 * @param string $post_type Optional. Post type to check. Defaults to post.
 * @param bool $public_only Optional. Only return counts for public posts.  Defaults to false.
 * @return array Amount of posts each user has written.
 */
function count_many_users_posts( $users, $post_type = 'post', $public_only = false ) {
	global $wpdb;

	$count = array();
	if ( empty( $users ) || ! is_array( $users ) )
		return $count;

	$userlist = implode( ',', array_map( 'absint', $users ) );
	$where = get_posts_by_author_sql( $post_type, true, null, $public_only );

	$result = $wpdb->get_results( "SELECT post_author, COUNT(*) FROM $wpdb->posts $where AND post_author IN ($userlist) GROUP BY post_author", ARRAY_N );
	foreach ( $result as $row ) {
		$count[ $row[0] ] = $row[1];
	}

	foreach ( $users as $id ) {
		if ( ! isset( $count[ $id ] ) )
			$count[ $id ] = 0;
	}

	return $count;
}

//
// User option functions
//

/**
 * Get the current user's ID
 *
 * @since MU
 *
 * @uses wp_get_current_user
 *
 * @return int The current user's ID
 */
function get_current_user_id() {
	$user = wp_get_current_user();
	return ( isset( $user->ID ) ? (int) $user->ID : 0 );
}

/**
 * Retrieve user option that can be either per Site or per Network.
 *
 * If the user ID is not given, then the current user will be used instead. If
 * the user ID is given, then the user data will be retrieved. The filter for
 * the result, will also pass the original option name and finally the user data
 * object as the third parameter.
 *
 * The option will first check for the per site name and then the per Network name.
 *
 * @since 2.0.0
 * @uses $wpdb WordPress database object for queries.
 * @uses apply_filters() Calls 'get_user_option_$option' hook with result,
 *		option parameter, and user data object.
 *
 * @param string $option User option name.
 * @param int $user Optional. User ID.
 * @param bool $deprecated Use get_option() to check for an option in the options table.
 * @return mixed
 */
function get_user_option( $option, $user = 0, $deprecated = '' ) {
	global $wpdb;

	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '3.0' );

	if ( empty( $user ) )
		$user = get_current_user_id();

	if ( ! $user = get_userdata( $user ) )
		return false;

	if ( $user->has_prop( $wpdb->prefix . $option ) ) // Blog specific
		$result = $user->get( $wpdb->prefix . $option );
	elseif ( $user->has_prop( $option ) ) // User specific and cross-blog
		$result = $user->get( $option );
	else
		$result = false;

	return apply_filters("get_user_option_{$option}", $result, $option, $user);
}

/**
 * Update user option with global blog capability.
 *
 * User options are just like user metadata except that they have support for
 * global blog options. If the 'global' parameter is false, which it is by default
 * it will prepend the WordPress table prefix to the option name.
 *
 * Deletes the user option if $newvalue is empty.
 *
 * @since 2.0.0
 * @uses $wpdb WordPress database object for queries
 *
 * @param int $user_id User ID
 * @param string $option_name User option name.
 * @param mixed $newvalue User option value.
 * @param bool $global Optional. Whether option name is global or blog specific. Default false (blog specific).
 * @return unknown
 */
function update_user_option( $user_id, $option_name, $newvalue, $global = false ) {
	global $wpdb;

	if ( !$global )
		$option_name = $wpdb->prefix . $option_name;

	// For backward compatibility. See differences between update_user_meta() and deprecated update_usermeta().
	// http://core.trac.wordpress.org/ticket/13088
	if ( is_null( $newvalue ) || is_scalar( $newvalue ) && empty( $newvalue ) )
		return delete_user_meta( $user_id, $option_name );

	return update_user_meta( $user_id, $option_name, $newvalue );
}

/**
 * Delete user option with global blog capability.
 *
 * User options are just like user metadata except that they have support for
 * global blog options. If the 'global' parameter is false, which it is by default
 * it will prepend the WordPress table prefix to the option name.
 *
 * @since 3.0.0
 * @uses $wpdb WordPress database object for queries
 *
 * @param int $user_id User ID
 * @param string $option_name User option name.
 * @param bool $global Optional. Whether option name is global or blog specific. Default false (blog specific).
 * @return unknown
 */
function delete_user_option( $user_id, $option_name, $global = false ) {
	global $wpdb;

	if ( !$global )
		$option_name = $wpdb->prefix . $option_name;
	return delete_user_meta( $user_id, $option_name );
}

/**
 * WordPress User Query class.
 *
 * @since 3.1.0
 */
class WP_User_Query {

	/**
	 * Query vars, after parsing
	 *
	 * @since 3.5.0
	 * @access public
	 * @var array
	 */
	var $query_vars = array();

	/**
	 * List of found user ids
	 *
	 * @since 3.1.0
	 * @access private
	 * @var array
	 */
	var $results;

	/**
	 * Total number of found users for the current query
	 *
	 * @since 3.1.0
	 * @access private
	 * @var int
	 */
	var $total_users = 0;

	// SQL clauses
	var $query_fields;
	var $query_from;
	var $query_where;
	var $query_orderby;
	var $query_limit;

	/**
	 * PHP5 constructor
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $args The query variables
	 * @return WP_User_Query
	 */
	function __construct( $query = null ) {
		if ( !empty( $query ) ) {
			$this->query_vars = wp_parse_args( $query, array(
				'blog_id' => $GLOBALS['blog_id'],
				'role' => '',
				'meta_key' => '',
				'meta_value' => '',
				'meta_compare' => '',
				'include' => array(),
				'exclude' => array(),
				'search' => '',
				'search_columns' => array(),
				'orderby' => 'login',
				'order' => 'ASC',
				'offset' => '',
				'number' => '',
				'count_total' => true,
				'fields' => 'all',
				'who' => ''
			) );

			$this->prepare_query();
			$this->query();
		}
	}

	/**
	 * Prepare the query variables
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function prepare_query() {
		global $wpdb;

		$qv =& $this->query_vars;

		if ( is_array( $qv['fields'] ) ) {
			$qv['fields'] = array_unique( $qv['fields'] );

			$this->query_fields = array();
			foreach ( $qv['fields'] as $field )
				$this->query_fields[] = $wpdb->users . '.' . esc_sql( $field );
			$this->query_fields = implode( ',', $this->query_fields );
		} elseif ( 'all' == $qv['fields'] ) {
			$this->query_fields = "$wpdb->users.*";
		} else {
			$this->query_fields = "$wpdb->users.ID";
		}

		if ( $qv['count_total'] )
			$this->query_fields = 'SQL_CALC_FOUND_ROWS ' . $this->query_fields;

		$this->query_from = "FROM $wpdb->users";
		$this->query_where = "WHERE 1=1";

		// sorting
		if ( in_array( $qv['orderby'], array('nicename', 'email', 'url', 'registered') ) ) {
			$orderby = 'user_' . $qv['orderby'];
		} elseif ( in_array( $qv['orderby'], array('user_nicename', 'user_email', 'user_url', 'user_registered') ) ) {
			$orderby = $qv['orderby'];
		} elseif ( 'name' == $qv['orderby'] || 'display_name' == $qv['orderby'] ) {
			$orderby = 'display_name';
		} elseif ( 'post_count' == $qv['orderby'] ) {
			// todo: avoid the JOIN
			$where = get_posts_by_author_sql('post');
			$this->query_from .= " LEFT OUTER JOIN (
				SELECT post_author, COUNT(*) as post_count
				FROM $wpdb->posts
				$where
				GROUP BY post_author
			) p ON ({$wpdb->users}.ID = p.post_author)
			";
			$orderby = 'post_count';
		} elseif ( 'ID' == $qv['orderby'] || 'id' == $qv['orderby'] ) {
			$orderby = 'ID';
		} else {
			$orderby = 'user_login';
		}

		$qv['order'] = strtoupper( $qv['order'] );
		if ( 'ASC' == $qv['order'] )
			$order = 'ASC';
		else
			$order = 'DESC';
		$this->query_orderby = "ORDER BY $orderby $order";

		// limit
		if ( $qv['number'] ) {
			if ( $qv['offset'] )
				$this->query_limit = $wpdb->prepare("LIMIT %d, %d", $qv['offset'], $qv['number']);
			else
				$this->query_limit = $wpdb->prepare("LIMIT %d", $qv['number']);
		}

		$search = trim( $qv['search'] );
		if ( $search ) {
			$leading_wild = ( ltrim($search, '*') != $search );
			$trailing_wild = ( rtrim($search, '*') != $search );
			if ( $leading_wild && $trailing_wild )
				$wild = 'both';
			elseif ( $leading_wild )
				$wild = 'leading';
			elseif ( $trailing_wild )
				$wild = 'trailing';
			else
				$wild = false;
			if ( $wild )
				$search = trim($search, '*');

			$search_columns = array();
			if ( $qv['search_columns'] )
				$search_columns = array_intersect( $qv['search_columns'], array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename' ) );
			if ( ! $search_columns ) {
				if ( false !== strpos( $search, '@') )
					$search_columns = array('user_email');
				elseif ( is_numeric($search) )
					$search_columns = array('user_login', 'ID');
				elseif ( preg_match('|^https?://|', $search) && ! wp_is_large_network( 'users' ) )
					$search_columns = array('user_url');
				else
					$search_columns = array('user_login', 'user_nicename');
			}

			$this->query_where .= $this->get_search_sql( $search, $search_columns, $wild );
		}

		$blog_id = absint( $qv['blog_id'] );

		if ( 'authors' == $qv['who'] && $blog_id ) {
			$qv['meta_key'] = $wpdb->get_blog_prefix( $blog_id ) . 'user_level';
			$qv['meta_value'] = 0;
			$qv['meta_compare'] = '!=';
			$qv['blog_id'] = $blog_id = 0; // Prevent extra meta query
		}

		$role = trim( $qv['role'] );

		if ( $blog_id && ( $role || is_multisite() ) ) {
			$cap_meta_query = array();
			$cap_meta_query['key'] = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';

			if ( $role ) {
				$cap_meta_query['value'] = '"' . $role . '"';
				$cap_meta_query['compare'] = 'like';
			}

			$qv['meta_query'][] = $cap_meta_query;
		}

		$meta_query = new WP_Meta_Query();
		$meta_query->parse_query_vars( $qv );

		if ( !empty( $meta_query->queries ) ) {
			$clauses = $meta_query->get_sql( 'user', $wpdb->users, 'ID', $this );
			$this->query_from .= $clauses['join'];
			$this->query_where .= $clauses['where'];

			if ( 'OR' == $meta_query->relation )
				$this->query_fields = 'DISTINCT ' . $this->query_fields;
		}

		if ( !empty( $qv['include'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['include'] ) );
			$this->query_where .= " AND $wpdb->users.ID IN ($ids)";
		} elseif ( !empty($qv['exclude']) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['exclude'] ) );
			$this->query_where .= " AND $wpdb->users.ID NOT IN ($ids)";
		}

		do_action_ref_array( 'pre_user_query', array( &$this ) );
	}

	/**
	 * Execute the query, with the current variables
	 *
	 * @since 3.1.0
	 * @access private
	 */
	function query() {
		global $wpdb;

		$qv =& $this->query_vars;

		if ( is_array( $qv['fields'] ) || 'all' == $qv['fields'] ) {
			$this->results = $wpdb->get_results("SELECT $this->query_fields $this->query_from $this->query_where $this->query_orderby $this->query_limit");
		} else {
			$this->results = $wpdb->get_col("SELECT $this->query_fields $this->query_from $this->query_where $this->query_orderby $this->query_limit");
		}

		if ( $qv['count_total'] )
			$this->total_users = $wpdb->get_var( apply_filters( 'found_users_query', 'SELECT FOUND_ROWS()' ) );

		if ( !$this->results )
			return;

		if ( 'all_with_meta' == $qv['fields'] ) {
			cache_users( $this->results );

			$r = array();
			foreach ( $this->results as $userid )
				$r[ $userid ] = new WP_User( $userid, '', $qv['blog_id'] );

			$this->results = $r;
		} elseif ( 'all' == $qv['fields'] ) {
			foreach ( $this->results as $key => $user ) {
				$this->results[ $key ] = new WP_User( $user );
			}
		}
	}

	/**
	 * Retrieve query variable.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @return mixed
	 */
	function get( $query_var ) {
		if ( isset( $this->query_vars[$query_var] ) )
			return $this->query_vars[$query_var];

		return null;
	}

	/**
	 * Set query variable.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed $value Query variable value.
	 */
	function set( $query_var, $value ) {
		$this->query_vars[$query_var] = $value;
	}

	/*
	 * Used internally to generate an SQL string for searching across multiple columns
	 *
	 * @access protected
	 * @since 3.1.0
	 *
	 * @param string $string
	 * @param array $cols
	 * @param bool $wild Whether to allow wildcard searches. Default is false for Network Admin, true for
	 *  single site. Single site allows leading and trailing wildcards, Network Admin only trailing.
	 * @return string
	 */
	function get_search_sql( $string, $cols, $wild = false ) {
		$string = esc_sql( $string );

		$searches = array();
		$leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
		$trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
		foreach ( $cols as $col ) {
			if ( 'ID' == $col )
				$searches[] = "$col = '$string'";
			else
				$searches[] = "$col LIKE '$leading_wild" . like_escape($string) . "$trailing_wild'";
		}

		return ' AND (' . implode(' OR ', $searches) . ')';
	}

	/**
	 * Return the list of users
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_results() {
		return $this->results;
	}

	/**
	 * Return the total number of users for the current query
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_total() {
		return $this->total_users;
	}
}

/**
 * Retrieve list of users matching criteria.
 *
 * @since 3.1.0
 * @uses $wpdb
 * @uses WP_User_Query See for default arguments and information.
 *
 * @param array $args Optional.
 * @return array List of users.
 */
function get_users( $args = array() ) {

	$args = wp_parse_args( $args );
	$args['count_total'] = false;

	$user_search = new WP_User_Query($args);

	return (array) $user_search->get_results();
}

/**
 * Get the blogs a user belongs to.
 *
 * @since 3.0.0
 *
 * @param int $user_id User ID
 * @param bool $all Whether to retrieve all blogs, or only blogs that are not marked as deleted, archived, or spam.
 * @return array A list of the user's blogs. An empty array if the user doesn't exist or belongs to no blogs.
 */
function get_blogs_of_user( $user_id, $all = false ) {
	global $wpdb;

	$user_id = (int) $user_id;

	// Logged out users can't have blogs
	if ( empty( $user_id ) )
		return array();

	$keys = get_user_meta( $user_id );
	if ( empty( $keys ) )
		return array();

	if ( ! is_multisite() ) {
		$blog_id = get_current_blog_id();
		$blogs = array( $blog_id => new stdClass );
		$blogs[ $blog_id ]->userblog_id = $blog_id;
		$blogs[ $blog_id ]->blogname = get_option('blogname');
		$blogs[ $blog_id ]->domain = '';
		$blogs[ $blog_id ]->path = '';
		$blogs[ $blog_id ]->site_id = 1;
		$blogs[ $blog_id ]->siteurl = get_option('siteurl');
		$blogs[ $blog_id ]->archived = 0;
		$blogs[ $blog_id ]->spam = 0;
		$blogs[ $blog_id ]->deleted = 0;
		return $blogs;
	}

	$blogs = array();

	if ( isset( $keys[ $wpdb->base_prefix . 'capabilities' ] ) && defined( 'MULTISITE' ) ) {
		$blog = get_blog_details( 1 );
		if ( $blog && isset( $blog->domain ) && ( $all || ( ! $blog->archived && ! $blog->spam && ! $blog->deleted ) ) ) {
			$blogs[ 1 ] = (object) array(
				'userblog_id' => 1,
				'blogname'    => $blog->blogname,
				'domain'      => $blog->domain,
				'path'        => $blog->path,
				'site_id'     => $blog->site_id,
				'siteurl'     => $blog->siteurl,
				'archived'    => 0,
				'spam'        => 0,
				'deleted'     => 0
			);
		}
		unset( $keys[ $wpdb->base_prefix . 'capabilities' ] );
	}

	$keys = array_keys( $keys );

	foreach ( $keys as $key ) {
		if ( 'capabilities' !== substr( $key, -12 ) )
			continue;
		if ( $wpdb->base_prefix && 0 !== strpos( $key, $wpdb->base_prefix ) )
			continue;
		$blog_id = str_replace( array( $wpdb->base_prefix, '_capabilities' ), '', $key );
		if ( ! is_numeric( $blog_id ) )
			continue;

		$blog_id = (int) $blog_id;
		$blog = get_blog_details( $blog_id );
		if ( $blog && isset( $blog->domain ) && ( $all || ( ! $blog->archived && ! $blog->spam && ! $blog->deleted ) ) ) {
			$blogs[ $blog_id ] = (object) array(
				'userblog_id' => $blog_id,
				'blogname'    => $blog->blogname,
				'domain'      => $blog->domain,
				'path'        => $blog->path,
				'site_id'     => $blog->site_id,
				'siteurl'     => $blog->siteurl,
				'archived'    => 0,
				'spam'        => 0,
				'deleted'     => 0
			);
		}
	}

	return apply_filters( 'get_blogs_of_user', $blogs, $user_id, $all );
}

/**
 * Find out whether a user is a member of a given blog.
 *
 * @since MU 1.1
 * @uses get_blogs_of_user()
 *
 * @param int $user_id Optional. The unique ID of the user. Defaults to the current user.
 * @param int $blog_id Optional. ID of the blog to check. Defaults to the current site.
 * @return bool
 */
function is_user_member_of_blog( $user_id = 0, $blog_id = 0 ) {
	$user_id = (int) $user_id;
	$blog_id = (int) $blog_id;

	if ( empty( $user_id ) )
		$user_id = get_current_user_id();

	if ( empty( $blog_id ) )
		$blog_id = get_current_blog_id();

	$blogs = get_blogs_of_user( $user_id );
	return array_key_exists( $blog_id, $blogs );
}

/**
 * Add meta data field to a user.
 *
 * Post meta data is called "Custom Fields" on the Administration Screens.
 *
 * @since 3.0.0
 * @uses add_metadata()
 * @link http://codex.wordpress.org/Function_Reference/add_user_meta
 *
 * @param int $user_id Post ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function add_user_meta($user_id, $meta_key, $meta_value, $unique = false) {
	return add_metadata('user', $user_id, $meta_key, $meta_value, $unique);
}

/**
 * Remove metadata matching criteria from a user.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 3.0.0
 * @uses delete_metadata()
 * @link http://codex.wordpress.org/Function_Reference/delete_user_meta
 *
 * @param int $user_id user ID
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value.
 * @return bool False for failure. True for success.
 */
function delete_user_meta($user_id, $meta_key, $meta_value = '') {
	return delete_metadata('user', $user_id, $meta_key, $meta_value);
}

/**
 * Retrieve user meta field for a user.
 *
 * @since 3.0.0
 * @uses get_metadata()
 * @link http://codex.wordpress.org/Function_Reference/get_user_meta
 *
 * @param int $user_id Post ID.
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function get_user_meta($user_id, $key = '', $single = false) {
	return get_metadata('user', $user_id, $key, $single);
}

/**
 * Update user meta field based on user ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and user ID.
 *
 * If the meta field for the user does not exist, it will be added.
 *
 * @since 3.0.0
 * @uses update_metadata
 * @link http://codex.wordpress.org/Function_Reference/update_user_meta
 *
 * @param int $user_id Post ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
	return update_metadata('user', $user_id, $meta_key, $meta_value, $prev_value);
}

/**
 * Count number of users who have each of the user roles.
 *
 * Assumes there are neither duplicated nor orphaned capabilities meta_values.
 * Assumes role names are unique phrases. Same assumption made by WP_User_Query::prepare_query()
 * Using $strategy = 'time' this is CPU-intensive and should handle around 10^7 users.
 * Using $strategy = 'memory' this is memory-intensive and should handle around 10^5 users, but see WP Bug #12257.
 *
 * @since 3.0.0
 * @param string $strategy 'time' or 'memory'
 * @return array Includes a grand total and an array of counts indexed by role strings.
 */
function count_users($strategy = 'time') {
	global $wpdb, $wp_roles;

	// Initialize
	$id = get_current_blog_id();
	$blog_prefix = $wpdb->get_blog_prefix($id);
	$result = array();

	if ( 'time' == $strategy ) {
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		$avail_roles = $wp_roles->get_names();

		// Build a CPU-intensive query that will return concise information.
		$select_count = array();
		foreach ( $avail_roles as $this_role => $name ) {
			$select_count[] = "COUNT(NULLIF(`meta_value` LIKE '%\"" . like_escape( $this_role ) . "\"%', false))";
		}
		$select_count = implode(', ', $select_count);

		// Add the meta_value index to the selection list, then run the query.
		$row = $wpdb->get_row( "SELECT $select_count, COUNT(*) FROM $wpdb->usermeta WHERE meta_key = '{$blog_prefix}capabilities'", ARRAY_N );

		// Run the previous loop again to associate results with role names.
		$col = 0;
		$role_counts = array();
		foreach ( $avail_roles as $this_role => $name ) {
			$count = (int) $row[$col++];
			if ($count > 0) {
				$role_counts[$this_role] = $count;
			}
		}

		// Get the meta_value index from the end of the result set.
		$total_users = (int) $row[$col];

		$result['total_users'] = $total_users;
		$result['avail_roles'] =& $role_counts;
	} else {
		$avail_roles = array();

		$users_of_blog = $wpdb->get_col( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '{$blog_prefix}capabilities'" );

		foreach ( $users_of_blog as $caps_meta ) {
			$b_roles = maybe_unserialize($caps_meta);
			if ( ! is_array( $b_roles ) )
				continue;
			foreach ( $b_roles as $b_role => $val ) {
				if ( isset($avail_roles[$b_role]) ) {
					$avail_roles[$b_role]++;
				} else {
					$avail_roles[$b_role] = 1;
				}
			}
		}

		$result['total_users'] = count( $users_of_blog );
		$result['avail_roles'] =& $avail_roles;
	}

	return $result;
}

//
// Private helper functions
//

/**
 * Set up global user vars.
 *
 * Used by wp_set_current_user() for back compat. Might be deprecated in the future.
 *
 * @since 2.0.4
 * @global string $userdata User description.
 * @global string $user_login The user username for logging in
 * @global int $user_level The level of the user
 * @global int $user_ID The ID of the user
 * @global string $user_email The email address of the user
 * @global string $user_url The url in the user's profile
 * @global string $user_identity The display name of the user
 *
 * @param int $for_user_id Optional. User ID to set up global data.
 */
function setup_userdata($for_user_id = '') {
	global $user_login, $userdata, $user_level, $user_ID, $user_email, $user_url, $user_identity;

	if ( '' == $for_user_id )
		$for_user_id = get_current_user_id();
	$user = get_userdata( $for_user_id );

	if ( ! $user ) {
		$user_ID = 0;
		$user_level = 0;
		$userdata = null;
		$user_login = $user_email = $user_url = $user_identity = '';
		return;
	}

	$user_ID    = (int) $user->ID;
	$user_level = (int) $user->user_level;
	$userdata   = $user;
	$user_login = $user->user_login;
	$user_email = $user->user_email;
	$user_url   = $user->user_url;
	$user_identity = $user->display_name;
}

/**
 * Create dropdown HTML content of users.
 *
 * The content can either be displayed, which it is by default or retrieved by
 * setting the 'echo' argument. The 'include' and 'exclude' arguments do not
 * need to be used; all users will be displayed in that case. Only one can be
 * used, either 'include' or 'exclude', but not both.
 *
 * The available arguments are as follows:
 * <ol>
 * <li>show_option_all - Text to show all and whether HTML option exists.</li>
 * <li>show_option_none - Text for show none and whether HTML option exists.</li>
 * <li>hide_if_only_one_author - Don't create the dropdown if there is only one user.</li>
 * <li>orderby - SQL order by clause for what order the users appear. Default is 'display_name'.</li>
 * <li>order - Default is 'ASC'. Can also be 'DESC'.</li>
 * <li>include - User IDs to include.</li>
 * <li>exclude - User IDs to exclude.</li>
 * <li>multi - Default is 'false'. Whether to skip the ID attribute on the 'select' element. A 'true' value is overridden when id argument is set.</li>
 * <li>show - Default is 'display_name'. User table column to display. If the selected item is empty then the user_login will be displayed in parentheses</li>
 * <li>echo - Default is '1'. Whether to display or retrieve content.</li>
 * <li>selected - Which User ID is selected.</li>
 * <li>include_selected - Always include the selected user ID in the dropdown. Default is false.</li>
 * <li>name - Default is 'user'. Name attribute of select element.</li>
 * <li>id - Default is the value of the 'name' parameter. ID attribute of select element.</li>
 * <li>class - Class attribute of select element.</li>
 * <li>blog_id - ID of blog (Multisite only). Defaults to ID of current blog.</li>
 * <li>who - Which users to query. Currently only 'authors' is supported. Default is all users.</li>
 * </ol>
 *
 * @since 2.3.0
 * @uses $wpdb WordPress database object for queries
 *
 * @param string|array $args Optional. Override defaults.
 * @return string|null Null on display. String of HTML content on retrieve.
 */
function wp_dropdown_users( $args = '' ) {
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
		'orderby' => 'display_name', 'order' => 'ASC',
		'include' => '', 'exclude' => '', 'multi' => 0,
		'show' => 'display_name', 'echo' => 1,
		'selected' => 0, 'name' => 'user', 'class' => '', 'id' => '',
		'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'include_selected' => false
	);

	$defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'who' ) );
	$query_args['fields'] = array( 'ID', $show );
	$users = get_users( $query_args );

	$output = '';
	if ( !empty($users) && ( empty($hide_if_only_one_author) || count($users) > 1 ) ) {
		$name = esc_attr( $name );
		if ( $multi && ! $id )
			$id = '';
		else
			$id = $id ? " id='" . esc_attr( $id ) . "'" : " id='$name'";

		$output = "<select name='{$name}'{$id} class='$class'>\n";

		if ( $show_option_all )
			$output .= "\t<option value='0'>$show_option_all</option>\n";

		if ( $show_option_none ) {
			$_selected = selected( -1, $selected, false );
			$output .= "\t<option value='-1'$_selected>$show_option_none</option>\n";
		}

		$found_selected = false;
		foreach ( (array) $users as $user ) {
			$user->ID = (int) $user->ID;
			$_selected = selected( $user->ID, $selected, false );
			if ( $_selected )
				$found_selected = true;
			$display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
			$output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
		}

		if ( $include_selected && ! $found_selected && ( $selected > 0 ) ) {
			$user = get_userdata( $selected );
			$_selected = selected( $user->ID, $selected, false );
			$display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
			$output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
		}

		$output .= "</select>";
	}

	$output = apply_filters('wp_dropdown_users', $output);

	if ( $echo )
		echo $output;

	return $output;
}

/**
 * Sanitize user field based on context.
 *
 * Possible context values are:  'raw', 'edit', 'db', 'display', 'attribute' and 'js'. The
 * 'display' context is used by default. 'attribute' and 'js' contexts are treated like 'display'
 * when calling filters.
 *
 * @since 2.3.0
 * @uses apply_filters() Calls 'edit_$field' passing $value and $user_id if $context == 'edit'.
 *  $field is prefixed with 'user_' if it isn't already.
 * @uses apply_filters() Calls 'pre_$field' passing $value if $context == 'db'. $field is prefixed with
 *  'user_' if it isn't already.
 * @uses apply_filters() Calls '$field' passing $value, $user_id and $context if $context == anything
 *  other than 'raw', 'edit' and 'db'. $field is prefixed with 'user_' if it isn't already.
 *
 * @param string $field The user Object field name.
 * @param mixed $value The user Object value.
 * @param int $user_id user ID.
 * @param string $context How to sanitize user fields. Looks for 'raw', 'edit', 'db', 'display',
 *               'attribute' and 'js'.
 * @return mixed Sanitized value.
 */
function sanitize_user_field($field, $value, $user_id, $context) {
	$int_fields = array('ID');
	if ( in_array($field, $int_fields) )
		$value = (int) $value;

	if ( 'raw' == $context )
		return $value;

	if ( !is_string($value) && !is_numeric($value) )
		return $value;

	$prefixed = false !== strpos( $field, 'user_' );

	if ( 'edit' == $context ) {
		if ( $prefixed ) {
			$value = apply_filters("edit_{$field}", $value, $user_id);
		} else {
			$value = apply_filters("edit_user_{$field}", $value, $user_id);
		}

		if ( 'description' == $field )
			$value = esc_html( $value ); // textarea_escaped?
		else
			$value = esc_attr($value);
	} else if ( 'db' == $context ) {
		if ( $prefixed ) {
			$value = apply_filters("pre_{$field}", $value);
		} else {
			$value = apply_filters("pre_user_{$field}", $value);
		}
	} else {
		// Use display filters by default.
		if ( $prefixed )
			$value = apply_filters($field, $value, $user_id, $context);
		else
			$value = apply_filters("user_{$field}", $value, $user_id, $context);
	}

	if ( 'user_url' == $field )
		$value = esc_url($value);

	if ( 'attribute' == $context )
		$value = esc_attr($value);
	else if ( 'js' == $context )
		$value = esc_js($value);

	return $value;
}

/**
 * Update all user caches
 *
 * @since 3.0.0
 *
 * @param object $user User object to be cached
 */
function update_user_caches($user) {
	wp_cache_add($user->ID, $user, 'users');
	wp_cache_add($user->user_login, $user->ID, 'userlogins');
	wp_cache_add($user->user_email, $user->ID, 'useremail');
	wp_cache_add($user->user_nicename, $user->ID, 'userslugs');
}

/**
 * Clean all user caches
 *
 * @since 3.0.0
 *
 * @param WP_User|int $user User object or ID to be cleaned from the cache
 */
function clean_user_cache( $user ) {
	if ( is_numeric( $user ) )
		$user = new WP_User( $user );

	if ( ! $user->exists() )
		return;

	wp_cache_delete( $user->ID, 'users' );
	wp_cache_delete( $user->user_login, 'userlogins' );
	wp_cache_delete( $user->user_email, 'useremail' );
	wp_cache_delete( $user->user_nicename, 'userslugs' );
}

/**
 * Checks whether the given username exists.
 *
 * @since 2.0.0
 *
 * @param string $username Username.
 * @return null|int The user's ID on success, and null on failure.
 */
function username_exists( $username ) {
	if ( $user = get_user_by('login', $username ) ) {
		return $user->ID;
	} else {
		return null;
	}
}

/**
 * Checks whether the given email exists.
 *
 * @since 2.1.0
 * @uses $wpdb
 *
 * @param string $email Email.
 * @return bool|int The user's ID on success, and false on failure.
 */
function email_exists( $email ) {
	if ( $user = get_user_by('email', $email) )
		return $user->ID;

	return false;
}

/**
 * Checks whether an username is valid.
 *
 * @since 2.0.1
 * @uses apply_filters() Calls 'validate_username' hook on $valid check and $username as parameters
 *
 * @param string $username Username.
 * @return bool Whether username given is valid
 */
function validate_username( $username ) {
	$sanitized = sanitize_user( $username, true );
	$valid = ( $sanitized == $username );
	return apply_filters( 'validate_username', $valid, $username );
}

/**
 * Insert an user into the database.
 *
 * Can update a current user or insert a new user based on whether the user's ID
 * is present.
 *
 * Can be used to update the user's info (see below), set the user's role, and
 * set the user's preference on whether they want the rich editor on.
 *
 * Most of the $userdata array fields have filters associated with the values.
 * The exceptions are 'rich_editing', 'role', 'jabber', 'aim', 'yim',
 * 'user_registered', and 'ID'. The filters have the prefix 'pre_user_' followed
 * by the field name. An example using 'description' would have the filter
 * called, 'pre_user_description' that can be hooked into.
 *
 * The $userdata array can contain the following fields:
 * 'ID' - An integer that will be used for updating an existing user.
 * 'user_pass' - A string that contains the plain text password for the user.
 * 'user_login' - A string that contains the user's username for logging in.
 * 'user_nicename' - A string that contains a URL-friendly name for the user.
 *		The default is the user's username.
 * 'user_url' - A string containing the user's URL for the user's web site.
 * 'user_email' - A string containing the user's email address.
 * 'display_name' - A string that will be shown on the site. Defaults to user's
 *		username. It is likely that you will want to change this, for appearance.
 * 'nickname' - The user's nickname, defaults to the user's username.
 * 'first_name' - The user's first name.
 * 'last_name' - The user's last name.
 * 'description' - A string containing content about the user.
 * 'rich_editing' - A string for whether to enable the rich editor. False
 *		if not empty.
 * 'user_registered' - The date the user registered. Format is 'Y-m-d H:i:s'.
 * 'role' - A string used to set the user's role.
 * 'jabber' - User's Jabber account.
 * 'aim' - User's AOL IM account.
 * 'yim' - User's Yahoo IM account.
 *
 * @since 2.0.0
 * @uses $wpdb WordPress database layer.
 * @uses apply_filters() Calls filters for most of the $userdata fields with the prefix 'pre_user'. See note above.
 * @uses do_action() Calls 'profile_update' hook when updating giving the user's ID
 * @uses do_action() Calls 'user_register' hook when creating a new user giving the user's ID
 *
 * @param mixed $userdata An array of user data or a user object of type stdClass or WP_User.
 * @return int|WP_Error The newly created user's ID or a WP_Error object if the user could not be created.
 */
function wp_insert_user( $userdata ) {
	global $wpdb;

	if ( is_a( $userdata, 'stdClass' ) )
		$userdata = get_object_vars( $userdata );
	elseif ( is_a( $userdata, 'WP_User' ) )
		$userdata = $userdata->to_array();

	extract( $userdata, EXTR_SKIP );

	// Are we updating or creating?
	if ( !empty($ID) ) {
		$ID = (int) $ID;
		$update = true;
		$old_user_data = WP_User::get_data_by( 'id', $ID );
	} else {
		$update = false;
		// Hash the password
		$user_pass = wp_hash_password($user_pass);
	}

	$user_login = sanitize_user($user_login, true);
	$user_login = apply_filters('pre_user_login', $user_login);

	//Remove any non-printable chars from the login string to see if we have ended up with an empty username
	$user_login = trim($user_login);

	if ( empty($user_login) )
		return new WP_Error('empty_user_login', __('Cannot create a user with an empty login name.') );

	if ( !$update && username_exists( $user_login ) )
		return new WP_Error( 'existing_user_login', __( 'Sorry, that username already exists!' ) );

	if ( empty($user_nicename) )
		$user_nicename = sanitize_title( $user_login );
	$user_nicename = apply_filters('pre_user_nicename', $user_nicename);

	if ( empty($user_url) )
		$user_url = '';
	$user_url = apply_filters('pre_user_url', $user_url);

	if ( empty($user_email) )
		$user_email = '';
	$user_email = apply_filters('pre_user_email', $user_email);

	if ( !$update && ! defined( 'WP_IMPORTING' ) && email_exists($user_email) )
		return new WP_Error( 'existing_user_email', __( 'Sorry, that email address is already used!' ) );

	if ( empty($nickname) )
		$nickname = $user_login;
	$nickname = apply_filters('pre_user_nickname', $nickname);

	if ( empty($first_name) )
		$first_name = '';
	$first_name = apply_filters('pre_user_first_name', $first_name);

	if ( empty($last_name) )
		$last_name = '';
	$last_name = apply_filters('pre_user_last_name', $last_name);

	if ( empty( $display_name ) ) {
		if ( $update )
			$display_name = $user_login;
		elseif ( $first_name && $last_name )
			/* translators: 1: first name, 2: last name */
			$display_name = sprintf( _x( '%1$s %2$s', 'Display name based on first name and last name' ), $first_name, $last_name );
		elseif ( $first_name )
			$display_name = $first_name;
		elseif ( $last_name )
			$display_name = $last_name;
		else
			$display_name = $user_login;
	}
	$display_name = apply_filters( 'pre_user_display_name', $display_name );

	if ( empty($description) )
		$description = '';
	$description = apply_filters('pre_user_description', $description);

	if ( empty($rich_editing) )
		$rich_editing = 'true';

	if ( empty($comment_shortcuts) )
		$comment_shortcuts = 'false';

	if ( empty($admin_color) )
		$admin_color = 'fresh';
	$admin_color = preg_replace('|[^a-z0-9 _.\-@]|i', '', $admin_color);

	if ( empty($use_ssl) )
		$use_ssl = 0;

	if ( empty($user_registered) )
		$user_registered = gmdate('Y-m-d H:i:s');

	if ( empty($show_admin_bar_front) )
		$show_admin_bar_front = 'true';

	$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $user_nicename, $user_login));

	if ( $user_nicename_check ) {
		$suffix = 2;
		while ($user_nicename_check) {
			$alt_user_nicename = $user_nicename . "-$suffix";
			$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $alt_user_nicename, $user_login));
			$suffix++;
		}
		$user_nicename = $alt_user_nicename;
	}

	$data = compact( 'user_pass', 'user_email', 'user_url', 'user_nicename', 'display_name', 'user_registered' );
	$data = stripslashes_deep( $data );

	if ( $update ) {
		$wpdb->update( $wpdb->users, $data, compact( 'ID' ) );
		$user_id = (int) $ID;
	} else {
		$wpdb->insert( $wpdb->users, $data + compact( 'user_login' ) );
		$user_id = (int) $wpdb->insert_id;
	}

	$user = new WP_User( $user_id );

	foreach ( _get_additional_user_keys( $user ) as $key ) {
		if ( isset( $$key ) )
			update_user_meta( $user_id, $key, $$key );
	}

	if ( isset($role) )
		$user->set_role($role);
	elseif ( !$update )
		$user->set_role(get_option('default_role'));

	wp_cache_delete($user_id, 'users');
	wp_cache_delete($user_login, 'userlogins');

	if ( $update )
		do_action('profile_update', $user_id, $old_user_data);
	else
		do_action('user_register', $user_id);

	return $user_id;
}

/**
 * Update an user in the database.
 *
 * It is possible to update a user's password by specifying the 'user_pass'
 * value in the $userdata parameter array.
 *
 * If $userdata does not contain an 'ID' key, then a new user will be created
 * and the new user's ID will be returned.
 *
 * If current user's password is being updated, then the cookies will be
 * cleared.
 *
 * @since 2.0.0
 * @see wp_insert_user() For what fields can be set in $userdata
 * @uses wp_insert_user() Used to update existing user or add new one if user doesn't exist already
 *
 * @param mixed $userdata An array of user data or a user object of type stdClass or WP_User.
 * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
 */
function wp_update_user($userdata) {
	if ( is_a( $userdata, 'stdClass' ) )
		$userdata = get_object_vars( $userdata );
	elseif ( is_a( $userdata, 'WP_User' ) )
		$userdata = $userdata->to_array();

	$ID = (int) $userdata['ID'];

	// First, get all of the original fields
	$user_obj = get_userdata( $ID );
	if ( ! $user_obj )
		return new WP_Error( 'invalid_user_id', __( 'Invalid user ID' ) );

	$user = $user_obj->to_array();

	// Add additional custom fields
	foreach ( _get_additional_user_keys( $user_obj ) as $key ) {
		$user[ $key ] = get_user_meta( $ID, $key, true );
	}

	// Escape data pulled from DB.
	$user = add_magic_quotes( $user );

	// If password is changing, hash it now.
	if ( ! empty($userdata['user_pass']) ) {
		$plaintext_pass = $userdata['user_pass'];
		$userdata['user_pass'] = wp_hash_password($userdata['user_pass']);
	}

	wp_cache_delete($user[ 'user_email' ], 'useremail');

	// Merge old and new fields with new fields overwriting old ones.
	$userdata = array_merge($user, $userdata);
	$user_id = wp_insert_user($userdata);

	// Update the cookies if the password changed.
	$current_user = wp_get_current_user();
	if ( $current_user->ID == $ID ) {
		if ( isset($plaintext_pass) ) {
			wp_clear_auth_cookie();
			wp_set_auth_cookie($ID);
		}
	}

	return $user_id;
}

/**
 * A simpler way of inserting an user into the database.
 *
 * Creates a new user with just the username, password, and email. For more
 * complex user creation use wp_insert_user() to specify more information.
 *
 * @since 2.0.0
 * @see wp_insert_user() More complete way to create a new user
 *
 * @param string $username The user's username.
 * @param string $password The user's password.
 * @param string $email The user's email (optional).
 * @return int The new user's ID.
 */
function wp_create_user($username, $password, $email = '') {
	$user_login = esc_sql( $username );
	$user_email = esc_sql( $email    );
	$user_pass = $password;

	$userdata = compact('user_login', 'user_email', 'user_pass');
	return wp_insert_user($userdata);
}

/**
 * Return a list of meta keys that wp_insert_user() is supposed to set.
 *
 * @since 3.3.0
 * @access private
 *
 * @param object $user WP_User instance.
 * @return array
 */
function _get_additional_user_keys( $user ) {
	$keys = array( 'first_name', 'last_name', 'nickname', 'description', 'rich_editing', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front' );
	return array_merge( $keys, array_keys( _wp_get_user_contactmethods( $user ) ) );
}

/**
 * Set up the default contact methods.
 *
 * @since 2.9.0
 * @access private
 *
 * @param object $user User data object (optional).
 * @return array $user_contactmethods Array of contact methods and their labels.
 */
function _wp_get_user_contactmethods( $user = null ) {
	$user_contactmethods = array(
		'aim' => __('AIM'),
		'yim' => __('Yahoo IM'),
		'jabber' => __('Jabber / Google Talk')
	);
	return apply_filters( 'user_contactmethods', $user_contactmethods, $user );
}
