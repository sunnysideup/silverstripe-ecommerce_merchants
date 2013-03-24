<?php

class MerchantPage extends ProductGroup {

	/****************************************
	 * Model Setup
	 ****************************************/

	public static $icon = "ecommerce_merchants/images/MerchantPage";

	static $db = array(
		'Website' => 'Varchar(255)',
	);

	static $defaults = array(
		'CanEditType' => 'OnlyTheseUsers'
	);

	static $default_parent = 'AllMerchantsPage';

	static $allowed_children = array('MerchantLocation', 'MerchantProduct');

	static $default_child = 'MerchantProduct';

	static $hide_ancestor = 'ProductGroup';

	static $can_be_root = false;

	static $singular_name = 'Merchant Page';
		function i18n_singular_name() {return _t('MerchantPage.SINGULARNAME', self::$singular_name);}

	static $plural_name = 'Merchant Pages';
		function i18n_plural_name() {return _t('MerchantPage.PLURALNAME', self::$plural_name);}

	function canEdit($member = null) {
		return $this->canFrontEndEdit($member);
	}

	protected static $active_filter = 'ShowInSearch = 1';

	public static function get_active_filter($checkMerchant = true) {
		$filter = self::$active_filter;
		if($checkMerchant) {
			$merchantID = intval(Cookie::get(Page_Controller::get_merchant_param()));
			if($merchantID) {
				$table = "MerchantPage";
				if(Versioned::current_stage() == "Live") {
					$table .= "_Live";
				}
				$filter .= " AND ".$table.".ID = ".($merchantID-0);
			}
		}
		return $filter;
	}

	/****************************************
	 * CRUD Forms
	 ****************************************/

	function getCMSFields() {
		$fields = parent::getCMSFields();
		foreach(array('Images', 'ProductDisplay', 'OtherProductsShown') as $name) {
			$fields->removeByName($name);
		}
		$fields->addFieldToTab('Root.Content.Main', new TextField('Website'), 'Content');
		if($this->ID) {
			$fields->addFieldToTab('Root.Content.Main', new LiteralField('OnlyShowLink', "<p class=\"message good\"><a href=\"{$this->OnlyShowLink()}\" target=\"_blank\">Only show link</a></p>"), 'Content');
		}
		$fields->replaceField('Content', new TextareaField('Content', _t('MerchantPage.CONTENT', 'Content')));
		$fields->addFieldToTab('Root.Content.Logo', new ImageField('Image', _t('MerchantPage.LOGO', 'Logo'), null, null, null, 'Logos'));
		return $fields;
	}

	static function get_image_extensions() {
		return array('jpg', 'gif', 'png', 'jpeg');
	}

	/****************************************
	 * Controller like methods
	 ****************************************/

	function Locations() {
		return DataObject::get('MerchantLocation', "\"ParentID\" = $this->ID AND " . MerchantLocation::get_active_filter(false));
	}

	function LocationsIncludingHiddenOnes() {
		return DataObject::get('MerchantLocation', "\"ParentID\" = $this->ID", "\"ShowInMenus\" DESC, \"Sort\" ASC");
	}

	function ProductsIncludingHiddenOnes() {
		return DataObject::get('MerchantProduct', "\"ParentID\" = $this->ID", "\"AllowPurchase\" DESC, \"Sort\" ASC");
	}

	function Products() {
		$products = DataObject::get('MerchantProduct', "\"ParentID\" = $this->ID");
		if($products) {
			foreach($products as $product) {
				if($product->canPurchase()) {
					$result[] = $product;
				}
			}
			if(isset($result)) {
				return new DataObjectSet($result);
			}
		}
	}

	function AddLocationLink() {
		return $this->Link('addlocation');
	}

	function AddProductLink() {
		return $this->Link('addproduct');
	}

