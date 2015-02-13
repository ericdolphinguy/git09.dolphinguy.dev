<?php
	require_once dirname(__FILE__) . '/base/BaseCartController.php';	
	require_once dirname(__FILE__) . '/../infrastructure/session/Cart.php';
	require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_MiniCartController extends Booki_BaseCartController{

	public function __construct(){
		parent::__construct();
		
		if (array_key_exists('booki_empty_cart', $_POST)){
			$this->emptyCart();
		}else if (array_key_exists('booki_remove_date', $_POST)){
			$this->removeDate();
		} else if (array_key_exists('booki_remove_optional', $_POST)){
			$this->removeOptional();
		}else if (array_key_exists('booki_remove_order', $_POST)){
			$this->removeOrder();
		}else if (array_key_exists('booki_remove_cascadingitem', $_POST)){
			$this->removeCascadingListItem();
		}
	}
	
	public function emptyCart(){
		$this->cart->clear();
	}
}
?>