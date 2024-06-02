(function() {
	tinymce.create('tinymce.plugins.PremiseInfoPlugin', {

		init : function(ed, url) {
			ed.addCommand('MCEPremiseSampleCopy', function() {
				insert_sample_content(ed.id);
			});
			ed.addButton('PremiseSampleCopy', {
				title: 'Insert Sample Copy',
				cmd: 'MCEPremiseSampleCopy',
				image: url + '/img/icon_sample-copy.png'
			});
			
			ed.addCommand('MCEPremiseInsertGraphic', function() {
				show_graphic_library(ed.id);
			});
			ed.addButton('PremiseInsertGraphic', {
				title: 'Insert Graphic',
				cmd: 'MCEPremiseInsertGraphic',
				image: url + '/img/icon_graphics-library.png'
			});
			
			ed.addCommand('MCEPremiseInsertOptIn', function() {
				show_opt_in_inserter(ed.id);
			});
			ed.addButton('PremiseInsertOptIn', {
				title: 'Insert Opt In Code',
				cmd: 'MCEPremiseInsertOptIn',
				image: url + '/img/icon_opt-in-form.png'
			});
			
			ed.addCommand('MCEPremiseInsertNoticeBox', function() {
				premise_send_to_editor('<div class="notice">Put your notice text here.</div>', ed.id);
			});
			ed.addButton('PremiseInsertNoticeBox', {
				title: 'Insert Notice Box',
				cmd: 'MCEPremiseInsertNoticeBox',
				image: url + '/img/icon_notice-box.png'
			});
			
			ed.addCommand('MCEPremiseInsertButton', function() {
				show_button_usage_inserter(ed.id);
			});
			ed.addButton('PremiseInsertButton', {
				title: 'Insert Custom Button',
				cmd: 'MCEPremiseInsertButton',
				image: url + '/img/icon_insert-button.png'
			});
			
		},

		createControl : function(n, cm) {
			return null;
		},

		
		getInfo : function() {
			return {
				longname : 'Premise',
				author   :  'Copyblogger Media',
				authorurl : 'http://www.copyblogger.com',
				infourl : 'http://www.copyblogger.com',
				version : "1.0.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('PremiseInfo', tinymce.plugins.PremiseInfoPlugin);
})();
