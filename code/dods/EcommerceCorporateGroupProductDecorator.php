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
			}
			return false;
		}
		else {
			//the only exception - Group not setup yet.
			return true;
		}
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
		$moneyObject->setValue(0);
		return $moneyObject;
	}

	public function canPurchase(){
		if($this->isApprovedCorporateCustomer()) {
			return null;
		}
		return false;
	}


}
