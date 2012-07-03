<?php

/**
 * this page can be used in conjunction with the AccountPage.
 * It provides an overview of the
 *
 * @author nicolaas
 */

class CorporateAccountPageUpdateDetails extends CorporateAccountPage {

	/**
	 * standard SS method
	 * @return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("CorporateAccountPageUpdateDetails", "\"ClassName\" = 'CorporateAccountPageUpdateDetails'");
	}

	/**
	 * Returns the link to the AccountPage on this site
	 * @return String (URLSegment)
	 */
	public static function find_link() {
		if($page = DataObject::get_one('CorporateAccountPageUpdateDetails', "\"ClassName\" = 'CorporateAccountPageUpdateDetails'")) {
			return $page->Link();
		}
	}


}

class CorporateAccountPageUpdateDetails_Controller extends CorporateAccountPage_Controller {

	/**
	 * returns a form... You can either update your details or request approval
	 * @return CorporateAccountOrganisationForm
	 */
	function OrganisationForm(){
		return new CorporateAccountOrganisationForm($this, "OrganisationForm", $this->AccountMember(), $this->AccountGroup());
	}





}
