<?php

class AllMerchantsPage extends ProductGroup {

	/****************************************
	 * MODEL DEFINITION
	 ****************************************/

	public static $icon = "ecommerce_merchants/images/AllMerchantsPage";

	/**
	 * can create only one
	 * @param Member $member
	 * @return Boolean
	 */
	function canCreate($member = null) {
		return ! DataObject::get_one($this->class);
	}

	/**
	 * SS standard variable
	 * @var Array
	 */
	static $allowed_children = array('MerchantPage');

	/**
	 * SS standard variable
	 * @var String
	 */
	static $default_child = 'MerchantPage';

	/**
	 * SS standard variable
	 * @var String
	 */
	static $hide_ancestor = 'ProductGroup';

	/**
	 * SS standard variable
	 * @var String
	 */
	static $singular_name = 'All Merchants Page';
		function i18n_singular_name() {return _t('AllMerchantsPage.SINGULARNAME', self::$singular_name);}

	/**
	 * SS standard variable
	 * @var String
	 */
	public static $plural_name = 'All Merchants Pages';
		function i18n_plural_name() {return _t('AllMerchantsPage.PLURALNAME', self::$plural_name);}


	/****************************************
	 * CRUD FORMS
	 ****************************************/

	/**
	 * SS standard method
	 * @return FieldSet
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		foreach(array('Images', 'ProductDisplay', 'OtherProductsShown') as $name) {
			$fields->removeByName($name);
		}
		$fields->addFieldToTab(
			"Root.Content.ProductsPerLoad",
			new NumericField("NumberOfProductsPerPage", _t("ProductGroup.PRODUCTSPERPAGE", "Number of products per page"))
		);
		return $fields;
	}

	/****************************************
	 * CONTROLLER LIKE METHODS
	 ****************************************/

	/**
	 * Link to a category Filter
	 * @param Int $category
	 * @param Boolean $fullLink
	 * @return String (HTML)
	 */
	function CategoryLink($categoryID, $fullLink = false) {
		$link = "";
		if($fullLink) {
			$link .= "{$this->Link()}/?";
		}
		if($categoryID) {
			$link .= "&amp;".AllMerchantsPage_Controller::get_category_param()."=".$categoryID;
		}
		return $link;
	}


	/**
	 * Link to a MerchantPage Filter
	 * @param Int $merchantPageID
	 * @param Boolean $fullLink
	 * @return String (HTML)
	 */
	function MerchantPageLink($merchantPageID, $fullLink = false) {
		$link = "";
		if($fullLink) {
			$link .= "{$this->Link()}/?";
		}
		if($merchantPageID) {
			$link .= "&amp;".AllMerchantsPage_Controller::get_merchant_page_param()."=".$merchantPageID;
		}
		return $link;
	}


	/**
	 * Link to a City Filter
	 * @param Int $cityID
	 * @param Boolean $fullLink
	 * @return String (HTML)
	 */
	function CityLink($cityID, $fullLink = false) {
		$link = "";
		if($fullLink) {
			$link .= "{$this->Link()}/?";
		}
		if($cityID) {
			$link .= "&amp;".AllMerchantsPage_Controller::get_city_param()."=".$cityID;
		}
		return $link;
	}


	/**
	 * Link to a Price from filter
	 * @param Int $priceFrom
	 * @param Boolean $fullLink
	 * @return String (HTML)
	 */
	function PriceFromLink($priceFrom, $fullLink = false) {
		$link = "";
		if($fullLink) {
			$link .= "{$this->Link()}/?";
		}
		if($priceFrom) {
			$link .= "&amp;".AllMerchantsPage_Controller::get_price_from_param()."=".$priceFrom;
		}
		return $link;
	}

