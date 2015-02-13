<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/DateHelper.php';
require_once 'TimeSelector.php';

class Booki_SettingGlobal extends Booki_EntityBase{
	public $id = -1;
	public $adminUserId = null;
	public $notificationEmailTo = null;
	public $continueBookingUrl;
	public $loginPageUrl = null;
	public $autoLoginAfterRegistration = true;
	public $tax = 0;
	public $enablePayments = false;
	public $autoConfirmOrderAfterPayment = true;
	public $unpaidOrderExpiry = 0;
	public $autoApproveBooking = false;
	public $autoInvoiceNotification = false;
	public $autoRefundNotification = true;
	public $autoNotifyAdminNewBooking = true;
	public $notifyBookingCancellation = true;
	public $notifyBookingReceivedSuccessfully = true;
	public $enableCartItemHeader = true;
	public $enableCoupons = true;
	public $timezone;
	public $mailChimpKey;
	public $theme = '-1';
	public $refBootstrapJS = true;
	public $refBootstrapStyleSheet = true;
	public $calendarTheme = 'smoothness';
	public $calendarFlatStyle = true;
	public $calendarBorderlessStyle = true;
	public $shorthandDateFormat = 'MM/DD/YYYY';
	public $enableEditors = false;
	public $enableUserCancelBooking = true;
	public $eventsLogExpiry = 7;
	public $deletePayPalCancelledBooking = false;
	public $autoTimezoneDetection = true;
	public $enableTimezoneEdit = true;
	public $debugMode = false;
	public $timeSelector = Booki_TimeSelector::DROPDOWNLIST;
	public $discount = 0;
	public $bookingMinimumDiscount = 0;
	public $includeBookingPrice = true;
	public $addToCart = true;
	public $useCartSystem = true;
	public $membershipRequired = true;
	public $useDashboardHistoryPage = true;
	public $calendarFirstDay = 7;
	public $showCalendarButtonPanel = false;
	public $enableBookingWithAndWithoutPayment = false;
	public $displayBookedTimeSlots = true;
	public $currencyCode;
	public $currencySymbol;
	public $highlightSelectedOptionals = false;
	public $oneForm = false;
	public $noCache = false;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->id = func_get_arg(0);
		}
		$timezone = get_option('timezone_string');
		if(!$timezone || is_numeric($timezone)){
			$timezone = date_default_timezone_get();
		}
		$this->timezone = $timezone;
		if(!$this->loginPageUrl){
			$this->loginPageUrl = wp_login_url( get_permalink() );
		}

		if($this->adminUserId === null || $this->adminUserId === ''){
			//first time, just get the first admin user
			$admins = get_users('fields=id&role=administrator');
			if($admins && count($admins) > 0){
				$this->adminUserId = (int)$admins[0];
			}
		}

		if($this->notificationEmailTo === null){
			$this->notificationEmailTo = Booki_Helper::getUserEmail($this->adminUserId);
		}
	}

	public function getServerFormatShorthandDate(){
		$dateFormat = $this->shorthandDateFormat;
		$separator = Booki_DateHelper::getSeparator($dateFormat);
		$result = sprintf('Y%1$sm%1$sd', $separator);
		if($dateFormat == sprintf('MM%1$sDD%1$sYYYY', $separator)) {
				$result = sprintf('m%1$sd%1$sY', $separator);
		}else if($dateFormat == sprintf('DD%1$sMM%1$sYYYY', $separator)){
				$result = sprintf('d%1$sm%1$sY', $separator);
		}else if($dateFormat == sprintf('YYYY%1$sMM%1$sDD', $separator)){
				$result = sprintf('Y%1$sm%1$sd', $separator);
		}
		return $result;
	}
	
	public function displayTimezone(){
		return $this->autoTimezoneDetection && $this->enableTimezoneEdit;
	}
}
?>