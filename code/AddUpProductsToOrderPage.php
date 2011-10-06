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

	public static $icon = "ecommerce_corporate_account/images/treeicons/AddUpProductsToOrderPage";

	public static $db = array(
		"OrderLogEntryTitle" => "Varchar",
		"RequestQuoteOnly" => "Boolean"
	);

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab(
			'Root.Content',
			array(
				new Tab(
					'PreviousEntries',
					new LiteralField("PreviousEntries", "<p>To review previous entries, please go to the <a href=\"/admin/sales/\">sales section</a> of the CMS and search for Order Logs.</p>")
				),
				new Tab(
					'Process',
					new TextField("OrderLogEntryTitle", "Title to use on orders for break down per item and name (e.g. order breakdown)"),
					new CheckboxField("RequestQuoteOnly", "Request quote only")
				)
			)
		);
		return $fields;
	}

}


class AddUpProductsToOrderPage_Controller extends Page_Controller {

	function init(){
		parent::init();
		Requirements::themedCSS("AddUpProductsToOrderPage");
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
		Requirements::javascript("ecommerce_corporate_account/javascript/AddUpProductsToOrderPage.js");
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
		elseif($savedValuesArray && count($savedValuesArray)) {
			$this->rowNumbers = count($savedValuesArray);
		}
		Requirements::customScript("AddUpProductsToOrderPage.setRowNumbers(".$this->RowNumbers().");", "setRowNumbers");
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
						if($buyable = DataObject::get_by_id($className, $id)) {
							$qty = round($_REQUEST["qty_$i"], $buyable->QuantityDecimals());
							if($qty) {
								$buyable->Qty = 0;
								$name = strtoupper(Convert::raw2sql(Convert::raw2xml($_REQUEST["name_$i"])));
								$buyableClassNameAndID = $className."_".$id;
								$price = $buyable->getCalculatedPrice();
								$total = $price * $qty;
								$innerArray = array(
									"Name" => $name,
									"Qty" => $qty,
									"BuyableClassNameAndID" => $buyableClassNameAndID,
									"ClassName" => $className,
									"ID" => $id,
									"Buyable" => $buyable,
									"Price" => $price,
									"Total" => $total,
									"MyTitle" => $buyable->Title
								);
								$array[$i] = $innerArray;
								$buyableArray[$buyableClassNameAndID][] = $innerArray;
								$nameArray[$name][$buyableClassNameAndID][] = $innerArray;
							}
						}
					}
				}
			}
		}

		Session::set("AddProductsToOrderRows", serialize($array));
		$array = null;
		$innerArray = null;
		$buyable = null;

		// per BUYABLE
		$buyableSummaryDos = null;
		$buyableGrandTotal = 0;
		if(count($buyableArray)) {
			$buyableSummaryDos = new DataObjectSet();
			foreach($buyableArray as $buyableClassNameAndID =>  $buyables) {
				$buyableQty = 0;
				$buyableTotal = 0;
				foreach($buyables as $buyableEntry) {
					$buyableQty += $buyableEntry["Qty"];
					$buyableTotal += $buyableEntry["Total"];
				}
				$myTitle = $buyableEntry["MyTitle"];
				$price = $buyableEntry["Price"];
				$buyableDo = new DataObject();
				$buyableDo->MyTitle = $myTitle;
				$buyableDo->Buyable = $buyableEntry["Buyable"];
				$buyableDo->Qty = $buyableQty;
				$buyableDo->Price = $price;
				$buyableDo->PriceNice = DBField::create("Currency", $price, "PriceNice".$buyableClassNameAndID)->Nice();
				$buyableDo->Total = $buyableTotal;
				$buyableDo->TotalNice = DBField::create("Currency", $buyableTotal, "TotalNice".$buyableClassNameAndID)->Nice();
				$buyableSummaryDos->push($buyableDo);
				$buyableGrandTotal += $buyableTotal;
			}
		}
		$buyableGrandTotalNice = DBField::create("Currency", $buyableGrandTotal, "buyableGrandTotal")->Nice();

		// per NAME
		$nameSummaryDos = null;
		$nameGrandTotal = 0;
		if(count($nameArray)) {
			$nameSummaryDos = new DataObjectSet();
			$buyablesDos = array();
			foreach($nameArray as $name => $nameEntry) {
				$nameDo = new DataObject();
				$nameDo->Name = $name;
				$nameTotal = 0;
				$nameQty = 0;
				if(count($nameEntry)) {
					$buyableDo = array();
					$nameDo->Buyables = new DataObjectSet();
					foreach($nameEntry as $buyableClassNameAndID => $buyables) {
						if(count($buyables)) {
							$buyableQty = 0;
							$buyableTotal = 0;
							foreach($buyables as $buyableEntry) {
								$buyableQty += $buyableEntry["Qty"];
								$buyableTotal += $buyableEntry["Total"];
								$nameQty += $buyableEntry["Qty"];
								$nameTotal += $buyableEntry["Total"];
							}
							$myTitle = $buyableEntry["MyTitle"];
							$price = $buyableEntry["Price"];
							$buyableDo[$buyableClassNameAndID] = new DataObject();
							$buyableDo[$buyableClassNameAndID]->MyTitle = $myTitle;
							//$buyableDo->Buyable = $buyableEntry["Buyable"];
							$buyableDo[$buyableClassNameAndID]->Qty = $buyableQty;
							$buyableDo[$buyableClassNameAndID]->Price = $price;
							$buyableDo[$buyableClassNameAndID]->PriceNice = DBField::create("Currency", $price, "PriceNice".$buyableClassNameAndID)->Nice();
							$buyableDo[$buyableClassNameAndID]->Total = $buyableTotal;
							$buyableDo[$buyableClassNameAndID]->TotalNice = DBField::create("Currency", $buyableTotal, "TotalNice".$buyableClassNameAndID)->Nice();
							$nameDo->Buyables->push($buyableDo[$buyableClassNameAndID]);
							$nameGrandTotal += $buyableTotal;
						}
					}
				}
				$nameDo->Qty = $nameQty;
				$nameDo->Total = $nameTotal;
				$nameDo->TotalNice = DBField::create("Currency", $nameTotal, "TotalNice".$name)->Nice();
				$nameSummaryDos->push($nameDo);
			}
		}
		$nameGrandTotalNice = DBField::create("Currency", $nameGrandTotal, "nameGrandTotal")->Nice();

		$customiseArray = array(
			"Message" => "",
			"BuyableSummary" => $buyableSummaryDos,
			"NameSummary" => $nameSummaryDos,
			"BuyableGrandTotalNice" => $buyableGrandTotalNice,
			"NameGrandTotalNice" => $nameGrandTotalNice
		);

		//submit?
		if((isset($_REQUEST["submit"]) && $_REQUEST["submit"]) || (isset($_REQUEST["quote"]) && $_REQUEST["quote"])) {
			if($buyableSummaryDos){
				$sc = ShoppingCart::singleton();
				foreach($buyableSummaryDos as $buyableDo) {
					$sc->addBuyable($buyableDo->Buyable, $buyableDo->Qty);
				}
				$checkoutPage = DataObject::get_one("CheckoutPage");
				$html = $this->customise($customiseArray)->renderWith("AddProductsToOrderResultsAjax");
				$logEntry = DataObject::get_one("AddUpProductsToOrderPageStatusLog", "OrderID = ".ShoppingCart::current_order()->ID);
				if(!$logEntry) {
					$logEntry = new AddUpProductsToOrderPageStatusLog();
					$logEntry->OrderID = $sc->currentOrder()->ID;
				}
				$logEntry->Title = ($this->OrderLogEntryTitle ? $this->OrderLogEntryTitle : "Order Breakdown");
				$logEntry->Note = $html;
				$logEntry->write();
				Session::set("AddProductsToOrderRows", null);
				Session::save();
				Session::clear("AddProductsToOrderRows");
				Session::save();
				$customiseArray["Message"] = "Entries updated.";
			}
			else {
				$customiseArray["Message"] = "No products added.";
			}
		}
		else {
			if($buyableSummaryDos){
				$customiseArray["Message"] = "Entries updated.";
			}
			else {
				$customiseArray["Message"] = "No products added.";
			}
		}
		return $this->customise($customiseArray)->renderWith("AddProductsToOrderResultsAjax");
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

