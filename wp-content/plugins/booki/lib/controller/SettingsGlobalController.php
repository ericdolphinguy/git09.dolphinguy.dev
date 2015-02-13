<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/SettingsGlobalRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/SettingGlobal.php';

class Booki_SettingsGlobalController extends Booki_BaseController{
	private $repo;
	private $id;
	public function __construct($createCallback, $updateCallback, $deleteCallback){
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_SettingsGlobalRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$setting = $this->process();
		$result = $this->repo->insert($setting);
		$this->executeCallback($callback, array());
	}
	
	public function update($callback){
		$id = isset($_POST['booki_update']) ? intval($_POST['booki_update']) : null;
		$setting = $this->process($id);
		$result = $this->repo->update($setting);
		$this->executeCallback($callback, array());
	}
	
	public function delete($callback){
		$id = isset($_POST['booki_delete']) ? intval($_POST['booki_delete']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array());
	}
	
	protected function process($id = -1) {
		$adminUserId = (int)$this->getPostValue('adminUserId');
		$notificationEmailTo = $this->getPostValue('notificationEmailTo');
		$autoTimezoneDetection = $this->getBoolPostValue('autoTimezoneDetection');
		$enableTimezoneEdit = $this->getBoolPostValue('enableTimezoneEdit');
		$continueBookingUrl = $this->getPostValue('continueBookingUrl');
		$loginPageUrl = $this->getPostValue('loginPageUrl');
		$calendarFirstDay = $this->getPostValue('calendarFirstDay');
		$showCalendarButtonPanel = $this->getBoolPostValue('showCalendarButtonPanel');
		$autoLoginAfterRegistration = $this->getBoolPostValue('autoLoginAfterRegistration');
		$tax = isset($_POST['tax']) ? (float)$_POST['tax'] : 0;
		$enablePayments = $this->getBoolPostValue('enablePayments');
		$enableBookingWithAndWithoutPayment = $this->getBoolPostValue('enableBookingWithAndWithoutPayment');
		$enableCoupons = $this->getBoolPostValue('enableCoupons');
		$autoConfirmOrderAfterPayment = $this->getBoolPostValue('autoConfirmOrderAfterPayment');
		$unpaidOrderExpiry = (int)$this->getPostValue('unpaidOrderExpiry');
		$eventsLogExpiry = (int)$this->getPostValue('eventsLogExpiry');
		$autoApproveBooking = $this->getBoolPostValue('autoApproveBooking');
		$autoInvoiceNotification = $this->getBoolPostValue('autoInvoiceNotification');
		$autoRefundNotification = $this->getBoolPostValue('autoRefundNotification');
		$autoNotifyAdminNewBooking = $this->getBoolPostValue('autoNotifyAdminNewBooking');
		$notifyBookingCancellation = $this->getBoolPostValue('notifyBookingCancellation');
		$notifyBookingReceivedSuccessfully = $this->getBoolPostValue('notifyBookingReceivedSuccessfully');
		$deletePayPalCancelledBooking =  $this->getBoolPostValue('deletePayPalCancelledBooking');
		$discount = (double)$this->getPostValue('discount');
		$bookingMinimumDiscount = (double)$this->getPostValue('bookingMinimumDiscount');
		$displayBookedTimeSlots = $this->getBoolPostValue('displayBookedTimeSlots');
		
		$enableUserCancelBooking = $this->getBoolPostValue('enableUserCancelBooking');
		$calendarFlatStyle = $this->getBoolPostValue('calendarFlatStyle');
		$calendarBorderlessStyle = $this->getBoolPostValue('calendarBorderlessStyle');
		
		$enableCartItemHeader = $this->getBoolPostValue('enableCartItemHeader');
		$includeBookingPrice = $this->getBoolPostValue('includeBookingPrice');
		$addToCart = $this->getBoolPostValue('addToCart');
		$useCartSystem = $this->getBoolPostValue('useCartSystem');
		$membershipRequired = $this->getBoolPostValue('membershipRequired');
		
		$mailChimpKey = $this->getPostValue('mailChimpKey');
		$theme = $this->getPostValue('theme');
		$timeSelector = (int)$this->getPostValue('timeSelector');
		$calendarTheme = $this->getPostValue('calendarTheme');
		
		$refBootstrapStyleSheet = $this->getBoolPostValue('refBootstrapStyleSheet');
		$refBootstrapJS = $this->getBoolPostValue('refBootstrapJS');
		$debugMode = $this->getBoolPostValue('debugMode');
		$timezone = $this->getPostValue('timezone');
		
		if(!$timezone){
			$timezone = $this->getPostValue('booki_timezone_selection');
		}
		
		$shorthandDateFormat = $this->getPostValue('shorthandDateFormat');
		$enableEditors = $this->getBoolPostValue('enableEditors');
		$useDashboardHistoryPage = $this->getBoolPostValue('useDashboardHistoryPage');
		
		$cartPageUrl = $this->getPostValue('cartPageUrl');
		$billPageUrl = $this->getPostValue('billPageUrl');
		$payPalConfirmationPageUrl = $this->getPostValue('payPalConfirmationPageUrl');
		$payPalCancelPageUrl = $this->getPostValue('payPalCancelPageUrl');
		$itemDetailsPageUrl = $this->getPostValue('itemDetailsPageUrl');
		$historyPageUrl = $this->getPostValue('historyPageUrl');
		$statsPageUrl = $this->getPostValue('statsPageUrl');
		$currencyCode = $this->getPostValue('currencyCode');
		$currencySymbol = $this->getPostValue('currencySymbol');
		$highlightSelectedOptionals = $this->getBoolPostValue('highlightSelectedOptionals');
		$oneForm = $this->getBoolPostValue('oneForm');
		$noCache = $this->getBoolPostValue('noCache');
		
		$globalSettings = new Booki_SettingGlobal();
		$globalSettings->adminUserId = $adminUserId;
		$globalSettings->notificationEmailTo = $notificationEmailTo;
		$globalSettings->timeSelector = $timeSelector;
		$globalSettings->autoTimezoneDetection = $autoTimezoneDetection;
		$globalSettings->enableTimezoneEdit = $enableTimezoneEdit;
		$globalSettings->continueBookingUrl = $continueBookingUrl;
		$globalSettings->loginPageUrl = $loginPageUrl;
		$globalSettings->calendarFirstDay = $calendarFirstDay;
		$globalSettings->showCalendarButtonPanel = $showCalendarButtonPanel;
		$globalSettings->autoLoginAfterRegistration = $autoLoginAfterRegistration;
		$globalSettings->tax = $tax;
		$globalSettings->enablePayments = $enablePayments;
		$globalSettings->enableBookingWithAndWithoutPayment = $enableBookingWithAndWithoutPayment;
		$globalSettings->enableCoupons = $enableCoupons;
		$globalSettings->autoConfirmOrderAfterPayment = $autoConfirmOrderAfterPayment;
		$globalSettings->unpaidOrderExpiry = $unpaidOrderExpiry;
		$globalSettings->eventsLogExpiry = $eventsLogExpiry;
		$globalSettings->autoApproveBooking = $autoApproveBooking;
		$globalSettings->autoInvoiceNotification = $autoInvoiceNotification;
		$globalSettings->autoRefundNotification = $autoRefundNotification;
		$globalSettings->autoNotifyAdminNewBooking = $autoNotifyAdminNewBooking;
		$globalSettings->notifyBookingCancellation = $notifyBookingCancellation;
		$globalSettings->notifyBookingReceivedSuccessfully = $notifyBookingReceivedSuccessfully;
		$globalSettings->deletePayPalCancelledBooking = $deletePayPalCancelledBooking;
		$globalSettings->discount = $discount;
		$globalSettings->bookingMinimumDiscount = $bookingMinimumDiscount;
		$globalSettings->displayBookedTimeSlots = $displayBookedTimeSlots;
		
		$globalSettings->enableUserCancelBooking = $enableUserCancelBooking;
		$globalSettings->enableCartItemHeader = $enableCartItemHeader;
		$globalSettings->includeBookingPrice = $includeBookingPrice;
		$globalSettings->addToCart = $addToCart;
		$globalSettings->useCartSystem = $useCartSystem;
		$globalSettings->membershipRequired = $membershipRequired;
		
		$globalSettings->timezone = $timezone;
		$globalSettings->mailChimpKey = $mailChimpKey;
		$globalSettings->theme = $theme;
		$globalSettings->refBootstrapStyleSheet = $refBootstrapStyleSheet;
		$globalSettings->refBootstrapJS = $refBootstrapJS;
		
		$globalSettings->debugMode = $debugMode;
		$globalSettings->calendarTheme = $calendarTheme;
		$globalSettings->calendarFlatStyle = $calendarFlatStyle;
		$globalSettings->calendarBorderlessStyle = $calendarBorderlessStyle;
		
		$globalSettings->shorthandDateFormat = $shorthandDateFormat;
		$globalSettings->enableEditors = $enableEditors;
		$globalSettings->useDashboardHistoryPage = $useDashboardHistoryPage;
		
		$globalSettings->cartPageUrl = $cartPageUrl;
		$globalSettings->billPageUrl = $billPageUrl;
		$globalSettings->payPalConfirmationPageUrl = $payPalConfirmationPageUrl;
		$globalSettings->payPalCancelPageUrl = $payPalCancelPageUrl;
		$globalSettings->itemDetailsPageUrl = $itemDetailsPageUrl;
		$globalSettings->historyPageUrl = $historyPageUrl;
		$globalSettings->statsPageUrl = $statsPageUrl;
		$globalSettings->currencyCode = $currencyCode;
		$globalSettings->currencySymbol = $currencySymbol;
		$globalSettings->highlightSelectedOptionals = $highlightSelectedOptionals;
		$globalSettings->oneForm = $oneForm;
		$globalSettings->noCache = $noCache;
		
		$globalSettings->id = $id;

		return $globalSettings;
	}
}
?>