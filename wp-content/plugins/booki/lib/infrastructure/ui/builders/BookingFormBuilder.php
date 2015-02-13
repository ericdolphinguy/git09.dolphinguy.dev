<?php
require_once  dirname(__FILE__) . '/../BookingForm.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../../session/Cart.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';

class Booki_BookingFormBuilder{
	private $localeInfo;
	public $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		
		$this->localeInfo = Booki_Helper::getLocaleInfo();
		$this->init();
	}
	
	protected function init(){
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$currency = $this->localeInfo['currency'];
		$currencySymbol = $this->localeInfo['currencySymbol'];
		$locale = $this->localeInfo['locale'];
			
		$result = Booki_BookingProvider::getBookingPeriod($this->projectId, $bookings);
		$this->result = new Booki_BookingForm($result->calendar, $result->calendarDays, $result->bookedDays, $result->project, $currency, $currencySymbol, $locale, $bookings);
	}
}
?>