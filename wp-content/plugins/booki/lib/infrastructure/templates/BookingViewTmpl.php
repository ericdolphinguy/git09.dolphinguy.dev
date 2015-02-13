<?php
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
class Booki_BookingViewTmpl{
	public $projectId;
	public $projectListArgs;
	public function __construct(){
		$this->projectId = isset($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
		add_filter( 'booki_project_id', array($this, 'getProjectId'));

		$this->projectListArgs = $args = array(
			'tags'=>isset($_GET['tags']) ? $_GET['tags'] : ''
			, 'heading'=>isset($_GET['heading']) ? $_GET['heading'] : __('Find a booking', 'booki')
			, 'fromLabel'=>isset($_GET['fromlabel']) ? $_GET['fromlabel'] : __('Check-in', 'booki')
			, 'toLabel'=>isset($_GET['tolabel']) ? $_GET['tolabel'] : __('Check-out', 'booki')
			, 'perPage'=>isset($_GET['perpage']) ? intval($_GET['perpage']) : 5
			, 'fullPager'=>isset($_GET['enablesearch']) ? filter_var($_GET['fullpager'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableSearch'=>isset($_GET['enablesearch']) ? filter_var($_GET['enablesearch'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableItemHeading'=>isset($_GET['enableitemheading']) ? filter_var($_GET['enableitemheading'], FILTER_VALIDATE_BOOLEAN) : false
			, 'projectId'=>$this->projectId
		);
	}
	
	public function getProjectId(){
		return $this->projectId;
	}
}
?>