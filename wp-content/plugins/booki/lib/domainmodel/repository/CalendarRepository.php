<?php
require_once  dirname(__FILE__) . '/../entities/Calendar.php';
require_once  dirname(__FILE__) . '/../entities/CalendarDay.php';
require_once  dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
require_once 'CalendarDayRepository.php';
class Booki_CalendarRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $calendar_table_name;
	private $calendarDay_table_name;
	private $order_days_table_name;
	private $order_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
		$this->calendarDay_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_table_name = $wpdb->prefix . 'booki_order';
	}
	
	private function populateCalendar($r){
		return new Booki_Calendar( 
			(int)$r->projectId 
			, new Booki_DateTime($r->startDate) 
			, new Booki_DateTime($r->endDate) 
			, (strlen($r->daysExcluded) === 0 ? array() : explode(',', $r->daysExcluded))
			, (strlen($r->timeExcluded) === 0 ? array() : explode(',', $r->timeExcluded))
			, (strlen($r->weekDaysExcluded) === 0 ? array() : Booki_Helper::convertToIntArray(explode(',', $r->weekDaysExcluded)))
			, (int)$r->hours 
			, (int)$r->minutes
			, (double)$r->cost
			, (int)$r->period
			, (int)$r->hourStartInterval
			, (int)$r->minuteStartInterval
			, (int)$r->bookingLimit
			, (bool)$r->displayCounter
			, (int)$r->minNumDaysDeposit
			, (double)$r->deposit
			, (int)$r->bookingStartLapse
			, (bool)$r->enableSingleHourMinuteFormat
			, (int)$r->currentBookingCount
			, (int)$r->id
		);
	}
	
	public function readByProject($projectId){
		$sql = "SELECT c.id, c.projectId, c.startDate, c.endDate, c.daysExcluded, c.timeExcluded, 
		c.weekDaysExcluded, c.hours, c.minutes, c.cost, c.period, c.hourStartInterval, c.minuteStartInterval,
		c.bookingLimit, c.displayCounter, c.minNumDaysDeposit, c.deposit, c.bookingStartLapse, c.enableSingleHourMinuteFormat,
		(SELECT COUNT(od.id) FROM $this->order_table_name as o 
			LEFT JOIN $this->order_days_table_name as od
			ON o.id = od.orderId
			WHERE projectId = c.projectId) as currentBookingCount
		FROM $this->calendar_table_name as c WHERE c.projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ));
		if( $result ){
			$r = $result[0];
			return $this->populateCalendar($r);
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT c.id, c.projectId, c.startDate, c.endDate, c.daysExcluded, c.timeExcluded, 
		c.weekDaysExcluded, c.hours, c.minutes, c.cost, c.period, c.hourStartInterval, c.minuteStartInterval,
		c.bookingLimit, c.displayCounter, c.minNumDaysDeposit, c.deposit, c.bookingStartLapse, c.enableSingleHourMinuteFormat,
		(SELECT COUNT(od.id) FROM $this->order_table_name as o 
			LEFT JOIN $this->order_days_table_name as od
			ON o.id = od.orderId
			WHERE projectId = c.projectId) as currentBookingCount
			FROM $this->calendar_table_name as c WHERE c.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return $this->populateCalendar($r);
		}
		return false;
	}

	public function insert($calendar){
		 $result = $this->wpdb->insert($this->calendar_table_name,  array(
			'projectId'=>$calendar->projectId
			, 'startDate'=>$calendar->startDate->format(BOOKI_DATEFORMAT)
			, 'endDate'=>$calendar->endDate->format(BOOKI_DATEFORMAT)
			, 'daysExcluded'=>implode(',', $calendar->daysExcluded)
			, 'timeExcluded'=>implode(',', $calendar->timeExcluded)
			, 'weekDaysExcluded'=>implode(',', $calendar->weekDaysExcluded)
			, 'hours'=>$calendar->hours
			, 'minutes'=>$calendar->minutes
			, 'cost'=>$calendar->cost
			, 'period'=>$calendar->period
			, 'hourStartInterval'=>$calendar->hourStartInterval
			, 'minuteStartInterval'=>$calendar->minuteStartInterval
			, 'bookingLimit'=>$calendar->bookingLimit
			, 'displayCounter'=>$calendar->displayCounter
			, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
			, 'bookingStartLapse'=>$calendar->bookingStartLapse
			, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
			, 'deposit'=>$calendar->deposit
		 ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d'));
		 
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($calendar){
		if($calendar->period === Booki_CalendarPeriod::BY_DAY){
			/**
				@description when switching from BY_TIME period setting on the calendar to a BY_DAY period
				then ensure all days do not have time settings.
			*/
			$result = $this->wpdb->update($this->calendarDay_table_name,  array(
				'timeExcluded'=>''
				, 'hours'=>23
				, 'minutes'=>60
			), array('calendarId'=>$calendar->id), array('%s', '%d', '%d'));
		}
		
		$result = $this->wpdb->update($this->calendar_table_name,  array(
			'startDate'=>$calendar->startDate->format(BOOKI_DATEFORMAT)
			, 'endDate'=>$calendar->endDate->format(BOOKI_DATEFORMAT)
			, 'daysExcluded'=>implode(',', $calendar->daysExcluded)
			, 'timeExcluded'=>implode(',', $calendar->timeExcluded)
			, 'weekDaysExcluded'=>implode(',', $calendar->weekDaysExcluded)
			, 'hours'=>$calendar->hours
			, 'minutes'=>$calendar->minutes
			, 'cost'=>$calendar->cost
			, 'period'=>$calendar->period
			, 'hourStartInterval'=>$calendar->hourStartInterval
			, 'minuteStartInterval'=>$calendar->minuteStartInterval
			, 'bookingLimit'=>$calendar->bookingLimit
			, 'displayCounter'=>$calendar->displayCounter
			, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
			, 'deposit'=>$calendar->deposit
			, 'bookingStartLapse'=>$calendar->bookingStartLapse
			, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
		), array('id'=>$calendar->id), array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d'));
		
		return $result;
	}
	
	public function delete($id){
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->calendarDay_table_name WHERE calendarId = %d", $id));
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->calendar_table_name WHERE id = %d", $id));
	}
}
?>