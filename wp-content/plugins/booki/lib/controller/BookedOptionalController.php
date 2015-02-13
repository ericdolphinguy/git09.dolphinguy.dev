<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/BookingStatus.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/BookedOptionalsRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/OrderRepository.php';
require_once  dirname(__FILE__) . '/../infrastructure/emails/OrderCancelNotificationEmailer.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_BookedOptionalController extends Booki_BaseController{
	private $bookedOptionalsRepo;
	private $bookedOptional;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedoptionals')){
			return;
		}
		
		$this->bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
		$this->hasFullControl = Booki_Helper::hasAdministratorPermission();
		$this->canEdit = Booki_Helper::hasEditorPermission();
		if(array_key_exists('approve', $_POST)){
			$this->approve($approveCallback);
		}else if(array_key_exists('cancel', $_POST)){
			$this->cancel($cancelCallback);
		}else if (array_key_exists('refund', $_POST)){
			$this->refund($refundCallback);
		}
	}
	
	public function approve($callback){
		if(!$this->canEdit){
			return;
		}
		$id = (int)$this->getPostValue('approve');
		$bookedOptional = $this->bookedOptionalsRepo->read($id);
		$bookedOptional->status = Booki_BookingStatus::APPROVED;
		$result = $this->bookedOptionalsRepo->update($bookedOptional);
		if($result){
		
			$userId = get_current_user_id();
			$this->bookedOptionalsRepo->setOwner($bookedOptional->id, $userId);

			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_OPTIONAL_ITEM_CONFIRMED, $bookedOptional->orderId, null, $bookedOptional->id);
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = (int)$this->getPostValue('cancel');
		$orderId = (int)$this->getPostValue('orderid');
		
		if($this->canEdit){
			$optionalItem = $this->bookedOptionalsRepo->read($id);
			if($optionalItem->cost > 0){
				$orderRepository = new Booki_OrderRepository();
				$order = $orderRepository->read($orderId);
				$cost = Booki_Helper::calcDeposit($optionalItem->deposit, $optionalItem->cost);
				if($order->discount > 0){
					$cost = Booki_Helper::calcDiscount($order->discount, $cost);
				}
				if($order->tax > 0){
					$cost += Booki_Helper::percentage($order->tax, $cost);
				}
				$order->totalAmount -= $cost;
				$orderRepository->update($order);
			}
			$this->bookedOptionalsRepo->delete($id);
		}else{
			$this->bookedOptionalsRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer($orderId, null, $id, null);
			$notificationEmailer->send();
		}
		
		if($this->canEdit){
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_OPTIONAL_ITEM_CANCELLED, $orderId, null, $id);
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){

		if(!$this->hasFullControl){
			return;
		}

		$id = (int)$this->getPostValue('refund');
		$this->refundOrderId = (int)$this->getPostValue('orderid');
		$this->bookedOptional = $this->bookedOptionalsRepo->read($id);
		$this->refundAmount = $this->bookedOptional->getCalculatedCost();
		$this->refundCurrency = (string)$this->getPostValue('currency');
		
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_optional', array($this, 'getBookedOptional'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getBookedOptional(){
		return $this->bookedOptional;
	}
	
	public function getRefundAmount(){
		return $this->refundAmount;
	}

	public function getRefundCurrency(){
		return $this->refundCurrency;
	}
	
	public function getRefundType(){
		return 'Partial';
	}
}
?>