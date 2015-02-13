<?php
require_once  dirname(__FILE__) . '/../ui/builders/OrderSummaryBuilder.php';
require_once  dirname(__FILE__) . '/../ui/builders/SettingsGlobalBuilder.php';
require_once  dirname(__FILE__) . '/../../controller/CartController.php';
require_once dirname(__FILE__) . '/../session/Cart.php';
class Booki_CheckoutGridTmpl{
	public $data;
	public $globalSettings;
	public $couponErrorMessage;
	public $checkoutSuccessMessage;
	public $coupon;
	public $confirmCheckout = false;
	public $checkoutFailure;
	public $paymentSuccess = false;
	public $orderHistoryUrl;
	public $orderId;
	public $enableCoupons;
	public $showFooter = true;
	public $editable = true;
	public $displayTimezone = true;
	public $resx;
	public function __construct(){
		$result = apply_filters( 'booki_cart_items', null);
		
		$this->globalSettings = $result->globalSettings;
		$this->enableCoupons = $this->globalSettings->enableCoupons;
		$this->data = $result->data;
		$this->resx = Booki_Helper::resx();
		
		if(isset($result->couponErrorMessage)){
			$this->couponErrorMessage = $result->couponErrorMessage;
		}
		if(isset($result->checkoutSuccessMessage)){
			$this->checkoutSuccessMessage = $result->checkoutSuccessMessage;
		}
		if(isset($result->coupon)){
			$this->coupon = $result->coupon;
		}
		if(isset($result->editable)){
			$this->editable = $result->editable;
		}
		if(isset($result->confirmCheckout)){
			$this->confirmCheckout = $result->confirmCheckout;
		}
		if(isset($result->checkoutFailure)){
			$this->checkoutFailure = $result->checkoutFailure;
		}
		if(isset($result->paymentSuccess)){
			$this->paymentSuccess = $result->paymentSuccess;
		}
		if(isset($result->enableCoupons)){
			$this->enableCoupons = $result->enableCoupons;
		}
		if(isset($result->showFooter)){
			$this->showFooter = $result->showFooter;
		}
		if($this->globalSettings->useDashboardHistoryPage){
			$this->orderHistoryUrl = admin_url() . 'admin.php?page=booki/userhistory.php';
		}else{
			$this->orderHistoryUrl = Booki_Helper::getUrl(Booki_PageNames::HISTORY_PAGE);
		}
		$this->displayTimezone = $this->globalSettings->displayTimezone();
		//Booki_Helper::noCache();
	}
}
?>