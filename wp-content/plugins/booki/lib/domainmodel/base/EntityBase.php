<?php
class Booki_EntityBase
{
	public function createTimeSlots($hours, $minutes, $hourStartInterval, $minuteStartInterval, $enableSingleHourMinuteFormat){
		$slots = array();
		$timeFormat = get_option('time_format');
		if(!$timeFormat){
			$timeFormat = 'g:i a';
		}
		if(!isset($hours) || !isset($minutes) || ($hours === 0 && $minutes === 0)){
			return $slots;
		}
		
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
			
			$start = new Booki_DateTime();
			$start->setTime($hourStart, $minuteStart);
			
			$end = new Booki_DateTime();
			$end->setTime($hourEnd, $minuteEnd);
			
			$text = $start->format($timeFormat);
			if(!$enableSingleHourMinuteFormat){
				$text .= ' - ' . $end->format($timeFormat);
			}
			//support am/pm from global settings.
			array_push($slots, array(
				'value'=>sprintf('%s:%s', $hourStart,  $minuteStart, $hourEnd, $minuteEnd)
				, 'text'=>$text
			));
			
			$startTime = $interval;
		}
		
		return $slots;
	}
	
	public function encode($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
	public function decode($value){
		return htmlspecialchars_decode($value, ENT_QUOTES);
	}
	
	public function keyExists($key, $arr){
		return array_key_exists($key, $arr);
	}
}
?>