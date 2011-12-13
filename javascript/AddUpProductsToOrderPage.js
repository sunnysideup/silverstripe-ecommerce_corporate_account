jQuery(document).ready(
	function() {
		AddUpProductsToOrderPage.init();
	}
);



var AddUpProductsToOrderPage = {

	/**
	 * number of rows
	 * @var Integer
	 */
	rowNumbers: 1,
		setRowNumbers: function(i) {this.rowNumbers = i;},

	/**
	 * url to the checkout page
	 * @var string
	 */
	CheckoutLink: "",
		setCheckoutLink: function(s) {this.CheckoutLink = s;},

	/**
	 * go through to the check out page?
	 * @var Boolean
	 */
	goToCheckoutLink: false,

	init: function() {
		AddUpProductsToOrderPage.setDefaultSelectValue();
		AddUpProductsToOrderPage.prepareAddAndDeleteRow();
		AddUpProductsToOrderPage.prepareDataEntryValidation();
		AddUpProductsToOrderPage.prepareAjaxForm();
	},

		// pre-submit callback
	showRequest: function (formData, jqForm, options) {
		AddUpProductsToOrderPage.goToCheckoutLink = false;
		for(var i  = 0; i < formData.length; i++) {
			if(formData[i].name == "submit") {
				AddUpProductsToOrderPage.rowNumbers = 0;
				jQuery("#AddProductsToOrderRowsTable tbody tr").remove()
				AddUpProductsToOrderPage.goToCheckoutLink = true;
			}
		}
		jQuery("#AddProductsToOrderRowsResult").text("validating ...").addClass("loading");
		for(var i = 0; i < AddUpProductsToOrderPage.rowNumbers; i++) {
			jQuery("#Name_"+i+" input").change();
			jQuery("#Buyable_"+i+" select").change();
			jQuery("#Qty_"+i+" input").change();
		}
		if(jQuery(".toBeCompleted").length > 0) {
			jQuery("#AddProductsToOrderRowsResult").text("please review entries").removeClass("loading");
			//return false;
		}
		jQuery("#AddProductsToOrderRowsResult").text("loading");
		return true;
	},

		// post-submit callback
	showResponse: function(responseText, statusText, xhr, jQueryform)  {
		if(AddUpProductsToOrderPage.goToCheckoutLink) {
			window.location = AddUpProductsToOrderPage.CheckoutLink;
		}
		jQuery("#AddProductsToOrderRowsResult").removeClass("loading");
	},

	/**
	 * work out the totals (based on qty + price)
	 */
	updateRows: function ()  {
		for(var i = 0; i < this.rowNumbers; i++) {
			var price = parseFloat(jQuery("#Buyable_"+i+" select option:selected").attr("rel"));
			var qty = parseFloat(jQuery("#Qty_"+i+" input").val());
			var total = Math.round((qty * price * 100))/100;
			if(total && total != NaN && total > 0) {
				jQuery("#Total_"+i).text("$" +total)
			}
			else {
				jQuery("#Total_"+i).text("tba");
			}
		}
	},

	/**
	 * set the selected item in a dropdown
	 * using the value in the rel attribute
	 */
	setDefaultSelectValue: function() {
		jQuery(".buyable select").each(
			function(i, el) {
				var selected = jQuery(el).children("option[value='0']").attr("selected");
				if(selected) {
					var rel = jQuery(el).attr("rel");
					if(rel) {
						jQuery(el).children("option[value='"+rel+"']").attr("selected", "selected");
					}
				}
			}
		);
	},


	/**
	 * prepare the add and delete row
	 * links
	 */
	prepareAddAndDeleteRow: function() {

		//delete a row
		jQuery(".reset a").click(
			function(event) {
				if(AddUpProductsToOrderPage.rowNumbers > 1) {
					event.preventDefault();
					AddUpProductsToOrderPage.rowNumbers--;
					jQuery("input[name='rowNumbers']").val(AddUpProductsToOrderPage.rowNumbers);
					jQuery('#AddProductsToOrderRowsTable tr:last').remove();
					jQuery('#AddProductsToOrderRowsForm').submit();
				}
				else {
					return true;
				}
			}
		);

		//add a row
		jQuery(".addProductsToOrderAddRows a").live(
			"click",
			function() {
				jQuery("#AddProductsToOrderRowsResult").addClass("loading");
				AddUpProductsToOrderPage.rowNumbers++;
				jQuery("input[name='rowNumbers']").val(AddUpProductsToOrderPage.rowNumbers);
				url = jQuery(this).attr("href");
				jQuery.ajax({
					url: url,
					data: {rowNumbers: AddUpProductsToOrderPage.rowNumbers},
					success: function(data) {
						jQuery('#AddProductsToOrderRowsTable tbody').append(data);
						AddUpProductsToOrderPage.setDefaultSelectValue();
						AddUpProductsToOrderPage.updateRows();
						jQuery("#AddProductsToOrderRowsResult").removeClass("loading");
						jQuery('#AddProductsToOrderRowsForm').submit();
					},
					dataType: "html"
				});
				return false;
			}
		);
	},

	/**
	 * data-entry validation setup
	 */
	prepareDataEntryValidation: function(){
			//NAME
		jQuery(".name input").live(
			"change",
			function(){
				var val = jQuery(this).val();
				if(val.length < 2) {
					jQuery(this).addClass("toBeCompleted");
					jQuery(this).removeClass("completed");
				}
				else {
					jQuery(this).removeClass("toBeCompleted");
					jQuery(this).addClass("completed");
				}
			}
		);
			//BUYABLE
		jQuery(".buyable select").live(
			"change",
			function(){
				var val = jQuery(this).val();
				if(val.length == 0 || val == 0 || !val) {
					jQuery(this).addClass("toBeCompleted");
					jQuery(this).removeClass("completed");
				}
				else {
					jQuery(this).removeClass("toBeCompleted");
					jQuery(this).addClass("completed");
				}
				AddUpProductsToOrderPage.updateRows();
			}
		);
			//QUANTITY
		jQuery(".qty input").live(
			"change",
			function(){
				var val = parseFloat(jQuery(this).val());
				if(!val) {
					jQuery(this).val(0);
					jQuery(this).addClass("toBeCompleted");
					jQuery(this).removeClass("completed");
				}
				else if(val < 0) {
					jQuery(this).addClass("toBeCompleted");
					jQuery(this).removeClass("completed");
					jQuery(this).val(0);

				}
				else {
					jQuery(this).removeClass("toBeCompleted");
					jQuery(this).addClass("completed");
					jQuery(this).val(val);
				}
				AddUpProductsToOrderPage.updateRows();
			}
		);
	},

	/**
	 * setup form
	 */
	prepareAjaxForm: function(){
		//PREPARE FORM SUBMISSION
		var options = {
			target:        '#AddProductsToOrderRowsResult',   // target element(s) to be updated with server response
			beforeSubmit:  AddUpProductsToOrderPage.showRequest,  // pre-submit callback
			success:       AddUpProductsToOrderPage.showResponse  // post-submit callback
		};
		jQuery('#AddProductsToOrderRowsForm').ajaxForm(options);
	}
}


