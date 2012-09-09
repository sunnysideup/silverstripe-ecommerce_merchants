<?php

class CreateEcommerceMerchantGroup extends BuildTask{

	protected $title = "Create E-commerce Merchants";

	protected $description = "Create the member group for merchants";

	/**
	 * run the task
	 */
	function run($request){
		$merchantCustomerGroup = EcommerceMerchantGroupDecorator::get_merchant_group();
		$approveCustomerPermissionCode = EcommerceMerchantGroupDecorator::get_permission_code();
		if(!$merchantCustomerGroup) {
			$merchantCustomerGroup = new Group();
			$merchantCustomerGroup->Code = EcommerceMerchantGroupDecorator::get_code();
			$merchantCustomerGroup->Title = EcommerceMerchantGroupDecorator::get_name();
			//$merchantCustomerGroup->ParentID = $parentGroup->ID;
			$merchantCustomerGroup->write();
			Permission::grant( $merchantCustomerGroup->ID, $approveCustomerPermissionCode);
			DB::alteration_message(EcommerceMerchantGroupDecorator::get_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$merchantCustomerGroup->ID."' AND \"Code\" LIKE '".$approveCustomerPermissionCode."'")->numRecords() == 0 ) {
			Permission::grant($merchantCustomerGroup->ID, $approveCustomerPermissionCode);
			DB::alteration_message(EcommerceMerchantGroupDecorator::get_name().' permissions granted',"created");
		}
		$merchantCustomerGroup = EcommerceMerchantGroupDecorator::get_approved_merchant_group();
		if(!$merchantCustomerGroup) {
			user_error("could not create user group");
		}
		else {
			DB::alteration_message(EcommerceMerchantGroupDecorator::get_name().' is ready for use',"created");
		}
	}

}

class CreateEcommerceMerchantGroup_SortGroups extends BuildTask{


	protected $title = "Sorts Merchant Groups Alphabetically";

	protected $description = "Goes through each merchant group and resorts based on the title";

	/**
	 * run the task
	 */
	function run($request){
		$merchantCustomerGroup = EcommerceMerchantGroupDecorator::get_approved_merchant_group();
		if($merchantCustomerGroup) {
			$groups = DataObject::get("Group", "ParentID = ".$merchantCustomerGroup->ID, "\"Title\" ASC");
			$sort = 0;
			foreach($groups as $group) {
				$sort = $sort+10;
				$group->Sort = $sort;
				$group->write();
			}
		}
	}

}


class CreateEcommerceMerchantGroup_AdminDecorator extends Extension{

	static $allowed_actions = array(
		"createecommercemerchantgroup" => true,
		"createecommercemerchantgroup_sortgroups" => true
	);

	function updateEcommerceDevMenuEcommerceSetup(&$buildTasks){
		$buildTasks[] = "createecommercemerchantgroup";
		$buildTasks[] = "createecommercemerchantgroup_sortgroups";
		//$buildTasks[] = "deleteobsoletemoduleowners";
		return $buildTasks;
	}


	/**
	 * executes build task: CreateEcommerceMerchantGroup
	 *
	 */
	public function createecommercemerchantgroup_sortgroups($request) {
		$buildTask = new CreateEcommerceMerchantGroup($request);
		$buildTask->run($request);
		$this->owner->displayCompletionMessage($buildTask);
	}

	/**
	 * executes build task: CreateEcommerceMerchantGroup_SortGroups
	 *
	 */
	public function createecommercemerchantgroup_sortgroups_sortgroups($request) {
		$buildTask = new CreateEcommerceMerchantGroup_SortGroups($request);
		$buildTask->run($request);
		$this->owner->displayCompletionMessage($buildTask);
	}



}

