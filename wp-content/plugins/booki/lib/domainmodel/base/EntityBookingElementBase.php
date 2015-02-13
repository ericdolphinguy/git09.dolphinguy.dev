<?php
require_once  'EntityBase.php';
require_once  dirname(__FILE__) . '/../entities/BookingStatus.php';

class Booki_EntityBookingElementBase extends Booki_EntityBase
{
	public $contextButtons = array();
	public $currentStatus;
	public function getStatusText(){
		if($this->status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('Pending Approval', 'booki');
		}else if($this->status === Booki_BookingStatus::APPROVED){
			return __('Approved Booking', 'booki');
		}else if($this->status === Booki_BookingStatus::CANCELLED){
			return __('Cancelled Booking', 'booki');
		}else if($this->status === Booki_BookingStatus::REFUNDED){
			return __('Refunded', 'booki');
		} else if ($this->status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('Pending User Cancel Request', 'booki');
		}
	}
	
	public function getStatusLabel(){
		if($this->status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('info', 'booki');
		}else if($this->status === Booki_BookingStatus::APPROVED){
			return __('success', 'booki');
		}else if($this->status === Booki_BookingStatus::CANCELLED){
			return __('danger', 'booki');
		}else if($this->status === Booki_BookingStatus::REFUNDED || $this->status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('warning', 'booki');
		}
	}
	
	public function fillContextMenu($canEdit, $canCancel, $refundable){
		if($this->status === Booki_BookingStatus::REFUNDED){
			$this->currentStatus = __('Refunded', 'booki');
		}else if($this->status === Booki_BookingStatus::USER_REQUEST_CANCEL && !$canEdit){
			$this->currentStatus =  __('Pending Cancel Request', 'booki');
		}else if($this->status === Booki_BookingStatus::CANCELLED){
			$this->currentStatus = __('Booking Cancelled', 'booki');
		}
		
		if($this->currentStatus){
			return;
		}

		if($canEdit && ($this->status !== Booki_BookingStatus::APPROVED)){
			$this->contextButtons['Approve'] = array('icon'=>'glyphicon-thumbs-up', 'label'=>'Approve Booking');
		}
		if($canCancel && 
			($this->status === Booki_BookingStatus::PENDING_APPROVAL || 
			$this->status === Booki_BookingStatus::APPROVED ||
			$this->status === Booki_BookingStatus::USER_REQUEST_CANCEL)){
			$this->contextButtons['Cancel'] = array('icon'=>'glyphicon-thumbs-down', 'label'=>'Cancel Booking');
		}
		if($refundable && $this->status !== Booki_BookingStatus::REFUNDED){
			$this->contextButtons['Refund'] = array('icon'=>'glyphicon-arrow-left', 'label'=>'Refund');
		}
	}
}
?>