function hex2rgb(hex) {
  var c, o = [], k = 0, m = hex.match(
    /^#(([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})|([0-9a-f])([0-9a-f])([0-9a-f]))$/i);
      
  if (!m) return {r:0,g:0,b:0};
  for (var i = 2, s = m.length; i < s; i++) {
    if (undefined === m[i]) continue;
    c = parseInt(m[i], 16);
    o[k++] = c + (i > 4 ? c * 16 : 0);
  }
  var obj = {r:o[0], g:o[1], b:o[2]};
  return '' + obj.r + ',' + obj.g + ',' + obj.b + '';
}

function premise_replace_editor_content(h, id) {
	if(id == '') {
		id = 'content';
	}
	
	var ed;
	if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.get(id) ) && !ed.isHidden() ) {
		ed.focus();
		ed.execCommand('mceSetContent', false, h);
	} else {
		var $canvas = jQuery('#'+id);
		$canvas.val(h);
	}
}

function premise_send_to_editor(h, id) {
	if((id == '' || id == undefined) && premise_send_to_editor_id_override == '') {
		premise_old_send_to_editor(h);
	} else {
		if(id == undefined) {
			id = premise_send_to_editor_id_override;
		}
		
		var ed;
		
		var $canvas = jQuery('#'+id);
		if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.get(id) ) && !ed.isHidden() ) {
			ed.focus();
			if (tinymce.isIE) {
				ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
			}

			ed.execCommand('mceInsertContent', false, h);
		} else if (typeof edInsertContent == 'function' && $canvas.parents('.postarea').length) {
			var myField = $canvas.get(0);
			edInsertContent(myField, h);
		} else {
			$canvas.val($canvas.val() + h);
		}

		tb_remove();
	}
}

var premise_send_to_editor_id_override = '';
if(typeof send_to_editor == 'function') {
	var premise_old_send_to_editor = send_to_editor;
	send_to_editor = premise_send_to_editor;
}

if(typeof tb_remove == 'function') {
	var premise_old_tb_remove = tb_remove;
	tb_remove = function() {
		premise_send_to_editor_id_override = '';
		premise_old_tb_remove();
	};
}

if(typeof(updateMediaForm) == 'function') {
	var oldUpdateMediaForm = updateMediaForm;
	updateMediaForm = function() {
		oldUpdateMediaForm();
		if(should_show_insert != undefined && should_show_insert) {
			jQuery('tr.send-to-premise-field').each(function() {
				var $this = jQuery(this);
				$this.parent().append($this);
				$this.show();
			});
		}
	};
}

function send_to_premise_field(fieldId, value) {
	if(fieldId != '') {
		jQuery('#' + fieldId).val(value);
	}
}

function show_graphic_library(editorId) {
	tb_show(Premise.graphics_title,jQuery('#premise-graphics-url').val().replace('send_to_premise_field_id=0', 'send_to_premise_field_id='+editorId));
	return false;
}

function show_opt_in_inserter(editorId) {
	tb_show(Premise.optin_title,jQuery('#premise-optin-url').val().replace('send_to_premise_field_id=0', 'send_to_premise_field_id='+editorId));
	return false;
}

function show_button_usage_inserter(editorId) {
	tb_show(Premise.button_title,jQuery('#premise-buttons-url').val().replace('send_to_premise_field_id=0', 'send_to_premise_field_id='+editorId));
	return false;
}

function insert_sample_content(editorId) {
	if(confirm('Do you want to override the editor contents with sample copy for this page type?')) {
		jQuery.post(
			ajaxurl,
			{
				action: 'premise_sample_copy',
				id: jQuery('#post_ID').val()
			},
			function(data, status) {
				if(data.error) {
					alert(data.error_message);
				} else {
					premise_replace_editor_content(data.copy, editorId);
				}
			},
			'json'
		);
	}
	return false;
}