	/**
	 * Link to a price up to filter
	 * @param Int $priceUpTo
	 * @param Boolean $fullLink
	 * @return String (HTML)
	 */
	function PriceUpToLink($priceUpTo, $fullLink = false) {
		$link = "";
		if($fullLink) {
			$link .= "{$this->Link()}/?";
		}
		if($priceUpTo) {
			$link .= "&amp;".AllMerchantsPage_Controller::get_price_upto_param()."=".$priceUpTo;
		}
		return $link;
	}


}

class AllMerchantsPage_Controller extends ProductGroup_Controller {

	protected static $merchant_product_session_array_name = 'merchantproductsessionarray';
		public static function get_merchant_product_session_array_name(){return self::$merchant_product_session_array_name;}
		public static function set_merchant_product_session_array_name($s){self::$merchant_product_session_array_name = $s;}

	protected static $ppp_param = 'ppp';
		public static function get_ppp_param(){return self::$ppp_param; }

	protected static $pos_param = 'pos';
		public static function get_pos_param(){return self::$pos_param; }

	protected static $category_param = 'category';
		public static function get_category_param(){return self::$category_param; }

	protected static $merchant_param = 'merchant';
		public static function get_merchant_page_param(){return self::$merchant_param; }

	protected static $city_param = 'city';
		public static function get_city_param(){return self::$city_param; }

	protected static $price_from_param = 'pricefrom';
		public static function get_price_from_param(){return self::$price_from_param; }

	protected static $price_upto_param = 'priceupto';
		public static function get_price_upto_param(){return self::$price_upto_param; }

	/**
	 *
	 * @var Boolean
	 */
	protected $mydebug = false;

	/**
	 *
	 * @var Array
	 */
	protected $productArray = array();

	/**
	 *
	 * @var Int
	 */
	protected $productCount = 0;

	/**
	 *
	 * @var Int
	 */
	protected $productsPerPage = 4;

	/**
	 *
	 * @var Int
	 */
	protected $productOffSet = 0;

	/**
	 *
	 * @var Int
	 */
	protected $categoryID = 0;

	/**
	 *
	 * @var Int
	 */
	protected $merchantPageID = 0;

	/**
	 *
	 * @var Int
	 */
	protected $cityID = 0;

	/**
	 *
	 * @var Int
	 */
	protected $priceFrom = 0;

	/**
	 *
	 * @var Int
	 */
	protected $priceUpTo = 0;

	function init() {
		parent::init();

		//  =======================
		//CURRENT SETTINGS
		//  =======================

		//ppp
		if(isset($_REQUEST[self::get_ppp_param()]) && $_REQUEST[self::get_ppp_param()]) {
			$this->productsPerPage = intval($_REQUEST[self::get_ppp_param()]);
		}
		else {
			$this->productsPerPage = $this->NumberOfProductsPerPage;
		}

		if(isset($_REQUEST["mydebug"])) {
			$this->mydebug = true;
		}

		//pos
		if(isset($_REQUEST[self::get_pos_param()])) {
			$this->productOffSet = intval($_REQUEST[self::get_pos_param()]);
		}
		else {
			$this->productOffSet = 0;
		}

		//category
		if(isset($_REQUEST[self::get_category_param()])) {
			$this->categoryID = intval($_REQUEST[self::get_category_param()]);
		}
		else {
			$this->categoryID = 0;
		}
		//merchant page
		if(isset($_REQUEST[self::get_merchant_page_param()])) {
			$this->merchantPageID = intval($_REQUEST[self::get_merchant_page_param()]);
		}
		else {
			$this->merchantPageID = 0;
		}
		//city
		if(isset($_REQUEST[self::get_city_param()])) {
			$this->cityID = intval($_REQUEST[self::get_city_param()]);
		}
		else {
			$this->cityID = 0;
		}
		//priceFrom
		if(isset($_REQUEST[self::get_price_from_param()])) {
			$this->priceFrom = intval($_REQUEST[self::get_price_from_param()]);
		}
		else {
			$this->priceFrom = 0;
		}
		//priceUpTo
		if(isset($_REQUEST[self::get_price_upto_param()])) {
			$this->priceUpTo = intval($_REQUEST[self::get_price_upto_param()]);
		}
		else {
			$this->priceUpTo = 0;
		}
		Requirements::javascript('ecommerce_merchants/javascript/filter.js');

	}

