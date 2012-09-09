<?php

/**
 * this page can be used in conjunction with the AccountPage.
 * It provides an overview of the
 *
 *
 *
 *
 *
 * @author nicolaas
 */

class MerchantAccountPage extends AccountPage {

	/**
	 * Standard SS variable
	 */
	public static $icon = 'ecommerce_merchants/images/icons/MerchantAccountPage';

	/**
	 * standard SS method
	 * @return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("MerchantAccountPage", "\"ClassName\" = 'MerchantAccountPage'");
	}

	/**
	 * Returns the link to the AccountPage on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if($page = DataObject::get_one('MerchantAccountPage', "\"ClassName\" = 'MerchantAccountPage'")) {
			return $page->Link();
		}
	}

	/**
	 * Overloads AccountPage::pastOrdersSelection
	 * rather than just returning the orders from the Member,
	 * it returns the orders from the group
	 * @return NULL | DataObjectSet
	 */
	protected function pastOrdersSelection(){
		return null;
	}

	/**
	 * overloads AccountMember from AccountPage
	 * only returns a member if it is an merchant member
	 * @return NULL | Member
	 */
	function AccountMember(){
		$member = Member::currentUser();
		if($member) {
			if($member->exists()) {
				if($member->isApprovedMerchantCustomer()) {
					return $member;
				}
			}
		}
	}

	/**
	 * returns the group for the account member
	 * @return NULL | Group
	 */
	function AccountGroup(){
		$member = $this->AccountMember();
		if($member) {
			return $member->MerchantAccountGroup();
		}
	}

	/**
	 * returns the members of the current Group.
	 * Includes the current member.
	 * @return NULL | DataObjectSet
	 */
	function GroupMembers() {
		$members = null;
		$group = $this->AccountGroup();
		if($group) {
			$members = $group->Members();
			if($members && $members->count()) {
				$currentMember = Member::currentUser();
				foreach($members as $member) {
					if($currentMember->ID == $member->ID) {
						$member->LinkingMode = "current";
					}
					else {
						$member->LinkingMode = "link";
					}
				}
			}
		}
		return $members;
	}

}

class MerchantAccountPage_Controller extends AccountPage_Controller {


	/**
	 * standard controller function
	 **/
	function init() {
		parent::init();
		Requirements::themedCSS("MerchantAccountPage");
	}

	/**
	 * returns a string of the name of the group
	 * @return String
	 */
	function GroupTitle(){
		$group = $this->AccountGroup();
		if($group) {
			return $group->CombinedMerchantGroupName();
		}
	}

	/**
	 * returns a form... You can either update your details or request approval
	 * @return MerchantAccountOrganisationForm
	 */
	function OrganisationForm(){
		return new MerchantAccountOrganisationForm($this, "OrganisationForm", $this->AccountMember(), $this->AccountGroup());
	}

	/**
	 * tells us whether the current members merchant group is approved.
	 * @return Boolean
	 */
	function IsApprovedMerchantGroup(){
		return $this->MerchantGroup() ? true : false;
	}



}
