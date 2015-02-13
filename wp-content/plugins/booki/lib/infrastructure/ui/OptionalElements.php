<?php
require_once  dirname(__FILE__) . '/../../domainmodel/entities/Optionals.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/OptionalsBookingMode.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/OptionalsListingMode.php';
require_once  dirname(__FILE__) . '/../utils/Helper.php';
class Booki_OptionalElements{
	public $optionals = array();
	public $currency;
	public $currencySymbol;
	public $optionalItemsLabel;
	public $optionalsBookingMode;
	public $optionalsListingMode;
	public $optionalsMinimumSelection;
	public $defaultSelection = 0;
	public $groupName;
	public function __construct(Booki_Optionals $optionals, $project, $currency, $currencySymbol){
		$this->groupName = 'booki_optional_group_'. $project->id;
		
		$this->currency = $currency;
		$this->currencySymbol = $currencySymbol;
		$this->optionalItemsLabel = $project->optionalItemsLabel_loc;
		$this->optionalsBookingMode = $project->optionalsBookingMode;
		$this->optionalsListingMode = $project->optionalsListingMode;
		$this->optionalsMinimumSelection = $project->optionalsMinimumSelection;

		$selections = array();
		if(isset($_POST[$this->groupName])){
			$selections = $_POST[$this->groupName];
		}
		if($optionals->count() > 0){
			$optional = $optionals->item(0);
			if(($this->optionalsListingMode === Booki_OptionalsListingMode::RADIOBUTTONLIST || 
				$this->optionalsMinimumSelection > 0) && count($selections) === 0){
				array_push($selections, (string)$optional->id);
			}
		}

		foreach($optionals as $optional){
			$o = new stdClass();
			$o->id = $optional->id;
			$o->name = $optional->name_loc;
			$o->currency = $currency;
			$o->currencySymbol = $currencySymbol;
			$o->cost = $optional->cost;
			$o->formattedCost = $currencySymbol . Booki_Helper::toMoney($optional->cost);
			$o->checkedStatus = in_array((string)$o->id, $selections) ? 'checked' : '';
			array_push($this->optionals, $o);
		}
	}
}
?>