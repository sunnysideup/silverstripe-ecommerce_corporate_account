<?php

class GroupCompanyBranchDOD extends DataObjectDecorator {
	
	static $address_types = array('Physical', 'Postal');
	
	static $address_fields = array(
		'Address' => 'Text',
		'Suburb' => 'Varchar',
		'Town' => 'Varchar',
		'Country' => 'Varchar(2)',
		'Phone' => 'Varchar'
	);
	
	static $company_group_title = 'Companies';
	
	function extraStatics() {
		foreach(self::$address_types as $type) {
			foreach(self::$address_fields as $name => $field) {
				$db["$type$name"] = $field;
			}
		}
		return array('db' => $db);
	}
	
	function updateCMSFields(FieldSet &$fields) {
		if($this->owner->isCompanyBranchGroup()) {
			foreach(self::$address_types as $type) {
				$cmsFields[] = new HeaderField($type);
				foreach(self::$address_fields as $name => $field) {
					$fieldClass = 'TextField';
					if($field == 'Text') $fieldClass = 'TextareaField';
					elseif($field == 'Varchar(2)') $fieldClass = 'CountryDropdownField';
					$cmsFields[] = new $fieldClass("$type$name", $name);
				}
			}
			$fields->addFieldsToTab('Root.Addresses', $cmsFields);
		}
	}
	
	function isCompanyBranchGroup() {
		$companyGroup = self::get_company_group();
		if($this->owner->ID && $this->owner->ParentID && $this->owner->ID != $companyGroup->ID) {
			if($this->owner->ParentID != $companyGroup->ID) {
				$parent = $this->owner->Parent();
				return $parent->isCompanyBranchGroup();
			}
			return true;
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
	
	static function get_company_group_code() {
		return strtolower(self::$company_group_title);
	}
	
	static function get_company_group() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$code = self::get_company_group_code();
		return DataObject::get_one('Group', "{$bt}ParentID{$bt} = 0 AND {$bt}Code{$bt} = '$code'");
	}
}