<?php
	require_once dirname(__FILE__) . '/base/BaseCartController.php';
	
class Booki_WizardController extends Booki_BaseCartController{
	public function __construct($addToCartCallback, $checkoutCallback){
		if(!Booki_NonceHelper::verify('booki-wizard')){
			return;
		}
		parent::__construct();

		if (array_key_exists('booki_add_cart', $_POST)){
			$this->addToCart($addToCartCallback);
		}else if (array_key_exists('booki_checkout', $_POST)){
			$this->checkout($checkoutCallback, $addToCartCallback);
		}
	}
}
?>