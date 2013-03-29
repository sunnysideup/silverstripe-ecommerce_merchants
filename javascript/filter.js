jQuery(document).ready(
	function() {
		AllMerchantsPageFilter.init();
		AllMerchantsPageFilter.initMoreLinks("#ProductsHolder");
	}
);

var AllMerchantsPageFilter = {

	useInfiniteScroll: false,
		set_UseInfiniteScroll: function(b) {this.useInfiniteScroll = b;},

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

				minPrice = parseInt(jQuery("select[name='pricefrom']").val());
				maxPrice = parseInt(jQuery("select[name='priceupto']").val());
				if(minPrice > 0 && maxPrice > 0 && (maxPrice <= minPrice)) {
					if(jQuery(this).attr("name") == "pricefrom") {
						var makeMaxHigher = true;
						var thatSelector = "select[name='priceupto']";
					}
					else {
						var makeMaxHigher = false;
						var thatSelector = "select[name='pricefrom']";
					}
					jQuery(thatSelector + " option").each(
						function(){
							value = jQuery(this).val();
							if(makeMaxHigher && (value > minPrice)){
								jQuery(thatSelector).val(value); //change();
							}
							else if(!makeMaxHigher && (value < maxPrice)) {
								jQuery(thatSelector).val(value); //change();
							}
						}
					);
				}

				var url = jQuery(form).attr('action');
				jQuery(".allMerchantsPageLoadingHolder").addClass("loading");
				var action = jQuery(form).find('input[type=submit]').attr('name');
				url = url.substr(0, url.lastIndexOf('/') + 1) + action.substr('action_'.length);
				jQuery.ajax({
					type: 'POST',
					url: url,
					data: jQuery(form).serialize(),
					dataType: 'json',
					success: function(response) {
						jQuery(".allMerchantsPageLoadingHolder").removeClass("loading");
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

		this.initInfiniteScroll();

		this.checkMinAndMax();

	},

	initMoreLinks: function(filter){
		jQuery(filter).find(".allMerchantsPageMore a").click(
			function(e) {
				e.preventDefault();
				var url = jQuery(this).attr("href");
				var target = jQuery(this).attr("rel");
				jQuery(".allMerchantsPageMore").addClass("loading");
				jQuery.ajax(
					{
						"url": url,
						"success": function(data,status,xhr) {
							jQuery("#AllMerchantsPageCurrentLink, .allMerchantsPageMore").remove();
							jQuery("#"+target).append(data);
							AllMerchantsPageFilter.initMoreLinks("#"+target);
							AllMerchantsPageFilter.updateLink();
						},
						"error": function(XMLHttpRequest, textStatus, errorThrown) {
							if (XMLHttpRequest.status == 0) {
								alert('Could not connect to website.');
							}
							else if (XMLHttpRequest.status == 404) {
								alert('Requested URL not found.');
							}
							else if (XMLHttpRequest.status == 500) {
								alert('Internel Server Error.');
							}
							else {
							 alert('Unknow Error.\n' + XMLHttpRequest.responseText);
							}
						}
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
	},

	/**
	 * auto-clicks the more button
	 * when during scrolling the scrollTop is equal to
	 * document.height - window.height (i.e. you are at the end of the document)
	 *
	 */
	initInfiniteScroll: function(){
		if(this.useInfiniteScroll) {
			jQuery(window).scroll(function() {
				if(jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
					if(jQuery('.allMerchantsPageMore a').length) {
						jQuery('.allMerchantsPageMore a').click();
					}
				}
			});
		}
	},

	/**
	 * check Min and Max Price and adjust if there are problems
	 *
	 *
	 */
	checkMinAndMax: function(){
		jQuery("select[name='pricefrom'], select[name='priceupto']").change(
			function(){



			}
		);
	}

}

