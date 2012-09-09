<?php
//to do:
//why does the Merchant only have one Category?
//can a merchant have more than one admin
class Merchant extends DataObject {

	public static $db = array(
		"Name" => "Text",
		"Address" => "Text",
		"City" => "Text",
		"Country" => "Text",
		"Disabled" => "Boolean"
	);

	public static $has_one = array(
		'Image' => 'Image',
		'MerchantAdmin' => 'Member',
		'Category' => 'Category'
	);

	public static $has_many = array(
		'Products' => 'Product'
	);


	//does this work on DataObject?
	//what does this do?
	public static $default_child = 'Product';

	//public static $icon = 'implement_this';

	static function get_form_fields() {
		$categories = DataObject::get("Category");
		$map = $categories->toDropDownMap('ID', 'Name');
		return new FieldSet(
			new TextField('Name', 'Name'),
			new TextField('Address', 'Address'),
			new TextField('City', 'City'),
			new TextField('Country', 'Country'),
			new DropdownField('CategoryID', 'Category', $map),
			new SimpleImageField('Image')
		);
	}

	//TODO: how can this be done reliably?
	//do not hard-code link
	//use controller method rather than get variable
	function ViewLink() {
		return '/view-merchant?MerchantID=' . $this->ID;
	}

	function EditMerchantLink(){
		return "/signup-as-merchant/edit?MerchantID=" . $this->ID;
	}

	function AddProductLink() {
		return '/merchant-product/add?MerchantID=' . $this->ID;
	}

	function ViewActivationCodesLink(){
		return "/view-activation-codes?MerchantID=" . $this->ID;
	}

	static function get_by_search_criteria($clauses){
		if($clauses){
			parse_str($clauses, $params);

			$limit_to_cities = "";
			if(isset($params['city'])){
				$cities = "";
				foreach($params['city'] as $city){
					$cities .= "'{$city}',";
				}
				$cities = rtrim($cities, ",");
				$limit_to_cities = "City IN ($cities)";
			}

			$limit_to_categories = "";
			if(isset($params['category'])){
				$categories = "";
				$category_ids = Category::get("Category");
				foreach($params['category'] as $category){
					$category_id = $category_ids->find('Name', $category)->ID;
					$categories .= "{$category_id},";
				}
				$categories = rtrim($categories, ",");
				$limit_to_categories = "CategoryID IN ($categories)";
			}

			$and = (!empty($categories) && !empty($cities)) ? " AND " : "";

			return Merchant::get("Merchant", "Disabled=0 AND {$limit_to_cities}{$and}{$limit_to_categories}");
		}
		else {
			return Merchant::get("Merchant", "Disabled=0");
		}
	}

}
