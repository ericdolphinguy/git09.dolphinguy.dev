<?php
require_once 'Booking.php';
require_once dirname(__FILE__) . '/../../domainmodel/base/CollectionBase.php';
require_once dirname(__FILE__) . '/../utils/Helper.php';
require_once dirname(__FILE__) . '/../utils/TimeHelper.php';
class Booki_Bookings extends Booki_CollectionBase{
	public $timezone;
	public $coupon;
	public function __construct($timezone = null){
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		$this->timezone = $this->timezoneInfo['timezone'];
	}
	public function setTimezone($value){
		$this->timezone = $value;
	}
	public function add($value) {
		if (! ($value instanceOf Booki_Booking) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Booking class.');
		}
        parent::add($value);
    }
}
?>