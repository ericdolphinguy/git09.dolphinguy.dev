<?php
require_once 'Helper.php';
class Booki_TimeHelper{
	public static function listCompactZones(){
		return array(	'Pacific/Majuro',
						'Pacific/Pago_Pago',
						'America/Adak',
						'Pacific/Honolulu',
						'Pacific/Marquesas',
						'Pacific/Gambier',
						'America/Anchorage',
						'America/Los_Angeles',
						'Pacific/Pitcairn',
						'America/Phoenix',
						'America/Denver',
						'America/Guatemala',
						'America/Chicago',
						'Pacific/Easter',
						'America/Bogota',
						'America/New_York',
						'America/Caracas',
						'America/Halifax',
						'America/Santo_Domingo',
						'America/Santiago',
						'America/St_Johns',
						'America/Godthab',
						'America/Argentina/Buenos_Aires',
						'America/Montevideo',
						'America/Noronha',
						'America/Noronha',
						'Atlantic/Azores',
						'Atlantic/Cape_Verde',
						'UTC',
						'Europe/London',
						'Europe/Berlin',
						'Europe/Rome',
						'Africa/Lagos',
						'Africa/Windhoek',
						'Asia/Beirut',
						'Africa/Johannesburg',
						'Asia/Baghdad',
						'Europe/Moscow',
						'Asia/Tehran',
						'Asia/Dubai',
						'Asia/Baku',
						'Asia/Kabul',
						'Asia/Yekaterinburg',
						'Asia/Karachi',
						'Asia/Kolkata',
						'Asia/Kathmandu',
						'Asia/Dhaka',
						'Asia/Omsk',
						'Asia/Rangoon',
						'Asia/Krasnoyarsk',
						'Asia/Jakarta',
						'Asia/Shanghai',
						'Asia/Irkutsk',
						'Australia/Eucla',
						'Australia/Eucla',
						'Asia/Yakutsk',
						'Asia/Tokyo',
						'Australia/Darwin',
						'Australia/Adelaide',
						'Australia/Brisbane',
						'Asia/Vladivostok',
						'Australia/Sydney',
						'Australia/Lord_Howe',
						'Asia/Kamchatka',
						'Pacific/Noumea',
						'Pacific/Norfolk',
						'Pacific/Auckland',
						'Pacific/Tarawa',
						'Pacific/Chatham',
						'Pacific/Tongatapu',
						'Pacific/Apia',
						'Pacific/Kiritimati');
	}

	public static function timezoneInfo($value = null){
		if($value){
			try{
				$testTimezone = new DateTimeZone($value);
			}catch(Exception $exception){
				$value = null;
			}
		}
		$timezone = $value;
		if(!$value){
			$globalSettings = Booki_Helper::globalSettings();
			$timezone = $globalSettings->timezone;
		}
		
		if(!$timezone || is_numeric($timezone)){
			//seems that whatever wordpress set in timezone_string
			//does not work for us, get default from php
			$timezone = date_default_timezone_get();
		}

		
		$transition = self::getTimezoneAbbrOffset($timezone);
		
		return array(
			'abbr'=>$transition['abbr']
			, 'timezone'=>$timezone
			, 'offset'=>$transition['offset']
			, 'transition'=>$transition
		);
	}
	
	public static function formatOffset($offset) {
		return sprintf('%+03d:%02u', floor($offset / 3600), floor(abs($offset) % 3600 / 60));
	}
	
