<?php

class MerchantPriceOption extends DataObject {

	static $db = array(
		'Price' => 'Currency',
		'ShowInFrom' => 'Boolean',
		'ShowInUpTo' => 'Boolean'
	);

	static $casting = array(
		'PriceNice' => 'HTMLText',
		'PriceInt' => 'Int'
	);

	static $summaryfields = array(
		'Price' => 'Price'
	);

	static $sort = "Price ASC";

	static $singular_name = 'Price Option';
		function i18n_singular_name() {return _t('MerchantPriceOption.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Price Options';
		function i18n_plural_name() {return _t('MerchantPriceOption.PLURALNAME', self::$plural_name);}

	function getPriceNice(){
		return Payment::site_currency().round($this->Price, 2);
	}

	function getPriceInt(){
		return round($this->Price);
	}

	function Link() {
		$page = DataObject::get_one('AllMerchantsPage');
		if($page) {
			$link = $page->Link()."?";
			$link .= $page->PriceFromLink($this->Price, false);
			$link .= $page->PriceUpToLink($this->Price, false);
			return $link;
		}
	}

}
