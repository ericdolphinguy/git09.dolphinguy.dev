<?php
require_once dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
require_once dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
require_once dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
require_once dirname(__FILE__) . '/../../controller/CartController.php';
require_once dirname(__FILE__) . '/../utils/DateHelper.php';

class Booki_MasterTmpl{
	public $projectId;
	public $bookingPeriodValid = false;
	public $hasAvailableBookings = false;
	public $active = true;
	public $globalSettings;
	public function __construct(){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$projectRepository = new Booki_ProjectRepository();
		$project = $projectRepository->read($this->projectId);

		$this->active = $project && $project->status === Booki_ProjectStatus::RUNNING;
		if(!$this->active){
			return;
		}
		$calendarRepository =  new Booki_CalendarRepository();
		$calendar = $calendarRepository->readByProject($this->projectId);
		if(!$calendar){
			return;
		}
		$this->bookingPeriodValid = Booki_DateHelper::todayLessThanOrEqualTo($calendar->endDate);
		if($this->bookingPeriodValid){
			$this->hasAvailableBookings = Booki_BookingProvider::hasAvailability($this->projectId);
		}

		if($calendar->exhausted()){
			$this->hasAvailableBookings = false;
		}
	}
}
?>