// open help in new window
jQuery(document).ready(function(){
	jQuery('#toplevel_page_premise-main a').click(function(e){
		var href = jQuery(this).attr('href');
		if(href.indexOf('?page=premise-help') > 0) {
			e.preventDefault();
			window.open(href,'_blank');
		}
	});
});