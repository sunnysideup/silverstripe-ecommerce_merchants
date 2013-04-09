<?php

class Category extends DataObject {

	static $db = array(
		'Name' => 'Varchar',
		'Featured' => 'Boolean',
		'Sort' => "Int"
	);

	static $has_one = array(
		'TinyImage' => 'Image',
		'SmallImage' => 'Image',
		'LargeImage' => 'Image'
	);

	static $belongs_many_many = array(
		'Products' => 'MerchantProduct'
	);

	static $indexes = array(
		'Sort' => true
	);

	static $casting = array(
		'Code' => 'Varchar'
	);

	static $field_labels = array(
		'Featured' => 'Is featured ?',
		'Sort' => 'Sort Number (lower numbers first)'
	);

	static $default_sort = "\"Sort\" ASC";

	static $singular_name = 'Category';
		function i18n_singular_name() {return _t('Category.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Categories';
		function i18n_plural_name() {return _t('Category.PLURALNAME', self::$plural_name);}


	/**
	 * keeps the categories for one city
	 * @var Array
	 */
	private static $categories_for_city_cache = array();

	/**
	 * Returns ALL the categories for one city
	 * @param Int | City $city
	 * @return DataObjectSet | Null
	 */
	public static function categories_for_city($city) {
		$resultArray = array();
		if($city instanceOf City) {
			$cityID = $city->ID;
		}
		if(is_numeric($city)) {
			$cityID = $city;
		}
		if(!isset(self::$categories_for_city_cache[$cityID])) {
			self::$categories_for_city_cache[$cityID] = null;
			//Q1. what merchant locations are in this city?
			if(intval($cityID) == 0) {
				$merchantLocations = DataObject::get("MerchantLocation", MerchantLocation::get_active_filter($checkMerchant = true));
			}
			else {
				$merchantLocations = DataObject::get("MerchantLocation", "CityID =".$cityID." AND ( ".MerchantLocation::get_active_filter($checkMerchant = true)." )");
			}
			if($merchantLocations) {
				foreach($merchantLocations as $merchantLocation) {
					//Q2. what categories are applicable for this merchant location?
					$categories = $merchantLocation->Categories();
					if($categories) {
						foreach($categories as $category) {
							$resultArray[$category->ID] = $category->ID;
						}
					}
				}
			}
			if(is_array($resultArray) && count($resultArray)) {
				self::$categories_for_city_cache[$cityID] = DataObject::get("Category", "\"Category\".\"ID\"  IN (".implode(",", $resultArray).")");
			}
		}
		return self::$categories_for_city_cache[$cityID];
	}

	function Link() {
		$page = DataObject::get_one('AllMerchantsPage');
		if($page) {
			return $page->CategoryLink($this->ID, true);
		}
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Images', array(
			new ImageField('LargeImage', _t('Category.LARGEIMAGE', 'Large icon'), null, null, null, 'Categories'),
			new ImageField('SmallImage', _t('Category.SMALLIMAGE', 'Small icon (label)'), null, null, null, 'Categories'),
			new ImageField('TinyImage', _t('Category.TINYIMAGE', 'Tiny icon (for use in forms)'), null, null, null, 'Categories')
		));
		return $fields;
	}

	function getFilterFormHTMLTitle() {
		$image = $this->SmallImage();
		if($image && $image->exists()) {
			return '<img src="' . $image->Filename . '" alt="' . $this->Name . '" /> ' . $this->Title;
		}
		else {
			return $this->Title;
		}
	}

	function getCode(){
		return preg_replace("/[^a-zA-Z0-9\s]/", "", $this->Name);
	}



}
