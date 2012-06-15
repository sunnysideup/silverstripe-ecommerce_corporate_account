<?php

/**
 * adds functionality for buyables
 *
 * @author nicolaas
 */

class EcommerceCorporateGroupBuyableDecorator extends DataObjectDecorator {

	/**
	 * If set to true, only approved customers can make purchases
	 * @var Boolean $only_approved_customers_can_purchase
	 */
	protected static $only_approved_customers_can_purchase = true;
		static function set_only_approved_customers_can_purchase($b) {self::$only_approved_customers_can_purchase = $b;}
		static function get_only_approved_customers_can_purchase() {return self::$only_approved_customers_can_purchase;}

	/**
	 * Is the current customer an approved member?
	 * @return Boolean
	 */
	public function isApprovedCorporateCustomer() {
		if(!EcommerceCorporateGroupBuyableDecorator::get_only_approved_customers_can_purchase()) {
			return true;
		}
		$member = Member::currentUser();
		if(!$member ) {
			$member = new Member();
		}
		return $member->isApprovedCorporateCustomer();
	}

	/**
	 * non-approved customers should not be able to see the price
	 * Note that because it is an extension is actually returns an array!!
	 * @param Currency | double
	 * @return Double | NULL | Currency (object)
	 */
	function updateCalculatedPrice($price){
		if($this->owner->isApprovedCorporateCustomer()) {
			return null;
		}
		if(is_object($price)) {
			$price->setValue(0);
			return $price;
		}
		return 0;
	}

	/**
	 * non-approved customers should not be able to see the price
	 * Note that because it is an extension is actually returns an array!!
	 * @param Money | value $moneyObject
	 * @return Double | NULL | Money (object)
	 */
	function  updateDisplayPrice($moneyObject){
		if($this->owner->isApprovedCorporateCustomer()) {
			return null;
		}
		if(is_object($moneyObject)) {
			$moneyObject->setValue(0);
			return $moneyObject;
		}
		return 0;
	}

	/**
	 * non-approved customers should not be able to see the price
	 * Note that because it is an extension is actually returns an array!!
	 * @param Member $member
	 * @return Boolean | NULL
	 */
	public function canPurchase($member = null){
		if($this->owner->isApprovedCorporateCustomer()) {
			//return null so that the original canPurchase is not affected.
			//MUST return null here
			return null;
		}
		return false;
	}


}
