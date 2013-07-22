<?php
/**
 * Remove `index.php` from permalinks when using Nginx
 *
 * If `index.php` is showing up as a level within your permalinks when running
 * WordPress on Nginx, it can be removed by hooking into 'got_rewrite' and
 * explicitly forcing WordPress to believe that rewrite is available.
 *
 */

function nginx_got_rewrite( $rewrite_available ) {
    return true;
}
add_filter( 'got_rewrite', 'nginx_got_rewrite', 999 );
