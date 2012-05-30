<?php

class EcommerceCorporateGroupGroupDecorator extends DataObjectDecorator {

	/**
	 * code for the corporate customer group
	 * @var String
	 */
	protected static $code = "approvedshopcustomers";
		static function set_code($s) {self::$code = $s;}
		static function get_code() {return self::$code;}

	/**
	 * name for the corporate customer group
	 * @var String
	 */
	protected static $name = "approved shop customers";
		static function set_name($s) {self::$name = $s;}
		static function get_name() {return self::$name;}

	/**
	 * permission code for the corporate customer group
	 * @var String
	 */
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

	/**
	 * address types
	 * @var array
	 */
	protected static $address_types = array(
		'Physical' => "Phyical Address",
		'Postal' => "Postal Address"
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
		'Country' => 'Varchar(3)',
		'Phone' => 'Varchar'
	);

	function extraStatics() {
		foreach(self::$address_types as $fieldGroupPrefix => $fieldGroupTitle) {
			foreach(self::$address_fields as $name => $field) {
				$db[$fieldGroupPrefix.$name] = $field;
			}
		}
		return array('db' => $db);
	}


	/**
	 * Combines all group names up to the corporate group holder
	 * @return TextField
	 */
	function CombinedCorporateGroupName(){
		$string = implode(" ", $this->CombinedCorporateGroupNameAsArray());
		return DBField::create('Text',$string);
	}

	/**
	 * Combines all group names up to the corporate group holder
	 * @return Array
	 */
	public function CombinedCorporateGroupNameAsArray(){
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
		return array_reverse($array);
	}

	/**
	 * Standard SS method
	 *
	 */
	function updateCMSFields(FieldSet &$fields) {
		if($this->owner->isCorporateAccount()) {
			$fields->addFieldsToTab('Root.Addresses', $this->CorporateAddressFieldsArray());
		}
	}

	/**
	 *
	 * @return Array
	 */
	public function CorporateAddressFieldsArray(){
		$fields = array();
		foreach(self::$address_types as $fieldGroupPrefix => $fieldGroupTitle) {
			$fields[] = new HeaderField("CombinedCorporateGroupName", $this->CombinedCorporateGroupName()->XML());
			$fields[] = new HeaderField($fieldGroupPrefix, $fieldGroupTitle);
			foreach(self::$address_fields as $name => $field) {
				$fieldClass = 'TextField';
				if($field == 'Text') {
					$fieldClass = 'TextareaField';
				}
				elseif($name == 'Country') {
					$fieldClass = 'CountryDropdownField';
				}
				$fields[] = new $fieldClass($fieldGroupPrefix.$name, $name);
			}
		}
		return $fields;
	}

	/**
	 * Is the current group part of the corporate account
	 * @return Boolean
	 */
	public function isCorporateAccount() {
		$companyGroup = self::get_approved_customer_group();
		if($companyGroup) {
			if($this->owner->ID && $this->owner->ParentID && $this->owner->ID != $companyGroup->ID) {
				if($this->owner->ParentID == $companyGroup->ID) {
					return true;
				}
				else {
					if($parent = DataObject::get_by_id("Group", $this->owner->ParentID)) {
						return $parent->isCorporateAccount();
					}
				}
			}
		}
		return false;
	}

	public function requireDefaultRecords() {
		if(self::get_approved_customer_group()) {
			$task = CreateEcommerceApprovedCustomerGroup();
			$task->run();
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
