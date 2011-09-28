(function($){
	$(document).ready(
		function() {
			OrderMarkerModifier.init();
		}
	);
})(jQuery);

var OrderMarkerModifier = {

	formSelector: "#OrderMarker_Form_OrderMarker",

	actionsSelector: ".Actions",

	init: function() {
		var options = {
			beforeSubmit:  OrderMarkerModifier.showRequest,  // pre-submit callback
			success: OrderMarkerModifier.showResponse,  // post-submit callback
			dataType: "json"
		};
		jQuery(OrderMarkerModifier.formSelector).ajaxForm(options);
		jQuery(OrderMarkerModifier.formSelector + " " + OrderMarkerModifier.actionsClass).hide();
		jQuery(OrderMarkerModifier.formSelector+ " input").change(
			function() {
				jQuery(OrderMarkerModifier.formSelector).submit();
			}
		);
	},

	// pre-submit callback
	showRequest: function (formData, jqForm, options) {
		jQuery(OrderMarkerModifier.formSelector).addClass(OrderMarkerModifier.loadingClass);
		return true;
	},

	// post-submit callback
	showResponse: function (responseText, statusText)  {
		jQuery(OrderMarkerModifier.formSelector).removeClass(OrderMarkerModifier.loadingClass);
		EcomCart.setChanges(responseText);
	}

}

