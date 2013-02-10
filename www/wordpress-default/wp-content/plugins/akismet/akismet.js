jQuery(document).ready(function () {
	jQuery('.akismet-status').each(function () {
		var thisId = jQuery(this).attr('commentid');
		jQuery(this).prependTo('#comment-' + thisId + ' .column-comment div:first-child');
	});
	jQuery('.akismet-user-comment-count').each(function () {
		var thisId = jQuery(this).attr('commentid');
		jQuery(this).insertAfter('#comment-' + thisId + ' .author strong:first').show();
	});
	jQuery('#the-comment-list tr.comment .column-author a[title ^= "http://"]').each(function () {
 		var thisTitle = jQuery(this).attr('title');
 		    thisCommentId = jQuery(this).parents('tr:first').attr('id').split("-");
 		
 		jQuery(this).attr("id", "author_comment_url_"+ thisCommentId[1]);
 		
 		if (thisTitle) {
 			jQuery(this).after(' <a href="#" class="remove_url" commentid="'+ thisCommentId[1] +'" title="Remove this URL">x</a>');
 		}
 	});
 	jQuery('.remove_url').live('click', function () {
 		var thisId = jQuery(this).attr('commentid');
 		var data = {
 			action: 'comment_author_deurl',
			_wpnonce: WPAkismet.comment_author_url_nonce,
 			id: thisId
 		};
 		jQuery.ajax({
		    url: ajaxurl,
		    type: 'POST',
		    data: data,
		    beforeSend: function () {
		        // Removes "x" link
	 			jQuery("a[commentid='"+ thisId +"']").hide();
	 			// Show temp status
		        jQuery("#author_comment_url_"+ thisId).html('<span>Removing...</span>');
		    },
		    success: function (response) {
		        if (response) {
	 				// Show status/undo link
	 				jQuery("#author_comment_url_"+ thisId).attr('cid', thisId).addClass('akismet_undo_link_removal').html('<span>URL removed (</span>undo<span>)</span>');
	 			}
		    }
		});

 		return false;
 	});
 	jQuery('.akismet_undo_link_removal').live('click', function () {
 		var thisId = jQuery(this).attr('cid');
		var thisUrl = jQuery(this).attr('href').replace("http://www.", "").replace("http://", "");
 		var data = {
 			action: 'comment_author_reurl',
			_wpnonce: WPAkismet.comment_author_url_nonce,
 			id: thisId,
 			url: thisUrl
 		};
		jQuery.ajax({
		    url: ajaxurl,
		    type: 'POST',
		    data: data,
		    beforeSend: function () {
	 			// Show temp status
		        jQuery("#author_comment_url_"+ thisId).html('<span>Re-adding…</span>');
		    },
		    success: function (response) {
		        if (response) {
	 				// Add "x" link
					jQuery("a[commentid='"+ thisId +"']").show();
					// Show link
					jQuery("#author_comment_url_"+ thisId).removeClass('akismet_undo_link_removal').html(thisUrl);
	 			}
		    }
		});
 		
 		return false;
 	});
 	jQuery('a[id^="author_comment_url"]').mouseover(function () {
		var wpcomProtocol = ( 'https:' === location.protocol ) ? 'https://' : 'http://';
		// Need to determine size of author column
		var thisParentWidth = jQuery(this).parent().width();
		// It changes based on if there is a gravatar present
		thisParentWidth = (jQuery(this).parent().find('.grav-hijack').length) ? thisParentWidth - 42 + 'px' : thisParentWidth + 'px';
		if (jQuery(this).find('.mShot').length == 0 && !jQuery(this).hasClass('akismet_undo_link_removal')) {
			var thisId = jQuery(this).attr('id').replace('author_comment_url_', '');
			jQuery('.widefat td').css('overflow', 'visible');
			jQuery(this).css('position', 'relative');
			var thisHref = jQuery.URLEncode(jQuery(this).attr('href'));
			jQuery(this).append('<div class="mShot mshot-container" style="left: '+thisParentWidth+'"><div class="mshot-arrow"></div><img src="'+wpcomProtocol+'s0.wordpress.com/mshots/v1/'+thisHref+'?w=450" width="450" class="mshot-image_'+thisId+'" style="margin: 0;" /></div>');
			setTimeout(function () {
				jQuery('.mshot-image_'+thisId).attr('src', wpcomProtocol+'s0.wordpress.com/mshots/v1/'+thisHref+'?w=450&r=2');
			}, 6000);
			setTimeout(function () {
				jQuery('.mshot-image_'+thisId).attr('src', wpcomProtocol+'s0.wordpress.com/mshots/v1/'+thisHref+'?w=450&r=3');
			}, 12000);
		} else {
			jQuery(this).find('.mShot').css('left', thisParentWidth).show();
		}
	}).mouseout(function () {
		jQuery(this).find('.mShot').hide();
	});
});
// URL encode plugin
jQuery.extend({URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;}
});
// Preload mshot images after everything else has loaded
jQuery(window).load(function() {
	var wpcomProtocol = ( 'https:' === location.protocol ) ? 'https://' : 'http://';
	jQuery('a[id^="author_comment_url"]').each(function () {
		jQuery.get(wpcomProtocol+'s0.wordpress.com/mshots/v1/'+jQuery.URLEncode(jQuery(this).attr('href'))+'?w=450');
	});
});
