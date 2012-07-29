<?php


/**
*@author Nicolaas [at] sunnysideup.co.nz
*
**/

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START ecommerce_corporate_account MODULE ----------------===================
//MUST SET... WHERE APPLICABLE
/**
 * ADD TO ECOMMERCE.YAML:
Order:
	modifiers: [
		...
		OrderMarker
	]
*/
//Object::add_extension("EcommerceDatabaseAdmin", "CreateEcommerceApprovedCustomerGroup_AdminDecorator");
//Object::add_extension("Product", "EcommerceCorporateGroupBuyableDecorator");
//Object::add_extension("ProductVariation", "EcommerceCorporateGroupBuyableDecorator");
//Object::add_extension("Member", "EcommerceCorporateGroupMemberDecorator");
//Object::add_extension("Group", "EcommerceCorporateGroupGroupDecorator");
//Object::add_extension("OrderAddress", "EcommerceCorporateGroupAddressDecorator");
//MAY SET
//EcommerceCorporateGroupAddressDecorator::set_update_group_from_order_address(true);
//EcommerceCorporateGroupAddressDecorator::set_update_order_address_from_group(true);
//---
//EcommerceCorporateGroupBuyableDecorator::set_only_approved_customers_can_purchase(true);
//---
//EcommerceCorporateGroupGroupDecorator::set_name("approved shop customers");
//EcommerceCorporateGroupGroupDecorator::set_permission_code("approvedshopcustomers");
//EcommerceCorporateGroupGroupDecorator::set_permission_code("APPROVEDSHOPCUSTOMER");
//===================---------------- END ecommerce_corporate_account MODULE ----------------===================

