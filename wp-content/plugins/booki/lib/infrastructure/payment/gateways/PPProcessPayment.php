<?php
	if(!class_exists('PayPalAPIInterfaceServiceService')){
		require_once BOOKI_PAYPAL_MERCHANT_SDK . 'lib/services/PayPalAPIInterfaceService/PayPalAPIInterfaceServiceService.php';
	}
	require_once dirname(__FILE__) . '/../../../infrastructure/emails/NotificationEmailer.php';
	require_once dirname(__FILE__) . '/../../../infrastructure/ui/BillSettlement.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/PaypalSettingRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/SettingsGlobalRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/PaymentStatus.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/OrderRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/CouponRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/BookedDaysRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/BookedOptionalsRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/service/BookingProvider.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/service/EventsLogProvider.php';
	require_once dirname(__FILE__) . '/../../utils/Helper.php';
	require_once 'PPGetExpressCheckoutDetails.php';
	
class Booki_PPProcessPayment{
	private $paypalSettings;
	private $globalSettings;
	private $token;
	private $payerId;
	private $orderRepository;
	private $couponRepository;
	public function __construct($token = null, $payerId = null)
	{
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $paypalSettingRepo->read();
		
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$this->token = isset($_GET['token']) ? $_GET['token'] : $token;
		$this->payerId = isset($_GET['PayerID']) ? $_GET['PayerID'] : $payerId;
		$this->orderRepository = new Booki_OrderRepository();
		$this->couponRepository = new Booki_CouponRepository();
	}
	
	public function expressCheckout()
	{
		$getExpressCheckoutDetails = new Booki_PPGetExpressCheckoutDetails();
		$result = $getExpressCheckoutDetails->getDetails();
		
		if($result){
			return $this->doExpressCheckout((int)$result['orderId'], $result['couponCode'], $result['payerEmail'], $result['firstName'], $result['lastName']);
		}
		
		return false;
	}
	
	protected function doExpressCheckout($orderId, $couponCode, $payerEmail, $firstName, $lastName){
		$order = $this->orderRepository->read($orderId);
		if(!$order || $order->status === Booki_PaymentStatus::PAID){
			return false;
		}
		
		$billing = new Booki_BillSettlement($orderId, $couponCode);
		$totalAmount = $billing->totalAmountIncludingTax;

		$paymentDetails = new PaymentDetailsType();
		$paymentDetails->OrderTotal = new BasicAmountType($this->paypalSettings->currency, Booki_Helper::toMoney($totalAmount));
		
		$doECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
		$doECRequestDetails->PayerID = $this->payerId;
		$doECRequestDetails->Token = $this->token;
		$doECRequestDetails->PaymentAction = 'Sale';
		$doECRequestDetails->PaymentDetails[0] = $paymentDetails;

		$doECRequest = new DoExpressCheckoutPaymentRequestType();
		$doECRequest->DoExpressCheckoutPaymentRequestDetails = $doECRequestDetails;


		$doECReq = new DoExpressCheckoutPaymentReq();
		$doECReq->DoExpressCheckoutPaymentRequest = $doECRequest;
		
		$paypalService = new PayPalAPIInterfaceServiceService();
		try {
			$doECResponse = @$paypalService->DoExpressCheckoutPayment($doECReq, new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
			));
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($doECResponse) && $doECResponse->Ack == 'Success') {
			if(isset($doECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo)) {
				if(!$order->userIsRegistered){
					$createUserResult = Booki_Helper::createUserIfNotExists($payerEmail, $firstName, $lastName);
					$order->userId = $createUserResult['userId'];
					$order->userIsRegistered = true;
				}
				$order->transactionId = $doECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
				$order->note = $doECResponse->DoExpressCheckoutPaymentResponseDetails->Note;
				$order->status = Booki_PaymentStatus::PAID;
				$order->totalAmount = $totalAmount;
				
				$order->paymentDate = new Booki_DateTime();
				
				if(isset($couponCode)){
					if(strlen($couponCode) === 40){
						//invalidate coupon.
						$coupon = $this->couponRepository->find($couponCode);
						if($coupon && $coupon->couponType === Booki_CouponType::REGULAR){
							$coupon->expire();
							$this->couponRepository->update($coupon);
							//update discount field on order
							$order->discount = $coupon->discount;
						}
					} else{
						$order->discount = (double)$couponCode;
					}
				}
				$this->orderRepository->update($order);
				try{
					$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::PAYMENT_RECEIVED, $order->id);
					$result = $notificationEmailer->send();
					
					if($this->globalSettings->autoApproveBooking){
						Booki_BookingProvider::approveOrderAndNotifyUser($order->id);
					}
					
					if($this->globalSettings->autoNotifyAdminNewBooking){
						$notificationToUserInfo = Booki_Helper::getUserInfoByEmail($this->globalSettings->notificationEmailTo);
						$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_ADMIN, $order->id, null, null, null, 0, $notificationToUserInfo);
						$notificationEmailer->send();
						
						//notifies also agents if projects in booking have agents
						$notificationEmailer = new Booki_AgentsNotificationEmailer(Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_AGENTS, $order->id);
						$notificationEmailer->send();
					}
				}catch(Exception $ex){
					Booki_EventsLogProvider::insert($ex);
				}
				return true;
			}
		}else{
			Booki_EventsLogProvider::insert($doECResponse);
		}
		return $doECResponse->Ack;
		//else serialize and log doECResponse
	}
}
?>