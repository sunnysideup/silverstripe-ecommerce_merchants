<?php
class ActivationCode extends DataObject {

	//to do: indexes????

	static $db = array(
		"Code" => 'Text',
		"Used" => "Boolean"
	);

	static $has_one = array(
		'Product' => 'Product'
	);

	static $belongs_to = array(
		'Purchase' => 'Purchase'
	);

	static function createCode($product){
		$code = new ActivationCode();
		$code->Code = rand(1000000000,9999999999);
		$code->ProductID = $product->ID;
		$code->write();

		return $code;
	}

	function setAsUsed(){
		$this->Used = true;
		$this->write();
	}

}
