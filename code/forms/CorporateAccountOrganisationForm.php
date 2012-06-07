<?php

/**
 *
 *
 *
 *
 */
class CorporateAccountOrganisationForm extends form {


	function __construct($controller, $name) {
		$member = Member::currentUser();
		$requiredFields = null;
		$fields = new FieldSet();
		$fields->push(new HeaderField('OurOrganisationHeading', _t('CorporateAccountOrganisationForm.OURORGANISATION','Our Organisation')));

		if($member && $member->exists() && $member->isApprovedCorporateCustomer()) {
			$requiredFields = new CorporateAccountOrganisationForm();
			$actions = new FieldSet(
				new FormAction('updatedetails', _t('CorporateAccountOrganisationForm.UPDATEDETAILS','Update Details'))
			);
		}
		else {
			$fields->push(new LiteralField('MemberNotApprovedYest', '<p>'._t('CorporateAccountOrganisationForm.NOTAPPROVEDYE','Your account has not been approved yet.').'</p>'));
			$actions = new FieldSet(
				new FormAction('requestapproval', _t('CorporateAccountOrganisationForm.REQUESTAPPROVAL','Request Approval'))
			);
		}

		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
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
		return $this->processForm($data, $form, $request, CheckoutPage::find_link());
	}




	/**
	 * request approval
	 */
	function requestapproval($data, $form, $request) {

	}



	function creatememberandaddtoorder($data, $form){
		$member = new Member();
		$order =  ShoppingCart::current_order();
		if($order && $order->exists()) {
			$form->saveInto($member);
			$member->write();
			if($member->exists()) {
				$order->MemberID = $member->ID;
				$order->write();
				$member->login();
				$this->sessionMessage(_t("ShopAccountForm.SAVEDDETAILS", "Your order has been saved."), "bad");
			}
			else {
				$this->sessionMessage(_t("ShopAccountForm.COULDNOTCREATEMEMBER", "Could not save your details."), "bad");
			}
		}
		else {
			$this->sessionMessage(_t("ShopAccountForm.COULDNOTFINDORDER", "Could not find order."), "bad");
		}
		Director::redirectBack();
	}



	/**
	 *@return Boolean + redirection
	 **/
	protected function processForm($data, $form, $request, $link = "") {
		$member = Member::currentUser();
		if(!$member) {
			$form->sessionMessage(_t('Account.DETAILSNOTSAVED','Your details could not be saved.'), 'bad');
			Director::redirectBack();
		}
		$form->saveInto($member);
		$member->write();
		if($link) {
			Director::redirect($link);
		}
		else {
			$form->sessionMessage(_t('Account.DETAILSSAVED','Your details have been saved.'), 'good');
			Director::redirectBack();
		}
		return true;
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
		$uniqueFieldName = Member::get_unique_identifier_field();
		$memberID = Member::currentUserID();
		if(isset($data[$uniqueFieldName]) && $memberID && $data[$uniqueFieldName]){
			$uniqueFieldValue = Convert::raw2sql($data[$uniqueFieldName]);
			//can't be taken
			if(DataObject::get_one('Member',"\"$uniqueFieldName\" = '$uniqueFieldValue' AND ID <> ".$memberID)){
				$message = sprintf(
					_t("Account.ALREADYTAKEN",  '%1$s is already taken by another member. Please log in or use another %2$s'),
					$uniqueFieldValue,
					$uniqueFieldName
				);
				$this->validationError(
					$uniqueFieldName,
					$message,
					"required"
				);
				$valid = false;
			}
		}
		// check password fields are the same before saving
		if(isset($data["Password"]["_Password"]) && isset($data["Password"]["_ConfirmPassword"])) {
			if($data["Password"]["_Password"] != $data["Password"]["_ConfirmPassword"]) {
				$this->validationError(
					"Password",
					_t('Account.PASSWORDSERROR', 'Passwords do not match.'),
					"required"
				);
				$valid = false;
			}
			if(!$memberID && !$data["Password"]["_Password"]) {
				$this->validationError(
					"Password",
					_t('Account.SELECTPASSWORD', 'Please select a password.'),
					"required"
				);
				$valid = false;
			}
		}
		if(!$valid) {
			$this->form->sessionMessage(_t('Account.ERRORINFORM', 'We could not save your details, please check your errors below.'), "bad");
		}
		return $valid;
	}

}



