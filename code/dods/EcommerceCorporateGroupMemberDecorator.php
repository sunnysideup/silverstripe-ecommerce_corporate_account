<?php




class EcommerceCorporateGroupMemberDecorator extends DataObjectDecorator {


	function augmentEcommerceFields(&$fields) {
		if($group = $this->getCorporateAccountGroup()) {
			$this->owner->OrganisationID = $group->ID;
			$fields->push(new ReadonlyField("OrganisationName", _t("OrderAddress.FOR", "For"),$group->CombinedCorporateGroupName()));
		}
	}

	/**
	 * Tells us whether this member is allowed to purchase a product.
	 * NOTE: it returns TRUE (can purchase) if no approved customer group
	 * has been setup yet.
	 * @return Boolean
	 */
	public function isApprovedCorporateCustomer() {
		$outcome = false;
		if(EcommerceCorporateGroupBuyableDecorator::get_only_approved_customers_can_purchase()) {
			$approvedCustomerGroup = self::get_approved_customer_group();
			if($approvedCustomerGroup) {
				if(!$this->owner->exists()) {
					//member does not exist yet
					//return false;
				}
				elseif($this->owner->inGroup($approvedCustomerGroup, false)) {
					//exception - customer is approved
					$outcome = true;

				}
				elseif($this->owner->IsShopAdmin() || $this->owner->IsAdmin()) {
					//exception - administrator
					$outcome = true;
				}
				else {
					//return false;
				}
			}
			else {
				//exception - Group not setup yet.
				$outcome = true;
			}
		}
		else {
			//exception - anyone can purchase anyway
			$outcome = true;;
		}
		//standard answer....
		return $outcome;
	}

	/**
	 * returns the MOST LIKELY (!) company or Corporate Account Group of the current member
	 * @return NULL | Group (object)
	 * @author: Nicolaas
	 */
	function CorporateAccountGroup(){return $this->getCorporateAccountGroup();}
	function getCorporateAccountGroup() {
		if($this->owner->exists()) {
			if($this->owner->isApprovedCorporateCustomer()) {
				$groups = $this->owner->Groups();
				if($groups && $groups->count()) {
					foreach($groups as $group) {
						//it is a corporate account (business)
						if($group->isCorporateAccount()) {
							//it does not have a child group
							if(!DataObject::get_one("Group", "\"ParentID\" = ".$group->ID)) {
								return $group;
							}
						}
					}
				}
			}
		}
	}

}
