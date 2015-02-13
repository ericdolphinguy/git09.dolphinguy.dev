<?php
	if(!class_exists('PayPalAPIInterfaceServiceService')){
		require_once BOOKI_PAYPAL_MERCHANT_SDK . 'lib/services/PayPalAPIInterfaceService/PayPalAPIInterfaceServiceService.php';
	}
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/PaypalSettingRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/OrderRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/BookedOptionalsRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/BookedDaysRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/BookedCascadingItemsRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/service/EventsLogProvider.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/PaymentStatus.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/BookingStatus.php';
	require_once dirname(__FILE__) . '/../../emails/NotificationEmailer.php';
	require_once dirname(__FILE__) . '/../../utils/Helper.php';
/**
	@description Refunding through Paypal API
*/
class Booki_PPRefund{
	private $orderId;
	private $refundSource;
	private $amount;
	private $refundType;
	private $memo;
	private $retryUntil;
	private $orderRepository;
	private $paypalSettings;
	private $bookedDayId;
	private $bookedOptionalId;
	private $bookedCascadingItemId;
	private $globalSettings;
	public function __construct($orderId, $refundSource, $amount = ''
								, $refundType = 'FULL', $memo = '', $retryUntil = ''
								, $bookedDayId = null, $bookedOptionalId = null, $bookedCascadingItemId = null)
	{	
		$this->orderId = $orderId;
		$this->refundSource = $refundSource;
		$this->amount = $amount;
		$this->refundType = $refundType;
		$this->memo = $memo;
		$this->retryUntil = $retryUntil;
		$this->bookedDayId = $bookedDayId;
		$this->bookedOptionalId = $bookedOptionalId;
		$this->bookedCascadingItemId = $bookedCascadingItemId;
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $paypalSettingRepo->read();
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$this->orderRepository = new Booki_OrderRepository();
	}
	
	public function refundTransaction()
	{
		$refundReqest = new RefundTransactionRequestType();
		$order = $this->orderRepository->read($this->orderId);
		
		if(!$order){
			return false;
		}
		if($this->amount != '' && strtoupper($this->refundType) != 'FULL') {
			$refundReqest->Amount = new BasicAmountType($order->currency, $this->amount);
		}

		$refundReqest->RefundType = $this->refundType;
		$refundReqest->TransactionID = $order->transactionId;
		$refundReqest->RefundSource = $this->refundSource;
		$refundReqest->Memo = $this->memo;
		$refundReqest->RetryUntil = $this->retryUntil;
		
		$refundReq = new RefundTransactionReq();
		$refundReq->RefundTransactionRequest = $refundReqest;

		$paypalService = new PayPalAPIInterfaceServiceService();
		try {
			$refundResponse = @$paypalService->RefundTransaction($refundReq, new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
			));
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($refundResponse)) {
			if($refundResponse->Ack === 'Success'){
				$order->status = Booki_PaymentStatus::REFUNDED;
				$order->refundAmount += $this->amount;
				$notify = $this->globalSettings->autoRefundNotification;
				
				$bookedDaysRepo = new Booki_BookedDaysRepository();
				$bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
				$bookedCascadingItemsRepo = new Booki_BookedCascadingItemsRepository();
				
				$emailType = Booki_EmailType::REFUNDED;
				if($this->bookedDayId !== null){
					$emailType = Booki_EmailType::BOOKING_DAY_REFUNDED;
					$bookedDaysRepo->updateStatus($this->bookedDayId, Booki_BookingStatus::REFUNDED);
				}else if ($this->bookedOptionalId !== null){
					$emailType = Booki_EmailType::BOOKING_OPTIONAL_ITEM_REFUNDED;
					$bookedOptionalsRepo->updateStatus($this->bookedOptionalId, Booki_BookingStatus::REFUNDED);
				}else if ($this->bookedCascadingItemId !== null){
					$emailType = Booki_EmailType::BOOKING_OPTIONAL_ITEM_REFUNDED;
					$bookedCascadingItemsRepo->updateStatus($this->bookedCascadingItemId, Booki_BookingStatus::REFUNDED);
				}else{
					$bookedDaysRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
					$bookedOptionalsRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
					$bookedCascadingItemsRepo->updateStatusByOrderId($this->orderId, Booki_BookingStatus::REFUNDED);
				}
				
				if($emailType !== Booki_EmailType::REFUNDED){
					$order->status = Booki_PaymentStatus::PARTIALLY_REFUNDED;
					//partial refunds always show notification, even if notification is turned off globally.
					//we currently have no means of manually sending notifications in case of partial refunds.
					//might add this in the future.
					$notify = true;
				}
				
				if($notify){
					$notificationEmailer = new Booki_NotificationEmailer($emailType, $this->orderId, $this->bookedDayId, $this->bookedOptionalId, $this->bookedCascadingItemId, $this->amount . ' ' . $order->currency);
					$result = $notificationEmailer->send();
					if($result){
						++$order->refundNotification;
					}
				}
				$this->orderRepository->update($order);
			} else{
				Booki_EventsLogProvider::insert($refundResponse);
			}
			return $refundResponse;
		}
		return false;
	}
	
}
?>