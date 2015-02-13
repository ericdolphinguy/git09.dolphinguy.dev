<?php
require_once  'NotificationEmailer.php';

class Booki_AgentsNotificationEmailer extends Booki_NotificationEmailer{
	public function __construct($emailType, $orderId){
		parent::__construct($emailType, $orderId);
	}
	
	public function send($projectId = null, $to = null){
		$projectList = Booki_BookingProvider::bookedDaysRepository()->readAgentToNotifyByOrderId($this->orderId);
		if($projectList && count($projectList) > 0){
			foreach($projectList as $project){
				if($project['notifyUserEmailList']){
					$result = parent::send($project['id'], $project['notifyUserEmailList']);
				}
			}
		}
	}
}
?>