<?php
class Booki_ContextMenuHelper{
	public static function getStatusText($item){
		if($item->status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('Pending Approval', 'booki');
		}else if($item->status === Booki_BookingStatus::APPROVED){
			return __('Approved Booking', 'booki');
		}else if($item->status === Booki_BookingStatus::CANCELLED){
			return __('Cancelled Booking', 'booki');
		}else if($item->status === Booki_BookingStatus::REFUNDED){
			return __('Refunded', 'booki');
		} else if ($item->status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('Pending User Cancel Request', 'booki');
		}
	}
	
	public static function getStatusLabel($item){
		if($item->status === Booki_BookingStatus::PENDING_APPROVAL){
			return __('info', 'booki');
		}else if($item->status === Booki_BookingStatus::APPROVED){
			return __('success', 'booki');
		}else if($item->status === Booki_BookingStatus::CANCELLED){
			return __('danger', 'booki');
		}else if($item->status === Booki_BookingStatus::REFUNDED || $item->status === Booki_BookingStatus::USER_REQUEST_CANCEL){
			return __('warning', 'booki');
		}
	}
	
	public static function getContextMenu($canEdit, $canCancel, $refundable, $item){
		$contextButtons = array();
		$currentStatus = null;
		if($item->status === Booki_BookingStatus::REFUNDED){
			$currentStatus = __('Refunded', 'booki');
		}else if($item->status === Booki_BookingStatus::USER_REQUEST_CANCEL && !$canEdit){
			$currentStatus =  __('Pending Cancel Request', 'booki');
		}else if($item->status === Booki_BookingStatus::CANCELLED){
			$currentStatus = __('Booking Cancelled', 'booki');
		}
		
		if($currentStatus){
			return array('currentStatus'=>$currentStatus, 'contextButtons'=>$contextButtons);
		}

		if($canEdit && ($item->status !== Booki_BookingStatus::APPROVED)){
			$contextButtons['Approve'] = array('icon'=>'glyphicon-thumbs-up', 'label'=>'Approve Booking');
		}
		if($canCancel && 
			($item->status === Booki_BookingStatus::PENDING_APPROVAL || 
			$item->status === Booki_BookingStatus::APPROVED ||
			$item->status === Booki_BookingStatus::USER_REQUEST_CANCEL)){
			$contextButtons['Cancel'] = array('icon'=>'glyphicon-thumbs-down', 'label'=>'Cancel Booking');
		}
		if($refundable && $item->status !== Booki_BookingStatus::REFUNDED){
			$contextButtons['Refund'] = array('icon'=>'glyphicon-arrow-left', 'label'=>'Refund');
		}
		return array('currentStatus'=>$currentStatus, 'contextButtons'=>$contextButtons);
	}
}
?>