	/****************************************
	 * Actions
	 ****************************************/

	/**
	 * AJAX Controller to show products
	 * @return String (HTML)
	 */
	function moreproducts(){
		if(Director::is_ajax()) {
			$productArrayAsString = Session::get(self::get_merchant_product_session_array_name());
			$productArray = explode(",", $productArrayAsString);
			$this->productCount = count($productArray);
			$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($productArray, $this->productOffSet(), $this->productsPerPage(), "MerchantProduct".$this->stageAppendix());
			self::$products_cache = DataObject::get(
				'MerchantProduct',
				$sortbyAndFilterIDMakerArray["Filter"],
				$sortbyAndFilterIDMakerArray["Sort"]
			);
			$variablesForTemplateArray = $this->variablesForTemplate();
			$variablesForTemplateArray["Products"] = self::$products_cache;
			return $this->customise($variablesForTemplateArray)->renderWith("ProductsHolder");
		}
		else {
			//$this->redirect($this->Link()."?".str_replace('&amp;', '&', $this->filterGetVariables()));
			return Array();
		}
	}

	function index(){
		Session::set(self::get_merchant_product_session_array_name(), null);
		Session::clear(self::get_merchant_product_session_array_name());
		Session::save();
		return array();
	}

	/****************************************
	 * FORMS
	 ****************************************/

	/**
	 * returns filter form for filtering products on page
	 * @return Form
	 */
	function FilterForm() {
		//  =======================
		//  DROPDOWN
		//  =======================
		//city
		$cities = City::cities_for_category_and_merchant_page($this->categoryID, $this->merchantPageID);
		if($cities) {
			$cities = $cities->map();
		}
		else {
			$cities = array();
		}
		$cities = array( 0 => _t("Merchants.ALL_CITIES", "-- All Cities")) + $cities;
		//category
		$categories = Category::categories_for_city_and_merchant_page($this->cityID, $this->merchantPageID);
		if($categories) {
			$categories = $categories->map('ID', 'Name');
		}
		else {
			$categories = array();
		}
		$categories = array( 0 => _t("Merchants.ALL_CATEGORIES", "-- All Categories")) + $categories;
		//merchants
		$merchantPages = MerchantPage::merchant_pages_for_city_and_category_cache($this->cityID, $this->categoryID);
		if($merchantPages) {
			$merchantPages = $merchantPages->map();
		}
		else {
			$merchantPages = array();
		}
		$merchantPages = array( 0 => _t("Merchants.ALL_MERCHANTS", "-- All Merchants")) + $merchantPages;
		//priceOptionsFrom
		$priceOptionsFrom = DataObject::get('MerchantPriceOption', "ShowInFrom = 1", "DefaultFrom ASC, Price ASC");
		if($priceOptionsFrom) {
			$priceOptionsFrom = $priceOptionsFrom->map("PriceInt", "PriceNice");
		}
		else {
			$priceOptionsFrom = array();
		}
		$priceOptionsFrom = array( 0 => _t("Merchants.UNSELECTED_FROM_PRICE", "-- From")) + $priceOptionsFrom;
		//priceOptionsUpTo
		$priceOptionsUpTo = DataObject::get('MerchantPriceOption', "ShowInUpTo = 1", "DefaultUpTo ASC, Price ASC");
		if($priceOptionsUpTo) {
			$priceOptionsUpTo = $priceOptionsUpTo->map("PriceInt", "PriceNice");
		}
		else {
			$priceOptionsUpTo = array();
		}
		$priceOptionsUpTo = array( 0 => _t("Merchants.UNSELECTED_UPTO_PRICE", "-- Up To")) + $priceOptionsUpTo;

		//==============================
		// CREATE DROPDOWNS
		//==============================
		$cityID = (isset($cities[$this->cityID]) ? $this->cityID : 0);
		$categoryID = (isset($categories[$this->categoryID]) ? $this->categoryID : 0);
		$merchantPageID = (isset($merchantPages[$this->merchantPageID]) ? $this->merchantPageID : 0);
		$priceFrom = (isset($priceOptionsFrom[$this->priceFrom]) ? $this->priceFrom : 0);
		$priceUpTo = (isset($priceOptionsUpTo[$this->priceUpTo]) ? $this->priceUpTo : 0);
		$fields = new FieldSet(
			new Dropdownfield(self::get_city_param(), _t("Merchants.SELECT_LOCATION", "Select Location"), $cities, $cityID),
			new Dropdownfield(self::get_category_param(), _t("Merchants.SELECT_CATEGORY", "Select Category"), $categories, $categoryID),
			new Dropdownfield(self::get_merchant_page_param(), _t("Merchants.SELECT_MERCHANT", "Select Merchant"), $merchantPages, $merchantPageID),
			new Dropdownfield(self::get_price_from_param(), _t("Merchants.PRICE_FROM", "Price From"), $priceOptionsFrom, $priceFrom),
			new Dropdownfield(self::get_price_upto_param(), _t("Merchants.PRICE_UNTIL", "Price Until"), $priceOptionsUpTo, $priceUpTo),
			new LiteralField("AllMerchantsPageLoadingHolder", "<div class=\"allMerchantsPageLoadingHolder\">&nbsp;</div>")
		);
		//reset City Form (needed to avoid discrepancies when using the Back Button)
		$actions = new FieldSet(new FormAction('filter', _t('AllMerchantsPage_Controller.FILTER', 'Filter')));
		return new Form($this, 'FilterForm', $fields, $actions);
	}


