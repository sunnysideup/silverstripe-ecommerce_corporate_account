<?php

class CreateEcommerceApprovedCustomerGroup extends BuildTask{

	protected $title = "Create E-commerce Approved Customers";

	protected $description = "Create the member group for approved customers";

	/**
	 *@return DataObject (Group)
	 **/
	public static function get_approved_customer_group() {
		$customerCode = EcommerceCorporateGroupGroupDecorator::get_code();
		$customerName = EcommerceCorporateGroupGroupDecorator::get_name();
		return DataObject::get_one("Group","\"Code\" = '".$customerCode."' OR \"Title\" = '".$customerName."'");
	}

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



class CreateEcommerceApprovedCustomerGroup_AdminDecorator extends Extension{

	static $allowed_actions = array(
		"createecommerceapprovedcustomergroup" => true
	);

	function updateEcommerceDevMenuEcommerceSetup(&$buildTasks){
		$buildTasks[] = "createecommerceapprovedcustomergroup";
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



}

