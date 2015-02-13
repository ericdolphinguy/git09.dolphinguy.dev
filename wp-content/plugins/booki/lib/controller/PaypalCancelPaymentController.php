<?php
	require_once dirname(__FILE__) . '/base/BaseController.php';
	require_once dirname(__FILE__) . '/../domainmodel/repository/OrderRepository.php';
	require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
	require_once dirname(__FILE__) . '/../infrastructure/payment/gateways/PPGetExpressCheckoutDetails.php';

class Booki_PaypalCancelPaymentController extends Booki_BaseController{
	
	public $orderId;
	public $token = null;
	public $payerId = null;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$cancelCallback = func_get_arg(0);
		}
		if($numArgs > 1){
			$this->token = func_get_arg(1);
		}
		if($numArgs > 2){
			$this->payerId = func_get_arg(2);
		}
		$this->token = isset($_GET['token']) ? $_GET['token'] : $this->token;
		$this->payerId = isset($_GET['PayerID']) ? $_GET['PayerID'] : $this->payerId;
		$globalSettings = Booki_Helper::globalSettings();
		if($globalSettings->deletePayPalCancelledBooking){
			$this->cancel($cancelCallback);
		}
	}
	
	public function cancel($callback){
		$orderRepository = new Booki_OrderRepository();
		$getExpressCheckoutDetails = new Booki_PPGetExpressCheckoutDetails($this->token, $this->payerId);
		$result = $getExpressCheckoutDetails->getDetails();
		if($result){
			$result = $orderRepository->delete($result['orderId']);
		}

		$this->executeCallback($callback, array($result));
	}
}
?>