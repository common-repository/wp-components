
function addFormField() {
	var wpcid = document.getElementById("wpcomponent_id").value;
	wpcid1 = (wpcid - 1) + 2;
	jQuery("#divTxt").append(" \
		<div id='section-" + wpcid + "' class='eachsection'> \
		<h3>New Component #" + wpcid1 + " \
		<a href='#' class='collapse' id='collapse-" + wpcid + "' onClick=\"collapseComp('" + wpcid + "'); return false;\">&mdash;</a> \
		&nbsp;<a href='#' class='expand' id='expand-" + wpcid + "' onClick=\"expandComp('" + wpcid + "'); return false;\">+</a> \
		</h3><div id='inside-" + wpcid + "'><p class='form-field'><label>Title:</label><br /> \
		<input type='text' name='wpcomponent_title[]' class='wpc_input' value='' /></p> \
		<p class='form-field'><label>Body:</label><br /> \
		<textarea rows='5' class='wpc_textarea' name='wpcomponent_val[]'></textarea> \
		<input type='hidden' name='wpcomponent_slug[]' value='' /> \
		<input type='hidden' name='wpcomponent_id[]' value='" + wpcid + "' /></p><p><small> \
		<a href='#' class='deletion delete' onClick='if ( confirm(\"You are about to delete this component\") ) { removeFormField(\"#section-" + wpcid + "\")}return false;'>delete</a></small></p></div></div> \
	");
	document.getElementById("wpcomponent_id").value = wpcid1;
};

function removeFormField(wpcid) {
	jQuery(wpcid).remove();
};

function collapseComp(wpcid) {
	jQuery("#inside-" + wpcid + "").css("display","none");
	jQuery("#collapse-" + wpcid + "").css("display","none");
	jQuery("#expand-" + wpcid + "").css("display","inline");
	document.cookie = "inside" + wpcid + "=collapsed; expires=60*60; path=/";
};

function expandComp(wpcid) {
	jQuery("#inside-" + wpcid + "").css("display","block");
	jQuery("#collapse-" + wpcid + "").css("display","inline");
	jQuery("#expand-" + wpcid + "").css("display","none");
	document.cookie = "inside" + wpcid + "=expanded; expires=60*60; path=/";

};


jQuery(document).ready(function() {
						
	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
	
	if (document.getElementById("wpcomponent_id")) {
		var wpcid = document.getElementById("wpcomponent_id").value;
		//confirm(wpcid);
		for(var i=0;i <= wpcid;i++) {
			var inside = readCookie('inside'+ i + '');
			//confirm(inside);
			if (inside == 'collapsed') {
				collapseComp(i);
			}
		}
	}
});
