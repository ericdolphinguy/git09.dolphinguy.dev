<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/EmailType.php';
require_once  dirname(__FILE__) . '/../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../infrastructure/emails/NotificationEmailer.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_ManageBookingsController extends Booki_BaseController{
	private $orderId;
	private $exportsPerPage;
	private $hasFullControl;
	private $canEdit;
	private $globalSettings;
	public $refundOrderId;
	public $refundAmount;
	public $refundCurrency;
	public $refundType;
	
	public function __construct($refundCallback, $deleteCallback, $addUserCallback, $registerUserCallback, $invoiceNotificationCallback, 
									$refundNotificationCallback, $approveAllCallback, $markPaidCallback, $exportCallback, $exportsPerPage ){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managebookings')){
			return;
		}
		
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$this->hasFullControl = Booki_Helper::hasAdministratorPermission();
		$this->canEdit = Booki_Helper::hasEditorPermission();
		
		$this->exportsPerPage = $exportsPerPage;
		
		$this->orderId = isset($_GET['orderid']) ? $_GET['orderid'] : null;
		if (array_key_exists('refund', $_POST)){
			$this->refund($refundCallback);
		}else if(array_key_exists('invoiceNotification', $_POST)){
			$this->invoiceNotification($invoiceNotificationCallback);
		}else if (array_key_exists('refundNotification', $_POST)){
			$this->refundNotification($refundNotificationCallback);
		}else if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if (array_key_exists('adduser', $_POST)){
			$this->addUser($addUserCallback);
		}else if (array_key_exists('export', $_POST)){
			$this->export($exportCallback);
		}else if (array_key_exists('approveAll', $_POST)){
			$this->approveAll($approveAllCallback);
		}else if (array_key_exists('markPaid', $_POST)){
			$this->markPaid($markPaidCallback);
		}else if (array_key_exists('registerUser', $_POST)){	
			$this->registerUser($registerUserCallback);
		}
	}
	
	public function registerUser($callback){
		$orderId = (int)$_POST['registerUser'];
		$firstName = (string)$_POST['userFirstname'];
		$lastName = (string)$_POST['userLastname'];
		$email = (string)$_POST['userEmail'];
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$isNew = false;
		if($order){
			$createUserResult = Booki_Helper::createUserIfNotExists($email, $firstName, $lastName);
			$order->userId = $createUserResult['userId'];
			$order->userIsRegistered = true;
			$result = Booki_BookingProvider::orderRepository()->update($order);
			$isNew = $createUserResult['isNew'];
		}
		$this->executeCallback($callback, array($isNew));
	}
	
	public function approveAll($callback){
		$orderId = (int)$_POST['approveAll'];
		$userIsRegistered = (bool)$_POST['userIsRegistered'];
		Booki_BookingProvider::approveOrderAndNotifyUser($orderId, $userIsRegistered);
	}
	
	public function markPaid($callback){
		$orderId = (int)$_POST['markPaid'];
		Booki_BookingProvider::orderRepository()->updateStatusByOrderId($orderId, Booki_PaymentStatus::PAID);
	}
	
	public function invoiceNotification($callback){
		if(!$this->canEdit){
			return;
		}
		$orderId = $_POST['invoiceNotification'];
		$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::INVOICE, $orderId);
		$result = $notificationEmailer->send();

		if($result){
			$order = Booki_BookingProvider::orderRepository()->read($orderId);
			++$order->invoiceNotification;
			Booki_BookingProvider::orderRepository()->update($order);
		}
		$this->executeCallback($callback, array($orderId, $result));
	}
	
	
	public function refundNotification($callback){
		if(!$this->hasFullControl){
			return;
		}
		$orderId = $_POST['refundNotification'];
		$refundAmount = $_POST['refundAmount'];
		$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::REFUNDED, $orderId, null, null, null, $refundAmount);
		$result = $notificationEmailer->send();
		if($result){
			$order = Booki_BookingProvider::orderRepository()->read($orderId);
			++$order->refundNotification;
			Booki_BookingProvider::orderRepository()->update($order);
		}
		$this->executeCallback($callback, array($orderId, $result));
	}
	
	public function delete($callback){
		if(!$this->hasFullControl){
			return;
		}
		$orderId = $_POST['delete'];
		Booki_BookingProvider::delete($orderId);
		$this->executeCallback($callback, array($orderId));
	}
	
	public function addUser($callback){
		if(!$this->hasFullControl){
			return;
		}
		$orderId = $this->getPostValue('adduser');
		$userEmail = $this->getPostValue('adduseremail');
		$order = Booki_BookingProvider::orderRepository()->read($orderId);
		$isNew = false;
		if($order){
			$createUserResult = Booki_Helper::createUserIfNotExists($userEmail);
			$order->userId = $createUserResult['userId'];
			$order->userIsRegistered = true;
			$result = Booki_BookingProvider::orderRepository()->update($order);
			$isNew = $createUserResult['isNew'];
		}
		$this->executeCallback($callback, array($isNew));
	}
	
	public function export($callback){
		if(!$this->hasFullControl){
			return;
		}
		$pageIndex = (int)$_POST['pageindex'];
		$result = Booki_BookingProvider::orderRepository()->readAll($pageIndex, $this->exportsPerPage);
		$this->executeCallback($callback, array($pageIndex));
	}
	
	public function refund($callback){
		if(!$this->hasFullControl){
			return;
		}
		$this->refundOrderId = (int)$this->getPostValue('orderId');
		$this->refundAmount = $this->getPostValue('amount');
		$this->refundCurrency = $this->getPostValue('currency');
		$this->refundType = $this->getPostValue('refundType');
		
		$order = Booki_BookingProvider::orderRepository()->read($this->refundOrderId);
		if(!$order->transactionId && !$order->token){
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::REFUNDED, $order->id, null, null, null, $order->totalAmount);
			$result = $notificationEmailer->send();
			$order->refundAmount = $order->totalAmount;
			$order->status = Booki_PaymentStatus::REFUNDED;
			Booki_BookingProvider::orderRepository()->update($order);
			return;
		}
		add_filter( 'booki_refund_order_id', array($this, 'getRefundOrderId'));
		add_filter( 'booki_refund_amount', array($this, 'getRefundAmount'));
		add_filter( 'booki_refund_currency', array($this, 'getRefundCurrency'));
		add_filter( 'booki_refund_type', array($this, 'getRefundType'));
		
		$this->executeCallback($callback, array($this->refundOrderId, $this->refundAmount, $this->refundCurrency, $this->refundType));
	}
	
	public function getRefundOrderId(){
		return $this->refundOrderId;
	}
	public function getRefundAmount(){
		return $this->refundAmount;
	}

	public function getRefundCurrency(){
		return $this->refundCurrency;
	}
	
	public function getRefundType(){
		return $this->refundType;
	}
}
?>