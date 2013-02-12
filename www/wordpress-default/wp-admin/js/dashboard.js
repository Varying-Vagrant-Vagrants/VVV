var ajaxWidgets, ajaxPopulateWidgets, quickPressLoad;

jQuery(document).ready( function($) {
	/* Dashboard Welcome Panel */
	var welcomePanel = $('#welcome-panel'),
		welcomePanelHide = $('#wp_welcome_panel-hide'),
	 	updateWelcomePanel = function( visible ) {
			$.post( ajaxurl, {
				action: 'update-welcome-panel',
				visible: visible,
				welcomepanelnonce: $('#welcomepanelnonce').val()
			});
		};

	if ( welcomePanel.hasClass('hidden') && welcomePanelHide.prop('checked') )
		welcomePanel.removeClass('hidden');

	$('.welcome-panel-close, .welcome-panel-dismiss a', welcomePanel).click( function(e) {
		e.preventDefault();
		welcomePanel.addClass('hidden');
		updateWelcomePanel( 0 );
		$('#wp_welcome_panel-hide').prop('checked', false);
	});

	welcomePanelHide.click( function() {
		welcomePanel.toggleClass('hidden', ! this.checked );
		updateWelcomePanel( this.checked ? 1 : 0 );
	});

	// These widgets are sometimes populated via ajax
	ajaxWidgets = [
		'dashboard_incoming_links',
		'dashboard_primary',
		'dashboard_secondary',
		'dashboard_plugins'
	];

	ajaxPopulateWidgets = function(el) {
		function show(i, id) {
			var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
			if ( e.length ) {
				p = e.parent();
				setTimeout( function(){
					p.load( ajaxurl + '?action=dashboard-widgets&widget=' + id, '', function() {
						p.hide().slideDown('normal', function(){
							$(this).css('display', '');
						});
					});
				}, i * 500 );
			}
		}

		if ( el ) {
			el = el.toString();
			if ( $.inArray(el, ajaxWidgets) != -1 )
				show(0, el);
		} else {
			$.each( ajaxWidgets, show );
		}
	};
	ajaxPopulateWidgets();

	postboxes.add_postbox_toggles(pagenow, { pbshow: ajaxPopulateWidgets } );

	/* QuickPress */
	quickPressLoad = function() {
		var act = $('#quickpost-action'), t;
		t = $('#quick-press').submit( function() {
			$('#dashboard_quick_press #publishing-action .spinner').show();
			$('#quick-press .submit input[type="submit"], #quick-press .submit input[type="reset"]').prop('disabled', true);

			if ( 'post' == act.val() ) {
				act.val( 'post-quickpress-publish' );
			}

			$('#dashboard_quick_press div.inside').load( t.attr( 'action' ), t.serializeArray(), function() {
				$('#dashboard_quick_press #publishing-action .spinner').hide();
				$('#quick-press .submit input[type="submit"], #quick-press .submit input[type="reset"]').prop('disabled', false);
				$('#dashboard_quick_press ul').next('p').remove();
				$('#dashboard_quick_press ul').find('li').each( function() {
					$('#dashboard_recent_drafts ul').prepend( this );
				} ).end().remove();
				quickPressLoad();
			} );
			return false;
		} );

		$('#publish').click( function() { act.val( 'post-quickpress-publish' ); } );

		$('#title, #tags-input').each( function() {
			var input = $(this), prompt = $('#' + this.id + '-prompt-text');

			if ( '' === this.value )
				prompt.removeClass('screen-reader-text');

			prompt.click( function() {
				$(this).addClass('screen-reader-text');
				input.focus();
			});

			input.blur( function() {
				if ( '' === this.value )
					prompt.removeClass('screen-reader-text');
			});

			input.focus( function() {
				prompt.addClass('screen-reader-text');
			});
		});

		$('#quick-press').on( 'click focusin', function() {
			wpActiveEditor = 'content';
		});
	};
	quickPressLoad();

} );
