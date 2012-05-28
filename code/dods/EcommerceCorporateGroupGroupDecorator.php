<?php

class EcommerceCorporateGroupGroupDecorator extends DataObjectDecorator {

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

	protected static $address_types = array('Physical', 'Postal');

	protected static $address_fields = array(
		'Address' => 'Text',
		'Address2' => 'Text',
		'Suburb' => 'Varchar',
		'Town' => 'Varchar',
		'Country' => 'Varchar(3)',
		'Phone' => 'Varchar'
	);

	protected static $company_group_title = 'Companies';
		public static function set_company_group_title($s){self::$company_group_title = $s;}
		public static function get_company_group_title(){return self::$company_group_title;}

	static $company_group_code = 'companies';
		public static function set_company_group_code($s){self::$company_group_code = $s;}
		public static function get_company_group_code(){return self::$company_group_code;}

	public static function get_company_group() {
		$code = self::get_company_group_code();
		return DataObject::get_one('Group', "\"Code\" = '$code'");
	}

	function extraStatics() {
		foreach(self::$address_types as $type) {
			foreach(self::$address_fields as $name => $field) {
				$db["$type$name"] = $field;
			}
		}
		return array('db' => $db);
	}

	function CombinedCorporateGroupName(){
		$array = array();
		if($this->isCorporateAccount()) {
			$array[] = $this->owner->Title;
		}
		if($this->owner->ParentID) {
			$parent = DataObject::get_by_id("Group", $this->owner->ParentID);
			if($parent && $parent->isCorporateAccount()) {
				$array[] = $parent->Title;
				if($parent->ParentID) {
					$grandParent = DataObject::get_by_id("Group", $parent->ParentID);
					if($grandParent && $grandParent->isCorporateAccount()) {
						$array[] = $grandParent->Title;
					}
				}
			}
		}
		$reverseArray = array_reverse($array);
		return implode(" ", $reverseArray);
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->owner->isCorporateAccount()) {
			foreach(self::$address_types as $type) {
				$cmsFields[] = new HeaderField($type);
				foreach(self::$address_fields as $name => $field) {
					$fieldClass = 'TextField';
					if($field == 'Text') {
						$fieldClass = 'TextareaField';
					}
					elseif($name == 'Country') {
						$fieldClass = 'CountryDropdownField';
					}
					$cmsFields[] = new $fieldClass("$type$name", $name);
				}
			}
			$fields->addFieldsToTab('Root.Addresses', $cmsFields);
		}
	}

	/**
	 * Is the current group part of the corporate account
	 * @return Boolean
	 */
	public function isCorporateAccount() {
		$companyGroup = self::get_company_group();
		if($companyGroup) {
			if($this->owner->ID && $this->owner->ParentID && $this->owner->ID != $companyGroup->ID) {
				if($this->owner->ParentID == $companyGroup->ID) {
					return true;
				}
				else {
					$parent = $this->owner->Parent();
					return $parent->isCorporateAccount();
				}
			}
		}
	}

	public function requireDefaultRecords() {
		$group = self::get_company_group();
		if(! $group) {
			$group = new Group(array(
				'Title' => self::$company_group_title,
				'Description' => 'Customers hierarchy of customers by companies and their branches',
				'Code' => self::get_company_group_code()
			));
			$group->write();
			DB::alteration_message('New companies group created', 'created');
		}
	}

	/**
	 * applies the details of the parent company to the child company
	 * UNLESS the details for the child company are already set.
	 * @author: Nicolaas
	 */
	public function onAfterWrite() {
		$statics = $this->extraStatics();
		$fields = $statics["db"];
		if($this->isCorporateAccount()) {
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