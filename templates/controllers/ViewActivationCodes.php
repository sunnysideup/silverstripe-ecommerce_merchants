<?php
class ViewActivationCodes extends Page {

	static $db = array();

	static $has_one = array();

}

class ViewActivationCodes_Controller extends Page_Controller {

	function ProductActivationCodes(){
		$merchantID = intval($_REQUEST['MerchantID']);
		$products = Product::get("Product", "MerchantID= $merchantID");
		$array = array();
		if($products && $products->exists()) {
			foreach($products as $product){
				$array[] = array('Product' => $product, 'ActivationCodes' => $product->ActivationCodes());
			}
			return new DataObjectSet($array);
		}
		return null;
		//$merchantID = Convert::raw2sql($_REQUEST['MerchantID']);
		//return ActivationCode::get("ActivationCode",
		//	"Product.MerchantID = $merchantID",
		//	"Product.ID DESC",
		//	"INNER JOIN Product ON Product.ID = ActivationCode.ProductID"
		//);
	}

}
