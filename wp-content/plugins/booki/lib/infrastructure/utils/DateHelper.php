<?php
require_once 'Helper.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/CalendarPeriod.php';
class Booki_DateHelper{
	public static function todayLessThanOrEqualTo($myDate){
		$today = new Booki_DateTime();
		$today->setTime(0, 0, 0);
		$myDate->setTime(0, 0, 0);
		return $today <= $myDate;
	}
	
	public static function todayMoreThan($myDate){
		$today = new Booki_DateTime();
		$today->setTime(0, 0, 0);
		$myDate->setTime(0, 0, 0);
		return $today > $myDate;
	}
	
	public static function daysAreEqual($d1, $d2){
		$d1->setTime(0, 0, 0);
		$d2->setTime(0, 0, 0);
		return $d1 == $d2;
	}
	
	public static function diffFromToday($dt2){
		$current = new DateTime("now");
		$current->setTime(0, 0, 0);
		$dt2->setTime(0, 0, 0); 
		return round(abs(strtotime($current) - strtotime($dt2))/86400);
	}
	
	public static function parseFormattedDateString($dateString){
		return self::formattedDateTime($dateString);
	}
	
	public static function getTimeSlotsCount($bookedDays, $calendar, $calendarDays){
		$days = array();
		$result = array();
		foreach($bookedDays as $bookedDay){
			
			if(!$bookedDay->hasTime()){
				continue;
			}
			
			$d = self::formatString($bookedDay->bookingDate);
			if(!isset($days[$d])){
				$days[$d] = 1;
			}else{
				$days[$d] += 1;
			}
		}
		
		foreach($calendarDays as $cd){
			foreach($days as $key=>$val){
				$currentDate = Booki_DateHelper::parseFormattedDateString($key);
				$areEqual = self::daysAreEqual($cd->day, $currentDate);
				if($areEqual){
					$timeSlots = self::createTimeSlots($cd->hours, $cd->minutes);
					array_push($result, sprintf('{"day": "%s", "used": %d, "count": %d}', $key, $val, count($timeSlots)));
					unset($days[$key]);
				}
			}
		}
		
		foreach($days as $key=>$val){
			$timeSlots = self::createTimeSlots($calendar->hours, $calendar->minutes);
			array_push($result, sprintf('{"used": %d, "count": %d}', $val, count($timeSlots)));
		}
		return $result;
	}
	
	public static function availabilityInRange($calendar, $calendarDays, $bookedDays){
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$days = array();
		$usedDays = array();
		$availableDays = array();
		$calendarPeriod = $calendar->period;

		foreach($bookedDays as $bookedDay){
			$d = self::formatString($bookedDay->bookingDate);
			if(!in_array($d, $days)){
				array_push($days, $d);
			}
		}
	
		foreach($calendarDays as $cd){
			foreach($days as $key=>$value){
				$areEqual = self::formatString($cd->day) == $value;
				if($areEqual){
					$timeSlots = $calendarPeriod === Booki_CalendarPeriod::BY_TIME ? self::createTimeSlots($cd->hours, $cd->minutes) : array();
					$slotsExhausted = count($timeSlots) == count($cd->timeExcluded);
					array_push($usedDays, array('day'=>$value, 'slotsExhausted'=>$slotsExhausted));
					unset($days[$key]);
				}
			}
		}
		
		foreach($days as $value){
			$timeSlots = $calendarPeriod === Booki_CalendarPeriod::BY_TIME ? self::createTimeSlots($calendar->hours, $calendar->minutes) : array();
			$slotsExhausted = count($timeSlots) == count($calendar->timeExcluded);
			array_push($usedDays, array('day'=>$value, 'slotsExhausted'=>$slotsExhausted));
		}

		$hasAvailableDays = true;
		$start = self::getStrToTime($calendar->startDate);
		$end = self::getStrToTime($calendar->endDate);
		for ( $i = $start; $i <= $end; $i += 86400 ){
			$formattedDateString = date ($dateFormat, $i);
			$weekDay = date( 'w', $i);
			if(in_array($weekDay, $calendar->weekDaysExcluded)){
				continue;
			}
			$flag = false;
			foreach($usedDays as $d){
				$usedDay = $d['day'];
				$slotsExhausted = $d['slotsExhausted'];
				if($usedDay === $formattedDateString && 
					(($calendarPeriod === Booki_CalendarPeriod::BY_TIME && $slotsExhausted) || 
					$calendarPeriod === Booki_CalendarPeriod::BY_DAY)){
					$flag = true;
					break;
				}
			}
			$date = self::parseFormattedDateString($formattedDateString);
			$dateExpired = self::todayMoreThan($date);
			if(!$flag && !$dateExpired){
				array_push($availableDays, $formattedDateString);
			}
		}
		return array('availableDays'=>$availableDays, 'usedDays'=>$usedDays);
	}
	
	public static function createTimeSlots($hour = 1, $minute = 0){
		$slots = array ();
		$startTime = strtotime ('00:00:00');
		$endTime = strtotime ('23:59:59');

		while ($startTime <= $endTime)
		{
			$interval = strtotime('+' . $hour . ' hours ' . $minute . ' minutes', $startTime);
			array_push($slots,  date ('H:i', $interval));
			$startTime = $interval;
		}
		return $slots;
	}
	
