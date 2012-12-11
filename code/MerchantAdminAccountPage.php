<?php

class MerchantAdminAccountPage extends Page {


	static $singular_name = 'Merchant Account Page';
		function i18n_singular_name() {return _t('MerchantAdminAccountPage.SINGULARNAME', self::$singular_name);}

	public static $plural_name = 'Merchant Account Pages';
		function i18n_plural_name() {return _t('MerchantAdminAccountPage.PLURALNAME', self::$plural_name);}

	/**
	 * icons for the page
	 * @var String
	 */
	public static $icon = "ecommerce_merchants/images/MerchantAdminAccountPage";

	public static $db = array(
		//"WelcomeMessage" => "HTMLText",
		//"UpdateMessage" => "HTMLText"
	);

	/**
	 * Standard SS method...
	 * We can only create one of these pages
	 * @return Boolean
	 */
	function canCreate() {
		return ! DataObject::get_one($this->class);
	}

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Content.Notification", new ReadonlyField("NotificationsAreSentTo", "Notifications Are Sent To", Order_Email::get_from_email()));
		//$fields->addFieldToTab("Root.Content.Welcome", new HTMLEditorField("WelcomeMessage", "Thank you message (replaces content after successful )"));
		//$fields->addFieldToTab("Root.Content.Update", new HTMLEditorField("UpdateMessage", "Thank you message (replaces content after successful )"));
		return $fields;
	}

	static $allowed_children = 'none';

}

class MerchantAdminAccountPage_Controller extends Page_Controller {

	function init(){
		parent::init();
		$member = Member::currentUser();
		if($member) {
			if($member->isApprovedMerchantAdmin()) {
				if($page = $member->AdminHomePage()) {
					$this->redirect($page->Link());
				}
			}
		}
	}

	function RegistrationForm() {
		list($fields, $requiredFields) = MerchantAdminDOD::get_registration_fields();
		$actions = new FieldSet(new FormAction('register', _t('MerchantAdminAccountPage_Controller.REGISTER', 'Register')));
		return new Form(
			$this,
			'RegistrationForm',
			$fields,
			$actions,
			$requiredFields
		);
	}

	function register($data, $form) {
		$member = true;
		try {
			$member = new Member();
			$form->saveInto($member);
			$member->write();
			$member->Groups()->add(MerchantGroupDOD::get_main_group());
			$member->login();
			$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.REGISTRATION_SUCCESS', 'Your personal details have been saved successfully and the admin team will contact you shortly.'), 'good');
			$this->notify($member, "added", 0);
		}
		catch(ValidationException $e) {
			$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EMAIL_ERROR', 'Your personal details could not be saved because the email is already been used.'), 'bad');
			$this->notify($member, "added", true);
		}
		return Director::redirectBack();
	}

	function EditForm() {
		$member = Member::currentUser();
		if($member) {
			list($fields, $requiredFields) = MerchantAdminDOD::get_edit_fields();
			$actions = new FieldSet(new FormAction('save', _t('MerchantAdminAccountPage_Controller.SAVE_DETAILS', 'Save Details')));
			$form = new Form($this, 'EditForm', $fields, $actions, $requiredFields);
			$form->loadDataFrom($member);
			$form->dataFieldByName("Password")->setValue("");
			return $form;
		}
	}

	function save($data, $form) {
		$member = Member::currentUser();
		if($member) {
			try {
				$form->saveInto($member);
				$member->write();
				$member->Groups()->add(MerchantGroupDOD::get_main_group());
				$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EDIT_SUCCESS', 'Your personal details have been saved successfully.'), 'good');
				$this->notify($member, "updated");
			}
			catch (ValidationException $e) {
				$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EMAIL_ERROR', 'Your personal details could not be saved because the email is already been used.'), 'bad');
				$this->notify($member, "updated", true);
			}
			return Director::redirectBack();
		}
		$form->sessionMessage(_t('MerchantAdminAccountPage_Controller.EMAIL_ERROR', 'You need to be logged in to update your details.'), 'bad');
		$this->notify($member, "updated", true);
		return Director::redirectBack();
	}

	function notify($member, $type = "added", $error = false) {
		$errorString = $error ? " ERROR: " : "OK";
		if($member instanceOf Member) {
			$name = $member->getName();
		}
		else {
			$name = "--- COULD NOT SAVE ---";
		}
		$email = new Email(
			$from = Order_Email::get_from_email(),
			$to =  Order_Email::get_from_email(),
			$subject = "A Merchant has been ".$type.": ".$name,
			$body = "A Merchant has been ".$type.": ".$name. ", result: ".$errorString
		);
		return $email->send();
	}
}
