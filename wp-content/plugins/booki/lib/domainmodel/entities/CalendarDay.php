<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_CalendarDay extends Booki_EntityBase{
	public $id = -1;
	public $calendarId;
	public $day;
	public $timeExcluded;
	public $hours = 23;
	public $minutes = 60;
	public $cost;
	public $hourStartInterval;
	public $minuteStartInterval;
	public $seasonName;
	public $minNumDaysDeposit;
	public $deposit;
	public $enableSingleHourMinuteFormat = false;
	public $lat;
	public $lng;
	public function  __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->calendarId = func_get_arg(0);
			$this->day = func_get_arg(1);
			$this->timeExcluded = func_get_arg(2);
			$this->hours = func_get_arg(3);
			$this->minutes = func_get_arg(4);
			$this->cost = func_get_arg(5);
			$this->hourStartInterval = func_get_arg(6);
			$this->minuteStartInterval = func_get_arg(7);
			$this->seasonName = func_get_arg(8);
			$this->minNumDaysDeposit = func_get_arg(9);
			$this->deposit = func_get_arg(10);
			if($numArgs === 12){
				$this->id = func_get_arg(11);
			}
		}
		if(!$this->timeExcluded){
			$this->timeExcluded = array();
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'calendarId'=>$this->calendarId
			, 'day'=>$this->day ? $this->day->format('Y-m-d') : null
			, 'timeExcluded'=>$this->timeExcluded
			, 'weekDaysExcluded'=>$this->weekDaysExcluded
			, 'hours'=>$this->hours
			, 'minutes'=>$this->minutes
			, 'cost'=>$this->cost
			, 'timeSlots'=>$this->createTimeSlots($this->hours, $this->minutes, $this->hourStartInterval, $this->minuteStartInterval, $this->enableSingleHourMinuteFormat)
			, 'hourStartInterval'=>$this->hourStartInterval
			, 'minuteStartInterval'=>$this->minuteStartInterval
			, 'seasonName'=>$this->seasonName
			, 'minNumDaysDeposit'=>$this->minNumDaysDeposit
			, 'deposit'=>$this->deposit
		);
	}
}
?>