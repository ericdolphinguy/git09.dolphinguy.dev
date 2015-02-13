<?php
require_once 'BookedOptionals.php';
require_once 'BookedCascadingItems.php';
require_once 'BookedFormElements.php';
require_once 'BookedDays.php';
require_once 'PaymentStatus.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_Order extends Booki_EntityBase{
	public $id = -1;
	public $orderDate;
	public $paymentDate;
	public $userId = null;
	public $status = Booki_PaymentStatus::UNPAID;
	public $token = null;
	public $transactionId = null;
	public $refundAmount = 0;
	public $currency;
	/**
		@description A special note a user can leave when checking out through paypal. 
		Value is retrieved from paypal after user payment.
	*/
	public $note = null;
	public $totalAmount = 0;
	/**
		@description Number of times an invoice was sent
	*/
	public $invoiceNotification = 0;
	public $refundNotification = 0;
	public $bookedOptionals;
	public $bookedCascadingItems;
	public $bookedFormElements;
	public $bookedDays;

	public $timezone;
	public $discount = 0;
	public $tax = 0;
	public $userIsRegistered = false;
	public $hasDaysPendingApproval = false;
	public $hasOptionalsPendingApproval = false;
	public $hasCascadingItemsPendingApproval = false;
	public $hasDaysPendingCancellation = false;
	public $hasOptionalsPendingCancellation = false;
	public $hasCascadingItemsPendingCancellation = false;
	public $hasPendingApproval;
	public $hasPendingCancellation;
	public $user = null;
	public $notRegUserFirstname = null;
	public $notRegUserLastname = null;
	public $notRegUserEmail = null;
	public $csvFields = array();
	public function __construct($args){
		if($this->keyExists('orderDate', $args)){
			$this->orderDate = new Booki_DateTime($args['orderDate']);
		}
		if($this->keyExists('userId', $args)){
			$this->userId = (int)$args['userId'];
		}
		if($this->keyExists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if($this->keyExists('token', $args)){
			$this->token = (string)$args['token'];
		}
		if($this->keyExists('transactionId', $args)){
			$this->transactionId = (string)$args['transactionId'];
		}
		if($this->keyExists('note', $args)){
			$this->note = (string)$args['note'];
		}
		if($this->keyExists('totalAmount', $args)){
			$this->totalAmount = (double)$args['totalAmount'];
		}
		if($this->keyExists('currency', $args)){
			$this->currency = (string)$args['currency'];
		}
		if($this->keyExists('discount', $args)){
			$this->discount = (double)$args['discount'];
		}
		if($this->keyExists('tax', $args)){
			$this->tax = (double)$args['tax'];
		}
		if($this->keyExists('invoiceNotification', $args)){
			$this->invoiceNotification = (int)$args['invoiceNotification'];
		}
		if($this->keyExists('refundNotification', $args)){
			$this->refundNotification = (int)$args['refundNotification'];
		}
		if($this->keyExists('refundAmount', $args)){
			$this->refundAmount = (double)$args['refundAmount'];
		}
		if($this->keyExists('paymentDate', $args)){
			$this->paymentDate = new Booki_DateTime($args['paymentDate']);
		}
		if($this->keyExists('timezone', $args)){
			$this->timezone = (string)$args['timezone'];
		}
		if($this->keyExists('isRegistered', $args)){
			$this->userIsRegistered = (bool)$args['isRegistered'];
		}
		if($this->keyExists('id', $args)){
			$this->id = (int)$args['id'];
		}
		if($this->keyExists('notRegUserFirstname', $args)){
			$this->notRegUserFirstname = (string)$args['notRegUserFirstname'];
		}
		if($this->keyExists('notRegUserLastname', $args)){
			$this->notRegUserLastname = (string)$args['notRegUserLastname'];
		}
		if($this->keyExists('notRegUserEmail', $args)){
			$this->notRegUserEmail = (string)$args['notRegUserEmail'];
		}
		if($this->keyExists('projectNames', $args)){
			$this->csvFields['projectNames'] = (string)$args['projectNames'];
		}
		if($this->keyExists('bookingDates', $args)){
			$this->csvFields['bookingDates'] = (string)$args['bookingDates'];
		}
		if($this->keyExists('optionals', $args)){
			$this->csvFields['optionals'] = (string)$args['optionals'];
		}
		if($this->keyExists('cascadingItems', $args)){
			$this->csvFields['cascadingItems'] = (string)$args['cascadingItems'];
		}
		if($this->keyExists('hasDaysPendingApproval', $args)){
			$this->hasDaysPendingApproval = (int)$args['hasDaysPendingApproval'] > 0;
		}
		if($this->keyExists('hasOptionalsPendingApproval', $args)){
			$this->hasOptionalsPendingApproval = (int)$args['hasOptionalsPendingApproval'] > 0;
		}
		if($this->keyExists('hasCascadingItemsPendingApproval', $args)){
			$this->hasCascadingItemsPendingApproval = (int)$args['hasCascadingItemsPendingApproval'] > 0;
		}
		if($this->keyExists('hasDaysPendingCancellation', $args)){
			$this->hasDaysPendingCancellation = (int)$args['hasDaysPendingCancellation'] > 0;
		}
		if($this->keyExists('hasOptionalsPendingCancellation', $args)){
			$this->hasOptionalsPendingCancellation = (int)$args['hasOptionalsPendingCancellation'] > 0;
		}
		if($this->keyExists('hasCascadingItemsPendingCancellation', $args)){
			$this->hasCascadingItemsPendingCancellation = (int)$args['hasCascadingItemsPendingCancellation'] > 0;
		}
		
		$globalSettings = Booki_Helper::globalSettings();
		
		if(!$this->currency){
			$localeInfo = Booki_Helper::getLocaleInfo();
			$this->currency = $localeInfo['currency'];
		}
		if(!($this->tax > 0) && $globalSettings->tax > 0){
			$this->tax = $globalSettings->tax;
		}
		$this->bookedOptionals = new Booki_BookedOptionals();
		$this->bookedFormElements = new Booki_BookedFormElements();
		$this->bookedDays = new Booki_BookedDays();
		$this->bookedCascadingItems = new Booki_BookedCascadingItems();
	}
	
	public function toArray(){
		
		$this->hasPendingApproval = $this->hasDaysPendingApproval || $this->hasOptionalsPendingApproval || $this->hasCascadingItemsPendingApproval;
		$this->hasPendingCancellation = $this->hasDaysPendingCancellation || $this->hasOptionalsPendingCancellation || $this->hasCascadingItemsPendingCancellation;
	
		$result = array(
			'id'=>$this->id
			, 'orderDate'=>$this->orderDate
			, 'userId'=>$this->userId
			, 'status'=>$this->status
			, 'token'=>$this->token
			, 'transactionId'=>$this->transactionId
			, 'note'=>$this->note
			, 'totalAmount'=>$this->totalAmount
			, 'currency'=>$this->currency
			, 'discount'=>$this->discount
			, 'tax'=>$this->tax
			, 'invoiceNotification'=>$this->invoiceNotification
			, 'refundNotification'=>$this->refundNotification
			, 'refundAmount'=>$this->refundAmount
			, 'paymentDate'=>$this->paymentDate
			, 'timezone'=>$this->timezone
			, 'userIsRegistered'=>$this->userIsRegistered
			, 'hasDaysPendingApproval'=>$this->hasDaysPendingApproval
			, 'hasOptionalsPendingApproval'=>$this->hasOptionalsPendingApproval
			, 'hasCascadingItemsPendingApproval'=>$this->hasCascadingItemsPendingApproval
			, 'hasDaysPendingCancellation'=>$this->hasDaysPendingCancellation
			, 'hasOptionalsPendingCancellation'=>$this->hasOptionalsPendingCancellation
			, 'hasCascadingItemsPendingCancellation'=>$this->hasCascadingItemsPendingCancellation
			, 'hasPendingApproval'=>$this->hasPendingApproval
			, 'hasPendingCancellation'=>$this->hasPendingCancellation
			, 'approvalStatus'=>$this->getApprovalStatus()
			, 'approvalStatusLabel'=>$this->getApprovalStatusLabel()
			, 'notRegUserFirstname'=>$this->notRegUserFirstname
			, 'notRegUserLastname'=>$this->notRegUserLastname
			, 'notRegUserEmail'=>$this->notRegUserEmail
		);
		if($this->user){
			$user = $this->user->toArray();
			unset($user['id']);
			$result = array_merge($result, $user);
		}
		return $result;
	}
	
	public function afterDiscount(){
		$totalAmount = $this->totalAmount;
		if($this->discount > 0){
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $this->totalAmount);
		}
		return $totalAmount;
	}
	
	protected function getApprovalStatus(){
		if ($this->hasPendingApproval){
			return __('Pending Approval', 'booki');
		}else if ($this->hasPendingCancellation){
			return __('Pending User Cancel Request', 'booki');
		}else if ($this->status === Booki_BookingStatus::REFUNDED){
			return __('Refunded', 'booki');
		}else{
			return __('Approved', 'booki');
		}
	}
	
	protected function getApprovalStatusLabel(){
		if ($this->hasPendingApproval){
			return 'info';
		}else if ($this->hasPendingCancellation){
			return 'danger';
		}else if ($this->status === Booki_BookingStatus::REFUNDED){
			return 'warning';
		}
		return 'success';
	}
}
?>