	public static function fillBookings($calendar, $calendarDays, $bookedDays){
		foreach($bookedDays as $bookedDay){
			$bookedDate = self::formatString($bookedDay->bookingDate);
			$time = null;
			$tempList = array();
			if($calendar->period === Booki_CalendarPeriod::BY_TIME && $bookedDay->hasTime()){
				$time = $bookedDay->hourStart . ':' . $bookedDay->minuteStart;
				foreach($calendarDays as $calendarDay){
					if(self::formatString($calendarDay->day) === $bookedDate){
						if(!in_array($time, $calendarDay->timeExcluded)){
							array_push($calendarDay->timeExcluded, $time);
						}
						array_push($tempList, $bookedDate);
						break;
					}
				}
			}
			if(!in_array($bookedDate, $tempList)){
				$calendarDays->add(new Booki_CalendarDay(
					-1
					, $bookedDay->bookingDate
					, $time ? array_merge($calendar->timeExcluded, array($time)) : array()
					, $calendar->hours
					, $calendar->minutes
					, $calendar->cost
					, $calendar->hourStartInterval
					, $calendar->minuteStartInterval
					, null
					, 0
					, 0
				));
			}
		}
	}
	/**
		@description Dates in the m/d/y or d-m-y formats are disambiguated by looking 
		at the separator between the various components: if the separator is a slash (/), 
		then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), 
		then the European d-m-y format is assumed.
	*/
	public static function getStrToTime($date){
		$dateString = self::formatString($date);
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$separator = self::getSeparator($dateFormat);
		if($dateFormat == 'd/m/Y'){
			$dateString = str_replace('/', '-', $dateString);
		}else if(in_array($dateFormat, array('m.d.Y','Y.m.d', 'm-d-Y'))){
			$dateString = str_replace($separator, '/', $dateString);
		}
		return strtotime($dateString);
	}
	
		
	public static function formatString($date){
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$separator = self::getSeparator($dateFormat);
		$result = $date->format($dateFormat);
		if($dateFormat == sprintf('d%1$sm%1$sY', $separator)){
			$result = $date->format('d') . $separator . $date->format('m') . $separator . $date->format('Y');
		} else if ($dateFormat == sprintf('m%1$sd%1$sY', $separator)){
			$result = $date->format('m') . $separator . $date->format('d') . $separator . $date->format('Y');
		} else if ($dateFormat == sprintf('Y%1$sm%1$sd', $separator)){
			$result = $date->format('Y') . $separator .  $date->format('m') . $separator . $date->format('d');
		}
		return $result;
	}
	
	public static function formattedDateTime($dateString) {
		//DateTime::setDate ( int $year , int $month , int $day )
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$separator = self::getSeparator($dateFormat);
		$result = new Booki_DateTime();
		$x = explode($separator, $dateString);
		if($dateFormat == sprintf('d%1$sm%1$sY', $separator)){
		  /*[0] = d
			[1] = m
			[2] = y*/
			
			$result->setDate($x[2], $x[1], $x[0]);
		} else if ($dateFormat == sprintf('m%1$sd%1$sY', $separator)){
		  /*[0] = m
			[1] = d
			[2] = y*/
			$result->setDate($x[2], $x[0], $x[1]);
		} else if ($dateFormat == sprintf('Y%1$sm%1$sd', $separator)){
		  /*[0] = y
			[1] = m
			[2] = d*/
			$result->setDate($x[0], $x[1], $x[2]);
		}
		return $result;
	}
	
	public static function dateIsValidFormat($dateString){
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$separator = self::getSeparator($dateFormat);
		$x = explode($separator, $dateString);
		return count($x) === 3;
	}
	/*excluded days are saved as string by booki. so convert back to admin defined format*/
	public static function fromDefaultToAdminSelectedFormat($dateString) {
		$globalSettings = Booki_Helper::globalSettings();
		$dateFormat = $globalSettings->getServerFormatShorthandDate();
		$separator = self::getSeparator($dateFormat);
		$x = explode($separator, $dateString);
		//default format used by booki admin area is: MM/DD/YYYY
		 /*[0] = m
			[1] = d
			[2] = y*/
		if($dateFormat == sprintf('d%1$sm%1$sY', $separator)){
			$dateString = $x[1] . $separator . $x[0] . $separator . $x[2];
		} else if ($dateFormat == sprintf('m%1$sd%1$sY', $separator)){
			$dateString = $x[0] . $separator . $x[1] . $separator . $x[2];
		} else if ($dateFormat == sprintf('Y%1$sm%1$sd', $separator)){
			$dateString = $x[2] . $separator . $x[0] . $separator . $x[1];
		}
		return $dateString;
	}
	
	public static function getJQueryCalendarFormat($dateFormat){
		$result = null;
		$separator = self::getSeparator($dateFormat);
		if($dateFormat == sprintf('MM%1$sDD%1$sYYYY', $separator)){
			$result = sprintf('mm%1$sdd%1$syy', $separator);
		} else if($dateFormat == sprintf('DD%1$sMM%1$sYYYY', $separator)){
			$result = sprintf('dd%1$smm%1$syy', $separator);
		} else{
			$result = sprintf('yy%1$smm%1$sdd', $separator);
		}
		return $result;
	}
	
	public static function getSeparator($dateFormat){
		$separator = '/';
		if(strpos($dateFormat, '.') !== false){
			$separator = '.';
		}else if (strpos($dateFormat, '-') !== false){
			$separator = '-';
		}
		return $separator;
	}

}
?>