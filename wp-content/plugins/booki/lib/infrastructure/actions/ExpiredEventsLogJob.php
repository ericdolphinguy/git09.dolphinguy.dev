<?php
require_once dirname(__FILE__) . '/../utils/Helper.php';
require_once dirname(__FILE__) . '/../../domainmodel/repository/EventsLogRepository.php';
class Booki_ExpiredEventsLogJob{
	public function __construct()
	{
		if ( !wp_next_scheduled('Booki_ExpiredEventsLogJobEventHook'))
		{
			wp_schedule_event( time(), 'daily', 'Booki_ExpiredEventsLogJobEventHook' );
		}
	}
	public static function init(){
		$globalSettings = Booki_Helper::globalSettings();
		$eventsLogRepo = new Booki_EventsLogRepository();
		$days = $globalSettings->eventsLogExpiry;
		if($days > 0){
			$today = new Booki_DateTime();
			$expiryDate = date('Y-m-d', strtotime($today->format('Y-m-d') . " - $days days"));
			$result = $eventsLogRepo->deleteExpired($expiryDate);
		}
	}
}
add_action( 'Booki_ExpiredEventsLogJobEventHook', array('Booki_ExpiredEventsLogJob', 'init'));

?>