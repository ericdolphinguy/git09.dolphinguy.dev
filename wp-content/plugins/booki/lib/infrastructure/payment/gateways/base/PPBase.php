<?php
require_once dirname(__FILE__) . '/../../../utils/Helper.php';
require_once dirname(__FILE__) . '/../../../session/OrderLogger.php';
require_once dirname(__FILE__) . '/../../../emails/NotificationEmailer.php';
require_once dirname(__FILE__) . '/../../../emails/AgentsNotificationEmailer.php';
	
require_once dirname(__FILE__) . '/../../../../domainmodel/repository/PaypalSettingRepository.php';
require_once dirname(__FILE__) . '/../../../../domainmodel/repository/SettingsGlobalRepository.php';
require_once dirname(__FILE__) . '/../../../../domainmodel/service/BookingProvider.php';
require_once dirname(__FILE__) . '/../../../../domainmodel/service/EventsLogProvider.php';

if(!class_exists('PayPalAPIInterfaceServiceService')){
	require_once BOOKI_PAYPAL_MERCHANT_SDK . 'lib/services/PayPalAPIInterfaceService/PayPalAPIInterfaceServiceService.php';
	require_once BOOKI_PAYPAL_MERCHANT_SDK . 'lib/auth/PPSignatureCredential.php';
}

class Booki_PPBase{
	private $paypalSettings;
	private $globalSettings;
	private $order;
	private $orderSummary;
	private $user;
	private $coupon;
	private $resx;
	public function __construct($order = null, $coupon = null){
		$this->order = $order;
		if(!$this->order){
			$this->orderLogger = new Booki_OrderLogger();
			$this->order = $this->orderLogger->order;
		}
		$this->coupon = $coupon;
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$globalSettingsRepo = new Booki_SettingsGlobalRepository();
		
		$this->paypalSettings = $paypalSettingRepo->read();
		$this->globalSettings = $globalSettingsRepo->read();
		$this->resx = Booki_Helper::resx();
		
		$this->user = get_userdata($this->order->userId);
	}
	
