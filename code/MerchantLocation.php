<?php

class MerchantLocation extends ProductGroup {


	/**
	 * icons for the page
	 * @var String
	 */
	public static $icon = "ecommerce_merchants/images/MerchantLocation";

	static $db = array(
		'Address' => 'Varchar(255)',
		'Address2' => 'Varchar(255)', // used as suburb / city (e.g. Brooklynn), used for locating on map.
		'PostalCode' => 'Varchar(30)',
		'Phone' => 'Varchar(50)',
		'OpeningHours' => 'Text',
		'Featured' => 'Boolean',
	);

	static $has_one = array(
		'City' => 'City', //main city for searching purposes, could be used as a region. - e.g. New York
		'AdditionalImage1' => 'Image',
		'AdditionalImage2' => 'Image',
		'AdditionalImage3' => 'Image',
		'AdditionalImage4' => 'Image'
	);

	static $active_filter = 'ShowInSearch = 1';

	static $default_parent = 'MerchantPage';

	static $allowed_children = 'none';

	static $hide_ancestor = 'ProductGroup';

	static $can_be_root = false;

	static $singular_name = 'Merchant Location';
	function i18n_singular_name() {return _t('MerchantLocation.SINGULARNAME', self::$singular_name);}

	static $plural_name = 'Merchant Locations';
	function i18n_plural_name() {return _t('MerchantLocation.PLURALNAME', self::$plural_name);}

	function canEdit($member = null) {
		return $this->canFrontEndEdit($member);
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		foreach(array('ProductDisplay', 'OtherProductsShown') as $name) {
			$fields->removeByName($name);
		}
		$fields->removeFieldFromTab('Root.Content.Main', 'Content');
		$cities = DataObject::get('City');
		$cities = $cities->map('ID', 'Name');
		$fields->addFieldsToTab('Root.Content.Images', array(
			new ImageField("AdditionalImage1"),
			new ImageField("AdditionalImage2"),
			new ImageField("AdditionalImage3"),
			new ImageField("AdditionalImage4")
		));
		$fields->addFieldsToTab('Root.Content.Main', array(
			new CheckboxField('Featured', 'Is featured ?'),
			new TextField('Address'),
			new TextField('PostalCode', 'Postal Code'),
			new TextField('Address2', 'Plaats'),
			new DropdownField('CityID', 'City', $cities, '', null, ''),
			new TextField('Phone'),
			new TextareaField('OpeningHours', 'Opening Hours')
		));
		if($this->ID) {
			$products = DataObject::get('MerchantProduct', "ParentID = $this->ParentID");
			if($products) {
				$fields->addFieldToTab('Root.Content.Products', new CheckboxSetField('AlsoShowProducts', '', $products));
			}
		}
		return $fields;
	}

	function Image() {
		$parent = DataObject::get_by_id('MerchantPage', $this->ParentID);
		if($parent && $parent->exists()) {
			return $parent->Image();
		}
	}

	function currentInitialProducts() {
		$products = $this->AlsoShowProducts();
		foreach($products as $product) {
			if($product->canPurchase()) {
				$result[] = $product;
			}
		}
		if(isset($result)) {
			return new DataObjectSet($result);
		}
	}

	function EditLink() {
		return $this->Link('edit');
	}

	function onBeforeWrite() {
		//add URLSegment first so that parent (SiteTree)
		//has a change to adjust the URLSegment
		//if needed.
		$this->URLSegment = strtolower($this->Address2);
		parent::onBeforeWrite();
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		$parent = $this->Parent();
		$filter = '';
		if($parent->exists() && is_a($parent, self::$default_parent)) {
			$products = DataObject::get('MerchantProduct', "ParentID = $this->ParentID");
			if($products) {
				$products = implode(',', $products->map('ID', 'ID'));
				$filter = " AND ProductID NOT IN ($products)";
			}
		}
		DB::query("DELETE FROM Product_ProductGroups WHERE ProductGroupID = $this->ID$filter");
	}

