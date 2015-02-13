<?php
require_once  dirname(__FILE__) . '/../base/EntityBookingElementBase.php';
require_once  dirname(__FILE__) . '/BookingStatus.php';
class Booki_BookedDay extends Booki_EntityBookingElementBase {
	public $id;
	public $projectId;
	public $orderId;
	public $bookingDate;
	public $hourStart = null;
	public $minuteStart = null;
	public $hourEnd = null;
	public $minuteEnd = null;
	public $cost;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $deposit;
	public $enableSingleHourMinuteFormat;
	public function __construct($args){
		if($this->keyExists('projectId', $args)){
			$this->projectId = (int)$args['projectId'];
		}
		if($this->keyExists('bookingDate', $args)){
			$this->bookingDate = $args['bookingDate'] instanceOf Booki_DateTime ? $args['bookingDate'] : new Booki_DateTime($args['bookingDate']);
		}
		if($this->keyExists('hourStart', $args) && $args['hourStart'] !== null){
			$this->hourStart = (int)$args['hourStart'] ;
		}
		if($this->keyExists('minuteStart', $args) && $args['minuteStart'] !== null){
			$this->minuteStart = (int)$args['minuteStart'];
		}
		if($this->keyExists('hourEnd', $args) &&  $args['hourEnd'] !== null){
			$this->hourEnd = (int)$args['hourEnd'];
		}
		if($this->keyExists('minuteEnd', $args) && $args['minuteEnd'] !== null){
			$this->minuteEnd = (int)$args['minuteEnd'];
		}
		if($this->keyExists('enableSingleHourMinuteFormat', $args)){
			$this->enableSingleHourMinuteFormat = (bool)$args['enableSingleHourMinuteFormat'];
		}
		if($this->keyExists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if($this->keyExists('deposit', $args)){
			$this->deposit = (double)$args['deposit'];
		}
		if($this->keyExists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if($this->keyExists('orderId', $args)){
			$this->orderId = (int)$args['orderId'];
		}
		if($this->keyExists('handlerUserId', $args)){
			$this->handlerUserId = (int)$args['handlerUserId'];
		}
		if($this->keyExists('notifyUserEmailList', $args)){
			$this->notifyUserEmailList = trim((string)$args['notifyUserEmailList']);
		}
		if($this->keyExists('projectName', $args)){
			$this->projectName = (string)$args['projectName'];
		}
		if($this->keyExists('id', $args)){
			$this->id = (int)$args['id'];
		}
	}
	
	public function hasTime(){
		if($this->hourStart !== null || $this->minuteStart !== null){
			return true;
		}
		return false;
	}
	
	public function hasEndTime(){
		if($this->hourEnd !== null || $this->minuteEnd !== null){
			return true;
		}
		return false;
	}
	
	public function compareTime($t2){
		return (($this->hourStart === $t2->hourStart && $this->minuteStart === $t2->minuteStart) &&
				($this->hourEnd === $t2->hourEnd && $this->minuteEnd === $t2->minuteEnd));
	}
	
	public function hasDeposit(){
		return $this->deposit > 0;
	}
}
?>