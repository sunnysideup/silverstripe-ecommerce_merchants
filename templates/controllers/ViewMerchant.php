<?php
class ViewMerchant extends Page {

	static $db = array();

	static $has_one = array();

}

class ViewMerchant_Controller extends Page_Controller {

	function Products(){
		return Product::get("Product", "MerchantID={$this->Merchant()->ID}");
	}

	function MemberIsAdmin(){
		$member = Member::currentUser();
		$merchant = $this->Merchant();
		return ($merchant && $member && $member->ID == $merchant->MerchantAdminID);
	}

	/* TEMP TEMP TEMP
	* this needs to be done inside the ecommerce module somehow
	*/
	function purchaseProduct(){
		$memberID = Member::currentUserID();
		$productID = $_REQUEST['ProductID'];
		//to do: if they always go together - can we save them in the DB or something like that - linked to the SessionID?
		$name = Session::get('FriendName');
		$socialNetwork = Session::get('SocialNetwork');
		$socialID = Session::get('SocialID');

		if(MerchantProductDecorator::gift_recipient_selected()){
			Purchase::createPurchase($memberID, $productID, $name, $socialNetwork, $socialID);
		}
		else {
			//to do: we can not hard-code links like that.
			return Director::redirect("/select-friend");
		}
		Director::redirectBack();
	}

}