	function SortProductsLink() {
		if(class_exists("DataObjectSorterController")) {
			return DataObjectSorterController::popup_link(
				$className = "MerchantProduct",
				$filterField = "ParentID",
				$filterValue = $this->ID,
				$linkText = _t("MerchantProduct.SORT_PRODUCT_LIST", "Producten sorteren"),
				$titleField = "Title"
			);
		}
	}

	/**
	 * This is used to have a set Merchant Group Code
	 * @return String
	 */
	protected function MerchantGroupCode(){
		if($this->exists()) {
			//NOTE: THIS MUST BE A HYPHEN!
			return "MerchantGroupCode-".$this->ID;
		}
	}

	/****************************************
	 * Read and Write
	 ****************************************/

	function onBeforeWrite() {
		parent::onBeforeWrite();
		$mainGroup = MerchantGroupDOD::get_main_group();
		if($mainGroup && $this->MerchantGroupCode()) {
			$merchantGroup = DataObject::get_one("Group", "\"Code\" = '".$this->MerchantGroupCode()."'", false);
			if(!$merchantGroup) {
				$merchantGroup = new Group();
				$merchantGroup->Code = $this->MerchantGroupCode();
				$merchantGroup->ParentID = $mainGroup->ID;
			}
			$merchantGroup->Title = $this->Title;
			$merchantGroup->write();
			//adding permissions
			$this->CanEditType = 'OnlyTheseUsers';
			$existingEditors = $this->EditorGroups();
			$existingEditors->add($merchantGroup);
		}
		$this->MetaTitle = $this->Title;
		$this->MetaDescription = strip_tags($this->Content);
	}

	//make sure that it is saved two times, the first time, just to be sure
	//that the editor groups are added.
	function onAfterWrite() {
		parent::onAfterWrite();
		$mainGroup = MerchantGroupDOD::get_main_group();
		if($mainGroup && $this->MerchantGroupCode()) {
			if($this->CanEditType != 'OnlyTheseUsers') {
				$this->writeToStage('Stage');
				$this->Publish('Stage', 'Live');
			}
		}
	}

	function OnlyShowLink() {
		if(is_a($this, 'MerchantPage')) {
			$page = DataObject::get_one('HomePage');
			return $page->Link("onlyshow/{$this->URLSegment}");
		}
	}

}

class MerchantPage_Controller extends ProductGroup_Controller {

	function init() {
		parent::init();
		if(! $this->canFrontEndEdit()) {
			foreach(array('Locations', 'Products') as $function) {
				$pages = $this->$function();
				if($pages) {
					$page = $pages->First();
					return Director::redirect($page->Link());
				}
			}
			$parent = $this->Parent();
			if($parent && $parent->exists()) {
				return Director::redirect($parent->Link());
			}
			return Director::redirect('/');
		}
	}

	/****************************************
	 * Forms: Member Form
	 ****************************************/

	function MemberForm() {
		$member = Member::currentUser();
		list($fields, $requiredFields) = MerchantAdminDOD::get_edit_fields();
		$actions = new FieldSet(
			new FormAction('savememberform', _t('MerchantPage_Controller.SAVE_PERSONAL_DETAILS', 'Save My Personal Details'))
		);
		$form = new Form($this, 'MemberForm', $fields, $actions, $requiredFields);
		$form->loadDataFrom($member);
		if($member && $member->Password ){
			if(!isset($_REQUEST["Password"])) {
				$fields->fieldByName("Password")->SetValue("");
			}
			$fields->fieldByName("Password")->setCanBeEmpty(true);
		}
		return $form;
	}

