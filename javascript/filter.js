jQuery(document).ready(
	function() {
		var form = jQuery('form#Form_FilterForm');

		jQuery(form).find('.Actions').hide();

		var location = getURLWithoutGetVars();

		if(location) {
			jQuery(form).find(".checkboxset").each(
				function(){
					var id = jQuery(this).attr("id");
					var selected = getUrlVars()[(id + '').toLowerCase()];
					jQuery(this).find("input[value="+selected+"]").attr("checked", "checked");
				}
			);
		}

		jQuery(form).find('input').change(
			function() {
				var url = jQuery(form).attr('action');
				var action = jQuery(form).find('input[type=submit]').attr('name');
				url = url.substr(0, url.lastIndexOf('/') + 1) + action.substr('action_'.length);
				jQuery.ajax({
					type: 'POST',
					url: url,
					data: jQuery(form).serialize(),
					dataType: 'json',
					success: function(response) {
						jQuery.each(response,
							function(id, html) {
								jQuery('#' + id).html(html).triggerHandler('onAfterWrite');
							}
						);
					}
				});
			}
		);
	}
);

function getUrlVars(){
	var vars = [], hash;
	if(window.location.href.indexOf('?')) {
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

		for(var i = 0; i < hashes.length; i++){
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
	}
	return vars;
}

function getURLWithoutGetVars(){
	var location = window.location.href.slice(0, window.location.href.indexOf('?') -1);
	return location;
}
