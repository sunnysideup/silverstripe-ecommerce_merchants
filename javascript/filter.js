jQuery(document).ready(
	function() {
		AllMerchantsPageFilter.init();
		AllMerchantsPageFilter.initMoreLinks();
	}
);

var AllMerchantsPageFilter = {

	init: function() {
		var form = jQuery('form#Form_FilterForm');

		jQuery(form).find('.Actions').hide();

		var location = AllMerchantsPageFilter.getURLWithoutGetVars();

		if(location) {
			jQuery(form).find(".dropdown").each(
				function(){
					var id = jQuery(this).attr("id");
					var selected = AllMerchantsPageFilter.getUrlVars()[(id + '').toLowerCase()];
					jQuery(this).find("input[value="+selected+"]").attr("checked", "checked");
				}
			);
		}

		jQuery(form).find('select').change(
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
						jQuery.each(
							response,
							function(id, html) {
								jQuery('#' + id).html(html).triggerHandler('onAfterWrite');
								AllMerchantsPageFilter.initMoreLinks('#' + id);
								AllMerchantsPageFilter.updateLink();
							}
						);
					}
				});
			}
		);
	},

	initMoreLinks: function(filter = "body"){
		jQuery(filter).find(".allMerchantsPageMore a").click(
			function(e) {
				e.preventDefault();
				var url = jQuery(this).attr("href");
				var target = jQuery(this).attr("rel");
				var parent = jQuery(this).parent();
				jQuery.get(
					url,
					function(data,status,xhr) {
						jQuery(parent).remove();
						jQuery("#AllMerchantsPageCurrentLink").remove();
						jQuery("#"+target).append(data);
						AllMerchantsPageFilter.initMoreLinks("#"+target);
						AllMerchantsPageFilter.updateLink();
					}
				);
			}
		)
	},

	updateLink: function(){
		var obj = jQuery("#AllMerchantsPageCurrentLink a");
		if(obj.length) {
			window.history.replaceState('Object', '', obj.attr("href"));
		}
	},

	getUrlVars: function (){
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
	},

	getURLWithoutGetVars: function (){
		var location = window.location.href.slice(0, window.location.href.indexOf('?') -1);
		return location;
	}


}