	function filter($data = null, $form = null) {
		//RETURN AJAX / NORMAL
		if(Director::is_ajax()) {
			$variablesForTemplateArray["Products"] = $this->renderWith("ProductsHolder");
			$variablesForTemplateArray["Form_FilterForm"] = $this->FilterForm()->renderWith("FilterForm");
			return Convert::array2json($variablesForTemplateArray);
		}
		else {
			return Array();
		}
	}

	/****************************************
	 * TEMPLATE CONTROLLERS
	 ****************************************/

	/**
	 *
	 * @var DataObjectSet
	 */
	private static $products_cache = null;

	/**
	 *
	 * @param String $filter
	 * @return DataObjectSet
	 */
	function Products($filter = null) {
		if(self::$products_cache === null) {
			$filters = array();
			if($filter !== null) {
				$filters[] = $filter;
			}
			$joins = array();
			//category
			if($this->categoryID) {
				$filters[] = ' MerchantProduct_Categories.CategoryID  = ' . $this->categoryID . ' ';
				$joins[] = 'INNER JOIN MerchantProduct_Categories ON SiteTree'.$this->stageAppendix().'.ID = MerchantProductID';
			}
			//merchants
			if($this->merchantPageID) {
				$filters[] = ' SiteTree'.$this->stageAppendix().'.ParentID = '.$this->merchantPageID;
			}
			//city
			if($this->cityID) {
				$filters[] = ' CityID = '.$this->cityID. ' ';
				$joins[] = 'INNER JOIN Product_ProductGroups ON SiteTree'.$this->stageAppendix().'.ID = ProductID';
				$joins[] = 'INNER JOIN MerchantLocation'.$this->stageAppendix().' ON MerchantLocation'.$this->stageAppendix().'.ID = ProductGroupID';
			}
			//priceFrom
			if($this->priceFrom) {
				$filters[] = ' Product'.$this->stageAppendix().'.Price >= '.$this->priceFrom;
			}
			//priceTo
			if($this->priceUpTo) {
				$filters[] = ' Product'.$this->stageAppendix().'.Price <= '.$this->priceUpTo;
			}
			$filters[] = MerchantProduct::get_active_filter();
			$sort = "RAND() DESC";

			//GLUE
			$filter = '('.implode(') AND (', $filters).')';
			$join = count($joins) ? implode(' ', $joins) : '';

			//Select Products
			unset($this->productArray);
			$this->productArray = array();
			$products = DataObject::get(
				"MerchantProduct",
				$filter,
				$sort,
				$join
			);
			if($products) {
				foreach($products as $product) {
					if(isset($_GET["flush"])) {
						$product->writeToStage('Stage');
						$product->doPublish();
					}
					if($product->canPurchase()) {
						if($product->Status == "Published") {
							$this->productArray[$product->ID] = $product->ID;
						}
					}
				}
			}
			$this->productCount = count($this->productArray);
			$productArrayAsString = implode(",", $this->productArray);
			Session::set(self::get_merchant_product_session_array_name(), $productArrayAsString);
			$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($this->productArray, $this->productOffSet(), $this->productsPerPage(), "MerchantProduct".$this->stageAppendix());
			self::$products_cache = DataObject::get(
				'MerchantProduct',
				$sortbyAndFilterIDMakerArray["Filter"],
				$sortbyAndFilterIDMakerArray["Sort"]
			);
		}
		return self::$products_cache;
	}

