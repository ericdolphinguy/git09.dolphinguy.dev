<?php
	if(!class_exists('PayPalAPIInterfaceServiceService')){
		require_once BOOKI_PAYPAL_MERCHANT_SDK . 'lib/services/PayPalAPIInterfaceService/PayPalAPIInterfaceServiceService.php';
	}
	require_once dirname(__FILE__) . '/../../../domainmodel/service/EventsLogProvider.php';
	require_once dirname(__FILE__) . '/../../utils/Helper.php';

class Booki_PPGetExpressCheckoutDetails{
	private $token;
	private $payerId;
	private $paypalSettings;
	public function __construct($token = null, $payerId = null)
	{
		$this->token = isset($_GET['token']) ? $_GET['token'] : $token;
		$this->payerId = isset($_GET['PayerID']) ? $_GET['PayerID'] : $payerId;
		
		$paypalSettingRepo = new Booki_PaypalSettingRepository();
		$this->paypalSettings = $paypalSettingRepo->read();
	}

	public function getDetails(){
		if(!$this->token)
		{
			return false;
		}

		$getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($this->token);

		$getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		
		$signatureCredentials = new PPSignatureCredential(
			$this->paypalSettings->username
			, $this->paypalSettings->password
			, $this->paypalSettings->signature
		);
			
		$paypalService = new PayPalAPIInterfaceServiceService();
		try {
			$getECResponse = @$paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq, $signatureCredentials);
		} catch (Exception $ex) {
			Booki_EventsLogProvider::insert($ex);
			return false;
		}
		if(!isset($getECResponse))
		{
			return false;
		}
		$responseDetails = $getECResponse->GetExpressCheckoutDetailsResponseDetails;

		if($this->payerId == $responseDetails->PayerInfo->PayerID && $getECResponse->Ack == 'Success'){
			$firstName = null;
			$lastName = null;
			if(isset($responseDetails->PayerInfo->PayerName)){
				$firstName = isset($responseDetails->PayerInfo->PayerName->FirstName) ? $responseDetails->PayerInfo->PayerName->FirstName : null;
				$lastName = isset($responseDetails->PayerInfo->PayerName->LastName) ? $responseDetails->PayerInfo->PayerName->LastName : null;
			}
			return array(
				'orderId'=>(int)$responseDetails->InvoiceID
				, 'couponCode'=>$responseDetails->Custom
				, 'payerEmail'=>$responseDetails->PayerInfo->Payer
				, 'firstName'=>$firstName
				, 'lastName'=>$lastName
			);
		}
		return false;
	}
}
?>