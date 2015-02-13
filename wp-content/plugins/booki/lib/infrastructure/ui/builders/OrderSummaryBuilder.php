<?php
require_once  dirname(__FILE__) . '/../OrderSummary.php';
require_once  dirname(__FILE__) . '/../../session/Cart.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';

class Booki_OrderSummaryBuilder{
	private $cart;
	private $localeInfo;
	public $result;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs === 1){
			$this->cart = func_get_arg(0);
		}else{
			$this->cart = new Booki_Cart();
		}
		$this->localeInfo = Booki_Helper::getLocaleInfo();
		$bookings = $this->cart->getBookings();
		$this->result = new Booki_OrderSummary($bookings, $this->localeInfo['currency'], $this->localeInfo['currencySymbol'], $bookings->timezone);
	}
}
?>