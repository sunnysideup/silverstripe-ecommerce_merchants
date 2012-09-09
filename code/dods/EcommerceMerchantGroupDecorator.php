<?php


/**
 * adds functionality for groups (organisations)
 * The key here is that we have one MAIN merchant group and all
 * groups underneath this one (child groups) are automatically merchants.
 * @author nicolaas
 * @TODO: move fields to proper system with field labels, etc....
 * @See EcommerceConfigDB for a good example
 */

class EcommerceMerchantGroupDecorator extends DataObjectDecorator {

	/**
	 * code for the Merchant customer group
	 * @var String
	 */
	protected static $code = "merchants";
		static function set_code($s) {self::$code = $s;}
		static function get_code() {return self::$code;}

	/**
	 * name for the Merchant customer group
	 * @var String
	 */
	protected static $name = "merchants";
		static function set_name($s) {self::$name = $s;}
		static function get_name() {return self::$name;}

	/**
	 * permission code for the Merchant customer group
	 * @var String
	 */
	protected static $permission_code = "MERCHANTS";
		static function set_permission_code($s) {self::$permission_code = $s;}
		static function get_permission_code() {return self::$permission_code;}

	/**
	 *@return DataObject (Group)
	 **/
	public static function get_approved_merchant_group() {
		$merchantCode = self::get_code();
		$merchantName = self::get_name();
		return DataObject::get_one("Group","\"Code\" = '".$merchantCode."' OR \"Title\" = '".$merchantName."'");
	}

	/**
	 * address types
	 * @var array
	 */
	protected static $address_types = array(
		'Postal' => "Billing Address (Postal)",
		'Pickup' => "Pickup Address (Physical)"
	);

	/**
	 * fields per address type
	 * @var array
	 */
	protected static $address_fields = array(
		'Address' => 'Text',
		'Address2' => 'Text',
		'Suburb' => 'Varchar',
		'Town' => 'Varchar',
		'PostalCode' => 'Varchar',
		'Country' => 'Varchar(3)',
		'Phone' => 'Varchar',
		'Fax' => 'Varchar'
	);

	function extraStatics() {
		foreach(self::$address_types as $fieldGroupPrefix => $fieldGroupTitle) {
			foreach(self::$address_fields as $name => $field) {
				$db[$fieldGroupPrefix.$name] = $field;
			}
		}
		return array(
			'db' => $db,
			'casting' => array(
				'CombinedMerchantGroupName' => 'Text'
			)
		);
	}


	/**
	 * Combines all group names up to the Merchant group holder
	 * @return TextField
	 */
	function CombinedMerchantGroupName(){return $this->owner->getCombinedMerchantGroupName();}
	function getCombinedMerchantGroupName(){
		$string = implode(" ", $this->owner->CombinedMerchantGroupNameAsArray());
		return $string;
	}

	/**
	 * Combines all group names up to the Merchant group holder
	 * @return Array
	 */
	public function CombinedMerchantGroupNameAsArray(){
		$array = array();
		if($this->owner->isMerchantAccount()) {
			$array[] = $this->owner->Title;
		}
		$approvedMerchantGroup = self::get_approved_customer_group();
		if($approvedMerchantGroup) {
			$item = $this->owner;
			$n = 0;
			while($item && $n < 99) {
				$item = DataObject::get_by_id("Group", $item->ParentID);
				if(!$item->owner->isMerchantAccount()) {
					$item = null;
				}
				elseif($item->ID != $approvedMerchantGroup->ID) {
					$array[] = $item->Title;
				}
				$n++;
			}
		}
		return array_reverse($array);
	}

