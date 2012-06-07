<?php

/**
 *
 *
 *
 *
 *
 *
 *
 *
 * TO DO: this class is uncomplete
 *
 */

class CorporateAccountPage extends AccountPage {

	public static $icon = 'ecommerce/images/icons/AccountPage';

	/**
	 * standard SS method
	 *@return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("CorporateAccountPage", "\"ClassName\" = 'CorporateAccountPage'");
	}
	/**
	 * standard SS method
	 * @return Boolean
	 **/
	function canView($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		return $member->isApprovedCorporateCustomer();
	}

	/**
	 * Returns the link to the AccountPage on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if($page = DataObject::get_one('CorporateAccountPage', "\"ClassName\" = 'CorporateAccountPage'")) {
			return $page->Link();
		}
	}


	/**
	 *
	 **/
	public function AllCompanyOrders() {
		return null;
	}


}

class CorporateAccountPage_Controller extends AccountPage_Controller {

	function CorporateAccountGroup(){
		$member = Member::currentUser();
		return $member->CorporateAccountGroup();
	}

	function OrganisationForm(){

	}

}


