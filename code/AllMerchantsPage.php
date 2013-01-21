<?php

class AllMerchantsPage extends ProductGroup {


	/**
	 * icons for the page
	 * @var String
	 */
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

	function getCMSFields() {
		$fields = parent::getCMSFields();
		foreach(array('Images', 'ProductDisplay', 'OtherProductsShown') as $name) {
			$fields->removeByName($name);
		}
		return $fields;
	}

	/**
	 *
	 * return String
	 */
	function CategoryLink(Category $category) {
		return "{$this->Link()}?" . AllMerchantsPage_Controller::$category_param . "=$category->ID";
	}


	/**
	 *
	 * return String
	 */
	function CityLink(City $city) {
		return "{$this->Link()}?" . AllMerchantsPage_Controller::$city_param . "=$city->ID";
	}
}

class AllMerchantsPage_Controller extends ProductGroup_Controller {

	static $category_param = 'category';

	static $city_param = 'city';

	function init() {
		parent::init();
		Requirements::javascript('ecommerce_merchants/javascript/filter.js');
	}

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
		if(! $data && Director::is_ajax()) {
			$data = $_POST;
		}
		if(isset($data['City'])) {
			Convert::raw2sql($data['City']);
			$filters[] = 'CityID IN (' . implode(',', ($data['City'])) . ')';
			$joins[] = 'INNER JOIN Product_ProductGroups ON SiteTree_Live.ID = ProductID';
			$joins[] = 'INNER JOIN MerchantLocation_Live ON MerchantLocation_Live.ID = ProductGroupID';
		}
		if(isset($data['Category'])) {
			Convert::raw2sql($data['Category']);
			$filters[] = 'MerchantProduct_Categories.CategoryID IN (' . implode(',', ($data['Category'])) . ')';
			$joins[] = 'INNER JOIN MerchantProduct_Categories ON SiteTree_Live.ID = MerchantProductID';
		}

		$filter = isset($filters) ? implode(' AND ', $filters) : '';
		$join = isset($joins) ? implode(' ', $joins) : '';
		$products = DataObject::get('MerchantProduct', $filter, '', $join);

		$locations = $this->Merchants($products, isset($data['City']) ? $filters[0] : '');

		$results = array(
			'Merchants' => $locations ? $locations : false,
			'Products' => isset($products) ? $products : false,
			'FilterForm' => $form
		);

		if(Director::is_ajax()) {
			unset($results['FilterForm']);
			foreach($results as $name => $result) {
				$results[$name] = $this->customise(array($name => $result))->renderWith("{$name}Holder");
			}
			return Convert::array2json($results);
		}

		return $results;
	}

	function Merchants($products = false, $filter = null) {
		$filters[] = MerchantLocation::$active_filter;
		if($filter) {
			$filters[] = $filter;
		}
		if($products === false) {
			$products = $this->Products();
		}
		if($products) {
			$join = 'INNER JOIN Product_ProductGroups ON SiteTree_Live.ID = ProductGroupID';
			$filters[] = 'ProductID IN (' . implode(',', $products->map('ID', 'ID')) . ')';
			$filter = "(".implode(') AND (', $filters).")";
			return DataObject::get('MerchantLocation', $filter, 'RAND() ASC', $join);
		}
	}

	function Products() {
		$filter = $join = '';
		if(isset($_REQUEST[self::$category_param])) {
			$categoryIDs = Convert::raw2sql($_REQUEST[self::$category_param]);
			$filter = "MerchantProduct_Categories.CategoryID IN ($categoryIDs)";
			$join = 'INNER JOIN MerchantProduct_Categories ON SiteTree_Live.ID = MerchantProductID';
		}
		if(isset($_REQUEST[self::$city_param])) {
			$cityIDs = Convert::raw2sql($_REQUEST[self::$city_param]);
			$filter = 'CityID IN (' . $cityIDs . ')';
			$join = 'INNER JOIN Product_ProductGroups ON SiteTree_Live.ID = ProductID INNER JOIN MerchantLocation_Live ON MerchantLocation_Live.ID = ProductGroupID';
		}
		$products = DataObject::get('MerchantProduct', $filter, 'RAND() ASC', $join);
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
}
