<?php

class MerchantProduct extends Product {

	/****************************************
	 * Model Setup
	 ****************************************/

	static $many_many = array(
		'Categories' => 'Category'
	);


	protected static $active_filter = 'ShowInSearch = 1 AND AllowPurchase = 1';
	public static function get_active_filter() {
		$filter = self::$active_filter;
		$merchantID = intval(Cookie::get(Page_Controller::get_merchant_param()));
		if($merchantID) {
			$filter .= " AND ParentID = $merchantID";
		}
		return $filter;
	}

	protected static $minimum_sort = 100000;

	static $default_parent = 'MerchantPage';

	static $allowed_children = 'none';

	static $hide_ancestor = 'Product';

	static $can_be_root = false;

	static $singular_name = 'Product';
		function i18n_singular_name() {return _t('MerchantProduct.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Products';
		function i18n_plural_name() {return _t('MerchantProduct.PLURALNAME', self::$plural_name);}

	function canEdit($member = null) {
		return $this->canFrontEndEdit($member);
	}

	/****************************************
	 * CRUD Forms
	 ****************************************/

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('AlsoShowHere');
		$fields->replaceField('Content', new TextareaField('Content', _t('MerchantProduct.CONTENT', 'Content')));
		$categories = DataObject::get('Category');
		$categories = $categories->map('ID', 'Name');
		$fields->addFieldToTab('Root.Content.Main', new CheckboxSetField('Categories', _t('MerchantProduct.CATEGORIES', 'Categories'), $categories));
		if($this->ID) {
			$parent = $this->Parent();
			$locations = $parent->Locations();
			if($locations) {
				$fields->addFieldToTab('Root.Content.Main', new CheckboxSetField('ProductGroups', _t('MerchantProduct.PRODUCTGROUPS', 'Locations'), $locations));
			}
		}
		return $fields;
	}


	function getFrontEndFields(MerchantPage $parent = null) {
		if(! $parent) {
			$parent = DataObject::get_by_id("MerchantPage", $this->ParentID);
		}
		$categories = DataObject::get('Category');
		$categories = $categories->map('ID', 'Name');
		$locations = $parent->Locations();
		$fields = new FieldSet(
			new TextField('Title', _t('MerchantProduct.TITLE', 'Product name')),
			new CheckboxField('AllowPurchase', _t('MerchantProduct.ALLOW_PURCHASE', 'For sale')),
			new TextareaField('Content', _t('MerchantProduct.CONTENT', 'Description')),
			new NumericField('Price', _t('MerchantProduct.PRICE', 'Price')),
			new TextField('InternalItemID', _t('MerchantProduct.CODE', 'Product Code')),
			new CheckboxSetField('Categories', _t('MerchantProduct.CATEGORIES', 'Categories'), $categories),
			new CheckboxSetField('ProductGroups', _t('MerchantProduct.PRODUCTGROUPS', 'Locations'), $locations),
			$imageField = new SimpleImageField('Image', _t('MerchantProduct.IMAGE', 'Image'), null, null, null, 'Products')
		);
		/*$validator = $imageField->getValidator();
		$validator->setAllowedExtensions(MerchantPage::get_image_extensions());
		$imageField->setValidator($validator);*/
		$requiredFields = new RequiredFields('Title', 'Content', 'Price', 'Categories');
		return array($fields, $requiredFields);
	}

	/****************************************
	 * Controller related stuff
	 ****************************************/

	function Locations() {
		return $this->getManyManyComponents('ProductGroups', MerchantLocation::get_active_filter(false));
	}

	function EditLink() {
		return $this->Link('edit');
	}


	/****************************************
	 * reading and writing
	 ****************************************/

	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->MetaTitle = $this->Title;
		$this->MetaDescription = strip_tags($this->Content);
		if($this->Sort < self::$minimum_sort) {
			$this->Sort = $this->Sort + self::$minimum_sort;
		}
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		$parent = $this->Parent();
		$filter = '';
		if($parent->exists() && is_a($parent, self::$default_parent)) {
			$locations = $parent->Locations();
			if($locations) {
				$locations = implode(',', $locations->map('ID', 'ID'));
				$filter = " AND ProductGroupID NOT IN ($locations)";
			}
		}
		DB::query("DELETE FROM Product_ProductGroups WHERE ProductID = $this->ID$filter");
	}

}

class MerchantProduct_Controller extends Product_Controller {

	/****************************************
	 * Actions
	 ****************************************/

	function edit() {
		if(! $this->canFrontEndEdit()) {
			return Director::redirect($this->Link());
		}
		return array();
	}


	/****************************************
	 * Forms
	 ****************************************/

	function EditForm() {
		list($fields, $requiredFields) = $this->getFrontEndFields();
		$actions = new FieldSet(
			new FormAction('saveeditform', _t('MerchantAdminAccountPage_Controller.SAVE_DETAILS', 'Save Details')),
			new FormAction('removeProduct', _t('ModelAdmin.DELETE', 'Delete'))
		);
		$form = new Form($this, 'EditForm', $fields, $actions, $requiredFields);
		$form->loadDataFrom($this);
		return $form;
	}

	function saveeditform($data, $form) {
		if($this->canFrontEndEdit()) {
			try {
				$form->saveInto($this->dataRecord); // Call on dataRecord to fix SimpleImageField issue
				$this->MenuTitle = $this->Title; // Copy of the title on the menu title
				$this->dataRecord->URLSegment = null; // To reset the value of the URLSegment in the onBeforeWrite of SiteTree
				$this->writeToStage('Stage');
				$this->Publish('Stage', 'Live');
				$form->sessionMessage(_t('MerchantProduct_Controller.SAVE_PRODUCT_DETAILS_SUCCESS', 'Your product details have been saved successfully.'), 'good');
			}
			catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantProduct_Controller.SAVE_PRODUCT_DETAILS_ERROR', 'Your product details could not be saved.'), 'bad');
			}
		}
		return Director::redirect($this->EditLink()); // Not redirectBack because the URLSegment might have changed
	}

	function removeproduct($data, $form) {
		if($this->canFrontEndEdit()) {
			$this->dataRecord->AllowPurchase = false;
			$this->writeToStage('Stage');
			$this->Publish('Stage', 'Live');
		}
		return Director::redirect($this->Parent()->Link());
	}

}