	function savememberform($data, $form) {
		$member = Member::currentUser();
		if($member) {
			try {
				$form->saveInto($member);
				$member->write();
				$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EDIT_SUCCESS', 'Your personal details have been saved successfully.'), 'good');
			} catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EMAIL_ERROR', 'Your personal details could not be saved because the email is already been used.'), 'bad');
			}
		}
		return Director::redirectBack();
	}

	/****************************************
	 * Forms: Merchant Page
	 ****************************************/

	function MerchantPageForm() {
		$fields = new FieldSet(
			new TextField('Website', _t('MerchantPage.WEBSITE', 'Website')),
			new TextareaField('Content', _t('MerchantPage.CONTENT', 'Content')),
			$imageField = new SimpleImageField('Image', _t('MerchantPage.LOGO', 'Logo'), null, null, null, 'Logos')
		);
		/*$validator = $imageField->getValidator();
		$validator->setAllowedExtensions(MerchantPage::get_image_extensions());
		$imageField->setValidator($validator);*/
		$requiredFields = new RequiredFields('Website', 'Description');
		$actions = new FieldSet(
			new FormAction('savemerchantpageform', _t('MerchantPage_Controller.SAVE_STORE_DETAILS', 'Save My Store Details'))
		);
		$form = new Form($this, 'MerchantPageForm', $fields, $actions, $requiredFields);
		$form->loadDataFrom($this);
		return $form;
	}

	function savemerchantpageform($data, $form) {
		if($this->canFrontEndEdit()) {
			try {
				$form->saveInto($this->dataRecord); // Call on dataRecord to fix SimpleImageField issue
				$this->writeToStage('Stage');
				$this->Publish('Stage', 'Live');
				$form->sessionMessage(_t('MerchantPage_Controller.SAVE_STORE_DETAILS_SUCCESS', 'Your store details have been saved successfully.'), 'good');
			} catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantPage_Controller.SAVE_STORE_DETAILS_ERROR', 'Your store details could not be saved.'), 'bad');
			}
		}
		return Director::redirectBack();
	}

	/****************************************
	 * Forms: Location
	 ****************************************/

	function AddLocationForm() {
		$singleton = Object::create('MerchantLocation');
		list($fields, $requiredFields) = $singleton->getFrontEndFields();
		$actions = new FieldSet(
			new FormAction('saveaddlocationform', _t('MerchantPage_Controller.ADD_NEW_STORE', 'Add New Store'))
		);
		return new Form($this, 'AddLocationForm', $fields, $actions, $requiredFields);
	}

	function saveaddlocationform($data, $form) {
		if($this->canFrontEndEdit()) {
			try {
				$location = Object::create('MerchantLocation');
				$form->saveInto($location);
				$location->MenuTitle = $location->Title; // Copy of the title on the menu title
				$location->ParentID = $this->ID;
				$location->writeToStage('Stage');
				$location->Publish('Stage', 'Live');
				return Director::redirect($location->EditLink());
			} catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantLocation_Controller.SAVE_STORE_DETAILS_ERROR', 'Your store details could not be saved.'), 'bad');
			}
		}
		return Director::redirectBack();
	}

	/****************************************
	 * Forms: Add Product
	 ****************************************/

	function AddProductForm() {
		$singleton = Object::create('MerchantProduct');
		list($fields, $requiredFields) = $singleton->getFrontEndFields($this);
		$actions = new FieldSet(new FormAction('saveaddproductform', _t('MerchantPage_Controller.ADD_NEW_PRODUCT', 'Add New Product')));
		return new Form($this, 'AddProductForm', $fields, $actions, $requiredFields);
	}

	function saveaddproductform($data, $form) {
		if($this->canFrontEndEdit()) {
			try {
				$product = Object::create('MerchantProduct');
				$form->saveInto($product);
				$product->MenuTitle = $product->Title; // Copy of the title on the menu title
				$product->ParentID = $this->ID;
				$product->writeToStage('Stage');
				$product->Publish('Stage', 'Live');

				// Second call to save categories and locations

				$form->saveInto($product);
				$product->writeToStage('Stage');
				$product->Publish('Stage', 'Live');
				return Director::redirect($product->EditLink());
			}
			catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantProduct_Controller.SAVE_PRODUCT_DETAILS_ERROR', 'Your product details could not be saved.'), 'bad');
			}
		}
		return Director::redirectBack();
	}
}
