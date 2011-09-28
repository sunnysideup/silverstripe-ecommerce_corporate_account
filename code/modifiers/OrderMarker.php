<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_delivery
 * @description: Shipping calculation scheme based on SimpleShippingModifier.
 * It lets you set fixed shipping costs, or a fixed
 * cost for each region you're delivering to.
 */
class OrderMarker extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	public static $db = array(
		"OrderFor" => "Varchar",
	);

	public static $defaults = array("Type" => "Chargeable");

// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}

	public static $singular_name = "Order Marker";
		function i18n_single_name() { return _t("ModifierExample.ORDERMARKER", "Modifier Marker");}

	public static $plural_name = "Order Markers";
		function i18n_plural_name() { return _t("ModifierExample.ORDERMARKERS", "Modifier Markers");}

// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)

	protected static $form_header = 'Order For ...';
		static function set_form_header(string $s) {self::$form_header = $s;}

// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

	public function runUpdate() {
		$this->checkField("OrderFor");
		parent::runUpdate();
	}

	function updateOrderFor($s) {
		$this->OrderFor = $s;
	}

// ######################################## *** form functions (e. g. showform and getform)


	public function showForm() {
		return $this->Order()->Items();
	}

	function getModifierForm($controller) {
		$fields = new FieldSet();
		$fields->push(new TextField('OrderFor', "enter name or code for this order", $this->MyField));
		$validator = new RequiredFields(array("OrderFor"));
		$actions = new FieldSet(
			new InlineFormAction('submit', 'Update Order')
		);
		return new OrderMarker_Form($controller, 'ModifierExample', $fields, $actions, $validator);
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
		return "Order For: ".$this->LiveOrderFor();
	}

	protected function LiveOrderFor() {
		return $this->OrderFor;
	}



// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)

// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

// ######################################## *** AJAX related functions
// ######################################## *** debug functions

}

class OrderMarker_Form extends OrderModifierForm {

	function __construct($optionalController = null, $name,FieldSet $fields, FieldSet $actions,$validator = null) {
		parent::__construct($optionalController, $name,$fields,$actions,$validator);
		Requirements::javascript("ecommerce_corporate_account/javascript/OrderMarkerModifier.js")
	}

	public function submit($data, $form) {
		$order = ShoppingCart::current_order();
		$modifiers = $order->Modifiers();
		foreach($modifiers as $modifier) {
			if (get_class($modifier) == 'OrderMarker') {
				if(isset($data['OrderFor'])) {
					$modifier->updateOrder(Convert::raw2sql($data["OrderFor"]));
					$modifier->write();
					return ShoppingCart::singleton()->setMessageAndReturn(_t("OrderMarker.UPDATED", "Order marker saved"), "good");
				}
			}
		}
		return ShoppingCart::singleton()->setMessageAndReturn(_t("OrderMarker.UPDATED", "Order marker could not be saved"), "bad");
	}
}


// prepare the form when the DOM is ready
$(document).ready(function() {
    var options = {
        target:        '#output1',   // target element(s) to be updated with server response
        beforeSubmit:  showRequest,  // pre-submit callback
        success:       showResponse  // post-submit callback

        // other available options:
        //url:       url         // override for form's 'action' attribute
        //type:      type        // 'get' or 'post', override for form's 'method' attribute
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
        //clearForm: true        // clear all form fields after successful submit
        //resetForm: true        // reset the form after successful submit

        // $.ajax options can be used here too, for example:
        //timeout:   3000
    };

    // bind form using 'ajaxForm'
    $('#myForm1').ajaxForm(options);
});

// pre-submit callback
function showRequest(formData, jqForm, options) {
    // formData is an array; here we use $.param to convert it to a string to display it
    // but the form plugin does this for you automatically when it submits the data
    var queryString = $.param(formData);

    // jqForm is a jQuery object encapsulating the form element.  To access the
    // DOM element for the form do this:
    // var formElement = jqForm[0];

    alert('About to submit: \n\n' + queryString);

    // here we could return false to prevent the form from being submitted;
    // returning anything other than false will allow the form submit to continue
    return true;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form)  {
    // for normal html responses, the first argument to the success callback
    // is the XMLHttpRequest object's responseText property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'xml' then the first argument to the success callback
    // is the XMLHttpRequest object's responseXML property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'json' then the first argument to the success callback
    // is the json data object returned by the server

    alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
        '\n\nThe output div should have already been updated with the responseText.');
}
