jQuery(document).ready(
	function() {
		AddUpProductsToOrderPage.init();
	}
);



var AddUpProductsToOrderPage = {

	rowNumbers: 1,
		setRowNumbers: function(i) {this.rowNumbers = i;},

	init: function() {
		jQuery(".addProductsToOrderAddRows a").live(
			"click",
			function() {
				AddUpProductsToOrderPage.rowNumbers++;
				jQuery("input[name='rowNumbers']").val(AddUpProductsToOrderPage.rowNumbers++);
				url = jQuery(this).attr("href");
				jQuery.ajax({
					url: url,
					data: {rowNumbers: AddUpProductsToOrderPage.rowNumbers},
					success: function(data) {
						jQuery('#AddProductsToOrderRowsTable tbody tr:last').after(data);
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
						if(val.length == 0) {
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
						var val = parseFloat(jQuery(this).val());
						if(!val) {
							jQuery(this).val(0);
						}
						else if(val < 0) {
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

				var options = {
						target:        '#AddProductsToOrderRowsResult',   // target element(s) to be updated with server response
						beforeSubmit:  showRequest,  // pre-submit callback
						success:       showResponse  // post-submit callback

						// other available options:
						//url:       url         // override for form's 'action' attribute
						//type:      type        // 'get' or 'post', override for form's 'method' attribute
						//dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
						//clearForm: true        // clear all form fields after successful submit
						//resetForm: true        // reset the form after successful submit

						// jQuery.ajax options can be used here too, for example:
						//timeout:   3000
				};

				// bind form using 'ajaxForm'
				jQuery('#AddProductsToOrderRowsForm').ajaxForm(options);
		});

		// pre-submit callback
		function showRequest(formData, jqForm, options) {
				jQuery("#AddProductsToOrderRowsResult").text("loading").addClass("loading");
				for(var i = 0; i < this.rowNumbers; i++) {
					jQuery("#Name_"+i+" input").change();
					jQuery("#Buyable_"+i+" select").change();
					jQuery("#Qty_"+i+" input").change();
				}
				// formData is an array; here we use jQuery.param to convert it to a string to display it
				// but the form plugin does this for you automatically when it submits the data
				var queryString = jQuery.param(formData);

				// jqForm is a jQuery object encapsulating the form element.  To access the
				// DOM element for the form do this:
				// var formElement = jqForm[0];

				//alert('About to submit: \n\n' + queryString);

				// here we could return false to prevent the form from being submitted;
				// returning anything other than false will allow the form submit to continue
				return true;
		}

		// post-submit callback
		function showResponse(responseText, statusText, xhr, jQueryform)  {
				// for normal html responses, the first argument to the success callback
				// is the XMLHttpRequest object's responseText property

				// if the ajaxForm method was passed an Options Object with the dataType
				// property set to 'xml' then the first argument to the success callback
				// is the XMLHttpRequest object's responseXML property

				// if the ajaxForm method was passed an Options Object with the dataType
				// property set to 'json' then the first argument to the success callback
				// is the json data object returned by the server
				//alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +'\n\nThe output div should have already been updated with the responseText.');
				jQuery("#AddProductsToOrderRowsResult").html(response).removeClass("loading");
		}
	},

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
	}
}


