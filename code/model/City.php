<?php

class City extends DataObject {

	static $db = array(
		'Name' => 'Varchar(255)',
		'Country' => 'Varchar(2)'
	);

	static $singular_name = 'City';
	static $plural_name = 'Cities';

	function Link() {
		$page = DataObject::get_one('AllMerchantsPage');
		if($page) {
			return $page->CityLink($this);
		}
	}

}
