<?php

class MerchantGroupDOD extends DataObjectDecorator {

	static $main_group = 'Merchants';

	static function get_main_group() {
		$group = DataObject::get_one('Group', "Code = '" . strtolower(self::$main_group) . "'");
		if(! $group) {
			$group = new Group(array('Title' => self::$main_group));
			$group->write();
		}
		return $group;
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->owner->ID && $this->owner->Code == strtolower(self::$main_group)) {
			$newMerchants = new ComplexTableField(
				Controller::has_curr() ? Controller::curr() : new Controller(),
				'NewMembers',
				'Member'
			);
			$newMerchants->setCustomSourceItems(self::get_new_merchants());
			$fields->addFieldToTab('Root', new Tab(_t('MerchantGroupDOD.NEWMERCHANTS', 'New Merchants'), $newMerchants), 'Members');
			$fields->findOrMakeTab('Root.Members')->setTitle(_t('MerchantGroupDOD.ALLMERCHANTS', 'All Merchants'));
			$fields->removeByName('Title');
		}
	}

	static function get_approved_groups() {
		$group = self::get_main_group();
		return $group->getAllChildren();
	}

	static function get_new_merchants() {
		$group = self::get_main_group();
		$newMerchants = $group->Members()->toArray();
		$approvedGroups = self::get_approved_groups();
		foreach($newMerchants as $index => $member) {
			if($member->inGroups($approvedGroups)) {
				unset($newMerchants[$index]);
			}
		}
		return new DataObjectSet($newMerchants);
	}
}
