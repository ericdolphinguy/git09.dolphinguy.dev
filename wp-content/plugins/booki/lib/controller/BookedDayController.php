<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/BookingStatus.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/BookedDaysRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/OrderRepository.php';
require_once  dirname(__FILE__) . '/../infrastructure/emails/OrderCancelNotificationEmailer.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_BookedDayController extends Booki_BaseController{
	private $bookedDaysRepo;
	private $bookedDay;
	private $refundAmount;
	private $refundCurrency;
	private $refundOrderId;
	private $hasFullControl;
	private $canEdit;
	public function __construct($approveCallback = null, $cancelCallback = null, $refundCallback = null){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookedday')){
			return;
		}

		$this->bookedDaysRepo = new Booki_BookedDaysRepository();
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
		$id = $this->getPostValue('approve');
		$orderId = (int)$this->getPostValue('orderid');
		$bookedDay = $this->bookedDaysRepo->read((int)$id);
		$bookedDay->status = Booki_BookingStatus::APPROVED;

		$result = $this->bookedDaysRepo->update($bookedDay);
		if($result){
			//editor approving a day becomes the owner
			$userId = get_current_user_id();
			$this->bookedDaysRepo->setOwner($bookedDay->id, $userId);
			
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_DAY_CONFIRMED, $bookedDay->orderId, $bookedDay->id);
			$notificationEmailer->send();
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function cancel($callback){
		$id = $this->getPostValue('cancel');
		$orderId = (int)$this->getPostValue('orderid');
		$orderRepository = new Booki_OrderRepository();
		$deleteOrder = false;
		if($this->canEdit){
			$bookedDays = $this->bookedDaysRepo->readByOrder($orderId);
			if($bookedDays->count() === 1){
				$deleteOrder = true;
			}else{
				$bookedDayItem = $this->bookedDaysRepo->read($id);
				if($bookedDayItem->cost > 0){
					$order = $orderRepository->read($orderId);
					$cost = Booki_Helper::calcDeposit($bookedDayItem->deposit, $bookedDayItem->cost);
					if($order->discount > 0){
						$cost = Booki_Helper::calcDiscount($order->discount, $cost);
					}
					if($order->tax > 0){
						$cost += Booki_Helper::percentage($order->tax, $cost);
					}
					$order->totalAmount -= $cost;
					$orderRepository->update($order);
				}
				$this->bookedDaysRepo->delete($id);
			}
		}else{
			$this->bookedDaysRepo->updateStatus($id, Booki_BookingStatus::USER_REQUEST_CANCEL);
			$notificationEmailer = new Booki_OrderCancelNotificationEmailer($orderId, $id, null, null);
			$notificationEmailer->send();
		}
		if($this->canEdit){
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_DAY_CANCELLED, $orderId, $id);
			$notificationEmailer->send();
			if($deleteOrder){
				$orderRepository->delete($orderId);
			}
		}
		$this->executeCallback($callback, array(true));
	}
	
	public function refund($callback){
		if(!$this->hasFullControl){
			return;
		}
		
		$id = (int)$this->getPostValue('refund');
		$this->refundOrderId = (int)$this->getPostValue('orderid');
		$this->refundCurrency = (string)$this->getPostValue('currency');

		$this->bookedDay = $this->bookedDaysRepo->read($id);
		$this->refundAmount = $this->bookedDay->cost;
		
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_booked_day', array($this, 'getBookedDay'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array());
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	
	public function getBookedDay(){
		return $this->bookedDay;
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