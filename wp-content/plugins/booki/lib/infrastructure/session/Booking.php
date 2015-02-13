<?php
require_once  dirname(__FILE__) . '/../../domainmodel/entities/FormElements.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/Optionals.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/CascadingItems.php';
class Booki_Booking{
	public $formElements;
	public $optionals;
	public $cascadingItems;
	public $dates;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $deposit = null;
	public $id;
	public $projectId;
	public $projectName;
	public function __construct($id, $projectId, $projectName, $dates, $time = null, $deposit = null){
		$this->id = $id;
		$this->projectId = $projectId;
		$this->projectName = $projectName;
		$this->dates = explode(',', $dates);
		$this->deposit = $deposit;
		if($time){
			$t = explode(',', $time);
			$tStart = explode(':', $t[0]);
			$this->hourStart = intval($tStart[0]);
			$this->minuteStart = intval($tStart[1]);
			if(count($t) > 1){
				$tEnd = explode(':', $t[1]);
				$this->hourEnd = intval($tEnd[0]);
				$this->minuteEnd = intval($tEnd[1]);
			}
		}
		
		$this->formElements = new Booki_FormElements();
		$this->optionals = new Booki_Optionals();
		$this->cascadingItems = new Booki_CascadingItems();
	}
	
	public function hasTime(){
		return $this->hourStart !== null || $this->minuteStart !== null;
	}
}
?>