	/**
	 * returns Link to show more products using Ajax
	 * @return String
	 */
	public function MoreProductsLink(){
		$currentEndPoint = intval($this->productOffSet() + $this->productsPerPage());
		if($this->productCount() > ($currentEndPoint)) {
			$link = $this->Link("moreproducts");
			$link .= "?pos=".$currentEndPoint."&amp;ppp=".$this->productsPerPage();
			$link .= $this->filterGetVariables();
			return $link;
		}
	}

	/**
	 * link for current page
	 * @return String
	 */
	public function CurrentPageLink(){
		$currentEndPoint = intval($this->productOffSet() + $this->productsPerPage());
		$link = $this->Link("");
		$link .= "?ppp=".$currentEndPoint;
		$link .= $this->filterGetVariables();
		return $link;
	}

	/**
	 * total number of products available for current filter
	 * @return Int
	 */
	function ProductCount(){
		return $this->productCount;
	}

	/**
	 * total number of products already showing
	 * @return Int
	 */
	public function CurrentlyShowing(){
		return $this->productOffSet()+$this->productsPerPage();
	}



	/****************************************
	 * HELPER FUNCTIONS
	 ****************************************/


	/**
	 * additional variables for template rendering
	 * @return Array
	 */
	protected function variablesForTemplate(){
		$variablesForTemplateArray = array(
			"ProductCount" => $this->ProductCount(),
			"CurrentlyShowing" => $this->CurrentlyShowing(),
			"CurrentPageLink" => $this->CurrentPageLink()
		);
		return $variablesForTemplateArray;
	}

	protected function filterGetVariables(){
		$getVariables = "";
		$getVariables .= $this->CategoryLink($this->categoryID, false);
		$getVariables .= $this->MerchantPageLink($this->merchantPageID, false);
		$getVariables .= $this->CityLink($this->cityID, false);
		$getVariables .= $this->PriceFromLink($this->priceFrom, false);
		$getVariables .= $this->PriceUpToLink($this->priceUpTo, false);
		return $getVariables;
	}


	protected function productLimit(){
		$offSet = $this->productOffSet();
		$perPage = $this->productsPerPage();
		return "$offSet, $perPage";
	}

	protected function productOffSet(){
		return $this->productOffSet;
	}

	protected function productsPerPage(){
		return $this->productsPerPage;
	}


