<?php
require_once  dirname(__FILE__) . '/../base/EntityBookingElementBase.php';

class Booki_BookedOptional extends Booki_EntityBookingElementBase{
	public $id;
	public $projectId;
	public $orderId;
	public $name;
	public $name_loc;
	public $cost;
	public $status = Booki_BookingStatus::PENDING_APPROVAL;
	public $handlerUserId;
	public $notifyUserEmailList;
	public $projectName;
	public $deposit;
	//count > 0 = cost * count
	public $count = 0;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->name = func_get_arg(1);
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
			return $this->name_loc . ' x ' . $this->count;
		}
		return $this->name_loc;
	}
	
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
}
?>