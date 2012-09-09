<?php
//to do: why not extend
class MerchantProductDecorator extends DataObjectDecorator {

	function extraStatics(){
		return array(
			"db" => array(
				"Category" => "Text"
			),
			"has_one" => array(
				'Merchant' => 'Merchant',
				'Category' => 'Category'
			),
			"has_many" => array(
				'Purchases' => 'Purchase',
				'ActivationCodes' => 'ActivationCode'
			),
		);
	}

	//to do: we need to get this link from somewhere
	//it can not be hard-coded
	function EditLink(){
		return '/merchant-product/edit?ProductID=' . $this->owner->ID;
	}

	//to do: we need to get this link from somewhere
	//it can not be hard-coded
	function CreateActivationCodesLink($amount){
		return "/merchant-product/createActivationCodes?ProductID=" . $this->owner->ID . "&Amount=$amount";
	}

	function ActivationCodesCount(){
		return DB::query("SELECT COUNT(*) FROM ActivationCode WHERE ProductID={$this->owner->ID}")->value();
	}

	function ActivationCodesLeft(){
		return DB::query("SELECT COUNT(*) FROM ActivationCode WHERE ProductID={$this->owner->ID} AND Used=0")->value();
	}

	function createActivationCodes($amount){
		$alreadyCreatedCodes = $this->ActivationCodesCount();
		if( $alreadyCreatedCodes < $amount ){
			$amount = $amount - $alreadyCreatedCodes;
			for($i = 0; $i < $amount; $i++){
				ActivationCode::createCode($this->owner);
			}
		}
	}

	function getActivationCode(){
		return ActivationCode::get_one("ActivationCode", "ProductID={$this->owner->ID} AND Used=0");
	}

	static function get_form_fields($product = false, $merchantID = false){
		$categories = DataObject::get("Category");
		$map = $categories->toDropDownMap('ID', 'Name');
		//REFACTOR!
		if($product){
			//EDIT
			return new FieldSet(
				new TextField('Title', "Title"),
				new TextField('Model', 'Model'),
				new DropdownField('CategoryID', 'product category', $map),
				new CurrencyField('Price', 'Price'),
				new NumericField('AmountOfActivationCodes', 'Amount of activation codes', $product->ActivationCodesCount()),
				new CheckboxField('FeaturedProduct', 'Featured Product'),
				new CheckboxField('AllowPurchase', 'Allow Purchase'),
				new SimpleImageField('Image'),
				new HiddenField("ShowInMenus", "", "0"),
				new HiddenField("ProductID", "", $product->ID)
			);
		} else {
			//SIGNUP
			return new FieldSet(
				new TextField('Title', "Title"),
				new TextField('Model', 'Model'),
				new DropdownField('CategoryID', 'product category', $map),
				new CurrencyField('Price', 'Price'),
				new NumericField('AmountOfActivationCodes', 'Amount of activation codes'),
				new CheckboxField('FeaturedProduct', 'Featured Product'),
				new CheckboxField('AllowPurchase', 'Allow Purchase'),
				new SimpleImageField('Image'),
				new HiddenField("ShowInMenus", "", "0"),
				new HiddenField("MerchantID", "", $merchantID)
			);
		}
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
				$limit_to_cities = "Merchant.City IN ($cities)";
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
			$join = (!empty($limit_to_cities)) ? "Inner Join Merchant On Merchant.ID = MerchantID" : null;

			return DataObject::get("Product", "{$limit_to_cities}{$and}{$limit_to_categories}", null, $join);
		}
		else {
			return Product::get("Product");
		}
	}

	static function gift_recipient_selected(){
		$name = Session::get('FriendName');
		$platform = Session::get('SocialNetwork');
		$social_id_or_email = Session::get('SocialID');

		return (!empty($name) && !empty($platform) && !empty($social_id_or_email));
	}

}
