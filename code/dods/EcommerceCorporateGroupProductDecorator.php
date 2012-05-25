<?php


class EcommerceCorporateGroupProductDecorator extends DataObjectDecorator {

	public function isApprovedCorporateCustomer() {
		$approvedCustomerGroup = CreateEcommerceApprovedCustomerGroup::get_approved_customer_group();
		if($approvedCustomerGroup) {
			if($member = Member::currentUser()) {
				if($member->inGroup($approvedCustomerGroup, false)) {
					return true;
				}
				elseif($member->IsShopAdmin()) {
					return true;
				}
				elseif($member->IsAdmin()) {
					return true;
				}
			}
		}
		else {
			//the only exception - Group not setup yet.
			return true;
		}
		return false;
	}

	function updateCalculatedPrice($price){
		if($this->isApprovedCorporateCustomer()) {
			return null;
		}
		return 0;
	}


	function  updateDisplayPrice($moneyObject){
		if($this->isApprovedCorporateCustomer()) {
			return null;
		}
		//todo: add current controller
		//to link back to to page before login
		return "<a href=\"Security/login/\">log in</a>";
	}

	public function canPurchase(){
		if($this->isApprovedCorporateCustomer()) {
			return null;
		}
		return false;
	}


}
