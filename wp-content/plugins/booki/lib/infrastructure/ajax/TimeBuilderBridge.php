<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
require_once  dirname(__FILE__) . '/../session/Cart.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_TimeBuilderBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_getTimeSlots', array($this, 'getTimeSlotsCallback')); 
		add_action('wp_ajax_nopriv_booki_getTimeSlots', array($this, 'getTimeSlotsCallback')); 
	}
	
	public  function getTimeSlotsCallback(){

		$model = $_POST['model'];
		$result = $this->createTimeSlots((int)$model['hours'], (int)$model['minutes'], (int)$model['hourStartInterval'], (int)$model['minuteStartInterval'], filter_var($model['enableSingleHourMinuteFormat'], FILTER_VALIDATE_BOOLEAN), $model['timezone']);
		$timezone = $model['timezone'] ? $model['timezone'] : null;
		
		$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		
		$result = array('timeslots'=>$result, 'timezoneInfo'=>$timezoneInfo);
		echo Booki_Helper::json_encode_response($result, 'result');

		die();
	}
	
	protected function createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $enableSingleHourMinuteFormat, $timezoneString = null){
		$slots = array();
		$timeFormat = get_option('time_format');
		if(!$timeFormat){
			$timeFormat = 'g:i a';
		}
		
		$timezoneInfo = Booki_TimeHelper::timezoneInfo($timezoneString);
		$timezoneString = $timezoneInfo['timezone'];
		
		$globalTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$globalTimezoneString = $globalTimezoneInfo['timezone'];
		
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
			
		$bookings->setTimezone($timezoneString);
		
		$dateTime = new Booki_DateTime();
		$dateTime->setTimeZone(new DateTimeZone($globalTimezoneString));
		
		$dateTimezone = new DateTimeZone($timezoneString);
		
		$offset = $dateTimezone->getOffset($dateTime);

		$startTime = strtotime(sprintf('%02d:%02d:00', $hourStartInterval, $minuteStartInterval));
		$endTime = $startTime + (86400 - 3600);

		while ($startTime <= $endTime)
		{
			$timeStartString = date ('H:i:s', $startTime);
			$timeStartSegments = explode(':', $timeStartString);
			$hourStart = (int)$timeStartSegments[0];
			$minuteStart = (int)$timeStartSegments[1];
			
			$interval = strtotime('+' . $hours . ' hours ' . $minutes . ' minutes', $startTime);
			$timeEndString = date ('H:i:s', $interval);
			$timeEndSegments = explode(':', $timeEndString);
			$hourEnd = (int)$timeEndSegments[0];
			$minuteEnd = (int)$timeEndSegments[1];
			if($minuteStart === 59){
				$minuteEnd = ($minuteStart + $minutes) % 60;
			}
			$dateTime->setTime($hourStart, $minuteStart);
			$fromStartEnd = date($timeFormat, $dateTime->format('U') + $offset);
			$rawFromStartEnd = date('G:i', $dateTime->format('U') + $offset);
			
			$dateTime->setTime($hourEnd, $minuteEnd);
			$toStartEnd = date($timeFormat, $dateTime->format('U') + $offset);
			$rawToStartEnd = date('G:i', $dateTime->format('U') + $offset);
			
			$text = $fromStartEnd;
			if($enableSingleHourMinuteFormat == false){
				$text .= ' - ' . $toStartEnd;
			}
			array_push($slots, array(
				'value'=>sprintf('%d:%d,%d:%d', $hourStart,  $minuteStart, $hourEnd, $minuteEnd)
				, 'text'=>$text
				, 'rawFrom'=>$rawFromStartEnd
				, 'rawTo'=>$rawToStartEnd
			));
			
			$startTime = $interval;
		}
		
		return $slots;
	}
}
?>
