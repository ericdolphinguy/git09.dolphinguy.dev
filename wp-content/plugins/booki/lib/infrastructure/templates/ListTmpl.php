<?php
require_once  dirname(__FILE__) . '/../../controller/SearchControlController.php';

class Booki_ListTmpl{
	public $projectList;
	public $heading;
	public $fromDate;
	public $toDate;
	public $fromLabel;
	public $toLabel;
	public $enableSearch;
	public $isWidget;
	public $uniqueKey;
	public $dateFormat;
	public $altFormat;
	public $calendarCssClasses;
	public $calendarFirstDay;
	public $showCalendarButtonPanel;
	public $enableItemHeading;
	
	public function __construct(){
		$listArgs = apply_filters('booki_list', null);
		$tags = $listArgs['tags'];
		$headingLength = isset($listArgs['headingLength']) ? intval($listArgs['headingLength']) : 0;
		$descriptionLength = isset($listArgs['descriptionLength']) ? intval($listArgs['descriptionLength']) : 0;
		$perPage = intval($listArgs['perPage']);
		$fullPager = $listArgs['fullPager'];
		
		$this->enableItemHeading = $listArgs['enableItemHeading'];
		$this->heading = $listArgs['heading'];
		$this->fromLabel = $listArgs['fromLabel'];
		$this->toLabel = $listArgs['toLabel'];
		$this->enableSearch = $listArgs['enableSearch'];
		$this->isWidget = isset($listArgs['widget']);
		$projectId = isset($listArgs['projectId']) ? $listArgs['projectId'] : -1;
		
		$this->uniqueKey = uniqid();
		
		$globalSettings = Booki_Helper::globalSettings();
		$this->dateFormat = $globalSettings->shorthandDateFormat;
		$this->altFormat = Booki_DateHelper::getJQueryCalendarFormat($this->dateFormat);
		
		$calendarStyles = array();
		if($globalSettings->calendarFlatStyle){
			array_push($calendarStyles, 'booki-flat');
		}
		if($globalSettings->calendarBorderlessStyle){
			array_push($calendarStyles, 'booki-borderless');
		}
		$this->calendarCssClasses = implode(' ', $calendarStyles);
		$this->calendarFirstDay = $globalSettings->calendarFirstDay;
		$this->showCalendarButtonPanel = $globalSettings->showCalendarButtonPanel ? 'true' : 'false';
		
		new Booki_SearchControlController($listArgs, array($this, 'search'));
		if (!(array_key_exists('controller', $_GET) 
			&& $_GET['controller'] == 'booki_searchcontrol')){
			return;
		}
		$this->fromDate = $_GET['fromDate'];
		$this->toDate = $_GET['toDate'];
	}
	public function search($result){
		$this->projectList = $result;
	}
}
?>