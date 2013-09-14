<?php

/**
 * Alter the timeout on cron requests from 0.01 to 0.5. Something about
 * the Vagrant and/or Ubuntu setup doesn't like these self requests 
 * happening so quickly.
 */
add_filter( 'cron_request', 'jf_cron_request', 10, 1 );
function jf_cron_request( $cron_request ) {
	$cron_request['args']['timeout'] = (float) 0.5;
	return $cron_request;
}