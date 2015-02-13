<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/ResxRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/Resx.php';

class Booki_ResxController extends Booki_BaseController{
	private $repo;
	private $id;
	public function __construct($createCallback, $updateCallback, $deleteCallback){
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_ResxRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$resx = $this->process();
		$result = $this->repo->insert($resx);
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$id = isset($_POST['booki_update']) ? intval($_POST['booki_update']) : null;
		$resx = $this->process($id);
		$result = $this->repo->update($resx);
		$this->executeCallback($callback, array($id));
	}
	
	public function delete($callback){
		$id = isset($_POST['booki_delete']) ? intval($_POST['booki_delete']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array());
	}
	
	protected function process($id = -1) {
		$resx = new Booki_Resx();
		$resx->id = $id;
		
		$resx->ORDER_ID_REF = $this->getStringPostValue('ORDER_ID_REF');
		$resx->VIEW_ORDER_HISTORY = $this->getStringPostValue('VIEW_ORDER_HISTORY');
		$resx->BOOKING_WIZARD_BOOKING_SNATCHED = $this->getStringPostValue('BOOKING_WIZARD_BOOKING_SNATCHED');
		$resx->BOOKING_WIZARD_REQUIRED_FIELD = $this->getStringPostValue('BOOKING_WIZARD_REQUIRED_FIELD');
		$resx->BOOKING_WIZARD_LAST_BOOKING_ADDED = $this->getStringPostValue('BOOKING_WIZARD_LAST_BOOKING_ADDED');
		$resx->CONGRATS = $this->getStringPostValue('CONGRATS');
		$resx->GOT_DISCOUNT = $this->getStringPostValue('GOT_DISCOUNT');
		$resx->BOOKING_WIZARD_PAY_ONLY_DEPOSIT = $this->getStringPostValue('BOOKING_WIZARD_PAY_ONLY_DEPOSIT');
		$resx->SUBTOTAL = $this->getStringPostValue('SUBTOTAL');
		$resx->DEPOSIT = $this->getStringPostValue('DEPOSIT');
		$resx->DISCOUNT = $this->getStringPostValue('DISCOUNT');
		$resx->TAX = $this->getStringPostValue('TAX');
		$resx->TOTAL = $this->getStringPostValue('TOTAL');
		$resx->BOOK_NOW = $this->getStringPostValue('BOOK_NOW');
		$resx->BOOK_NOW_PAY_LATER = $this->getStringPostValue('BOOK_NOW_PAY_LATER');
		$resx->BOOKING_WIZARD_CREATE_BOOKING = $this->getStringPostValue('BOOKING_WIZARD_CREATE_BOOKING');
		$resx->BOOKING_WIZARD_CHECK_OUT = $this->getStringPostValue('BOOKING_WIZARD_CHECK_OUT');
		$resx->HISTORY = $this->getStringPostValue('HISTORY');
		$resx->BOOKING_FOR = $this->getStringPostValue('BOOKING_FOR');
		$resx->DAYS_BOOKED = $this->getStringPostValue('DAYS_BOOKED');
		$resx->EXTRAS = $this->getStringPostValue('EXTRAS');
		$resx->DISCOUNT_BY_PERCENTAGE = $this->getStringPostValue('DISCOUNT_BY_PERCENTAGE');
		$resx->COUPON = $this->getStringPostValue('COUPON');
		$resx->PROMOTIONS = $this->getStringPostValue('PROMOTIONS');
		$resx->FROM = $this->getStringPostValue('FROM');
		$resx->TO = $this->getStringPostValue('TO');
		
		$resx->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT = $this->getStringPostValue('CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT');
		$resx->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT = $this->getStringPostValue('CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT');
		$resx->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY = $this->getStringPostValue('CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY');
		$resx->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE = $this->getStringPostValue('CHECKOUT_GRID_BOOKING_NOT_AVAILABLE');
		$resx->CHECKOUT_GRID_CHECKOUT_CART_HEADING = $this->getStringPostValue('CHECKOUT_GRID_CHECKOUT_CART_HEADING');
		$resx->CHECKOUT_GRID_BOOKINGS_EXHAUSTED = $this->getStringPostValue('CHECKOUT_GRID_BOOKINGS_EXHAUSTED');
		$resx->CHECKOUT_GRID_COST = $this->getStringPostValue('CHECKOUT_GRID_COST');
		$resx->CHECKOUT_GRID_BOOKING_DATE = $this->getStringPostValue('CHECKOUT_GRID_BOOKING_DATE');
		$resx->CHECKOUT_GRID_BOOKING_TIME = $this->getStringPostValue('CHECKOUT_GRID_BOOKING_TIME');
		$resx->CHECKOUT_GRID_TIMEZONE_OFFSET = $this->getStringPostValue('CHECKOUT_GRID_TIMEZONE_OFFSET');
		$resx->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW = $this->getStringPostValue('CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW');
		$resx->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL = $this->getStringPostValue('CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL');
		$resx->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT = $this->getStringPostValue('CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT');
		$resx->CHECKOUT_GRID_ENTER_COUPON_CODE = $this->getStringPostValue('CHECKOUT_GRID_ENTER_COUPON_CODE');
		$resx->CHECKOUT_GRID_REDEEM = $this->getStringPostValue('CHECKOUT_GRID_REDEEM');
		$resx->CHECKOUT_GRID_REMOVE_COUPON = $this->getStringPostValue('CHECKOUT_GRID_REMOVE_COUPON');
		$resx->CHECKOUT_GRID_COUPON_HELP = $this->getStringPostValue('CHECKOUT_GRID_COUPON_HELP');
		$resx->CHECKOUT_GRID_HOW_COUPONS_WORK = $this->getStringPostValue('CHECKOUT_GRID_HOW_COUPONS_WORK');
		$resx->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP = $this->getStringPostValue('CHECKOUT_GRID_ENTER_COUPON_CODE_HELP');
		$resx->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP = $this->getStringPostValue('CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP');
		$resx->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP = $this->getStringPostValue('CHECKOUT_GRID_ENTER_COUPON_VALID_HELP');
		$resx->CHECKOUT_GRID_PAYMENT_AUTHORIZED = $this->getStringPostValue('CHECKOUT_GRID_PAYMENT_AUTHORIZED');
		$resx->CHECKOUT_GRID_EMPTY_CART = $this->getStringPostValue('CHECKOUT_GRID_EMPTY_CART');
		$resx->CHECKOUT_GRID_BOOK_MORE = $this->getStringPostValue('CHECKOUT_GRID_BOOK_MORE');
		$resx->CHECKOUT_GRID_PROCEED = $this->getStringPostValue('CHECKOUT_GRID_PROCEED');
		$resx->CHECKOUT_GRID_CONFIRM_AND_PAY = $this->getStringPostValue('CHECKOUT_GRID_CONFIRM_AND_PAY');

		return $resx;
	}
	
	protected function getStringPostValue($value){
		return stripcslashes((string)$this->getPostValue($value));
	}
}
?>