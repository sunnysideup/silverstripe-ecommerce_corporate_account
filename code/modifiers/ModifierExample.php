<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_delivery
 * @description: Shipping calculation scheme based on SimpleShippingModifier.
 * It lets you set fixed shipping costs, or a fixed
 * cost for each region you're delivering to.
 */
class ModifierExample extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	public static $db = array(
		"MyField" => "Varchar",
		"MyReduction" => "Currency"
	);

	public static $defaults = array("Type" => "Chargeable");

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}

	public static $singular_name = "Modifier Example";
		function i18n_single_name() { return _t("ModifierExample.MODIFIEREXAMPLE", "Modifier Example");}

	public static $plural_name = "Modifier Examples";
		function i18n_plural_name() { return _t("ModifierExample.MODIFIEREXAMPLES", "Modifier Examples");}

// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

	protected static $form_header = 'Modifier Example';
		static function set_form_header(string $s) {self::$form_header = $s;}

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

	public function runUpdate() {
		$this->checkField("MyField");
		$this->checkField("MyReduction");
		parent::runUpdate();
	}

	function updateMyField($s) {
		$this->MyField = $s;
	}

	function updateMyReduction($int) {
		$this->MyReduction = $int;
		$this->CalculationValue = -1 * $int;
	}

// ######################################## *** form functions (e. g. showform and getform)


	public function showForm() {
		return $this->Order()->Items();
	}

	function getModifierForm($controller) {
		$fields = new FieldSet();
		$fields->push(new TextField('MyField', "enter value for testing", $this->MyField));
		$fields->push(new NumericField('MyReduction', "what discount would you like?", $this->MyReduction));
		$validator = null;
		$actions = new FieldSet(
			new InlineFormAction('submit', 'Update Order')
		);
		return new ModifierExample_Form($controller, 'ModifierExample', $fields, $actions, $validator);
	}

// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

	public function ShowInTable() {
		return true;
	}
	public function CanBeRemoved() {
		return false;
	}
	public function TableValue() {
		return $this->MyReduction;
	}

	public function TableTitle() {
		return $this->MyField;
	}

// ######################################## ***  inner calculations.... USES CALCULATED VALUES



// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES

	protected function LiveName() {
		return "EXAMPLE: ".$this->LiveMyField();
	}

	protected function LiveMyField() {
		return $this->MyField;
	}


	protected function LiveMyReduction() {
		return $this->MyReduction;
	}


// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

	public function IsChargeable () {
		return true;
	}

// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}

class ModifierExample_Form extends OrderModifierForm {

	public function submit($data, $form) {
		$order = ShoppingCart::current_order();
		$modifiers = $order->Modifiers();
		foreach($modifiers as $modifier) {
			if (get_class($modifier) == 'ModifierExample') {
				if(isset($data['MyField'])) {
					$modifier->updateMyField(Convert::raw2sql($data["MyField"]));
					$modifier->updateMyReduction(floatval($data["MyReduction"]));
					$modifier->write();
				}
			}
		}
		return parent::submit($data, $form);
	}
}
