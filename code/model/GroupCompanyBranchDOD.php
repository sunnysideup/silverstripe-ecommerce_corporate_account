<?php

class GroupCompanyBranchDOD extends DataObjectDecorator {

	static $address_types = array('Physical', 'Postal');

	static $address_fields = array(
		'Address' => 'Text',
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
	function isCorporateAccount() {
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

	function requireDefaultRecords() {
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


}
