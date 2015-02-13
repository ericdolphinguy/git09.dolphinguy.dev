<?php
require_once  dirname(__FILE__) . '/../base/EntityBookingElementBase.php';
require_once  dirname(__FILE__) . '/BookingStatus.php';
class Booki_BookedCascadingItem extends Booki_EntityBookingElementBase {
	public $id;
	public $projectId;
	public $orderId;
	public $cost;
	public $value;
	public $value_loc;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $count = 0;
	public $deposit;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->value = func_get_arg(1);
			$this->cost = func_get_arg(2);
			$this->deposit = func_get_arg(3);
			if($numArgs >= 5){
				$this->status = func_get_arg(4);
			}
			if($numArgs >= 6){
				$this->orderId = func_get_arg(5);
			}
			if($numArgs >= 7){
				$this->handlerUserId = func_get_arg(6);
			}
			if($numArgs >= 8){
				$this->notifyUserEmailList = func_get_arg(7);
			}
			if($numArgs >= 9){
				$this->projectName = func_get_arg(8);
			}
			if($numArgs >= 10){
				$this->count = func_get_arg(9);
			}
			if($numArgs === 11){
				$this->id = func_get_arg(10);
			}
		}
		$this->init();
	}
	
	public function getCalculatedCost(){
		if($this->count > 0){
			return $this->cost * $this->count;
		}
		return $this->cost;
	}
	
	public function getName(){
		if($this->count > 0){
			return $this->value_loc . ' x ' . $this->count;
		}
		return $this->value_loc;
	}
	
	protected function init(){
		$this->value_loc = Booki_WPMLHelper::t('cascading_item_' . $this->value . '_value', $this->value);
	}
}
?>