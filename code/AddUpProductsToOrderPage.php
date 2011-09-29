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
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
		Requirements::javascript("ecommerce_corporate_account/javascript/AddUpProductsToOrderPage.js");
		Requirements::customScript("AddUpProductsToOrderPage.setRowNumbers(".$this->RowNumbers().");", "setRowNumbers");
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
		$this->rowNumbers = intval($_REQUEST["rowNumbers"]) + 1;
		$array = array();
		for($i = 0 ; $i < $this->rowNumbers; $i++){
			if(isset($_REQUEST["buyable_$i"])) {
				list($className, $id) = explode("_", $_REQUEST["buyable_$i"]);
				if(class_exists($className)) {
					if($buyable = DataObject::get_by_id($className, intval($id))) {
						$array[$i] = array(
							"Name" => $_REQUEST["name_$i"],
							"Qty" => $_REQUEST["qty_$i"],
							"BuyableClassNameAndID" => $_REQUEST["buyable_$i"],
							"ClassName" => $className,
							"ID" => $id,
							"Buyable" => $buyable
						);
					}
				}
			}
			if(!isset($array[$i])) {
				$array[$i] = array(
					"Name" => "",
					"Qty" => 0,
					"BuyableClassNameAndID" => 0,
					"ClassName" => "",
					"ID" => 0,
					"Buyable" => null
				);
			}
		}
		Session::set("AddProductsToOrderRows", serialize($array));
		$summaryDos = new DataObjectSet();
		$summaryArray = array();
		if($array) {
			if(count($array)) {
				foreach($array as $arrayInner) {
					if(isset($arrayInner["Buyable"]) && $arrayInner["Buyable"]) {
						$className = $arrayInner["ClassName"];
						if(!isset($summaryArray[$className])) {
							$summaryArray[$className] = array();
						}
						$id = $arrayInner["ID"];
						$qty = $arrayInner["Qty"];
						$buyable = $arrayInner["Buyable"];
						if(isset($summaryArray[$className.$id])) {
							$buyable->Qty = $buyable->Qty + $qty;
						}
						else {
							$summaryArray[$className.$id] = true;
							$buyable->Qty = $qty;
							$summaryDos->push($buyable);
						}
					}
				}
			}
		}
		return $this->customise(array("Summary" => $summaryDos))->renderWith("AddProductsToOrderResultsAjax");
	}

	function addrow($request){
		$getVarArray = $request->getVars();
		$this->rowNumbers = intval($getVarArray["rowNumbers"]);
		return $this->renderWith("AddProductsToOrderAjax");
	}

	function RowNumbers(){
		return $this->rowNumbers;
	}

}

