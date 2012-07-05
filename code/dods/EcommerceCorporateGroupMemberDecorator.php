<?php


/**
 * adds functionality for Members
 *
 * @author nicolaas
 */


class EcommerceCorporateGroupMemberDecorator extends DataObjectDecorator {

	/**
	 * Standard SS Method
	 *
	 */
	public function extraStatics() {
		return array (
			'db' => array (
				'ApprovalEmailSent' => 'Boolean'
			),
		);
	}

	/**
	 * Adds fields to the Member Ecommerce FieldSet.
	 * In this case, we add the name of the organisation as READ-ONLY.
	 * @param FieldSet $fields
	 * @return FieldSet
	 */
	function augmentEcommerceFields(&$fields) {
		if($group = $this->owner->getCorporateAccountGroup()) {
			$fields->push(new ReadonlyField("OrganisationName", _t("EcommerceCorporateAccount.FOR", "For"),$group->CombinedCorporateGroupName()));
		}
		return $fields;
	}

	/**
	 * Standard SS Method
	 * @param FieldSet
	 * @return FieldSet
	 */
	function updateCMSFields(&$fields) {
		if($group = $this->owner->getCorporateAccountGroup()) {
			$fields->addFieldToTab("Root.Organisation", new ReadonlyField("OrganisationName", _t("EcommerceCorporateAccount.WORKSFOR", "Works For"),$group->CombinedCorporateGroupName()));
		}
		$fields->addFieldToTab("Root.Organisation", new CheckboxField("ApprovalEmailSent", _t("EcommerceCorporateAccount.APPROVALEMAILSENT", "Approval Email Sent")));
		$fields->removeByName("Password");
		return $fields;
	}

	/**
	 * Tells us whether this member is allowed to purchase a product.
	 * NOTE: it returns TRUE (can purchase) if no approved customer group
	 * has been setup yet.
	 * @return Boolean
	 */
	public function isApprovedCorporateCustomer() {
		$outcome = false;
		if(!$this->owner->exists()) {
			return false;
		}
		$approvedCustomerGroup = EcommerceCorporateGroupGroupDecorator::get_approved_customer_group();
		if($approvedCustomerGroup) {
			if($this->owner->inGroup($approvedCustomerGroup, false)) {
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
		//standard answer....
		return $outcome;
	}

	/**
	 * returns the MOST LIKELY (!) company or Corporate Account Group of the current member
	 * @return NULL | Group (object)
	 */
	function CorporateAccountGroup(){return $this->owner->getCorporateAccountGroup();}
	function getCorporateAccountGroup() {
		$groupArray = array();
		if($this->owner->exists()) {
			if($this->owner->isApprovedCorporateCustomer()) {
				$groups = $this->owner->Groups();
				if($groups && $groups->count()) {
					foreach($groups as $group) {
						//it is a corporate account (business)
						if($group->isCorporateAccount()) {
							//if therer are two at the same level of the hierarchy, then we just take one!
							$groupArray[$group->numberOfParentGroups()] = $group;
						}
					}
				}
			}
		}
		//we prefer a "front-line" security group (more specific)
		if(count($groupArray)) {
			krsort($groupArray);
			foreach($groupArray as $group) {
				return $group;
			}
		}
	}

	/**
	 * standard SS Method
	 * Sends an email to the member letting her / him know that the account has been approved.
	 */
	function onAfterWrite(){
		if($this->owner->isApprovedCorporateCustomer()) {
			if(!$this->owner->ApprovalEmailSent) {
				$config = SiteConfig::current_site_config();
				$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
				$email = new Email();
				$email->setTo($this->owner->Email);
				$email->setSubject(_t("EcommerceCorporateAccount.ACCOUNTAPPROVEDFOR", "Account approved for "). $config->Title);
				$email->setBcc(Order_Email::get_from_email());
				$email->setTemplate('EcommerceCorporateGroupApprovalEmail');
				$email->populateTemplate(array(
					'SiteConfig'       => $config,
					'EcommerceConfig'  => $ecommerceConfig,
					'Member'           => $this->owner
				));
				$email->send();
				$this->owner->ApprovalEmailSent = 1;
				$this->owner->write();
			}
		}
	}

}
