<?php
require_once dirname(__FILE__) . '/../ui/builders/OrderSummaryBuilder.php';
require_once dirname(__FILE__) . '/../../controller/CartController.php';
require_once dirname(__FILE__) . '/../session/Cart.php';
require_once dirname(__FILE__) . '/../utils/Helper.php';

class Booki_CartTmpl{
	public $data;
	public $globalSettings;
	public $couponErrorMessage;
	public $checkoutSuccessMessage;
	public $coupon;
	public $editable = true;
	public $enableCoupons;
	public $showFooter = true;
	public function __construct(){
		$this->globalSettings = Booki_Helper::globalSettings();

		$this->enableCoupons = $this->globalSettings->enableCoupons;
	
		new Booki_CartController(array($this, 'couponCallback'), array($this, 'checkoutCallback'));
		
		if(!$this->data){
			$orderBuilder = new Booki_OrderSummaryBuilder();
			$this->data = $orderBuilder->result;
			
			$cart = new Booki_Cart();
			$this->coupon = $cart->getCoupon();
		}
		add_filter( 'booki_cart_items', array($this, 'getData'));
	}
	
	public function getData(){
		return $this;
	}
	
	public function couponCallback($errorMessage){
		$this->couponErrorMessage = $errorMessage;
	}
	
	public function checkoutCallback($errorMessage, $orderId = null, $couponCode = null){
		$this->checkoutSuccessMessage = $errorMessage;
		if($orderId){
			$this->editable = false;
			$this->enableCoupons = false;
			$this->showFooter = false;
			$this->data = new Booki_BillSettlement($orderId, $couponCode);
		}
	}
}
?>