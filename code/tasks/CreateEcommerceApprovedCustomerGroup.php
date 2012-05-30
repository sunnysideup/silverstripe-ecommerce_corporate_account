<?php

class CreateEcommerceApprovedCustomerGroup extends BuildTask{

	protected $title = "Create E-commerce Approved Customers";

	protected $description = "Create the member group for approved customers";

	/**
	 * run the task
	 */
	function run($request){
		$approveCustomerGroup = EcommerceCorporateGroupGroupDecorator::get_approved_customer_group();
		$approveCustomerPermissionCode = EcommerceCorporateGroupGroupDecorator::get_permission_code();
		if(!$approveCustomerGroup) {
			$approveCustomerGroup = new Group();
			$approveCustomerGroup->Code = EcommerceCorporateGroupGroupDecorator::get_code();
			$approveCustomerGroup->Title = EcommerceCorporateGroupGroupDecorator::get_name();
			//$approveCustomerGroup->ParentID = $parentGroup->ID;
			$approveCustomerGroup->write();
			Permission::grant( $approveCustomerGroup->ID, $approveCustomerPermissionCode);
			DB::alteration_message(EcommerceCorporateGroupGroupDecorator::get_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$approveCustomerGroup->ID."' AND \"Code\" LIKE '".$approveCustomerPermissionCode."'")->numRecords() == 0 ) {
			Permission::grant($approveCustomerGroup->ID, $approveCustomerPermissionCode);
			DB::alteration_message(EcommerceCorporateGroupGroupDecorator::get_name().' permissions granted',"created");
		}
		$approveCustomerGroup = EcommerceCorporateGroupGroupDecorator::get_approved_customer_group();
		if(!$approveCustomerGroup) {
			user_error("could not create user group");
		}
		else {
			DB::alteration_message(EcommerceCorporateGroupGroupDecorator::get_name().' is ready for use',"created");
		}
	}

}

class CreateEcommerceApprovedCustomerGroup_SortGroups extends BuildTask{


	protected $title = "Sorts Approved Customer Groups Alphabetically";

	protected $description = "Goes through each approved customer group and resorts based on the title";

	/**
	 * run the task
	 */
	function run($request){
		$approveCustomerGroup = EcommerceCorporateGroupGroupDecorator::get_approved_customer_group();
		if($approveCustomerGroup) {
			$groups = DataObject::get("Group", "ParentID = ".$approveCustomerGroup->ID, "\"Title\" ASC");
			$sort = 0;
			foreach($groups as $group) {
				$sort = $sort+10;
				$group->Sort = $sort;
				$group->write();
			}
		}
	}

}


class CreateEcommerceApprovedCustomerGroup_AdminDecorator extends Extension{

	static $allowed_actions = array(
		"createecommerceapprovedcustomergroup" => true,
		"createecommerceapprovedcustomergroup_sortgroups" => true
	);

	function updateEcommerceDevMenuEcommerceSetup(&$buildTasks){
		$buildTasks[] = "createecommerceapprovedcustomergroup";
		$buildTasks[] = "createecommerceapprovedcustomergroup_sortgroups";
		//$buildTasks[] = "deleteobsoletemoduleowners";
		return $buildTasks;
	}


	/**
	 * executes build task: ImportModulesTask
	 *
	 */
	public function createecommerceapprovedcustomergroup($request) {
		$buildTask = new CreateEcommerceApprovedCustomerGroup($request);
		$buildTask->run($request);
		$this->owner->displayCompletionMessage($buildTask);
	}

	/**
	 * executes build task: CreateEcommerceApprovedCustomerGroup_SortGroups
	 *
	 */
	public function createecommerceapprovedcustomergroup_sortgroups($request) {
		$buildTask = new CreateEcommerceApprovedCustomerGroup_SortGroups($request);
		$buildTask->run($request);
		$this->owner->displayCompletionMessage($buildTask);
	}



}

