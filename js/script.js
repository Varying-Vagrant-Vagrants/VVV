/* global wct_vars */
/*!
 * WordCamp Talks script
 */

( function( $ ) {

	// Only use raty if loaded
	if ( typeof wct_vars.raty_loaded !== 'undefined' ) {

		wpis_update_rate_num( 0 );

		$( 'div#rate' ).raty( {
			cancel     : false,
			half       : false,
			halfShow   : true,
			starType   : 'i',
			readOnly   : wct_vars.readonly,
			score      : wct_vars.average_rate,
			targetKeep : false,
			noRatedMsg : wct_vars.not_rated,
			hints      : wct_vars.hints,
			number     : wct_vars.hints_nb,
			click      : function( score ) {
				if ( ! wct_vars.can_rate ) {
					return;
				}
				// Disable the rating stars
				$.fn.raty( 'readOnly', true, '#rate' );
				// Update the score
				wct_post_rating( score );
			}
		} );
	}

	function wct_post_rating( score ) {
		$( '.rating-info' ).html( wct_vars.wait_msg );

		var data = {
			action: 'wct_rate',
			rate: score,
			wpnonce: wct_vars.wpnonce,
			talk:$('#rate').data('talk')
		};

		$.post( wct_vars.ajaxurl, data, function( response ) {
			if( response && response > 0  ){
				$( '.rating-info' ).html( wct_vars.success_msg + ' ' + response ).fadeOut( 2000, function() {
					wpis_update_rate_num( 1 );
					$(this).show();
				} );
			} else {
				$( '.rating-info' ).html( wct_vars.error_msg );
				$.fn.raty( 'readOnly', false, '#rate' );
			}
		});
	}

	function wpis_update_rate_num( rate ) {
		var number = Number( wct_vars.rate_nb ) + rate,
			msg;

		if ( 1 === number ) {
			msg = wct_vars.one_rate;
		} else if( 0 === number ) {
			msg = wct_vars.not_rated;
		} else {
			msg = wct_vars.x_rate.replace( '%', number );
		}

		$( '.rating-info' ).html( '<a>' + msg + '</a>' );
	}

	if ( typeof wct_vars.tagging_loaded !== 'undefined' ) {
		$( '#_wct_the_tags' ).tagging( {
			'tags-input-name'      : 'wct[_the_tags]',
			'edit-on-delete'       : false,
			'tag-char'             : '',
			'forbidden-chars'      : [ '.', '_', '?', '<', '>' ],
			'forbidden-words'      : ['script'],
			'no-duplicate-text'    : wct_vars.duplicate_tag,
			'forbidden-chars-text' : wct_vars.forbidden_chars,
			'forbidden-words-text' : wct_vars.forbidden_words
		} );

		// Make sure the title gets the focus
		$( '#_wct_the_title' ).focus();

		// Add most used tags
		$( '#wct_most_used_tags .tag-items a' ).on( 'click', function( event ) {
			event.preventDefault();

			$( '#_wct_the_tags' ).tagging( 'add', $( this ).html() );
		} );

		// Reset tags
		$( '#wordcamp-talks-form' ).on( 'reset', function() {
			$( '#_wct_the_tags' ).tagging( 'reset' );
		} );
	}

	// Set the interval and the namespace event
	if ( typeof wp !== 'undefined' && typeof wp.heartbeat !== 'undefined' && typeof wct_vars.pulse !== 'undefined' ) {
		wp.heartbeat.interval( wct_vars.pulse );

		$.fn.extend( {
			'heartbeat-send': function() {
				return this.bind( 'heartbeat-send.wc_talks' );
			}
		} );
	}

	// Send the current talk ID being edited
	$( document ).on( 'heartbeat-send.wc_talks', function( e, data ) {
		data.wc_talks_heartbeat_current_talk = wct_vars.talk_id;
	} );

	// Inform the user if data has been returned
	$( document ).on( 'heartbeat-tick', function( e, data ) {

		// Only proceed if an admin took the lead
		if ( ! data.wc_talks_heartbeat_response ) {
			return;
		}

		if ( ! $( '#wordcamp-talks .message' ).length ) {
			$( '#wordcamp-talks' ).prepend(
				'<div class="message info">' +
					'<p>' + wct_vars.warning + '</p>' +
				'</div>'
			);
		} else {
			$( '#wordcamp-talks .message' ).removeClass( 'error' ).addClass( 'info' );
			$( '#wordcamp-talks .message p' ).html( wct_vars.warning );
		}

		$( '#wordcamp-talks .submit input[name="wct[save]"]' ).remove();
	} );

	if ( typeof wct_vars.is_profile !== 'undefined' ) {
		var reset_height = $( '#item-header-content' ).innerHeight() || 0;

		$( '.wp-embed-share-input' ).on( 'click', function ( e ) {
			e.target.select();
		} );

		$( '.wp-embed-share-dialog-open' ).on( 'click', function () {
			$( '#item-header-content' ).css( {
				'width'  : '600px',
				'height' : '200px'
			} );

			$( '.wp-embed-share-dialog' ).removeClass( 'hidden' );
			$( '.wp-embed-share-tab-button [aria-selected="true"]' ).focus();
		} );

		$( '.wp-embed-share-dialog-close' ).on( 'click', function () {
			$( '.wp-embed-share-dialog' ).addClass( 'hidden' );
			$( '.wp-embed-share-dialog-open' ).focus();

			$( '#item-header-content' ).css( {
				'width'  : 'auto',
				'height' : reset_height + 'px'
			} );
		} );

		$( '.wp-embed-share-tab-button button' ).on( 'click', function( e ) {
			var control = $( e.target ).attr( 'aria-controls' );

			$( '.wp-embed-share-tab' ).each( function( t, tab ) {
				if ( control === $( tab ).prop( 'id' ) ) {
					$( tab ).attr( 'aria-hidden', 'false' );
				} else {
					$( tab ).attr( 'aria-hidden', 'true' );
				}
			} );
		} );
	}

})( jQuery );
