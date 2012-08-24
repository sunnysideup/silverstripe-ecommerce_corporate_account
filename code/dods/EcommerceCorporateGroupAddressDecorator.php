<?php

/**
 * adds functionality for order address
 *
 * @author nicolaas
 */


class EcommerceCorporateGroupAddressDecorator extends DataObjectDecorator {


	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
			'db' => array(
				'DataMovedFromOrganisationToAddress' => 'Boolean'
			),
			'has_one' => array(
				'Organisation' => 'Group'
			)
		);
	}

	/**
	 * Do we update the company (group) after the order address is saved?
	 * @var Boolean
	 */
	protected static $update_group_from_order_address = true;
		static function set_update_group_from_order_address($b) {self::$update_group_from_order_address = $b;}
		static function get_update_group_from_order_address() {return self::$update_group_from_order_address;}


	/**
	 * Do we update the update the order address from the group (company)
	 * @var Boolean
	 */
	protected static $update_order_address_from_group = true;
		static function set_update_order_address_from_group($b) {self::$update_order_address_from_group = $b;}
		static function get_update_order_address_from_group() {return self::$update_order_address_from_group;}

	/**
	 * standard SS method
	 * @return FieldSet
	 */
	function updateCMSFields(&$fields) {
		if($group = DataObject::get_by_id("Group", $this->owner->OrganisationID)){
			$organisationField = $fields->dataFieldByName("OrganisationID")->performReadonlyTransformation();
			$organisationField->setTitle(_t("EcommerceCorporateAccount.FOR", "For"));
			$fields->replaceField("OrganisationID", $organisationField);
			$fields->addFieldToTab("Root."._t("EcommerceCorporateAccount.FORTAB", "for"), $organisationField);
			$fields->removeByName("DataMovedFromOrganisationToAddress");
		}
		return $fields;
	}

	/**
	 * When creating the address, we grab the details from the company.
	 * to pre-populate the data
	 */
	function populateDefaults(){
		$this->owner->moveAddress();
	}

	/**
	 * Standard SS Method
	 * When saving the data, we update the company details.
	 */
	function onBeforeWrite(){
		if($group = $this->owner->relatedGroup()) {
			$this->owner->OrganisationID = $group->ID;
		}
	}

	/**
	 * Standard SS Method
	 * When saving the data, we update the company details AND/OR the order address
	 */
	function onAfterWrite(){
		$this->owner->moveAddress();
	}

	/**
	 *
	 * returns the related group (company or corporate account) - if any
	 * @return Group | Null
	 */
	public function relatedGroup(){
		if($member = $this->owner->getMemberFromOrder()) {
			return $member->getCorporateAccountGroup();
		}
	}

	/**
	 * move address from group to order address
	 * move address from order address back to group
	 */
	public function moveAddress(){
		if($this->owner->DataMovedFromOrganisationToAddress) {
			if(self::get_update_group_from_order_address()) {
				if($group = $this->owner->relatedGroup()) {
					if($this->owner instanceOf ShippingAddress) {
						$group->PhysicalAddress = $this->owner->ShippingAddress;
						$group->PhysicalAddress2 = "";
						$group->PhysicalSuburb = $this->owner->ShippingAddress2;
						$group->PhysicalTown = $this->owner->ShippingCity;
						$group->PhysicalPostalCode = $this->owner->ShippingPostalCode;
						$group->PhysicalCountry = $this->owner->ShippingCountry;
						$group->PhysicalPhone = $this->owner->ShippingPhone;
					}
					elseif($this->owner instanceOf BillingAddress) {
						$group->PostalAddress = $this->owner->Address;
						$group->PostalAddress2 = "";
						$group->PostalSuburb = $this->owner->Address2;
						$group->PostalTown = $this->owner->City;
						$group->PostalPostalCode = $this->owner->PostalCode;
						$group->PostalCountry = $this->owner->Country;
						$group->PostalPhone = $this->owner->Phone;
					}
					else {
						user_error("unknown address type", E_USER_WARNING);
					}
					$group->write();
				}
			}
		}
		else {
			if(self::get_update_order_address_from_group()) {
				if($group = $this->relatedGroup()) {
					if($this->owner instanceOf ShippingAddress) {
						$this->owner->ShippingAddress = $group->PhysicalAddress." ".$group->PhysicalAddress2;
						$this->owner->ShippingAddress2 = $group->PhysicalSuburb;
						$this->owner->ShippingCity = $group->PhysicalTown;
						$this->owner->ShippingPostalCode = $group->PhysicalPostalCode;
						$this->owner->ShippingCountry = $group->PhysicalCountry;
						$this->owner->ShippingPhone = $group->PhysicalPhone;
					}
					elseif($this->owner instanceOf BillingAddress) {
						$this->owner->Address = $group->PostalAddress." ".$group->PostalAddress2;
						$this->owner->Address2 = $group->PostalSuburb;
						$this->owner->City = $group->PostalTown;
						$this->owner->PostalCode = $group->PostalPostalCode;
						$this->owner->Country = $group->PostalCountry;
						$this->owner->Phone = $group->PostalPhone;
					}
					else {
						user_error("unknown address type", E_USER_WARNING);
					}
				}
			}
			if($group) {
				$this->owner->DataMovedFromOrganisationToAddress = 1;
				$this->owner->write();
			}
		}
	}

}
