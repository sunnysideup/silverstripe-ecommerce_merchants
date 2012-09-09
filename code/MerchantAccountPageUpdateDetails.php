<?php

/**
 * this page can be used in conjunction with the AccountPage.
 * It provides an overview of the
 *
 * @author nicolaas
 */

class MerchantAccountPageUpdateDetails extends MerchantAccountPage {

	/**
	 * standard SS method
	 * @return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("MerchantAccountPageUpdateDetails", "\"ClassName\" = 'MerchantAccountPageUpdateDetails'");
	}

	/**
	 * Returns the link to the AccountPage on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if($page = DataObject::get_one('MerchantAccountPageUpdateDetails', "\"ClassName\" = 'MerchantAccountPageUpdateDetails'")) {
			return $page->Link();
		}
	}


}

class MerchantAccountPageUpdateDetails_Controller extends MerchantAccountPage_Controller {

	/**
	 * returns a form... You can either update your details or request approval
	 * @return MerchantAccountOrganisationForm
	 */
	function OrganisationForm(){
		return new MerchantAccountOrganisationForm($this, "OrganisationForm", $this->AccountMember(), $this->AccountGroup());
	}





}