	function getFrontEndFields() {
		$cities = DataObject::get('City');
		$cities = $cities->map('ID', 'Name');
		$fields = new FieldSet(
			new TextField('Title', $this->fieldLabel('Title')),
			new CheckboxField('Featured', _t('MerchantLocation.IS_FEATURED', 'Is featured ?')),
			new TextField('Address', _t('OrderAddress.ADDRESS', 'Address')),
			new TextField('Address2', ''),
			new TextField('PostalCode', _t('OrderAddress.POSTALCODE', 'Postal Code')),
			new DropdownField('CityID', _t('City.SINGULARNAME', 'City'), $cities, '', null, ''),
			new TextField('Phone', _t('OrderAddress.PHONE', 'Phone')),
			new TextareaField('OpeningHours', _t('MerchantLocation.OPENING_HOURS', 'Opening Hours')),
			//new HeaderField('ImageHeader', _t('MerchantLocation.IMAGES', 'Afbeeldingen')),
			new HeaderField('Images', _t('MerchantLocation.IMAGES', 'Images')),
			new SimpleImageField('AdditionalImage1', _t('MerchantLocation.IMAGE', 'Afbeelding')." 1"),
			new SimpleImageField('AdditionalImage2', _t('MerchantLocation.IMAGE', 'Afbeelding')." 2"),
			new SimpleImageField('AdditionalImage3', _t('MerchantLocation.IMAGE', 'Afbeelding')." 3"),
			new SimpleImageField('AdditionalImage4', _t('MerchantLocation.IMAGE', 'Afbeelding')." 4")
		);
		$requiredFields = new RequiredFields('Title', 'Address', 'CityID');
		return array($fields, $requiredFields);
	}

	function Map($width = 400, $height = 260, $zoom = 15){
		return $this->getMap($width, $height, $zoom);
	}

	function getMap($width = 400, $height = 260, $zoom = 15) {
		$city = $this->City();
		if($city && $city->exists()) {
			$address = array($this->Address, $this->Address2, $this->PostalCode, Geoip::countryCode2name($city->Country));
			$address = urlencode(implode(' ', $address));

			// 1) Image

			$imageLink = 'http://maps.googleapis.com/maps/api/staticmap?';
			$params = array(
				'center' => $address,
				'zoom' => $zoom,
				'size' => "{$width}x{$height}",
				'maptype' => 'roadmap',
				'markers' => "color:red|$address",
				'sensor' => 'false'
			);
			$imageLink .= http_build_query($params);

			// 2) Google Map

			$gmapLink = 'http://maps.google.com/maps?';
			$params = array(
				'q' => $address,
				'z' => $zoom
			);
			$gmapLink .= http_build_query($params);

			return new ArrayData(array('ImageLink' => $imageLink, 'GMapLink' => $gmapLink));
		}
		return array();
	}

	private static $categories_cache = array();
	/**
	 * returns the categories for all the products sold in the location
	 * @return DataObjectSet | Null
	 */
	function Categories(){
		if(!isset(self::$categories_cache[$this->ID])) {
			$array = array();
			$products = $this->currentInitialProducts();
			if($products) {
				foreach($products as $product) {
					$categories = $product->Categories();
					if($categories && $categories->count()) {
						foreach($categories as $category) {
							$array[$category->ID] = $category->ID;
						}
					}
				}
			}
			self::$categories_cache[$this->ID] = $array;
		}
		if(count(self::$categories_cache[$this->ID])) {
			return DataObject::get("Category", "\"Category\".\"ID\" IN (".implode(",", self::$categories_cache[$this->ID]).")");
		}
		return null;
	}

	/***
	 * Returns a list of all the merchant cities of this merchant
	 * @return Null | DataObjectSet
	 */
	function AllMerchantCities() {
		$parent = $this->Parent();
		$locations = $parent->Locations();
		if($locations) {
			$cities = $locations->map('CityID', 'City');
			return new DataObjectSet(array_values($cities));
		}
	}

	/***
	 * Returns a list of all the merchant address2 entries
	 * @return Null | DataObjectSet
	 */
	function AllMerchantAddress2() {
		$parent = $this->Parent();
		$locations = $parent->Locations();
		if($locations) {
			$address2 = $locations->map('Address2', 'Address2');
			if($address2) {
				$dos = new DataObjectSet();
				foreach($address2 as $value) {
					if($value) {
						$dos->push(new ArrayData(array("Name" => $value)));
					}
				}
			}
		}
	}


	/**
	 * Returns the class we are working with
	 * @return String
	 */
	protected function getClassNameSQL(){
		return "Product";
	}

	/**
	 * Do products occur in more than one group
	 * @return Boolean
	 */
	protected function getProductsAlsoInOtherGroups(){
		return true;
	}

