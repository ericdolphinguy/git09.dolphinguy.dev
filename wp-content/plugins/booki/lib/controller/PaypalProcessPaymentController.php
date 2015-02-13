<?php
	require_once dirname(__FILE__) . '/base/BaseController.php';
	require_once  dirname(__FILE__) .  '/../infrastructure/payment/gateways/PPProcessPayment.php';

class Booki_PaypalProcessPaymentController extends Booki_BaseController{
	
	public $orderId;
	public function __construct(){
		$fillDataCallback = null;
		$processPaymentCallback = null;
		$autoConfirmOrder = false;
		$numArgs = func_num_args();
		if($numArgs > 0){
			$autoConfirmOrder = func_get_arg(0);
		}
		if($numArgs > 1){
			$fillDataCallback = func_get_arg(1);
		}
		if($numArgs > 2){
			$processPaymentCallback = func_get_arg(2);
		}
		if ($autoConfirmOrder || array_key_exists('booki_paypal_process_payment', $_POST)){
			$this->proceed($processPaymentCallback);
		}else{
			$this->executeCallback($fillDataCallback, array());
		}
	}
	
	public function proceed($callback){
		$processPayment = new Booki_PPProcessPayment();
		$result = $processPayment->expressCheckout();
		//redirect to user history page on success.
		$this->executeCallback($callback, array($result));
	}
}
?>