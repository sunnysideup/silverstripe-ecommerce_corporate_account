<?php

/**
 * this page can be used in conjunction with the AccountPage.
 * It provides an overview of the
 *
 * @author nicolaas
 */

class CorporateAccountPageUpdateDetails extends CorporateAccountPage {


	/**
	 * Standard SS variable
	 */
	public static $icon = 'ecommerce_corporate_account/images/icons/CorporateAccountPageUpdateDetails';

	/**
	 * Standard SS variable.
	 */
	public static $singular_name = "Corporate Account Update Details Page";
		function i18n_singular_name() { return _t("CorporateAccount.CORPORATEACCOUNTUPDATEDETAILSPAGE", "Corporate Account Update Details Page");}

	/**
	 * Standard SS variable.
	 */
	public static $plural_name = "Corporate Account Update Details Pages";
		function i18n_plural_name() { return _t("CorporateAccount.CORPORATEACCOUNTUPDATEDETAILSPAGES", "Corporate Account Update Details Pages"); }


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
