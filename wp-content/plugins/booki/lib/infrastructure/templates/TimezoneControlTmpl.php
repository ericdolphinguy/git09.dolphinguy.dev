<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';

class Booki_TimezoneControlTmpl{
	public $regions;
	public $autoTimezoneDetection;
	public $imezone;
	public function __construct(){
		$this->regions = $this->timeZoneRegions();
		$globalSettings = Booki_Helper::globalSettings();

		$this->autoTimezoneDetection = $globalSettings->autoTimezoneDetection;
		$this->timezone = $globalSettings->timezone;
	}
	
	public function timeZoneRegions(){
		$result = array();
		$regions = array('Africa', 'America', 'Antarctica', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific', 'Arctic', 'UTC');
		foreach($regions as $region){
			array_push($result, '<option value="' . $region . '">');
			array_push($result, $region);
			array_push($result, '</option>');
		}
		return join( "\n", $result );
	}
}
?>