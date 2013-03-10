<?php

class Category extends DataObject {

	static $db = array(
		'Name' => 'Varchar',
		'Featured' => 'Boolean'
	);

	static $has_one = array(
		'TinyImage' => 'Image',
		'SmallImage' => 'Image',
		'LargeImage' => 'Image'
	);

	static $belongs_many_many = array(
		'Products' => 'MerchantProduct'
	);

	static $casting = array(
		'Code' => 'Varchar'
	);

	static $field_labels = array(
		'Featured' => 'Is featured ?'
	);

	static $singular_name = 'Category';
		function i18n_singular_name() {return _t('Category.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Categories';
		function i18n_plural_name() {return _t('Category.PLURALNAME', self::$plural_name);}

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