	public function checkout(){
		if(!$this->order || $this->order->status === Booki_PaymentStatus::PAID){
			return false;
		}
		$returnUrl = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_CONFIRMATION_HANDLER);
		$cancelUrl = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_CANCEL_HANDLER);
		
		$currency = $this->paypalSettings->currency;
		$paymentDetails = new PaymentDetailsType();
		$orderTotal = 0;
		$discount = 0;
		$i = 0;
		
		$deposits = array();
		
		foreach($this->order->bookedDays as $day){
			if($day->cost == 0){
				continue;
			}
			$cost = $this->calcDeposit($day->deposit, $day->cost);
			$orderTotal += $cost;
			if($day->deposit){
				if(!isset($deposits[$day->projectId])){
					$deposits[$day->projectId] = array('name'=>$this->removeSpecialChars($day->projectName), 'cost'=>$cost);
				}else{
					$deposits[$day->projectId]['cost'] += $cost;
				}
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($day->cost));
			$itemDetails->Name = $this->resx->BOOKING_FOR_LOC . ' ' . Booki_Helper::formatDate( $day->bookingDate);
			if($day->hasTime()){
				$itemDetails->Name .= ',  ' . Booki_TimeHelper::formatTime($day, $this->order->timezone, $day->enableSingleHourMinuteFormat);
			}

			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->DAYS_BOOKED_LOC;

			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;			
		}
		
		foreach( $this->order->bookedOptionals as $optional ){
			if($optional->cost == 0){
				continue;
			}
			$cost = $this->calcDeposit($optional->deposit, $optional->getCalculatedCost());
			$orderTotal += $cost;
			if($optional->deposit){
				if(!isset($deposits[$optional->projectId])){
					$deposits[$optional->projectId] = array('name'=>$this->removeSpecialChars($optional->projectName), 'cost'=>$cost);
				}else{
					$deposits[$optional->projectId]['cost'] += $cost;
				}
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($optional->getCalculatedCost()));
			$itemDetails->Name = $this->removeSpecialChars($optional->getName());
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->EXTRAS_LOC;
			
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}
		
		foreach( $this->order->bookedCascadingItems as $cascadingItem ){
			if($cascadingItem->cost == 0){
				continue;
			}
			$cost = $this->calcDeposit($cascadingItem->deposit, $cascadingItem->getCalculatedCost());
			$orderTotal += $cost;
			if($cascadingItem->deposit){
				if(!isset($deposits[$cascadingItem->projectId])){
						$deposits[$cascadingItem->projectId] = array('name'=>$this->removeSpecialChars($cascadingItem->projectName), 'cost'=>$cost);
				}else{
					$deposits[$cascadingItem->projectId]['cost'] += $cost;
				}
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($cascadingItem->getCalculatedCost()));
			$itemDetails->Name = $this->removeSpecialChars($cascadingItem->getName());
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->EXTRAS_LOC;
			
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}

		foreach($deposits as $projectId=>$value){
			if($value['cost'] == 0){
				continue;
			}
			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, Booki_Helper::toMoney($value['cost']));
			$itemDetails->Name = $this->removeSpecialChars($value['name']) . ' ' . $this->resx->DEPOSIT;
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->resx->DEPOSIT;
			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
			if($this->globalSettings->tax){
				$itemDetails->Tax = new BasicAmountType(
					$currency
					, Booki_Helper::toMoney(Booki_Helper::percentage($this->globalSettings->tax, $value['cost']))
				);	
			}
		}
		
		if($this->coupon && $this->coupon->isValid()){
			$discount = $this->coupon->discount;
			$discountValue = Booki_Helper::percentage($discount, $orderTotal);
			$orderTotal = $this->coupon->deduct($orderTotal);

			$itemDetails = new PaymentDetailsItemType();
			$itemDetails->Amount = new BasicAmountType($currency, '-' . Booki_Helper::toMoney($discountValue));
			$itemDetails->Name = sprintf($this->resx->DISCOUNT_BY_PERCENTAGE_LOC, $discount) . '%';
			$itemDetails->Quantity = 1;
			$itemDetails->ItemCategory = $this->paypalSettings->itemCategory;
			$itemDetails->ProductCategory = $this->coupon->id !== -1 ? $this->resx->COUPON_LOC : $this->resx->PROMOTIONS_LOC;

			$paymentDetails->PaymentDetailsItem[$i++] = $itemDetails;
		}
		
		$paymentDetails->ItemTotal = new BasicAmountType($currency, Booki_Helper::toMoney($orderTotal));
		$tax = $this->globalSettings->tax;
		if($this->order && $this->order->tax > 0){
			$tax = $this->order->tax;
		}
		if($tax > 0){
			$taxTotal = Booki_Helper::percentage($tax, $orderTotal);
			$orderTotal = $orderTotal + $taxTotal;
			$paymentDetails->TaxTotal = new BasicAmountType($currency, Booki_Helper::toMoney($taxTotal));
		}
		
		$paymentDetails->OrderTotal = new BasicAmountType($currency, Booki_Helper::toMoney($orderTotal));

		$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
		if($this->user){
			$setECReqDetails->BuyerEmail = $this->user->user_email;
		}
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;
		$setECReqDetails->CancelURL = $cancelUrl;
		$setECReqDetails->ReturnURL = $returnUrl;
		
		// Display options
		$setECReqDetails->cppheaderimage = $this->paypalSettings->headerImage;
		$setECReqDetails->cppheaderbordercolor = $this->parseColor($this->paypalSettings->headerBorderColor);
		$setECReqDetails->cppheaderbackcolor = $this->parseColor($this->paypalSettings->headerBackColor);
		$setECReqDetails->cpppayflowcolor = $this->parseColor($this->paypalSettings->payFlowColor);
		$setECReqDetails->cppcartbordercolor = $this->parseColor($this->paypalSettings->cartBorderColor);
		$setECReqDetails->cpplogoimage = $this->paypalSettings->logo;
		$setECReqDetails->PageStyle = $this->paypalSettings->customPageStyle;
		$setECReqDetails->BrandName = $this->paypalSettings->brandName;

		// Advanced options
		$setECReqDetails->AllowNote = (int)$this->paypalSettings->allowBuyerNote;
		
		if(isset($this->orderLogger)){
			$this->order->discount = $discount;
			$this->order = $this->orderLogger->log();
		}
		/*
			Seeing duplicate invoice error: 
				Log in to your Paypal account and go to Profile > 
					Payment Receiving Preferences and under Block accidental payments select No, 
					allow multiple payments per invoice ID
		*/
		$setECReqDetails->InvoiceID = $this->order->id;
		/*
			offering Guest Checkout by setting SOLUTIONTYPE=Sole and LandingPage=Billing.
			This will greatly increase the ability of your account to offer Guest Checkout.
			You should see Guest Checkout much more often now.
		*/
		//$setECReqDetails->SolutionType = 'Sole';
		//$setECReqDetails->LandingPage = 'Billing';
		
		if($this->coupon && ($this->coupon->id === -1 || $this->coupon->isValid())){
			$setECReqDetails->Custom = $this->coupon->code ? $this->coupon->code : $this->coupon->discount;
		}
		
		$setECReqType = new SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;
		
		$signatureCredentials = new PPSignatureCredential(
				$this->paypalSettings->username
				, $this->paypalSettings->password
				, $this->paypalSettings->signature
		);
		if($this->paypalSettings->appId)
		{
			@$signatureCredentials->setApplicationId($this->paypalSettings->appId);
		}
		$paypalService = new PayPalAPIInterfaceServiceService();
		
		try {
			$setECResponse = @$paypalService->SetExpressCheckout($setECReq, $signatureCredentials);
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}

		if(isset($setECResponse) && $setECResponse->Ack == 'Success') 
		{
			$this->tokenize($setECResponse->Token);
			$host = $this->paypalSettings->useSandBox ? 'https://www.sandbox.paypal.com/' : 'https://www.paypal.com/';
			$url = $host . 'webscr?cmd=_express-checkout&token=' . $this->order->token;
			wp_redirect($url);
		}else{
			Booki_EventsLogProvider::insert($setECResponse);
		}
		return false;
	}

	public function tokenize($token){
		$this->order->token = $token;
		Booki_BookingProvider::update($this->order);
	}
	
	protected function toMoney($val)
	{
		return Booki_Helper::toMoney($val);
	}
	
	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	protected function removeSpecialChars($value){
		return $value;
	}
	/**
		@description Color must not contain the hash symbol and must be 6 characters in length.
	*/
	protected function parseColor($value)
	{
		if(strlen($value) > 6)
		{
			return substr($value, 1, 6);
		}
		return $value;
	}
}
?>