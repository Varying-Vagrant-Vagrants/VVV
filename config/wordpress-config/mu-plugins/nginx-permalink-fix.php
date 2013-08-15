<?php
/**
 * Remove `index.php` from permalinks when using Nginx
 *
 * If `index.php` is showing up as a level within your permalinks when running
 * WordPress on Nginx, it can be removed by hooking into 'got_rewrite' and
 * explicitly forcing WordPress to believe that rewrite is available.
 *
 */
add_filter( 'got_rewrite', '__return_true', 999 );