	/**
	 * returns the filter SQL, based on the $_GET or default entry.
	 * The standard filter excludes the product group filter.
	 * The default would be something like "ShowInSearch = 1"
	 * @return String
	 */
	protected function getStandardFilter(){
		if(isset($_GET['filterfor'])) {
			$filterKey = Convert::raw2sqL($_GET['filterfor']);
		}
		else {
			$filterKey = $this->MyDefaultFilter();
		}
		$filter = $this->getFilterOptionSQL($filterKey);
		return $filter;
	}

	/**
	 * works out the group filter baswed on the LevelOfProductsToShow value
	 * it also considers the other group many-many relationship
	 * this filter ALWAYS returns something: 1 = 1 if nothing else.
	 * @return String
	 */
	protected function getGroupFilter(){
		return "ParentID = ".$this->ParentID;
	}

	/**
	 * If products are show in more than one group
	 * Then this returns a where phrase for any products that are linked to this
	 * product group
	 *
	 * @return String
	 */
	protected function getProductsToBeIncludedFromOtherGroups() {
		//TO DO: this should actually return
		//Product.ID = IN ARRAY(bla bla)
		$array = array();
		if($this->getProductsAlsoInOtherGroups()) {
			$array = $this->AlsoShowProducts()->map("ID", "ID");
		}
		if(count($array)) {
			$stage = '';
			//@to do - make sure products are versioned!
			if(Versioned::current_stage() == "Live") {
				$stage = "_Live";
			}
			return " OR (\"Product$stage\".\"ID\" IN (".implode(",", $array).")) ";
		}
		return "";
	}


	/**
	 * returns the CLASSNAME part of the final selection of products.
	 * @return String
	 */
	protected function currentClassNameSQL() {
		return "Product";
	}

	/**
	 * returns the SORT part of the final selection of products.
	 * @return String
	 */
	protected function currentSortSQL() {
		return "\"Sort\" ASC";
	}


	/**
	 *@return Integer
	 **/
	function ProductsPerPage() {return $this->MyNumberOfProductsPerPage();}
	function MyNumberOfProductsPerPage() {
		return 1000;
	}

	function AllImages(){
		$dos = new DataObjectSet();
		$images = array();
		$images[0] = $this->BestAvailableImage();
		$images[1] = $this->AdditionalImage1();
		$images[2] = $this->AdditionalImage2();
		$images[3] = $this->AdditionalImage3();
		$images[4] = $this->AdditionalImage4();
		foreach($images as $key => $image) {
			if($image && $image->exists() && file_exists($image->getFullPath())) {
				$dos->push($image);
			}
			else {
				unset($images[$key]);
			}
		}
		if(count($images)) {
			return $dos;
		}
		return null;
	}

}

class MerchantLocation_Controller extends ProductGroup_Controller {

	function edit() {
		if(! $this->canFrontEndEdit()) {
			return Director::redirect($this->Link());
		}
		return array();
	}

	function EditForm() {
		list($fields, $requiredFields) = $this->getFrontEndFields();
		$actions = new FieldSet(new FormAction('saveEditForm', _t('MerchantAdminAccountPage_Controller.SAVE_DETAILS', 'Save Details')), new FormAction('disableLocation', _t('ModelAdmin.DELETE', 'Delete')));
		$form = new Form($this, 'EditForm', $fields, $actions, $requiredFields);
		$form->loadDataFrom($this);
		return $form;
	}

	function saveEditForm($data, $form) {
		if($this->canFrontEndEdit()) {
			try {
				$form->saveInto($this->dataRecord);
				$this->MenuTitle = $this->Title; // Copy of the title on the menu title
				$this->dataRecord->URLSegment = null; // To reset the value of the URLSegment in the onBeforeWrite of SiteTree
				$this->writeToStage('Stage');
				$this->Publish('Stage', 'Live');
				$form->sessionMessage(_t('MerchantLocation_Controller.EDIT_SUCCESS', 'Your store details have been saved successfully.'), 'good');
			} catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantLocation_Controller.SAVE_STORE_DETAILS_ERROR', 'Your store details could not be saved.'), 'bad');
			}
		}
		return Director::redirect($this->EditLink()); // Not redirectBack because the URLSegment might have changed
	}

	function disableLocation($data, $form) {
		if($this->canFrontEndEdit()) {
			$this->dataRecord->ShowInMenus = $this->dataRecord->ShowInSearch = false;
			$this->writeToStage('Stage');
			$this->Publish('Stage', 'Live');
			$this->dataRecord->extend('onAfterDisable');
		}
		return Director::redirect($this->Parent()->Link());
	}
}