	public static function timeZoneChoice( $selectedZone = null ) {
		$utc = new DateTimeZone('UTC');
		$dt = new Booki_DateTime('now', $utc);
		$regions = array(
							'Africa' , 'America' , 'Antarctica' , 'Asia' , 'Atlantic'
							, 'Australia' , 'Brazil', 'Canada', 'Europe'
							, 'Indian', 'Pacific' , 'US'
							, 'Arctic', 'Chile', 'Cuba', 'Egypt', 'Hongkong'
							, 'Iceland', 'Iran', 'Israel', 'Jamaica', 'Japan', 'Kwajalein', 'Libya'
							, 'Mexico', 'NZ', 'Navajo', 'Poland', 'Portugal', 'Singapore', 'Turkey'
							, 'Greenwich', 'UTC', 'Universal', 'Zulu'
		);

		$timezones = DateTimeZone::listIdentifiers();
		sort($timezones);
		$result = array();
		$region = null;
		$abbrGrouping = array();
		foreach($timezones as $timezone) {
			$pair = explode('/', $timezone, 2);
			$regionName = $pair[0];
			if(!in_array($regionName, $regions)){
				continue;
			}
			
			$transition = self::getTimezoneAbbrOffset($timezone);
			if(!$transition){
				continue;
			}
			
			if($region !== $regionName){
				$region = $regionName;
				if(count($result) > 0){
					array_push($result, '</optgroup>');
				}
				array_push($result, '<optgroup label="' . $region . '">');
			}
			
			
			$offset = $transition['offset'];

			if(!isset($abbrGrouping[$offset])){
				$abbrGrouping[$offset] = array();
			}
			array_push($abbrGrouping[$offset], isset($pair[1]) ? $pair[1] : $pair[0]);
			$selected = ($selectedZone === $timezone) ? 'selected ' : '';
			
			array_push($result, '<option ' . $selected . 'value="' . $timezone . '">' . $timezone . ' ' . self::formatTimezoneTransition($transition) . '</option>');
		}

		array_push($result, '</optgroup>');
		return join( "\n", $result );
	}
	
	public static function formatTimezoneTransition($transition){
		$result = $transition['abbr'] . ' ' . self::formatOffset($transition['offset']);
		if($transition['isdst'] ){
			$result .= ' - DST';
		}
		return $result;
	}
	
	public static function getTimezoneAbbrOffset($timezone){
		try{
			$dtz = new DateTimeZone($timezone);
		}catch(Exception $exception){
			$dtz = null;
		}
		
		if($dtz === null){
			return null;
		}
		
		$transitions = $dtz->getTransitions();
		$transition = null;
		$year = date('Y');
		if(!$transitions){
			return null;
		}
		
		$length = count($transitions) - 1;

		if(($transitions[$length]['ts'] < time())){
			return $transitions[$length];
		}
		
		foreach ($transitions as $key=>$value){
			// look for current year
			if (substr($value['time'],0,4) === $year){
				$transition = $value;
				break;
			}
		}
		return $transition;
	}
	
	public static function formatTime($item, $timezone, $enableSingleHourMinuteFormat, $timeFormat = null){
		if($item->hourStart === null){
			return '';
		}
		
		if(!$timeFormat){
			$timeFormat = get_option('time_format');
		}
		
		$globalTimezoneInfo = self::timezoneInfo();
		$globalTimezoneString = $globalTimezoneInfo['timezone'];
		
		$hasOffset = true;
		if(!$timezone){
			$timezone = $globalTimezoneString;
			$hasOffset = false;
		}
		
		$timezoneInfo = self::timezoneInfo($timezone);
		$timezoneString = $timezoneInfo['timezone'];
		$dateTime = new Booki_DateTime();
		$dateTime->setTimeZone(new DateTimeZone($globalTimezoneString));
		
		$dateTimezone = new DateTimeZone($timezoneString);
		$timezoneOffset = $dateTimezone->getOffset($dateTime);

		$offset = !$hasOffset ? 0 : $timezoneOffset;
		$formattedTime = '';
		if($item->hourStart !== null && $item->minuteStart !== null){
			$dateTime->setTime($item->hourStart, $item->minuteStart);
			$formattedTime = date($timeFormat, $dateTime->format('U') + $offset);
		}
		if(!$enableSingleHourMinuteFormat && ($item->hourEnd !== null && $item->minuteEnd !== null)){
			$dateTime->setTime($item->hourEnd, $item->minuteEnd);
			$formattedTime .= ' - ' . date($timeFormat, $dateTime->format('U') + $offset);
		}
		return $formattedTime;
	}
}
?>