function add_new_quicktags_to_editor(toolbarId, editorId) {
	var $toolbar = jQuery(toolbarId);

	$toolbar.append('<input type="button" value="sample content" title="Insert sample content" onclick="insert_sample_content(\''+editorId+'\');" class="ed_button" id="ed_sample_content" />');
	$toolbar.append('<input type="button" value="graphic library" title="Insert graphic from library" onclick="show_graphic_library(\''+editorId+'\');" class="ed_button" id="ed_graphic_library" />');
	$toolbar.append('<input type="button" value="opt in code" title="Insert opt in code" onclick="show_opt_in_inserter(\''+editorId+'\');" class="ed_button" id="ed_opt_in_code" />');
	$toolbar.append('<input type="button" value="custom buttons" title="Insert custom button" onclick="show_button_usage_inserter(\''+editorId+'\');" class="ed_button" id="ed_button_code" />');
}

var premise_editor_quicktags = new Array();
var premise_fetching_image = false;

jQuery(document).ready(function($) {
	$('.premise-sharing-type').bind('change click', function(event) {
		var $this = $(this);
		if($this.is(':checked')) {
			var value = $this.val();
			var $dependent = $('.premise-sharing-type-enhanced-dependent');
			
			if(1 == value) {
				$dependent.show();
			} else {
				$dependent.hide();
			}
		}
	}).change();
	
	$('#premise_style_title').keyup(function(event) {
		var $this = $(this);
		var title = $.trim($this.val());
		
		if('' == title) {
			title = 'Default';
		}
		
		$('#editing-style-name').text(title);
	}).keyup();

	$('.button-usage-cancel').click(function(event) {
		event.preventDefault();
		
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
	});
	
	$('#premise-button-insert').click(function(event) {
		event.preventDefault();
		
		var text = $.trim($('#premise-button-text').val());
		var url = $.trim($('#premise-button-url').val());
		var style = $('#premise-button-style').val();
		
		var link = text;
		
		var string = '[premise-button id="'+style+'" href="'+url+'"]'+link+'[/premise-button]';
		
		var win = window.dialogArguments || opener || parent || top;
		
		var id = $('#send_to_premise_field_id').attr('content');
		win.premise_send_to_editor(string, id);
		win.tb_remove();
	});

	
	$('.premise-media-buttons a').click(function(event) {
		premise_send_to_editor_id_override = $(this).parent().attr('rel');
	});
	
	$('#insert-optin-form-form').submit(function(event) {
		event.preventDefault();
		
		var $provider = $('#premise-optin-provider');
		var provider = $provider.val();
		var string;
		
		switch(provider) {
			case 'aweber':
				string = '[aweber-optin-form id="'+$('#aweber-list-forms').val()+'" align="'+$('#aweber-align').val()+'" title="'+$('#premise-optin-header').val()+'"]';
				break;
			case 'constant-contact':
				string = '[constant-contact-optin-form id="'+$('#constant-contact-list').val()+'" align="'+$('#constant-contact-align').val()+'" title="'+$('#premise-optin-header').val()+'"]';
				break;
			case 'mailchimp':
				string = '[mailchimp-optin-form id="'+$('#mailchimp-list').val()+'" align="'+$('#mailchimp-align').val()+'" title="'+$('#premise-optin-header').val()+'"]';
				break;
			case 'manual':
				// save the code to an option, and then add the shortcode to the page content
				$('#ajax-feedback-process-submit').css('visibility','visible');
				$('#insert-optin-form').attr('disabled','disabled');
				$.post(
					ajaxurl,
					{
						action: 'premise_save_optin_manual',
						code: $('#manual-form-code').val()
					},
					function(data,status) {
						$('#insert-optin-form').removeAttr('disabled');
						$('#ajax-feedback-process-submit').css('visibility','hidden');
						if(data.error) {
							alert('Error saving form code.  Please try again.');
						} else {
							string = '[manual-optin-form id="'+data.id+'" align="'+$('#manual-align').val()+'" title="'+$('#premise-optin-header').val()+'"]';
							
							var id = $('#send_to_premise_field_id').attr('content');
							
							var win = window.dialogArguments || opener || parent || top;
							win.premise_send_to_editor(string, id);
						}
					},
					'json'
				);
				return;
				break;
		}
		
		string = "\n" + string + "\n";
		
		var id = $('#send_to_premise_field_id').attr('content');
		
		var win = window.dialogArguments || opener || parent || top;
		win.premise_send_to_editor(string, id);
	});
	
	$('#premise-optin-provider').change(function(event) {
		var $this = $(this);
		var val = $this.val();
		
		var $div = $('#'+val+'-info');
		var $list = $('#'+val+'-list');
		
		$('.premise-optin-provider-info:visible').hide();
		
		if(val != 'manual' && $list.find('option').size() == 0) {
			$this.attr('disabled','disabled');
			$('#ajax-feedback-get-lists').css('visibility','visible');
			$.post(
				ajaxurl,
				{
					action: 'premise_get_lists',
					provider: val
				},
				function(data, status) {
					$this.removeAttr('disabled');
					$list.empty();
					$.each(data, function(i,val) {
						$item = $('<option value="'+this.id+'">'+this.name+'</option>');
						$item.data('forms', this.forms);
						$list.append($item);
					});
					$list.change();
					$div.show('fast');
					$('#ajax-feedback-get-lists').css('visibility','hidden');
				},
				'json'
			);
		} else {
			$div.show('fast');
		}
	}).change();
	
	$('#aweber-list').change(function(event) {
		var $this = $(this);
		var $selected = $this.find('option:selected');
		var val = $this.val();
		var $forms = $('#'+$this.attr('id')+'-forms');
		$forms.empty();
		
		var data = $selected.data('forms'); 
		if(undefined != data) { 
			$.each(data, function(i, val) {
				$forms.append($('<option value="'+this.id+'">'+this.name+'</option>'));
			});
		}
	});

	$('#_premise_settings\\[optin\\]\\[aweber-api\\]').bind('change click', function(event) {
		if ($(this).is(':checked')) {
			$('.premise-aweber-enhanced').slideDown();
			$('.premise-aweber-basic').slideUp();
		} else {
			$('.premise-aweber-enhanced').slideUp();
			$('.premise-aweber-basic').slideDown();
		}
	}).change();
	
	$('#_premise_settings\\[optin\\]\\[aweber-enhanced\\]').bind('change click', function(event) {
		if ($(this).is(':checked')) {
			$('.premise-aweber-app-id-wrap').slideDown();
		} else {
			$('.premise-aweber-app-id-wrap').slideUp();
		}
		if (event.type == 'click')
			$('.premise-aweber-authorization-url').toggle();
	}).change();
	
	$('a.send-to-premise-field').live('click', function(event) {
		event.preventDefault();
		var fieldId = $('#send_to_premise_field_id').attr('content');
		
		var win = window.dialogArguments || opener || parent || top;
		win.send_to_premise_field(fieldId, $(this).attr('href'));
		win.tb_remove();
	});
	
	$('.premise-graphic-use-this').live('click', function(event) {
		event.preventDefault();

		if(!premise_fetching_image) {
			premise_fetching_image = true;

			$('.premise-graphic-use-this').hide();
			$('#ajax-loading').appendTo($(this).parent()).css('visibility','visible');
			
			$.post(
				ajaxurl,
				{
					action: 'premise_use_graphic',
					slug: $(this).attr('data-slug'),
					name: $(this).attr('data-name'),
					filename: $(this).attr('data-filename')
				},
				function(data,status) {
					premise_fetching_image = false;
					if(data.error) {
						alert(data.error_message);
						$('.premise-graphic-use-this').show();
						$('#ajax-loading').appendTo($('#ajax-loading-container')).css('visibility','hidden');
					} else {
						var id = $('#send_to_premise_field_id').attr('content');
						
						var win = window.dialogArguments || opener || parent || top;
						win.premise_send_to_editor(data.html, id);
					}
				},
				'json'	
			);
			
		}
	});
	
	$('#_premise_settings\\[main\\]\\[rewrite\\]').keyup(function(event) {
		$(this).attr('size', $(this).val().length + 1);
	}).keyup();
	
	$('#wpbody-content .wrap > h2:first').append($('#premise-landing-page-type-name').show());
	
	$('#_premise_settings\\[main\\]\\[rewrite-root\\]').bind('change click', function() {
		if($(this).is(':checked')) {
			$('#premise-main-rewrite-container').hide();
		} else {
			$('#premise-main-rewrite-container').show();
		}
	}).change();
	
	var $subheading = $('#premise-subhead');
	if($subheading.size() > 0) {
		$('form#post').submit(function(event) {
			var value = $(this).find('#title').val();
			if(value == '') {
				$(this).find('#title').val(' ');
			}
		});
		
		$('input[type="radio"][value!="2"][name="screen_columns"]').parent().remove();
		
		$('#title-prompt-text').text('Enter headline here');
		
		$subheading.parents('.premise-option-box').hide();
		$subheading.remove();
		
		var $subheadingWrap = $('#subheadingwrap');
		$subheadingWrap.append($subheading);
		
		$('#titlediv .inside').before($subheadingWrap.show());
		
		if ( $('#premise-subhead').val() == '' ) {
			$('#subheading').siblings('#subheading-prompt-text').css('visibility', 'visible');
		}
		$('#subheading-prompt-text').click(function(){
			$(this).css('visibility', 'hidden').siblings('#subheading').focus();
		});
		$('#premise-subhead').blur(function(){
			if (this.value == '') {
				$(this).siblings('#subheading-prompt-text').css('visibility', 'visible');
			}
		}).focus(function(){
			$(this).siblings('#subheading-prompt-text').css('visibility', 'hidden');
		}).keydown(function(e){
			$(this).siblings('#subheading-prompt-text').css('visibility', 'hidden');
			$(this).unbind(e);
		}).blur();
		
		$('#content').attr('tabindex',3);
	}
	
	$('#premise-education-inside').width( $('#premise-education-actions').width() - 4 );
	$('#premise-education-toggle, #premise-education-inside').bind('mouseenter', function() {
		$('#premise-education-inside').removeClass('slideUp').addClass('slideDown');
		setTimeout(function() {
			if ( $('#premise-education-inside').hasClass('slideDown') ) {
				$('#premise-education-inside').slideDown(100);
				$('#premise-education-first').addClass('slide-down');
			}
		}, 200);
	}).bind('mouseleave', function() {
		$('#premise-education-inside').removeClass('slideDown').addClass('slideUp');
		setTimeout(function() {
			if ( $('#premise-education-inside').hasClass('slideUp') ) {
				$('#premise-education-inside').slideUp(100, function() {
					$('#premise-education-first').removeClass('slide-down');
				});
			}
		}, 300);
	}); 
	
	if($('#premise-content-scrollers-order-container').size() > 0) { 
		$('#premise-content-scrollers-order-container').sortable({
			axis: 'y',
			stop: function(event, ui) {
				premise_reorder_content_scroller_postboxes();
				premise_renumerate_content_sliders();
			}
		});
		$('#premise-content-scrollers-order-container').disableSelection();
	}
	
	$('#premise-tracking-ab').change(function(event) {
		if($(this).is(':checked')) {
			$('#premise-tracking-ab-original-container').show();
		} else {
			$('#premise-tracking-ab-original-container').hide();
		}
	}).change();
	
	$('#premise-tracking-page-type-test').change(function(event) {
		if($(this).is(':checked')) {
			$('#premise-tracking-link-click-conversion-container').show();
		} else {
			$('#premise-tracking-link-click-conversion-container').hide();
		}
	}).change();
	
	$('#premise-tracking-page-type-goal').change(function(event) {
		if($(this).is(':checked')) {
			$('#premise-tracking-link-click-conversion-container').hide();
		} else {
			$('#premise-tracking-link-click-conversion-container').show();
		}
	});
	
	$('.premise-character-count').parents('.premise-option-box').find('input, textarea').keyup(function(event) {
		var $this = $(this);
		var value = $this.val();
		
		var $count = $this.parents('.premise-option-box').find('.premise-character-count:first');
		var suggested = parseInt($count.siblings('.premise-character-count-suggested:first').text());
		var length = value.length;
		
		$count.text(length);
		if(length > suggested) {
			$count.addClass('exceeds');
		} else {
			$count.removeClass('exceeds');
		}
	}).keyup();
	
	$('#premise-header-image-hide').change(function(event) {
		var $this = $(this);
		var $container = $('.'+$this.attr('id')+'-dependent-container');
		if($this.is(':checked')) {
			$container.hide();
		} else {
			$container.show();
		}
	}).change();
	
	$('#premise-footer').change(function(event) {
		var $this = $(this);
		var $container = $('.'+$this.attr('id')+'-dependent-container');
		if($(this).is(':checked')) {
			$container.hide();
		} else {
			$container.show();
		}
	}).change();
	
	/// PRICING SPECIFIC STUFF
	
	$('#premise-pricing-columns').detach().insertBefore($('#normal-sortables'));
	
	$('.premise-pricing-title').live('keyup', function(event) {
		var $this = $(this);
		var $title = $this.parents('.postbox').find('h3.hndle span span.tab-name');
		var $item = $('#premise-pricing-order-'+$this.attr('id').replace(/\D/g,'')).find('span');
		var value = $this.val().replace(/^\s+|\s+$/g, '');
		
		if('' == value) {
			$title.text('(No Title)');
			$item.text('(No Title)');
		} else {
			$title.text(value);
			$item.text(value);
		}
	}).keyup();
	
	$('.premise-pricing-add-another-tab').live('click', function(event) {
		event.preventDefault();

		var $this = $(this);
		var $postbox = $('.premise-pricing-postbox.postbox:last');
		var $clone = $postbox.clone();
		var $item = $('.premise-pricing-order-item:last');
		var $itemClone = $item.clone();
		var number = $('div.premise-pricing-postbox').size();

		$clone.attr('id', 'premise-pricing-'+number);
		
		var $input = $clone.find('input.premise-pricing-title');
		$input.attr('id','premise-pricing-'+number+'-title');
		$input.attr('name',$input.attr('name').replace(/\d+/,number));
		$clone.find('h3.hndle span span.tab-name').text('Column '+(number+1));
		$clone.find('input.premise-pricing-title:first').val('Column '+(number+1));
		$itemClone.find('span').text('Column '+(number+1));
		$itemClone.attr('id','premise-pricing-order-'+number);
		$itemClone.find('input').val(number);
		
		$clone.find('.premise-pricing-attribute-input, .premise-pricing-cta-text, .premise-pricing-cta-url, .premise-pricing-cta-newwindow').each(function(i) {
			var $this = $(this);
			
			$this.attr('name',$(this).attr('name').replace(/\d+/, number));
			if($this.is('input[type="checkbox"]')) { 
				$this.removeAttr('checked');
			} else {
				$this.val('');
			}
		});
		
		$item.after($itemClone);
		$postbox.after($clone);
		$clone.find('input.premise-pricing-title:first').focus();
		
		$clone.find('.premise-pricing-attributes-container').sortable({
			axis: 'y',
			handle: 'span'
		});
		
		premise_renumerate_pricing_columns();
	});
	
	$('.premise-pricing-attributes-container').each(function(i) {
		$(this).sortable({
			axis: 'y',
			handle: 'span'
		});
	});
	
	$('.premise-pricing-delete-column').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $postbox = $this.parents('div.postbox');
		$postbox.slideUp(500, function() { 
			$(this).remove();
			$('#premise-pricing-order-'+$(this).attr('id').replace(/\D/g,'')).remove();
		});
		premise_renumerate_pricing_columns();
	});
	
	$('.remove-attribute-from-pricing-column').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $item = $this.parents('li');
		$item.slideUp('fast', function() {$item.remove();});
	});
	
	$('.premise-pricing-add-another-attribute').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $template = $this.parents('.postbox').find('.premise-pricing-attribute-template');
		var $clone = $template.clone();
		$clone.removeClass('premise-pricing-attribute-template').show().insertAfter($this.parents('.postbox').find('.premise-pricing-attribute-container:last'));
	});
	
	$('div.premise-pricing-postbox:first .premise-pricing-delete-column').hide();
	
	function premise_renumerate_pricing_columns() {
		var $boxes = $('div.premise-pricing-postbox');
		$boxes.each(function(i) {
			var $this = $(this);
			$delete = $this.find('.premise-pricing-delete-column');
			$delete.hide();
			if(i > 0) {
				$delete.show();
			}
		});
	}
	
	function premise_reorder_pricing_columns_postboxes() {
		var $postboxes = $('div.premise-pricing-postbox');
		
		$('.premise-pricing-order-item').each(function(i) {
			var number = $(this).attr('id').replace(/\D/g,'');
			var $postbox = $postboxes.filter('[id*="'+number+'"]');
			$('#premise-pricing-columns').append($postbox);
		});
	}
	
	premise_renumerate_pricing_columns();
	
	if($('#premise-pricing-order-container').size() > 0) { 
		$('#premise-pricing-order-container').sortable({
			axis: 'y',
			stop: function(event, ui) {
				premise_reorder_pricing_columns_postboxes();
				premise_renumerate_pricing_columns();
			}
		});
		$('#premise-pricing-order-container').disableSelection();
	}
	
	/// CONTENT SCROLL SPECIFIC STUFF
	$scrollertabs = $('#premise-content-scroller-tabs');
	if ($scrollertabs.length)
		$('#normal-sortables').detach().insertAfter($scrollertabs);
	
	$('.premise-content-scrollers-title').live('keyup', function(event) {
		var $this = $(this);
		var $title = $this.parents('.postbox').find('h3.hndle span span.tab-name');
		var $item = $('#premise-content-scrollers-order-'+$this.attr('id').replace(/\D/g,'')).find('span');
		var value = $this.val().replace(/^\s+|\s+$/g, '');
		
		if('' == value) {
			$title.text('(No Title)');
			$item.text('(No Title)');
		} else {
			$title.text(value);
			$item.text(value);
		}
	}).keyup();
	
	$('.premise-content-scrollers-add-another-tab').live('click', function(event) {
		event.preventDefault();

		// send it back to WP to add the tab
		$('#premise-add-another-content-scroller-tab').val('1');
		$('#save').click();
	});
	
	$('.premise-content-scrollers-delete-tab').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $postbox = $this.parents('div.postbox');
		$postbox.slideUp(500, function() { 
			$(this).remove();
			$('#premise-content-scrollers-order-'+$(this).attr('id').replace(/\D/g,'')).remove();
		});
		premise_renumerate_content_sliders();
	});
	
	$('div[id|="premise-content-scroller"]:first .premise-content-scrolls-delete-tab').hide();
	
	$('.premise-editor-area').each(function(i) {
		if($(this).find('.premise-editor-button-preview').hasClass('active')) {
			$(this).find('.premise-editor-quicktags').hide();
		}
	});
	
	$('.premise-editor-toolbar-button').live('click', function(event) {
		var $this = jQuery(this);
		var $editorArea = $this.parents('.premise-editor-area');
		var $editor = $editorArea.find('textarea:first');
		var $quicktags = $editorArea.find('.premise-editor-quicktags');
		var $html = $editorArea.find('.premise-editor-button-html');
		var $visual = $editorArea.find('.premise-editor-button-preview');	
		var id = $editor.attr('id');
		var number = id.replace(/\D/g,'');
		var value = $editor.val();
		var mode = $this.attr('data-mode');
		var ed;

		try {
			ed = tinyMCE.get(id);
		} catch(e) {
			ed = false;
		}
		
		if('tinymce' == mode) {
			$quicktags.hide();
			$visual.addClass('active');
			
			if(ed && !ed.isHidden()) {
				return false;
			}
			
			setUserSetting('editor', 'tinymce');
			
			var quicktags_object = window["premise_editor_quicktags_"+number];
			quicktags_object.edCloseAllTags();
			$quicktags.hide();
			
			value = switchEditors.wpautop(value);
			$editor.val(value);
			
			try {
				if(ed) {
					ed.show();
				} else {
					tinyMCE.execCommand("mceAddControl", false, id);
				}
			} catch(e) { }
			
		} else {
			$html.addClass('active');
			$visual.removeClass('active');
			
			$quicktags.show();
			setUserSetting('editor', 'html');
			
			if(ed && !ed.isHidden()) {
				$editor.css('height', ed.getContentAreaContainer().offsetHeight + 24 + "px");
				ed.hide();
			}
		}
	});
	
	function premise_reorder_content_scroller_postboxes() {
		var $postboxes = $('div.premise-content-scrollers-postbox');
		$postboxes.each(function(i) {
			var $textarea = $(this).find('textarea');
			
			if(typeof tinyMCE != 'undefined') {
				tinyMCE.execCommand("mceRemoveControl", false, $textarea.attr('id'));
			}
			
			$(this).remove();
		});
		
		$('.premise-content-scrollers-order-item').each(function(i) {
			var number = $(this).attr('id').replace(/\D/g,'');
			var $postbox = $postboxes.filter('[id*="'+number+'"]');
			var $textarea = $postbox.find('textarea');
			$('#premise-content-scroller-tabs').append($postbox);
			
			if($postbox.find('.premise-editor-button-preview.active').length > 0 && (typeof tinyMCE != 'undefined')) {
				tinyMCE.execCommand("mceAddControl", false, $textarea.attr('id'));
			}
		});
	}
	
	function premise_renumerate_content_sliders() {
		var $boxes = $('div.premise-content-scrollers-postbox');
		$boxes.each(function(i) {
			var $this = $(this);
			$delete = $this.find('.premise-content-scrollers-delete-tab');
			$delete.hide();
			if(i > 0) {
				$delete.show();
			}
		});
		
		if($boxes.size() > 0 && $boxes.size() >= parseInt(Premise.tabs_warning)) {
			$('#content-scroller-size-warning').show();
		} else {
			$('#content-scroller-size-warning').hide();
		}
	}
	
	premise_renumerate_content_sliders();
});

