<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_corporate_account
 */

class AddUpProductsToOrderPageModifier extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	public static $db = array(
		"AddUpProductsToOrderPageNotes" => "HTMLText"
	);

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}

	public static $singular_name = "Add products to order note";
		function i18n_single_name() { return _t("OrderMarker.ADDUPPRODUCTSTOORDERPAGENOTE", "Add products to order note");}

	public static $plural_name = "Add products to order notes";
		function i18n_plural_name() { return _t("OrderMarker.ADDUPPRODUCTSTOORDERPAGENOTES", "Add products to order notes");}

// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

	public function runUpdate() {
		$this->checkField("AddUpProductsToOrderPageNotes");
		parent::runUpdate();
	}

// ######################################## *** form functions (e. g. showform and getform)


// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

	public function ShowInTable() {
		return true;
	}
	public function CanBeRemoved() {
		return false;
	}

	public function TableTitle() {return $this->getTableTitle();}
	public function getTableTitle() {
		return $this->AddUpProductsToOrderPageNotes;
	}

// ######################################## ***  inner calculations.... USES CALCULATED VALUES



// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES

	protected function LiveName() {
		return $this->AddUpProductsToOrderPageNotes;
	}

	protected function LiveAddUpProductsToOrderPageNotes() {
		return $this->AddUpProductsToOrderPageNotes;
	}



// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}
