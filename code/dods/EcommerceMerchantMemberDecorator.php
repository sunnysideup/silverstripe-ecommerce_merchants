<?php


/**
 * adds functionality for Members
 *
 * @author nicolaas
 */


class EcommerceMerchantMemberDecorator extends DataObjectDecorator {

	/**
	 * Standard SS Method
	 *
	 */
	public function extraStatics() {
		return array (
			'db' => array (
				'ApprovalEmailSent' => 'Boolean'
			),
		);
	}

	/**
	 * Adds fields to the Member Ecommerce FieldSet.
	 * In this case, we add the name of the organisation as READ-ONLY.
	 * @param FieldSet $fields
	 * @return FieldSet
	 */
	function augmentEcommerceFields(&$fields) {
		if($group = $this->owner->getMerchantAccountGroup()) {
			$fields->push(new ReadonlyField("OrganisationName", _t("EcommerceMerchantAccount.FOR", "For"),$group->CombinedMerchantGroupName()));
		}
		return $fields;
	}

	/**
	 * Standard SS Method
	 * @param FieldSet
	 * @return FieldSet
	 */
	function updateCMSFields(&$fields) {
		if($group = $this->owner->getMerchantAccountGroup()) {
			$fields->addFieldToTab("Root.Organisation", new ReadonlyField("OrganisationName", _t("EcommerceMerchantAccount.WORKSFOR", "Works For"),$group->CombinedMerchantGroupName()));
		}
		$fields->addFieldToTab("Root.Organisation", new CheckboxField("ApprovalEmailSent", _t("EcommerceMerchantAccount.APPROVALEMAILSENT", "Approval Email Sent")));
		$fields->removeByName("Password");
		return $fields;
	}

	/**
	 * Tells us whether this member is allowed to purchase a product.
	 * NOTE: it returns TRUE (can purchase) if no approved customer group
	 * has been setup yet.
	 * @return Boolean
	 */
	public function isApprovedMerchantCustomer() {
		$outcome = false;
		if(!$this->owner->exists()) {
			return false;
		}
		$merchantGroup = EcommerceMerchantGroupDecorator::get_approved_merchant_group();
		if($merchantGroup) {
			if($this->owner->inGroup($merchantGroup, false)) {
				//exception - customer is merchant
				$outcome = true;

			}
			elseif($this->owner->IsShopAdmin() || $this->owner->IsAdmin()) {
				//exception - administrator
				$outcome = true;
			}
			else {
				//return false;
			}
		}
		else {
			//exception - Group not setup yet.
			$outcome = true;
		}
		//standard answer....
		return $outcome;
	}

	/**
	 * returns the MOST LIKELY (!) company or Merchant Account Group of the current member
	 * @return NULL | Group (object)
	 */
	function MerchantAccountGroup(){return $this->owner->getMerchantAccountGroup();}
	function getMerchantAccountGroup() {
		$groupArray = array();
		if($this->owner->exists()) {
			if($this->owner->isApprovedMerchant()) {
				$groups = $this->owner->Groups();
				if($groups && $groups->count()) {
					foreach($groups as $group) {
						//it is a Merchant account (business)
						if($group->isMerchantAccount()) {
							//if therer are two at the same level of the hierarchy, then we just take one!
							$groupArray[$group->numberOfParentGroups()] = $group;
						}
					}
				}
			}
		}
		//we prefer a "front-line" security group (more specific)
		if(count($groupArray)) {
			krsort($groupArray);
			foreach($groupArray as $group) {
				return $group;
			}
		}
	}

	/**
	 * standard SS Method
	 * Sends an email to the member letting her / him know that the merchant has been approved.
	 */
	function onAfterWrite(){
		if($this->owner->isApprovedMerchant()) {
			if(!$this->owner->ApprovalEmailSent) {
				$config = SiteConfig::current_site_config();
				$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
				$email = new Email();
				$email->setTo($this->owner->Email);
				$email->setSubject(_t("EcommerceMerchant.ACCOUNTAPPROVEDFOR", "Merchant Account approved for "). $config->Title);
				$email->setBcc(Order_Email::get_from_email());
				$email->setTemplate('EcommerceMerchantGroupApprovalEmail');
				$email->populateTemplate(array(
					'SiteConfig'       => $config,
					'EcommerceConfig'  => $ecommerceConfig,
					'Member'           => $this->owner
				));
				$email->send();
				$this->owner->ApprovalEmailSent = 1;
				$this->owner->write();
			}
		}
	}

}