function create_premise_quicktags(number) {
	if (window["premise_editor_quicktags_"+number])
		return;

	toolbar = '#qt_'+number+'_toolbar';
	jQuery(toolbar).addClass('premise-editor-quicktags-toolbar');
	add_new_quicktags_to_editor(toolbar, number);
}

if(typeof(ZeroClipboard) != 'undefined') {
	ZeroClipboard.setMoviePath(PremiseZeroClipboard.movie_path);
}

/*
Product Autosuggest
*/
(function($) {
	$.fn.PremiseProductSuggest = function(options) {
		var dependencies = $.extend({}, 
			$.fn.PremiseProductSuggest.defaults, options);

		$(dependencies.target).autocomplete({
			appendTo: dependencies.wrap,
			delay: 150,
			html: true,
			minLength: 1,
			source: dependencies.source,
			close: function(){
				if(typeof dependencies.callback == 'function')
					dependencies.callback();
			}
		});
	
		// add key up/down handling to autocomplete	
		$(dependencies.target).keyup(function(event){
			if (event.keyCode == jQuery.ui.keyCode.UP && dependencies.index > 0) {
				dependencies.index--;
			} else if (event.keyCode == jQuery.ui.keyCode.DOWN && dependencies.index < jQuery(dependencies.wrap + ' > ul.ui-autocomplete > li').length) {
				dependencies.index++;
			} else {
				dependencies.index = 0;
			}
			$(dependencies.wrap + ' > ul.ui-autocomplete > li').removeClass('current');
			if (dependencies.index > 0)
				$(dependencies.wrap + ' > ul.ui-autocomplete li:nth-child(' + dependencies.index + ')').addClass('current');
		});
	};

	$.fn.PremiseProductSuggest.defaults = {
		target: '',
		wrap: '',
		callback: '',
		source: '',
		index: 0
	};
})(jQuery);