	/**
	 * Standard SS method
	 *
	 */
	function updateCMSFields(FieldSet &$fields) {
		if($this->owner->isMerchantAccount()) {
			$fields->addFieldsToTab('Root.Addresses', $this->owner->MerchantAddressFieldsArray($forCMS = true));
			$header = _t("EcommerceMerchantGroup.NOTAPPROVEDACCOUNT", "NB: This is an approved merchant group.");
		}
		else {
			$header = _t("EcommerceMerchantGroup.NOTAPPROVEDACCOUNT", "NB: This is NO approved merchant group");
		}
		$fields->addFieldToTab('Root.Members', new HeaderField("ApprovedMerchantGroup", $header), "Title");
	}

	/**
	 * returns an array of fields for the Merchant account
	 * @return Array
	 */
	public function MerchantAddressFieldsArray($forCMS = false){
		$fields = array();
		if($this->owner->Title != $this->owner->CombinedMerchantGroupName()) {
			$fields[] = new ReadOnlyField("CombinedMerchantGroupName",_t("EcommerceMerchantGroup.FULLNAME", "Full Name") , $this->owner->CombinedMerchantGroupName());
		}
		foreach(self::$address_types as $fieldGroupPrefix => $fieldGroupTitle) {
			if($forCMS) {
				$fields[] = new HeaderField($fieldGroupPrefix."Header", $fieldGroupTitle, 4);
			}
			else {
				$composite = new CompositeField();
				$composite->setID($fieldGroupPrefix);
				$composite->push(new HeaderField($fieldGroupPrefix."Header", $fieldGroupTitle, 4));
			}
			foreach(self::$address_fields as $name => $field) {
				$fieldClass = 'TextField';
				if($field == 'Text') {
					$fieldClass = 'TextareaField';
				}
				elseif($name == 'Country') {
					$fieldClass = 'CountryDropdownField';
				}
				if($forCMS) {
					$fields[] =  new $fieldClass($fieldGroupPrefix.$name, $name);
				}
				else {
					$composite->push(new $fieldClass($fieldGroupPrefix.$name, $name));
				}
			}
			if($forCMS) {
				//
			}
			else {
				$fields[] = $composite;
			}
		}
		return $fields;
	}

	/**
	 * Is the current group part of the Merchant account?
	 * @return Boolean
	 */
	public function isMerchantAccount() {
		if($this->owner->exists()) {
			$approvedMerchantGroup = self::get_approved_merchant_group();
			if($approvedMerchantGroup) {
				if($this->owner->ParentID) {
					if($this->owner->ParentID == $approvedMerchantGroup->ID || $this->owner->ID = $approvedMerchantGroup->ID) {
						return true;
					}
					elseif($parent = DataObject::get_by_id("Group", $this->owner->ParentID)) {
						return $parent->isMerchantAccount();
					}
				}
			}
			else {
				user_error("No approved merchant group has been setup", E_USER_NOTICE);
			}
		}
		return false;
	}

	/**
	 * returns the level in the hierarchy
	 * 0 = no parents
	 * 12 = twelve parent groups
	 * Max of 99... just in case.
	 * @return Int
	 */
	public function NumberOfParentGroups(){
		$n = 0 ;
		$item = $this->owner;
		while($item && $n < 99) {
			$item = DataObject::get_by_id("Group", $item->ParentID);
			$n++;
		}
		return $n;
	}

	/**
	 * applies the details of the parent company to the child company
	 * UNLESS the details for the child company are already set.
	 * @author: Nicolaas
	 */
	public function onAfterWrite() {
		$statics = $this->extraStatics();
		$fields = $statics["db"];
		if($this->owner->isMerchantAccount()) {
			if($childGroup = DataObject::get_one("Group", "\"ParentID\" = ".$this->owner->ID)) {
				$write = false;
				foreach($fields as $field) {
					$update = false;
					if(!isset($childGroup->$field)) {
						$update = true;
					}
					elseif(!$childGroup->$field) {
						$update = true;
					}
					if($update) {
						$childGroup->$field = $this->owner->$field;
						$write = true;
					}
				}
				if($write) {
					$childGroup->write();
				}
			}
		}
	}


}
