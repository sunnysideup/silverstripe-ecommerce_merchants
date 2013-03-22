<?php

class MerchantPriceOption extends DataObject {

	protected static $currency_symbol = "$";
	static function set_currency_symbol($s) {self::$currency_symbol = $s;}
	static function get_currency_symbol() {return self::$currency_symbol;}

	static $db = array(
		'Price' => 'Currency',
		'ShowInFrom' => 'Boolean',
		'ShowInUpTo' => 'Boolean',
		'DefaultFrom' => 'Boolean',
		'DefaultUpTo' => 'Boolean'
	);

	static $casting = array(
		'PriceNice' => 'HTMLText',
		'PriceInt' => 'Int'
	);

	static $summaryfields = array(
		'Price' => 'Price',
		'ShowInFrom' => 'Boolean',
		'ShowInUpTo' => 'Boolean',
		'DefaultFrom' => 'Boolean',
		'DefaultUpTo' => 'Boolean'
	);

	static $sort = "Price ASC";

	static $singular_name = 'Price Option';
		function i18n_singular_name() {return _t('MerchantPriceOption.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Price Options';
		function i18n_plural_name() {return _t('MerchantPriceOption.PLURALNAME', self::$plural_name);}

	function getPriceNice(){
		return self::get_currency_symbol().round($this->Price, 2);
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
		return "/";
	}

}
