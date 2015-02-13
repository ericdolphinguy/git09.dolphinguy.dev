<?php
require_once dirname(__FILE__) . '/../ui/BillSettlement.php';
require_once dirname(__FILE__) . '/../../controller/BillSettlementController.php';
require_once dirname(__FILE__) . '/../utils/Helper.php';

class Booki_BillSettlementTmpl{
	public $data;
	public $globalSettings;
	public $couponErrorMessage;
	public $checkoutSuccessMessage;
	public $couponCode;
	public $enableCoupons;
	public $editable = false;
	public $showFooter = true;
	public function __construct(){
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$this->enableCoupons = $this->globalSettings->enableCoupons;
		
		new Booki_BillSettlementController(array($this, 'couponCallback'), array($this, 'checkoutCallback'));
		
		$orderId = null;
		if(isset($_GET['orderid'])){
			$orderId = (int)$_GET['orderid'];
		}

		$this->data = new Booki_BillSettlement($orderId, $this->couponCode);
		
		if(!$this->data->hasBookings){
			$this->showFooter = false;
		}
		add_filter( 'booki_cart_items', array($this, 'getData'));
	}
	
	public function getData(){
		return $this;
	}
	
	public function couponCallback($couponCode, $errorMessage){
		$this->couponCode = $couponCode;
		$this->couponErrorMessage = $errorMessage;
	}
	
	public function checkoutCallback($couponCode, $errorMessage){
		$this->couponCode = $couponCode;
		$this->checkoutSuccessMessage = $errorMessage;
	}
}
?>