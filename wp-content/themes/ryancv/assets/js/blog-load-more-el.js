( function( $ ) {
	"use strict";
	
	var count = 2;
	var total = ajax_blog_infinite_scroll_data.max_num;
	var flag = 1;
	var card_wrap = $('.blog').closest('.card-inner').find('.card-wrap');
	var card_items = card_wrap.find('.blog .row');

	$('.blog .load-more').on( 'click', function(){
		if ( count > total ) {
            $(this).closest('.bts').hide();
        } else {
        	if( flag == 1 ){
        		//console.log('pageNumber: ' + count);
            	loadContent(count);

            	if ( count + 1 > total ) {
            		$(this).closest('.bts').fadeOut(500);
            	}
            }
        }
        if( flag == 1 ){
        	flag = 0;
        	count++;
        }

        return false;
	});

	function loadContent(pageNumber) {
	    $.ajax({
	        url: ajax_blog_infinite_scroll_data.url,
	        type:'POST',
	        data: "action=infinite_scroll_el&page_no="+ pageNumber + '&post_type=post' + '&page_id=' + ajax_blog_infinite_scroll_data.page_id + '&order_by=' + ajax_blog_infinite_scroll_data.order_by + '&order=' + ajax_blog_infinite_scroll_data.order + '&per_page=' + ajax_blog_infinite_scroll_data.per_page + '&source=' + ajax_blog_infinite_scroll_data.source + '&temp=' + ajax_blog_infinite_scroll_data.temp + '&cat_ids=' + ajax_blog_infinite_scroll_data.cat_ids,
	        success: function(html){
	            var $html = $(html);
	            var $container = card_items;
	            $container.find('> .clear').remove();
	            $container.append($html);
	            $container.append('<div class="clear"></div>');

	            flag = 1;
	        }
	    });
	    return false;
	}
} )( jQuery );