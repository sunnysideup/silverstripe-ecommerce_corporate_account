<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_corporate_account
 */

class OrderMarker extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	/**
	 *
	 * @var Boolean
	 */
	protected static $order_for_is_required = false;
		static function set_order_for_is_required($b) {self::$order_for_is_required = $b;}
		static function get_order_for_is_required() {return self::$order_for_is_required;}


	public static $db = array(
		"OrderFor" => "Varchar",
	);

	public static $singular_name = "Purchase Order";
		function i18n_single_name() { return _t("EcommerceCorporateAccount.ORDERMARKER", "Purchase Order");}

	public static $plural_name = "Purchase Orders";
		function i18n_plural_name() { return _t("EcommerceCorporateAccount.ORDERMARKERS", "Purchase Orders");}

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}

// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)


// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions
	/**
	 * updates database fields
	 * @param Bool $force - run it, even if it has run already
	 * @return void
	 */
	public function runUpdate($force = true) {
		if (isset($_GET['debug_profile'])) Profiler::mark('OrderMaker::runUpdate');
		if(!$this->IsRemoved()) {
			$this->checkField("OrderFor");
			parent::runUpdate($force);
		}
		if (isset($_GET['debug_profile'])) Profiler::unmark('OrderMaker::runUpdate');
	}

	/**
	 * updates the Order Modifier (but does NOT write it)
	 * updates the OrderStatusLog (and writes it)
	 * @param String $s - new value
	 */
	function updateOrderFor($s) {
		$this->OrderFor = $s;
		$log = DataObject::get_one("OrderMarker_StatusLog", "\"OrderID\" = ".$this->OrderID."");
		if(!$log) {
			$log = new OrderMarker_StatusLog();
		}
		$log->OrderID = $this->OrderID;
		$log->Title = $this->Heading();
		$log->Note = $s;
		$log->Write();
	}

// ######################################## *** form functions (e. g. showform and getform)


	public function ShowForm() {
		return $this->Order()->Items();
	}

	function getModifierForm($optionalController = null, $optionalValidator = null) {
		$fields = new FieldSet();
		$fields->push($this->headingField());
		$fields->push(new TextField('OrderFor', $this->Description(), $this->OrderFor));
		$fields->push(new LiteralField('OrderForConfirmation', "<div><div id=\"OrderForConfirmation\" class=\"middleColumn\"></span></div>"));
		if(self::get_order_for_is_required()) {
			$optionalValidator = new RequiredFields(array("OrderFor"));
		}
		else {
			$optionalValidator = null;
		}
		$actions = new FieldSet(
			new FormAction('submit', _t("EcommerceCorporateAccount.UPDATEORDER", "Update Order"))
		);
		return new OrderMarker_Form($optionalController, 'OrderMarker', $fields, $actions, $optionalValidator);
	}

// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

	public function ShowInTable() {
		return false;
	}
	public function CanBeRemoved() {
		return false;
	}
// ######################################## ***  inner calculations.... USES CALCULATED VALUES



// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES

	protected function LiveName() {
		return _t("EcommerceCorporateAccount.ORDERFOR", "Order For")." ".$this->LiveOrderFor();
	}

	protected function LiveOrderFor() {
		return $this->OrderFor;
	}



// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}


	function onBeforeRemove(){
		$this->OrderFor = "";
		parent::onBeforeRemove();
	}

// ######################################## *** AJAX related functions

	/**
	* some modifiers can be hidden after an ajax update (e.g. if someone enters a discount coupon and it does not exist).
	* There might be instances where ShowInTable (the starting point) is TRUE and HideInAjaxUpdate return false.
	*@return Boolean
	**/
	public function HideInAjaxUpdate() {
		if(parent::HideInAjaxUpdate()) {
			return true;
		}
		if($this->OrderFor) {
			return false;
		}
		return true;
	}

// ######################################## *** debug functions

}

class OrderMarker_Form extends OrderModifierForm {

	function __construct($optionalController = null, $name,FieldSet $fields, FieldSet $actions,$validator = null) {
		parent::__construct($optionalController, $name,$fields,$actions,$validator);
		Requirements::javascript("ecommerce_corporate_account/javascript/OrderMarkerModifier.js");
	}

	public function submit($data, $form) {
		if(isset($data['OrderFor'])) {
			$order = ShoppingCart::current_order();
			if($order) {
				if($modifiers = $order->Modifiers("OrderMarker")) {
					foreach($modifiers as $modifier) {
						$modifier->updateOrderFor(Convert::raw2sql($data["OrderFor"]));
						$modifier->write();
					}
					return ShoppingCart::singleton()->setMessageAndReturn(_t("EcommerceCorporateAccount.UPDATED", "Order saved as '".Convert::raw2xml($data["OrderFor"]))."'.", "good");
				}
			}
		}
		return ShoppingCart::singleton()->setMessageAndReturn(_t("EcommerceCorporateAccount.UPDATED", "Order marker could not be saved"), "bad");
	}
}

class OrderMarker_StatusLog extends OrderStatusLog{


	/**
	 * standard SS method
	 */
	function populateDefaults() {
		parent::populateDefaults();
		$this->AuthorID = Member::currentUserID();
		$this->InternalUseOnly = false;
	}

}

