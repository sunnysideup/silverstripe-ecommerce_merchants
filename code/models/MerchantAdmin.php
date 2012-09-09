<?php
class MerchantAdmin extends ModelAdmin {

	public static $managed_models = array(
		'Merchant'
	);
	
	static $url_segment = 'merchants';
	
	static $menu_title = 'Merchants';
	
}