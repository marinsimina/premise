<script type="text/javascript">
var scribe_seo_dependency_override = null;
function premise_scribe_focus_out() {
	var $ = jQuery;
	var $combined = $('#premise-combined-content').val('');
	var string = '';
	$('form#post textarea:not(#premise-combined-content)').each(function() {
		var $textarea = $(this);
		var id = $textarea.attr('id');
		var ed = typeof(tinyMCE) == 'undefined' ? null : tinyMCE.get(id);
		if(ed) {
			if(!ed.isHidden()) {
				string += ed.getContent();
			} else {
				string += $textarea.val();
			}
		} else {
			string += $textarea.val();
		}
	});

	$combined.val($.trim(string));
};

if (typeof scribe_seo == 'object') {

	scribe_seo.dependency_map.content = 'premise-combined-content';
<?php if ( isset( $use_premise_seo ) && $use_premise_seo ) { ?>
	scribe_seo.dependency_map.title = 'premise-seo-title';
	scribe_seo.dependency_map.description = 'premise-seo-description';
<?php } ?>

	scribe_seo_dependency_override = function() {
		premise_scribe_focus_out();
		scribe_seo.check_dependencies();
	}
} else if (typeof(ecordia) == 'object') {
	ecordia.ecordia_dependency = 'user-defined';
	ecordia.elementIds['title'] = 'premise-seo-title';
	ecordia.elementIds['description'] = 'premise-seo-description';

	ecordia.elementIds['content'] = 'premise-combined-content';

	var old_ecordia_blur_event = ecordia.blurEvent;
	ecordia.blurEvent = function() {
		premise_scribe_focus_out();
		old_ecordia_blur_event();
	};

	var old_ecordia_ecordia_addTinyMCEEvent = ecordia_addTinyMCEEvent;
	ecordia_addTinyMCEEvent = function(ed) {
		old_ecordia_ecordia_addTinyMCEEvent(ed);
		ed.on('keyup', function(ed, e) { 
			ecordia.blurEvent();
		});
	}

	scribe_seo_dependency_override = function() {
		ecordia.blurEvent();
	}
} 

jQuery(document).ready(function($) {
	if ( typeof scribe_seo_dependency_override == 'function' ) {

		$('form#post').append($('<textarea id="premise-combined-content"></textarea>').hide());

		$('form#post textarea').live('focusout', scribe_seo_dependency_override);

		scribe_seo_dependency_override();
	}
});

</script>
