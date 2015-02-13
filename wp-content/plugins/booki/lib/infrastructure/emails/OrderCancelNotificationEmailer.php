<?php
require_once  'NotificationEmailer.php';

class Booki_OrderCancelNotificationEmailer extends Booki_NotificationEmailer{
	public function __construct($orderId, $bookedDayId = null, $bookedOptionalId = null, $bookedCascadingItemId = null){
		parent::__construct(Booki_EmailType::BOOKING_CANCEL_REQUEST, $orderId, $bookedDayId, $bookedOptionalId, $bookedCascadingItemId);
	}
	
	public function send($projectId = null, $to = null){
		$projectList = Booki_BookingProvider::bookedDaysRepository()->readAgentToNotifyByOrderId($this->orderId);
		if($this->bookedDay){
			$projectId = $this->bookedDay->projectId;
		}else if($this->bookedOptional){
			$projectId = $this->bookedOptional->projectId;
		}else if($this->bookedCascadingItem){
			$projectId = $this->bookedCascadingItem->projectId;
		}
		
		if($projectList && count($projectList) > 0){
			foreach($projectList as $project){
				$settings = Booki_Helper::globalSettings();
				$recipient = $settings->notificationEmailTo;
				if($project['notifyUserEmailList']){
					$recipient = $project['notifyUserEmailList'];
				}
				
				if($projectId === $project['id'] || $projectId === null){
					$id = $projectId === null ? $project['id'] : $projectId;
					$result = parent::send($id, $recipient);
				}
			}
		}
	}
}
?>