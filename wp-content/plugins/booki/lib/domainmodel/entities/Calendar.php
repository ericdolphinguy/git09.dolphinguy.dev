<?php
require_once 'CalendarDays.php';
require_once 'CalendarPeriod.php';
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_Calendar extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $startDate;
	public $endDate;
	public $hours;
	public $minutes;
	public $cost;
	public $calendarDays;
	public $startHour;
	public $endHour;
	public $startMinute;
	public $endMinute;
	public $daysExcluded;
	public $timeExcluded;
	public $weekDaysExcluded;
	public $period = Booki_CalendarPeriod::BY_DAY;
	public $hourStartInterval;
	public $minuteStartInterval;
	public $lat;
	public $lng;
	public $bookingLimit;
	public $currentBookingCount;
	public $displayCounter;
	public $minNumDaysDeposit;
	public $deposit;
	public $bookingStartLapse;
	public $enableSingleHourMinuteFormat;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->startDate = func_get_arg(1);
			$this->endDate = func_get_arg(2);
			$this->daysExcluded = func_get_arg(3);
			$this->timeExcluded = func_get_arg(4);
			$this->weekDaysExcluded = func_get_arg(5);
			$this->hours = func_get_arg(6);
			$this->minutes = func_get_arg(7);
			$this->cost = func_get_arg(8);
			$this->period = func_get_arg(9);
			$this->hourStartInterval = func_get_arg(10);
			$this->minuteStartInterval = func_get_arg(11);
			$this->bookingLimit = func_get_arg(12);
			$this->displayCounter = func_get_arg(13);
			$this->minNumDaysDeposit = func_get_arg(14);
			$this->deposit = func_get_arg(15);
			$this->bookingStartLapse = func_get_arg(16);
			$this->enableSingleHourMinuteFormat = func_get_arg(17);
			if($numArgs >= 19){
				$this->currentBookingCount = func_get_arg(18);
			}
			if($numArgs === 20){
				$this->id = func_get_arg(19);
			}
		}

		$this->calendarDays = new Booki_CalendarDays();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'startDate'=>$this->startDate->format('Y-m-d')
			, 'endDate'=>$this->endDate->format('Y-m-d')
			, 'daysExcluded'=>$this->daysExcluded
			, 'timeExcluded'=>$this->timeExcluded
			, 'weekDaysExcluded'=>$this->weekDaysExcluded
			, 'hours'=>$this->hours
			, 'minutes'=>$this->minutes
			, 'cost'=>$this->cost
			, 'calendarDays'=>$this->calendarDays->get_items()
			, 'timeSlots'=>$this->createTimeSlots($this->hours, $this->minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat)
			, 'period'=>$this->period
			, 'hourStartInterval'=>$this->hourStartInterval
			, 'minuteStartInterval'=>$this->minuteStartInterval
			, 'bookingLimit'=>$this->bookingLimit
			, 'currentBookingCount'=>$this->currentBookingCount
			, 'displayCounter'=>$this->displayCounter
			, 'minNumDaysDeposit'=>$this->minNumDaysDeposit
			, 'deposit'=>$this->deposit
			, 'bookingStartLapse'=>$this->bookingStartLapse
			, 'enableSingleHourMinuteFormat'=>$this->enableSingleHourMinuteFormat
		);
	}
	
	public function exhausted(){
		if($this->bookingLimit > 0){
			return $this->bookingLimit <= $this->currentBookingCount;
		}
		return false;
	}
	
	public function timeSlots($hours, $minutes){
		return $this->createTimeSlots($hours, $minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat);
	}
}
?>