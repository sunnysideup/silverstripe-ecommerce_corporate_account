<?php




class EcommerceCorporateGroupAddressDecorator extends DataObjectDecorator {


	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
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


	function updateCMSFields(&$fields) {
		if($group = DataObject::get_by_id("Group", $this->owner->OrganisationID)){
			$organiationField = $fields->dataFieldByName("OrganisationID")->performReadonlyTransformation();
			$organiationField->setTitle(_t("OrderAddress.FOR", "For"));
			$fields->replaceField("OrganisationID", $organiationField);
			$fields->addFieldToTab("Root."._t("OrderAddress.FORTAB", "for"), $organiationField);
		}
		return $fields;
	}

	/**
	 * When creating the address, we grab the details from the company.
	 * to pre-populate the data
	 */
	function populateDefaults(){
		if(self::get_update_order_address_from_group()) {
			if($group = $this->relatedGroup()) {
				$this->owner->OrganisationID = $group->ID;
				if($this instanceOf ShippingAddress) {
					$this->owner->ShippingAddress = $group->PhysicalAddress;
					$this->owner->ShippingAddress2 = $group->PhysicalSuburb;
					$this->owner->ShippingCity = $group->PhysicalTown;
					$this->owner->ShippingCountry = $group->PhysicalCountry;
					$this->owner->ShippingPhone = $group->PhysicalPhone;
				}
				elseif($this instanceOf BillingAddress) {
					$this->owner->BillingAddress = $group->PostalAddress;
					$this->owner->BillingAddress2 = $group->PostalSuburb;
					$this->owner->BillingCity = $group->PostalTown;
					$this->owner->BillingCountry = $group->PostalCountry;
					$this->owner->BillingPhone = $group->PostalPhone;
				}
			}
		}
	}

	/**
	 * Standard SS Method
	 * When saving the data, we update the company details.
	 */
	function onBeforeWrite(){
		if($group = $this->relatedGroup()) {
			$this->owner->OrganisationID = $group->ID;
		}
	}

	/**
	 * Standard SS Method
	 * When saving the data, we update the company details.
	 */
	function onAfterWrite(){
		if(self::get_update_group_from_order_address()) {
			if($group = $this->relatedGroup()) {
				if($this instanceOf ShippingAddress) {
					$group->PhysicalAddress = $this->owner->ShippingAddress;
					$group->PhysicalSuburb = $this->owner->ShippingAddress2;
					$group->PhysicalTown = $this->owner->ShippingCity;
					$group->PhysicalCountry = $this->owner->ShippingCountry;
					$group->PhysicalPhone = $this->owner->ShippingPhone;
				}
				elseif($this instanceOf BillingAddress) {
					$group->PostalAddress = $this->owner->BillingAddress;
					$group->PostalSuburb = $this->owner->BillingAddress2;
					$group->PostalTown = $this->owner->BillingCity;
					$group->PostalCountry = $this->owner->BillingCountry;
					$group->PostalPhone = $this->owner->BillingPhone;
				}
				$group->write();
			}
		}
	}

	/**
	 *
	 * returns the related group (company or corporate account) - if any
	 * @return Group | Null
	 */
	protected function relatedGroup(){
		if($member = $this->owner->getMemberFromOrder()) {
			if($group = $member->getCorporateAccountGroup()) {
				return $group;
			}
		}
	}

}
