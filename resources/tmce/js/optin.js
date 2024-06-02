var PremiseOptIn = {
	init : function() {},

	insert : function() {
		var embedCode = '<div class="optin-box">'+document.forms[0].optincode.value+'</div>';
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, embedCode);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(PremiseOptIn.init, PremiseOptIn);