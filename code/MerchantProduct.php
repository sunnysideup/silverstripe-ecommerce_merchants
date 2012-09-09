<?php
class MerchantProduct extends Page {

	static $db = array();

	static $has_one = array();

}

class MerchantProduct_Controller extends Page_Controller {

	public function add(){
		return array();
	}

	public function Product(){
		if($_REQUEST['ProductID']){
			return Product::get_by_id("Product", $_REQUEST['ProductID']);
		}
	}

	public function AddProductForm(){
		$user = Member::currentUser();
		$fields = MerchantProductDecorator::get_form_fields(false, $user->Merchant()->ID);
		$actions = new FieldSet(new FormAction('doAddProduct', 'Add product'));
		$form = new Form($this, 'AddProductForm', $fields, $actions);
		return $form;
	}

	public function doAddProduct($data, $form){
		//to do: avoid SQL injection
		$product = new Product();
		$form->saveInto($product);
		$product->writeToStage('Stage');
		$product->Publish('Stage', 'Live');
		$product->Status = "Published";
		$product->flushCache();
		$product->write();
		$product->createActivationCodes($data['AmountOfActivationCodes']);

		$user = Member::currentUser();
		$merchant = $user->Merchant();

		return Director::redirect($merchant->ViewLink());
	}

	public function edit(){
		return array();
	}

	public function EditProductForm(){
		$productID = Convert::raw2sql($_REQUEST['ProductID']);
		$product = Product::get_by_id("Product", $productID);
		$fields = MerchantProductDecorator::get_form_fields($product);
		$actions = new FieldSet(new FormAction('doEditProduct', 'Edit product'));
		$form = new Form($this, 'EditProductForm', $fields, $actions);
		$form->loadDataFrom($product);
		return $form;
	}

	public function doEditProduct($data, $form){
		$product = Product::get_by_id("Product", $data["ProductID"]);
		$form->saveInto($product);
		$product->createActivationCodes($data['AmountOfActivationCodes']);
		$product->write();
		//to do: why redirect to the home page?
		return Director::redirect("/");
	}

	//public function createActivationCodes(){
	//	$this->Product()->createActivationCodes($_REQUEST['Amount']);
	//	Director::redirectBack();
	//}

}
