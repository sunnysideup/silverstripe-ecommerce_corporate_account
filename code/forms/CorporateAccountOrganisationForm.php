<?php

/**
 * A form that allows editing the members's group.
 * OR, if no group exists, to request approval.
 *
 * @author Nicolaas
 */
class CorporateAccountOrganisationForm extends form {

	/**
	 * @var Member
	 */
	protected $member = null;

	/**
	 * @var Group
	 */
	protected $group = null;

	/**
	 * Standard form
	 *
	 */
	function __construct($controller, $name, Member $member, Group $group) {
		$this->group = $group;
		$this->member = $member;
		$fields = new FieldSet();
		$actions = new FieldSet();
		$requiredFields = null;
		if($this->group) {
			$fieldsArray = $this->group->CorporateAddressFieldsArray();
			$fields->push(new TextField("Title", _t("EcommerceCorporateAccount.NAME", "Name")));
			foreach($fieldsArray as $newField) {
				$fields->push($newField);
			}
			$requiredFields = new CorporateAccountOrganisationForm_Validator();
			$actions->push(
				new FormAction('updatedetails', _t('EcommerceCorporateAccount.UPDATEDETAILS','Update Details'))
			);
		}
		elseif($this->member) {
			$fields->push(new LiteralField('MemberNotApprovedYest', '<p>'._t('EcommerceCorporateAccount.NOTAPPROVEDYE','Your account has not been approved yet.').'</p>'));
			$actions->push(
				new FormAction('requestapproval', _t('EcommerceCorporateAccount.REQUESTAPPROVAL','Request Approval'))
			);
		}
		//construct the form...
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		if($this->group) {
			$this->loadDataFrom($this->group);
		}
		//extensions need to be set after __construct
		if($this->extend('updateFields',$fields) !== null) {$this->setFields($fields);}
		if($this->extend('updateActions',$actions) !== null) {$this->setActions($actions);}
		if($this->extend('updateValidator',$requiredFields) !== null) {$this->setValidator($requiredFields);}
		$this->extend('updateCorporateAccountOrganisationForm',$this);
	}

	/**
	 * Update the details for the organisation
	 */
	function updatedetails($data, $form, $request) {
		if($this->group) {
			$this->saveInto($this->group);
			$this->group->write();
			$form->sessionMessage(_t('EcommerceCorporateAccount.DETAILSHAVEBEENUPDATED','Your details have been updated.'), 'good');
			Director::redirectBack();
		}
		else {
			$form->sessionMessage(_t('EcommerceCorporateAccount.DETAILSCOULDNOTBEUPATED','Your details have not been updated.'), 'bad');
			Director::redirectBack();
		}
	}

	/**
	 * request approval: sends email to shop admin to request approval.
	 */
	function requestapproval($data, $form, $request) {
		if($this->member) {
			$email = new Email();
			$email->setTo(Order_Email::get_from_email());
			$email->setSubject(_t("EcommerceCorporateAccount.REQUESTINGACCOUNTAPPROVE", "A request for an account approval from "). $this->member->Email);
			$email->setTemplate('EcommerceCorporateGroupApprovalRequest');
			$config = SiteConfig::current_site_config();
			$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
			$email->populateTemplate(array(
				'SiteConfig'       => $config,
				'EcommerceConfig'  => $ecommerceConfig,
				'Member'           => $this->member
			));
			$email->send();
			$form->sessionMessage(_t('EcommerceCorporateAccount.REQUESTHASBEENSENT','The request has been sent.'), 'good');
			Director::redirectBack();
		}
		else {
			$form->sessionMessage(_t('EcommerceCorporateAccount.REQUESTCOULDNOTBESEND','The request could not be sent.'), 'bad');
			Director::redirectBack();
		}
	}



}


class CorporateAccountOrganisationForm_Validator extends RequiredFields{

	/**
	 * Ensures member unique id stays unique and other basic stuff...
	 * @param $data = array Form Field Data
	 * @return Boolean
	 **/
	function php($data){
		$valid = parent::php($data);
			// check password fields are the same before saving
		/** EXAMPLE **
		if(isset($data["Password"]["_Password"]) && isset($data["Password"]["_ConfirmPassword"])) {
			if($data["Password"]["_Password"] != $data["Password"]["_ConfirmPassword"]) {
				$this->validationError(
					"Password",
					_t('EcommerceCorporateAccount.PASSWORDSERROR', 'Passwords do not match.'),
					"required"
				);
				$valid = false;
			}
			if(!$memberID && !$data["Password"]["_Password"]) {
				$this->validationError(
					"Password",
					_t('EcommerceCorporateAccount.SELECTPASSWORD', 'Please select a password.'),
					"required"
				);
				$valid = false;
			}
		}
		*/
		if(!$valid) {
			$this->form->sessionMessage(_t('EcommerceCorporateAccount.ERRORINFORM', 'We could not save your details, please check your errors below.'), "bad");
		}
		return $valid;
	}

}



