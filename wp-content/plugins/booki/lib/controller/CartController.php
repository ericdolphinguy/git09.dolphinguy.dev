<?php
	require_once dirname(__FILE__) . '/base/BaseCartController.php';
	require_once 'MiniCartController.php';
	
class Booki_CartController extends Booki_BaseCartController{

	public function __construct(){
		$couponCallback = null;
		$checkoutCallback = null;
		$numArgs = func_num_args();

		if($numArgs > 0){
			$couponCallback = func_get_arg(0);
		}
		if($numArgs > 1){
			$checkoutCallback = func_get_arg(1);
		}
		
		if(!Booki_NonceHelper::verify('booki-checkout-grid')){
			return;
		}
		
		parent::__construct();
		
		new Booki_MiniCartController();

		if (array_key_exists('booki_checkout', $_POST)){
			$this->checkout($checkoutCallback);
		}else if (array_key_exists('booki_continue_booking', $_POST)){
			$this->continueBooking();
		}else if(array_key_exists('booki_redeem_coupon', $_POST)){
			$this->redeemCoupon($couponCallback);
		}else if(array_key_exists('booki_cancel_coupon', $_POST)){
			$this->cancelCoupon();
		}
	}
}
?>