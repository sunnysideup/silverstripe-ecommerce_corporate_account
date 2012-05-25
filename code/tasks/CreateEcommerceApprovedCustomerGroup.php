<?php

class CreateEcommerceApprovedCustomerGroup extends BuildTask{

	protected $title = "Create E-commerce Approved Customers";

	protected $description = "Create the member group for approved customers";

	protected static $code = "approvedshopcustomers";
		static function set_code($s) {self::$code = $s;}
		static function get_code() {return self::$code;}

	protected static $name = "approved shop customers";
		static function set_name($s) {self::$name = $s;}
		static function get_name() {return self::$name;}

	protected static $permission_code = "APPROVEDSHOPCUSTOMER";
		static function set_permission_code($s) {self::$permission_code = $s;}
		static function get_permission_code() {return self::$permission_code;}

	/**
	 *@return DataObject (Group)
	 **/
	public static function get_approved_customer_group() {
		$customerCode = self::get_code();
		$customerName = self::get_name();
		return DataObject::get_one("Group","\"Code\" = '".$customerCode."' OR \"Title\" = '".$customerName."'");
	}


	function run($request){
		$parentGroup = EcommerceRole::get_customer_group();
		if($parentGroup) {
			$approveCustomerGroup = self::get_approved_customer_group();
			$approveCustomerPermissionCode = self::get_permission_code();
			if(!$approveCustomerGroup) {
				$approveCustomerGroup = new Group();
				$approveCustomerGroup->Code = self::get_code();
				$approveCustomerGroup->Title = self::get_name();
				//$approveCustomerGroup->ParentID = $parentGroup->ID;
				$approveCustomerGroup->write();
				Permission::grant( $approveCustomerGroup->ID, $approveCustomerPermissionCode);
				DB::alteration_message(self::get_name().' Group created',"created");
			}
			elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$approveCustomerGroup->ID."' AND \"Code\" LIKE '".$approveCustomerPermissionCode."'")->numRecords() == 0 ) {
				Permission::grant($approveCustomerGroup->ID, $approveCustomerPermissionCode);
				DB::alteration_message(self::get_name().' permissions granted',"created");
			}
			$approveCustomerGroup = self::get_approved_customer_group();
			if(!$approveCustomerGroup) {
				user_error("could not create user group");
			}
			else {
				DB::alteration_message(self::get_name().' is ready for use',"created");
			}
		}
		else {
			DB::alteration_message('Customer group does not exist',"deleted");
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

