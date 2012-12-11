<?php

class City extends DataObject {

	static $db = array(
		'Name' => 'Varchar(255)',
		'Country' => 'Varchar(2)'
	);

	static $singular_name = 'City';
		function i18n_singular_name() {return _t('City.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Cities';
		function i18n_plural_name() {return _t('City.PLURALNAME', self::$plural_name);}

	function Link() {
		$page = DataObject::get_one('AllMerchantsPage');
		if($page) {
			return $page->CityLink($this);
		}
	}

}
