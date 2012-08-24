<?php


/**
 * adds functionality for groups (organisations)
 * The key here is that we have one approved group and all
 * groups underneath this one (child groups) are automatically approved.
 * @author nicolaas
 * @TODO: move fields to proper system with field labels, etc....
 * @See EcommerceConfigDB for a good example
 */

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
		'Postal' => "Billing Address (Postal)",
		'Physical' => "Delivery Address (Physical)"
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
				'CombinedCorporateGroupName' => 'Text'
			)
		);
	}


	/**
	 * Combines all group names up to the corporate group holder
	 * @return TextField
	 */
	function CombinedCorporateGroupName(){return $this->owner->getCombinedCorporateGroupName();}
	function getCombinedCorporateGroupName(){
		$string = implode(" ", $this->owner->CombinedCorporateGroupNameAsArray());
		return $string;
	}

	/**
	 * Combines all group names up to the corporate group holder
	 * @return Array
	 */
	public function CombinedCorporateGroupNameAsArray(){
		$array = array();
		if($this->owner->isCorporateAccount()) {
			$array[] = $this->owner->Title;
		}
		$approvedCustomerGroup = self::get_approved_customer_group();
		if($approvedCustomerGroup) {
			$item = $this->owner;
			$n = 0;
			while($item && $n < 99) {
				$item = DataObject::get_by_id("Group", $item->ParentID);
				if(!$item->owner->isCorporateAccount()) {
					$item = null;
				}
				elseif($item->ID != $approvedCustomerGroup->ID) {
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
		if($this->owner->isCorporateAccount()) {
			$fields->addFieldsToTab('Root.Addresses', $this->owner->CorporateAddressFieldsArray($forCMS = true));
			$header = _t("EcommerceCorporateGroup.NOTAPPROVEDACCOUNT", "NB: This is an approved account group.");
		}
		else {
			$header = _t("EcommerceCorporateGroup.NOTAPPROVEDACCOUNT", "NB: This is NOT an approved account group");
		}
		$fields->addFieldToTab('Root.Members', new HeaderField("ApprovedAccountGroup", $header), "Title");
	}

	/**
	 * returns an array of fields for the corporate account
	 * @return Array
	 */
	public function CorporateAddressFieldsArray($forCMS = false){
		$fields = array();
		if($this->owner->Title != $this->owner->CombinedCorporateGroupName()) {
			$fields[] = new ReadOnlyField("CombinedCorporateGroupName",_t("EcommerceCorporateGroup.FULLNAME", "Full Name") , $this->owner->CombinedCorporateGroupName());
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
	 * Is the current group part of the corporate account?
	 * @return Boolean
	 */
	public function isCorporateAccount() {
		if($this->owner->exists()) {
			$approvedCustomerGroup = self::get_approved_customer_group();
			if($approvedCustomerGroup) {
				if($this->owner->ParentID) {
					if($this->owner->ParentID == $approvedCustomerGroup->ID || $this->owner->ID = $approvedCustomerGroup->ID) {
						return true;
					}
					elseif($parent = DataObject::get_by_id("Group", $this->owner->ParentID)) {
						return $parent->isCorporateAccount();
					}
				}
			}
			else {
				user_error("No approved customer group has been setup", E_USER_NOTICE);
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
		if($this->owner->isCorporateAccount()) {
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
