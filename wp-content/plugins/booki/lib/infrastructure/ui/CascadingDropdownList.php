<?php
require_once  dirname(__FILE__) . '/../../domainmodel/entities/CascadingItem.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/CascadingList.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/CascadingLists.php';
require_once  dirname(__FILE__) . '/../utils/Helper.php';
class Booki_CascadingDropdownList{
	public $cascadingLists;
	public $currency;
	public $currencySymbol;
	public $optionalsBookingMode;
	public function __construct(Booki_CascadingLists $cascadingLists, $project, $currency, $currencySymbol){
		$this->cascadingLists = $cascadingLists;
		$this->currency = $currency;
		$this->currencySymbol = $currencySymbol;
		$this->optionalsBookingMode = $project->optionalsBookingMode;
	}
}
?>