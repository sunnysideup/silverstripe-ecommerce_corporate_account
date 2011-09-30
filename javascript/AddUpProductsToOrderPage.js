jQuery(document).ready(
	function() {
		AddUpProductsToOrderPage.init();
	}
);



var AddUpProductsToOrderPage = {

	rowNumbers: 1,
		setRowNumbers: function(i) {this.rowNumbers = i;},

	CheckoutLink: "",
		setCheckoutLink: function(s) {this.CheckoutLink = s;},

	goToCheckoutLink: false,

	init: function() {
		AddUpProductsToOrderPage.setDefaultSelectValue();

		//add a row function
		jQuery(".addProductsToOrderAddRows a").live(
			"click",
			function() {
				AddUpProductsToOrderPage.rowNumbers++;
				jQuery("input[name='rowNumbers']").val(AddUpProductsToOrderPage.rowNumbers);
				url = jQuery(this).attr("href");
				jQuery.ajax({
					url: url+"?flush=1",
					data: {rowNumbers: AddUpProductsToOrderPage.rowNumbers, flush: 1},
					success: function(data) {
						jQuery('#AddProductsToOrderRowsTable tbody').append(data);
						AddUpProductsToOrderPage.setDefaultSelectValue();
						AddUpProductsToOrderPage.updateRows();
					},
					dataType: "html"
				});
				return false;
			}
		);


		// prepare the form when the DOM is ready
		jQuery(document).ready(function() {
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
				jQuery(".qty input").live(
					"change",
					function(){
						var val = parseInt(jQuery(this).val());
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

				var options = {
					target:        '#AddProductsToOrderRowsResult',   // target element(s) to be updated with server response
					beforeSubmit:  showRequest,  // pre-submit callback
					success:       showResponse  // post-submit callback
				};

				// bind form using 'ajaxForm'
				jQuery('#AddProductsToOrderRowsForm').ajaxForm(options);
		});

		// pre-submit callback
		function showRequest(formData, jqForm, options) {
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
		}

		// post-submit callback
		function showResponse(responseText, statusText, xhr, jQueryform)  {
			if(AddUpProductsToOrderPage.goToCheckoutLink) {
				window.location = AddUpProductsToOrderPage.CheckoutLink;
			}
			jQuery("#AddProductsToOrderRowsResult").removeClass("loading");
		}
	},

	updateRows: function ()  {
		for(var i = 0; i < this.rowNumbers; i++) {
			var price = parseFloat(jQuery("#Buyable_"+i+" select option:selected").attr("rel"));
			var qty = parseInt(jQuery("#Qty_"+i+" input").val());
			var total = Math.round((qty * price * 100))/100;
			if(total && total != NaN && total > 0) {
				jQuery("#Total_"+i).text("$" +total)
			}
			else {
				jQuery("#Total_"+i).text("tba");
			}
		}
	},

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
	}

}


