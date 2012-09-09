<?php
class SignupAsMerchant extends Page {

	static $db = array();

	static $has_one = array();

}

class SignupAsMerchant_Controller extends Page_Controller {

	public function SignupForm(){
		$fields = new FieldSet(
			new TextField('FirstName', 'First Name'),
			new TextField('Surname', 'Last Name'),
			new TextField('Email', 'Email'),
			new ConfirmedPasswordField('Password', 'Password *')
		);
		$actions = new FieldSet(new FormAction('doSignup', 'Signup'));
		$form = new Form($this, 'SignupForm', $fields, $actions);
		return $form;
	}

	public function doSignup($data, $form){
		//to do: elegant solution ... does it work?
		try {
			$member = new Member();
			$form->saveInto($member);
			$member->write();
			$member->login();
		} catch (ValidationException $e){
			$this->setMessage("Changes to profile could not be saved because email is already in use.", "bad");
		}
		return Director::redirectBack();
	}

	public function SignupMerchantForm(){
		$fields = Merchant::get_form_fields();
		$actions = new FieldSet(new FormAction('doSignupMerchant', 'Signup'));
		$form = new Form($this, 'SignupMerchantForm', $fields, $actions);
		return $form;
	}

	public function doSignupMerchant($data, $form){
		$member = Member::currentUser();
		try {
			$merchant = new Merchant();
			$form->saveInto($merchant);
			$merchant->MerchantAdminID = $member->ID;
			$merchant->write();
		} catch (ValidationException $e){
			$this->setMessage("Changes to profile could not be saved because email is already in use.", "bad");
		}
		return Director::redirect('/?flush=1');
	}

	public function edit(){
		return array();
	}

	public function EditMerchantForm(){
		$merchantID = Convert::raw2sql($_REQUEST['MerchantID']);
		$merchant = Merchant::get_by_id("Merchant", $merchantID);
		$fields = Merchant::get_form_fields();
		$fields->push(new HiddenField('MerchantID', '', $merchantID));
		$actions = new FieldSet(new FormAction('doEditMerchant', 'Signup'));
		$form = new Form($this, 'EditMerchantForm', $fields, $actions);
		$form->loadDataFrom($merchant);
		return $form;
	}

	public function doEditMerchant($data, $form){
		try {
			$merchant = Merchant::get_by_id("Merchant", $data["MerchantID"]);
			$form->saveInto($merchant);
			$merchant->write();
		} catch (ValidationException $e){
			$this->setMessage("Changes to profile could not be saved because email is already in use.", "bad");
		}
		return Director::redirect('/');
	}

}
