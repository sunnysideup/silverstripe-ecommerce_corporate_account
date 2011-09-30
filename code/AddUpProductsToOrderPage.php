<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_delivery
 * @description: Shipping calculation scheme based on SimpleShippingModifier.
 * It lets you set fixed shipping costs, or a fixed
 * cost for each region you're delivering to.
 */
class AddUpProductsToOrderPage extends Page {

}


class AddUpProductsToOrderPage_Controller extends Page_Controller {

	function init(){
		parent::init();
		Requirements::themedCSS("AddUpProductsToOrderPage");
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
		Requirements::javascript("ecommerce_corporate_account/javascript/AddUpProductsToOrderPage.js");
		Requirements::customScript("AddUpProductsToOrderPage.setRowNumbers(".$this->RowNumbers().");", "setRowNumbers");
		$checkoutPage = DataObject::get_one("CheckoutPage");
		Requirements::customScript("AddUpProductsToOrderPage.setCheckoutLink('".$checkoutPage->Link()."');", "setCheckoutLink");
	}

	protected $rowNumbers = 1;

	/**
	 *@return DOS
	 *
	 **/
	function AddProductsToOrderRows(){
		$buyables = DataObject::get("Product");
		$dos = new DataObjectSet();
		$savedValuesArray = unserialize(Session::get("AddProductsToOrderRows"));
		$startNumber = 0;
		if(Director::is_ajax()) {
			$startNumber = $this->rowNumbers - 1;
		}
		for($i = $startNumber ; $i < $this->rowNumbers; $i++){
			if(!isset($savedValuesArray[$i])) {$savedValuesArray[$i] = array();}
			if(!isset($savedValuesArray[$i]["Name"])){$savedValuesArray[$i]["Name"]= "";}
			if(!isset($savedValuesArray[$i]["Qty"])){$savedValuesArray[$i]["Qty"]= 0;}
			if(!isset($savedValuesArray[$i]["BuyableClassNameAndID"])){$savedValuesArray[$i]["BuyableClassNameAndID"]= 0;}
			if(!isset($savedValuesArray[$i]["Total"])){$savedValuesArray[$i]["Total"]= 0;}
			$do = new DataObject();
			$do->RowNumber = $i;
			$do->Name = $savedValuesArray[$i]["Name"];
			$do->Qty = $savedValuesArray[$i]["Qty"];
			$do->BuyableClassNameAndID = $savedValuesArray[$i]["BuyableClassNameAndID"];
			$do->Total = $savedValuesArray[$i]["Total"];
			$do->Buyables = $buyables;
			$dos->push($do);
		}
		return $dos;
	}

	function submit($request){
		$this->rowNumbers = intval($_REQUEST["rowNumbers"]);
		$array = array();
		for($i = 0 ; $i <= $this->rowNumbers; $i++){
			if(isset($_REQUEST["buyable_$i"])) {
				$explodeArray = explode("_", $_REQUEST["buyable_$i"]);
				if(is_array($explodeArray) && count($explodeArray) == 2) {
					list($className, $id) = $explodeArray ;
					if(class_exists($className)) {
						$id = intval($id);
						$qty = intval($_REQUEST["qty_$i"]);
						if($qty) {
							if($buyable = DataObject::get_by_id($className, $id)) {
								$buyable->Qty = 0;
								$array[$i] = array(
									"Name" => Convert::raw2sql($_REQUEST["name_$i"]),
									"Qty" => $qty,
									"BuyableClassNameAndID" => $className."_".$id,
									"ClassName" => $className,
									"ID" => $id,
									"Buyable" => $buyable
								);
							}
						}
					}
				}
			}
		}

		Session::set("AddProductsToOrderRows", serialize($array));

		$buyableSummaryDos = null;
		$nameSummaryDos = null;
		$nameArray = array();
		if(is_array($array)) {
			if(count($array)) {
				$buyableSummaryDos = new DataObjectSet();
				foreach($array as $arrayInner) {
					if(isset($arrayInner["Buyable"]) && $arrayInner["Buyable"]) {
						$name = $arrayInner["Name"];
						$className = $arrayInner["ClassName"];
						$id = $arrayInner["ID"];
						$qty = $arrayInner["Qty"];
						$buyable = $arrayInner["Buyable"];
						//quantity
						$buyable->Qty = $buyable->Qty + $qty;
						$buyableSummaryDos->push($buyable, $className.$id);
						// by name
						if(!isset($nameArray[$name])) {
							$nameArray[$name] = new DataObject();
							$nameArray[$name]->SumTotalCalculation = 0;
							$nameArray[$name]->Name = $name;
							$quantitiesArray[$name] = array();
							$nameArray[$name]->quantitiesArray = Array();
							$nameArray[$name]->Buyables = new DataObjectSet();
						}
						$price = $buyable->getCalculatedPrice();
						$itemTotalCalculation = $price * $qty;

						$nameArray[$name]->SumTotalCalculation += $itemTotalCalculation;
						$sumTotal = DBField::create("Currency", $nameArray[$name]->SumTotalCalculation, "sumTotal".$name.$className.$id)->Nice();
						$nameArray[$name]->SumTotal = $sumTotal;
						if(!isset($quantitiesArray[$name][$className.$id])) {
							$quantitiesArray[$name][$className.$id] = 0;
						}
						$quantitiesArray[$name][$className.$id] += $qty;
						$do = new DataObject();
						$do->Buyable = $buyable;
						$do->Qty += $quantitiesArray[$name][$className.$id];
						$do->Price = DBField::create("Currency", $price, "itemPrice".$name.$className.$id)->Nice();
						$do->ItemTotal =  DBField::create("Currency", $quantitiesArray[$name][$className.$id] * $price, "itemTotal".$name.$className.$id)->Nice();
						$nameArray[$name]->Buyables->push($do, $buyable->ID);
					}
				}
			}
		}
		if(is_array($nameArray) && count($nameArray) ) {
			$nameSummaryDos = new DataObjectSet();
			foreach($nameArray as $nameDo) {
				$nameSummaryDos->push($nameDo);
			}
		}
		if(isset($_REQUEST["submit"])) {
			if($buyableSummaryDos){
				$sc = ShoppingCart::singleton();
				foreach($buyableSummaryDos as $buyable) {
					$sc->addBuyable($buyable, $buyable->Qty);
				}
				$checkoutPage = DataObject::get_one("CheckoutPage");
				Session::clear("AddProductsToOrderRows");
				Session::save();
				$msg = "Products added to cart ...<a href=\"".$checkoutPage->Link()."\">continue to ".$checkoutPage->Title."</a>.
				<script type=\"text/javascript\">window.location(www.cnn.com)</script>";
			}
			else {
				$msg = "No products added";
			}
		}
		else {
			$msg = "Entries updated";
		}
		return $this->customise(array("Message" => $msg, "BuyableSummary" => $buyableSummaryDos, "NameSummary" => $nameSummaryDos))->renderWith("AddProductsToOrderResultsAjax");
	}

	function addrow($request){
		$getVarArray = $request->getVars();
		$this->rowNumbers = intval($getVarArray["rowNumbers"]);
		return $this->renderWith("AddProductsToOrderAjax");
	}

	function RowNumbers(){
		return $this->rowNumbers;
	}

	function reset() {
		Session::clear("AddProductsToOrderRows");
		Session::save();
		Director::redirectBack();
	}

}