	protected function sortbyAndFilterIDMaker(Array $array, $offSet, $perPage, $table){
		$count = 0;
		$min = $offSet;
		$max = ($offSet + $perPage);
		$filterSting = $table.".ID IN (0";
		$sortString = "IF(".$table.".ID=0, 0";
		$closingBracket = ", 999)";
		if(count($array)) {
			foreach($array as $value) {
				$count++;
				if($count > $min && $count <= $max ) {
					$filterSting .= ",$value";
					$sortString .= ", IF(".$table.".ID=$value, $count";
					$closingBracket .= ")";
				}
			}
			$filterSting .= ")";
			return array(
				"Filter" => $filterSting,
				"Sort" => $sortString."".$closingBracket." ASC"
			);
		}
		return array(
			"Filter" => "-1 = 0",
			"Sort" => ""
		);
	}

	private static $stage_appendix_cache = null;

	protected function stageAppendix(){
		if(self::$stage_appendix_cache === null) {
			if(Versioned::current_stage() == "Live") {
				self::$stage_appendix_cache = "_Live";
			}
		}
		return self::$stage_appendix_cache;
	}

	function MyDebug(){
		if($this->mydebug) {
			return
				print_r($this->productArray, 1).
				"<hr />".
				print_r($this->ProductCount());
				"<hr />".
				print_r($this->CurrentlyShowing());
		}
	}


		/**
		 * LONG TERM "ONLY SHOW" FILTER
		 * This allows you to filter the entire website for one merchant for
		 * xxx number of minutes
		 *
		 */
		//  =======================


	/**
	 * The number of minutes the site will only show the particular merchant.
	 * @var Int
	 */
	protected static $merchant_only_show_number_of_minutes = 30;
		public static function set_merchant_only_show_number_of_minutes($i) {self::$merchant_only_show_number_of_minutes = $i;}
		public static function get_merchant_only_show_number_of_minutes() {return self::$merchant_only_show_number_of_minutes;}

	/**
	 * The session variable name used to set the merchant
	 * @var String
	 */
	protected static $merchant_session_param = 'merchant';
		public static function set_merchant_session_param($s) { self::$merchant_session_param = $s;}
		public static function get_merchant_session_param() {return self::$merchant_session_param;}

	/**
	 * Returns the ID of the Merchant set to "Only show" - if any
	 * @var Int
	 */
	public static function get_only_show_filter() {
		$time = Session::get(self::get_merchant_session_param()."_time");
		if($time && $time > 0) {
			$secondsPerMinute = 60;
			if($time + ($secondsPerMinute * self::get_merchant_only_show_number_of_minutes()) < time()) {
				Session::set(self::get_merchant_session_param(), null);
				Session::set(self::get_merchant_session_param()."_time", null);
				Session::clear(self::get_merchant_session_param());
				Session::clear(self::get_merchant_session_param()."_time");
			}
			else {
				return intval(Session::get(self::get_merchant_session_param()));
			}
		}
		return 0;
	}

	/**
	 *
	 * action to show only one merchant for particular space of time
	 * @param SS_HTTPRequest
	 */
	function onlyshow($request = null) {
		if(!empty($this->urlParams['ID'])) {
			$merchantURLSegment = Convert::raw2sql($this->urlParams['ID']);
			$merchant = DataObject::get_one('MerchantPage', "URLSegment = '$merchantURLSegment'");
			if($merchant) {
				Session::set(self::get_merchant_session_param(), $merchant->ID);
				//mark starting time....
				Session::set(self::get_merchant_session_param()."_time", time());
			}
		}
		//reload page!
		$this->redirect($this->Link());
	}

	/**
	 *
	 * action to clear the only show
	 * @param SS_HTTPRequest
	 */
	function clearonlyshow($request = null) {
		Session::set(self::get_merchant_session_param(), null);
		Session::set(self::get_merchant_session_param()."_time", null);
		Session::clear(self::get_merchant_session_param());
		Session::clear(self::get_merchant_session_param()."_time");
		return Director::redirectBack();
	}

}
