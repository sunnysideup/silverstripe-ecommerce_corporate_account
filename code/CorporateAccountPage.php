<?php

/**
 * this page can be used in conjunction with the AccountPage.
 * It provides an overview of the
 *
 *
 *
 *
 *
 * @author nicolaas
 */

class CorporateAccountPage extends AccountPage {

	/**
	 * Standard SS variable
	 */
	public static $icon = 'ecommerce/images/icons/AccountPage';

	/**
	 * standard SS method
	 * @return Boolean
	 **/
	function canCreate($member = null) {
		return !DataObject :: get_one("CorporateAccountPage", "\"ClassName\" = 'CorporateAccountPage'");
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
	 * Overloads AccountPage::pastOrdersSelection
	 * rather than just returning the orders from the Member,
	 * it returns the orders from the group
	 * @return NULL | DataObjectSet
	 */
	protected function pastOrdersSelection(){
		$members = $this->GroupMembers();
		$memberIDArray = array(-1);
		if($members && $members->count()) {
			foreach($members as $member) {
				$memberIDArray[$member->ID] = $member->ID;
			}
		}
		return DataObject::get(
			"Order",
			"\"Order\".\"MemberID\" IN (".implode(",", $memberIDArray).") AND (\"CancelledByID\" = 0 OR \"CancelledByID\" IS NULL) ",
			"\"Created\" DESC",
			//why do we have this?
			"INNER JOIN \"OrderAttribute\" ON \"OrderAttribute\".\"OrderID\" = \"Order\".\"ID\" INNER JOIN \"OrderItem\" ON \"OrderItem\".\"ID\" = \"OrderAttribute\".\"ID\""
		);
	}

	/**
	 * overloads AccountMember from AccountPage
	 * only returns a member if it is an approved member
	 * @return NULL | Member
	 */
	function AccountMember(){
		$member = Member::currentUser();
		if($member) {
			if($member->exists()) {
				if($member->isApprovedCorporateCustomer()) {
					return $member;
				}
			}
		}
	}

	/**
	 * returns the group for the account member
	 * @return NULL | Group
	 */
	function AccountGroup(){
		$member = $this->AccountMember();
		if($member) {
			return $member->CorporateAccountGroup();
		}
	}

	/**
	 * returns the members of the current Group.
	 * Includes the current member.
	 * @return NULL | DataObjectSet
	 */
	function GroupMembers() {
		$members = null;
		$group = $this->AccountGroup();
		if($group) {
			$members = $group->Members();
			if($members && $members->count()) {
				$currentMember = Member::currentUser();
				foreach($members as $member) {
					if($currentMember->ID == $member->ID) {
						$member->LinkingMode = "current";
					}
					else {
						$member->LinkingMode = "link";
					}
				}
			}
		}
		return $members;
	}

}

class CorporateAccountPage_Controller extends AccountPage_Controller {


	/**
	 * standard controller function
	 **/
	function init() {
		parent::init();
		Requirements::themedCSS("CorporateAccountPage");
	}

	/**
	 * returns a string of the name of the group
	 * @return String
	 */
	function GroupTitle(){
		$group = $this->AccountGroup();
		if($group) {
			return $group->CombinedCorporateGroupName();
		}
	}

	/**
	 * returns a form... You can either update your details or request approval
	 * @return CorporateAccountOrganisationForm
	 */
	function OrganisationForm(){
		return new CorporateAccountOrganisationForm($this, "OrganisationForm", $this->AccountMember(), $this->AccountGroup());
	}

	/**
	 * tells us whether the current members account group is approved.
	 * @return Boolean
	 */
	function IsApprovedAccountGroup(){
		return $this->AccountGroup() ? true : false;
	}



}
