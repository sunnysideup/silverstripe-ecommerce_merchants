<?php

class AllMerchantsPage extends ProductGroup {

	/****************************************
	 * MODEL DEFINITION
	 ****************************************/

	public static $icon = "ecommerce_merchants/images/AllMerchantsPage";

	function canCreate() {
		return ! DataObject::get_one($this->class);
	}

	static $allowed_children = array('MerchantPage');

	static $default_child = 'MerchantPage';

	static $hide_ancestor = 'ProductGroup';

	static $singular_name = 'All Merchants Page';
		function i18n_singular_name() {return _t('AllMerchantsPage.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'All Merchants Pages';
		function i18n_plural_name() {return _t('AllMerchantsPage.PLURALNAME', self::$plural_name);}


	/****************************************
	 * CRUD FORMS
	 ****************************************/

	function getCMSFields() {
		$fields = parent::getCMSFields();
		foreach(array('Images', 'ProductDisplay', 'OtherProductsShown') as $name) {
			$fields->removeByName($name);
		}
		return $fields;
	}

	/****************************************
	 * CONTROLLER LIKE METHODS
	 ****************************************/

	function CategoryLink(Category $category) {
		return "{$this->Link()}?" . AllMerchantsPage_Controller::get_category_param() . "=$category->ID";
	}


	function CityLink(City $city) {
		return "{$this->Link()}?" . AllMerchantsPage_Controller::get_city_param() . "=$city->ID";
	}
}

class AllMerchantsPage_Controller extends ProductGroup_Controller {

	protected static $category_param = 'category';
		public static function get_category_param(){return self::$category_param; }

	protected static $city_param = 'city';
		public static function get_city_param(){return self::$city_param; }

	protected $productsPerPage = 16;

	protected $merchantsPerPage = 6;

	protected $productCount = 0;

	protected $merchantCount = 0;

	protected $productArray = array();

	protected $merchantArray = array();

	protected $cityFilter = '';

	function init() {
		parent::init();
		if(Director::is_ajax()) {

		}
		else {

		}
		Requirements::javascript('ecommerce_merchants/javascript/filter.js');
	}

	/****************************************
	 * Actions
	 ****************************************/

	function moreproducts(){
		if(Director::is_ajax()) {
			$productArrayAsString = Session::get("productArrayAsString");
			$productArray = explode(",", $productArrayAsString);
			$this->productCount = count($productArray);
			$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($productArray, $this->productOffSet(), $this->productsPerPage, "MerchantProduct".$this->stageAppendix());
			self::$products_cache = DataObject::get(
				'MerchantProduct',
				$sortbyAndFilterIDMakerArray["Filter"],
				$sortbyAndFilterIDMakerArray["Sort"]
			);
			return $this->customise(array("Products" => self::$products_cache))->renderWith("ProductsHolder");
		}
		else {
			//$this->productsPerPage = $this->productOffSet();
			//unset($_GET["productoffset"]);
			return Array();
		}
	}

	function moremerchants(){
		$merchants = $this->Merchants();
		if(Director::is_ajax()) {
			$merchantArrayAsString = Session::get("merchantArrayAsString");
			$merchantArray = explode(",", $merchantArrayAsString);
			$this->merchantCount = count($merchantArray);
			$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($merchantArray, $this->merchantOffSet(), $this->merchantsPerPage, "MerchantLocation".$this->stageAppendix());
			self::$merchants_cache = DataObject::get(
				'MerchantLocation',
				$sortbyAndFilterIDMakerArray["Filter"],
				$sortbyAndFilterIDMakerArray["Sort"]
			);
			return $this->customise(array("Merchants" => self::$merchants_cache))->renderWith("MerchantsHolder");
		}
		else {
			//$this->merchantsPerPage = $this->merchantOffSet();
			//unset($_GET["merchantoffset"]);
			return Array();
		}
	}

	/****************************************
	 * FORMS
	 ****************************************/

	function FilterForm() {
		$cities = DataObject::get('City');
		$cities = $cities->map();
		$categories = DataObject::get('Category');
		$categories = $categories->map('ID', 'getFilterFormHTMLTitle');
		if(isset($_REQUEST[self::$city_param])) {
			$cityID = $_REQUEST[self::$city_param];
			$cityArray = $_REQUEST[self::$city_param];
		}
		else {
			$cityID = 0;
			$cityArray = null;
		}
		if(isset($_REQUEST[self::$category_param])) {
			$categoryID = $_REQUEST[self::$category_param];
			$categoryArray = $_REQUEST[self::$category_param];
		}
		else {
			$categoryID = 0;
			$categoryArray = null;
		}

		$fields = new FieldSet(
			new CheckboxSetField('City', 'Selecteer locatie', $cities, $cityArray),
			new CheckboxSetField('Category', 'Selecteer categorie', $categories, $categoryArray)
		);
		//reset City Form (needed to avoid discrepancies when using the Back Button)
		Requirements::customScript("
			jQuery(document).ready(
				function() {
					jQuery(\"#City input\").each(
							function(i, el){
								if($cityID > 0 && jQuery(el).val() == $cityID) {
									jQuery(el).attr(\"checked\",\"checked\");
								}
								else {
									jQuery(el).removeAttr(\"checked\");
								}
							}
					);
					jQuery(\"#Category input\").each(
							function(i, el){
								if($categoryID > 0 && jQuery(el).val() == $categoryID) {
									jQuery(el).attr(\"checked\",\"checked\");
								}
								else {
									jQuery(el).removeAttr(\"checked\");
								}
							}
					);

				}
			);
		", "CityAndCategoryLink");
		$actions = new FieldSet(new FormAction('filter', _t('AllMerchantsPage_Controller.FILTER', 'Filter')));
		return new Form($this, 'FilterForm', $fields, $actions);
	}

	function filter($data = null, $form = null) {

		//CREATE RESULTS
		$results = array(
			//put products first!
			'Products' => $this->Products(),
			'Merchants' => $this->Merchants()
		);

		//RETURN AJAX / NORMAL
		if(Director::is_ajax()) {
			foreach($results as $name => $result) {
				$results[$name] = $this->customise(array($name => $result))->renderWith("{$name}Holder");
			}
			return Convert::array2json($results);
		}
		$results["FilterForm"] = $form;
		return $results;
	}

	/****************************************
	 * TEMPLATE CONTROLLERS
	 ****************************************/

	function ProductCount(){
		return $this->productCount;
	}

	function MerchantCount(){
		return $this->merchantCount;
	}

	private static $merchants_cache = null;

	function Merchants($filter = null) {
		if(self::$merchants_cache === null) {
			$filters = array();
			if($filter) {
				$filters[] = $filter;
			}
			if($this->Products() && count($this->productArray) > 0) {
				$productArrayAsString = implode(",", $this->productArray);
				$join = 'INNER JOIN Product_ProductGroups ON SiteTree'.$this->stageAppendix().'.ID = ProductGroupID';
				$filters[] = 'ProductID IN (' . $productArrayAsString . ')';
				if($this->cityFilter) {
					$filters[] = $this->cityFilter;
				}
				//glue
				$filterAsString = implode(') AND (', $filters);
				$filter = "(".$filterAsString.") AND ".MerchantLocation::get_active_filter();
				$sort = "LastEdited DESC";
				$this->merchantArray = array();
				$merchants = DataObject::get(
					"MerchantLocation",
					$filter,
					$sort,
					$join
				);
				if($merchants) {
					foreach($merchants as $merchant) {
						$this->merchantArray[$merchant->ID] = $merchant->ID;
					}
				}
				$this->merchantCount = count($this->merchantArray);
				$merchantArrayAsString = implode(",", $this->merchantArray);
				Session::set("merchantArrayAsString", $merchantArrayAsString);
				$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($this->merchantArray, $this->merchantOffSet(), $this->merchantsPerPage, "MerchantLocation".$this->stageAppendix());
				self::$merchants_cache = DataObject::get(
					'MerchantLocation',
					$sortbyAndFilterIDMakerArray["Filter"],
					$sortbyAndFilterIDMakerArray["Sort"]
				);
			}
		}
		return self::$merchants_cache;
	}

	private static $products_cache = null;

	function Products($filter = null) {
		if(self::$products_cache === null) {
			$data = Convert::raw2sql($_REQUEST);

			$filters = array();
			if($filter !== null) {
				$filters[] = $filter;
			}
			$joins = array();
			if(isset($data[ucfirst(self::$city_param)])) {
				$this->cityFilter = 'CityID IN (' . implode(',', ($data[ucfirst(self::$city_param)])) . ')';
				$filters[] = $this->cityFilter;
				$joins[] = 'INNER JOIN Product_ProductGroups ON SiteTree'.$this->stageAppendix().'.ID = ProductID';
				$joins[] = 'INNER JOIN MerchantLocation'.$this->stageAppendix().' ON MerchantLocation'.$this->stageAppendix().'.ID = ProductGroupID';
			}
			if(isset($data[ucfirst(self::$category_param)])) {
				$filters[] = 'MerchantProduct_Categories.CategoryID IN (' . implode(',', ($data[ucfirst(self::$category_param)])) . ')';
				$joins[] = 'INNER JOIN MerchantProduct_Categories ON SiteTree'.$this->stageAppendix().'.ID = MerchantProductID';
			}
			$filters[] = MerchantProduct::get_active_filter();
			$sort = "LastEdited DESC";

			//GLUE
			$filter = '('.implode(') AND (', $filters).')';
			$join = count($joins) ? implode(' ', $joins) : '';

			//Select Products
			$this->productArray = array();
			$products = DataObject::get(
				"MerchantProduct",
				$filter,
				$sort,
				$join
			);
			if($products) {
				foreach($products as $product) {
					if($product->canPurchase()) {
						$this->productArray[$product->ID] = $product->ID;
					}
				}
			}
			$this->productCount = count($this->productArray);
			$productArrayAsString = implode(",", $this->productArray);
			Session::set("productArrayAsString", $productArrayAsString);
			$sortbyAndFilterIDMakerArray = $this->sortbyAndFilterIDMaker($this->productArray, $this->productOffSet(), $this->productsPerPage, "MerchantProduct".$this->stageAppendix());
			self::$products_cache = DataObject::get(
				'MerchantProduct',
				$sortbyAndFilterIDMakerArray["Filter"],
				$sortbyAndFilterIDMakerArray["Sort"]
			);
		}
		return self::$products_cache;
	}

	function MoreProductsLink(){
		$currentEndPoint = intval($this->productOffSet() + $this->productsPerPage);
		if($this->productCount > $currentEndPoint) {
			return $this->Link("moreproducts")."?productoffset=".$currentEndPoint."&amp;ppp=".$this->productsPerPage."&amp;count=".$this->productCount;
		}
	}

	function MoreMerchantsLink(){
		$currentEndPoint = intval($this->merchantOffSet() + $this->merchantsPerPage);
		if($this->merchantCount > $currentEndPoint) {
			return $this->Link("moremerchants")."?merchantoffset=".$currentEndPoint."&amp;ppp=".$this->merchantsPerPage."&amp;count=".$this->merchantCount;;
		}
	}


	/****************************************
	 * SQL HELPER FUNCTIONS
	 ****************************************/

	protected function productLimit(){
		$offSet = $this->productOffSet();
		$perPage = $this->productsPerPage;
		return "$offSet, $perPage";
	}

	protected function merchantLimit(){
		$offSet = $this->merchantOffSet();
		$perPage = $this->merchantsPerPage;
		return "$offSet, $perPage";
	}

	protected function productOffSet(){
		$offSet = 0;
		if(isset($_GET["productoffset"])) {
			$offSet = intval($_GET["productoffset"]);
		}
		return $offSet;
	}

	protected function merchantOffSet(){
		$offSet = 0;
		if(isset($_GET["merchantoffset"])) {
			$offSet = intval($_GET["merchantoffset"]);
		}
		return $offSet;
	}

	protected function sortbyAndFilterIDMaker(Array $array, $offSet, $perPage, $table){
		$count = 0;
		$sortString = "";
		$closingBracket = ", 999";
		$min = $offSet;
		$max = ($offSet + $perPage);
		$filterSting = $table.".ID IN (";
		if(count($array)) {
			foreach($array as $value) {
				$count++;
				if($count > $min && $count <= $max ) {
					if($count > ($min + 1)) {
						$sortString .= ",";
						$filterSting .= ",";
					}
					$sortString .= "IF(".$table.".ID=$value, $count";
					$closingBracket .= ")";
					$filterSting .= "$value";
				}
			}
			$filterSting .= ")";
			return array(
				"Sort" => $sortString."".$closingBracket." ASC",
				"Filter" => $filterSting
			);
		}
		return array(
			"Sort" => "",
			"Filter" => ""
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

}
