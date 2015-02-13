<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';

class Booki_Resx extends Booki_EntityBase{
	public $id = -1;
	public $ORDER_ID_REF;
	public $VIEW_ORDER_HISTORY;
	public $BOOKING_WIZARD_BOOKING_SNATCHED;
	public $BOOKING_WIZARD_REQUIRED_FIELD;
	public $BOOKING_WIZARD_LAST_BOOKING_ADDED;
	public $CONGRATS;
	public $GOT_DISCOUNT;
	public $BOOKING_WIZARD_PAY_ONLY_DEPOSIT;
	public $SUBTOTAL;
	public $DEPOSIT;
	public $DISCOUNT;
	public $TAX;
	public $TOTAL;
	public $BOOK_NOW;
	public $BOOK_NOW_PAY_LATER;
	public $BOOKING_WIZARD_CREATE_BOOKING;
	public $BOOKING_WIZARD_CHECK_OUT;
	public $HISTORY;
	
	public $CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT;
	public $CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT;
	public $CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY;
	public $CHECKOUT_GRID_BOOKING_NOT_AVAILABLE;
	public $CHECKOUT_GRID_CHECKOUT_CART_HEADING;
	public $CHECKOUT_GRID_BOOKINGS_EXHAUSTED;
	public $CHECKOUT_GRID_COST;
	public $CHECKOUT_GRID_BOOKING_DATE;
	public $CHECKOUT_GRID_BOOKING_TIME;
	public $CHECKOUT_GRID_TIMEZONE_OFFSET;
	public $CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW;
	public $CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL;
	public $CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT;
	public $CHECKOUT_GRID_ENTER_COUPON_CODE;
	public $CHECKOUT_GRID_REDEEM;
	public $CHECKOUT_GRID_REMOVE_COUPON;
	public $CHECKOUT_GRID_COUPON_HELP;
	public $CHECKOUT_GRID_HOW_COUPONS_WORK;
	public $CHECKOUT_GRID_ENTER_COUPON_CODE_HELP;
	public $CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP;
	public $CHECKOUT_GRID_ENTER_COUPON_VALID_HELP;
	public $CHECKOUT_GRID_PAYMENT_AUTHORIZED;
	public $CHECKOUT_GRID_EMPTY_CART;
	public $CHECKOUT_GRID_BOOK_MORE;
	public $CHECKOUT_GRID_PROCEED;
	public $CHECKOUT_GRID_CONFIRM_AND_PAY;
	public $BOOKING_FOR;
	public $DAYS_BOOKED;
	public $EXTRAS;
	public $DISCOUNT_BY_PERCENTAGE;
	public $COUPON;
	public $PROMOTIONS;
	public $FROM;
	public $TO;
	//localized
	public $ORDER_ID_REF_LOC;
	public $VIEW_ORDER_HISTORY_LOC;
	public $SUBTOTAL_LOC;
	public $DEPOSIT_LOC;
	public $DISCOUNT_LOC;
	public $TAX_LOC;
	public $TOTAL_LOC;
	public $HISTORY_LOC;
	public $CONGRATS_LOC;
	public $GOT_DISCOUNT_LOC;
	public $BOOK_NOW_LOC;
	public $BOOK_NOW_PAY_LATER_LOC;
	public $BOOKING_FOR_LOC;
	public $DAYS_BOOKED_LOC;
	public $EXTRAS_LOC;
	public $DISCOUNT_BY_PERCENTAGE_LOC;
	public $COUPON_LOC;
	public $PROMOTIONS_LOC;
	public $FROM_LOC;
	public $TO_LOC;
	
	public $BOOKING_WIZARD_BOOKING_SNATCHED_LOC;
	public $BOOKING_WIZARD_REQUIRED_FIELD_LOC;
	public $BOOKING_WIZARD_LAST_BOOKING_ADDED_LOC;
	public $BOOKING_WIZARD_PAY_ONLY_DEPOSIT_LOC;
	public $BOOKING_WIZARD_CREATE_BOOKING_LOC;
	public $BOOKING_WIZARD_CHECK_OUT_LOC;
	
	//checkoutgrid.php
	public $CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT_LOC;
	public $CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT_LOC;
	public $CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY_LOC;
	public $CHECKOUT_GRID_BOOKING_NOT_AVAILABLE_LOC;
	public $CHECKOUT_GRID_CHECKOUT_CART_HEADING_LOC;
	public $CHECKOUT_GRID_BOOKINGS_EXHAUSTED_LOC;
	public $CHECKOUT_GRID_COST_LOC;
	public $CHECKOUT_GRID_BOOKING_DATE_LOC;
	public $CHECKOUT_GRID_BOOKING_TIME_LOC;
	public $CHECKOUT_GRID_TIMEZONE_OFFSET_LOC;
	public $CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW_LOC;
	public $CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL_LOC;
	public $CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT_LOC;
	public $CHECKOUT_GRID_ENTER_COUPON_CODE_LOC;
	public $CHECKOUT_GRID_REDEEM_LOC;
	public $CHECKOUT_GRID_REMOVE_COUPON_LOC;
	public $CHECKOUT_GRID_COUPON_HELP_LOC;
	public $CHECKOUT_GRID_HOW_COUPONS_WORK_LOC;
	public $CHECKOUT_GRID_ENTER_COUPON_CODE_HELP_LOC;
	public $CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP_LOC;
	public $CHECKOUT_GRID_ENTER_COUPON_VALID_HELP_LOC;
	public $CHECKOUT_GRID_PAYMENT_AUTHORIZED_LOC;
	public $CHECKOUT_GRID_EMPTY_CART_LOC;
	public $CHECKOUT_GRID_BOOK_MORE_LOC;
	public $CHECKOUT_GRID_PROCEED_LOC;
	public $CHECKOUT_GRID_CONFIRM_AND_PAY_LOC;
	
	public function __construct(){
		$this->ORDER_ID_REF =  __('For your reference, your order id is #%s.', 'booki');
		$this->VIEW_ORDER_HISTORY = __('You can view full details of your order at anytime in your order %s page.');
		$this->SUBTOTAL = __('Subtotal','booki');
		$this->DEPOSIT = __('Deposit','booki');
		$this->DISCOUNT = __('Discount','booki');
		$this->TAX = __('Tax', 'booki');
		$this->TOTAL = __('Total','booki');
		$this->HISTORY = __('history', 'booki');
		$this->CONGRATS = __('Congrats!', 'booki');
		$this->GOT_DISCOUNT = __('You got a %d%% discount!');
		$this->BOOK_NOW = __('Book now', 'booki');
		$this->BOOK_NOW_PAY_LATER = __('Book now, pay later', 'booki');
		$this->BOOKING_FOR = __('Booking for', 'booki');
		$this->DAYS_BOOKED = __('Days Booked', 'booki');
		$this->EXTRAS = __('Extras', 'booki');
		$this->DISCOUNT_BY_PERCENTAGE = __('Discount %s', 'booki');
		$this->COUPON = __('Coupon', 'booki');
		$this->PROMOTIONS = __('Promotions', 'booki');
		$this->FROM = __('from', 'booki');
		$this->TO = __('to', 'booki');
		
		$this->BOOKING_WIZARD_BOOKING_SNATCHED = __('Some bookings are not available anymore and cannot be booked.', 'booki');
		$this->BOOKING_WIZARD_REQUIRED_FIELD = __('Please fill out all of the required fields and ensure there are no validation errors.', 'booki');
		$this->BOOKING_WIZARD_LAST_BOOKING_ADDED = __('You have added the last availabe booking to cart. Checkout ?', 'booki');
		$this->BOOKING_WIZARD_PAY_ONLY_DEPOSIT = __('You only need to pay a deposit to confirm your booking. The remainder needs to be paid on arrival.', 'booki');
		$this->BOOKING_WIZARD_CREATE_BOOKING = __('Create booking', 'booki');
		$this->BOOKING_WIZARD_CHECK_OUT = __('Check out', 'booki');
		
		//checkoutgrid.php
		$this->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT = __('There was a problem processing your payment [%s]. Please try again, if problem persists contact support. We apologize for any inconvenience.', 'booki');
		$this->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT = __('Your payment has been processed successfully.', 'booki');
		$this->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY = __('Your booking will be confirmed shortly.', 'booki');
		$this->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE = __('Some bookings are not available anymore and have been removed from cart.', 'booki');
		$this->CHECKOUT_GRID_CHECKOUT_CART_HEADING = __('Bookings', 'booki');
		$this->CHECKOUT_GRID_BOOKINGS_EXHAUSTED = __('Bookings are exhausted.', 'booki');
		$this->CHECKOUT_GRID_COST =__('Cost', 'booki');
		$this->CHECKOUT_GRID_BOOKING_DATE = __('Booking date', 'booki');
		$this->CHECKOUT_GRID_BOOKING_TIME = __('Booking time', 'booki');
		$this->CHECKOUT_GRID_TIMEZONE_OFFSET = __('Timezone offset', 'booki');
		$this->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW = __('Deposit required now', 'booki');
		$this->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL = __('Amount due on arrival', 'booki');
		$this->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT = __('There are no items to checkout.', 'booki');
		$this->CHECKOUT_GRID_ENTER_COUPON_CODE = __('Enter Coupon Code', 'booki');
		$this->CHECKOUT_GRID_REDEEM = __('Redeem', 'booki');
		$this->CHECKOUT_GRID_REMOVE_COUPON = __('Remove coupon', 'booki');
		$this->CHECKOUT_GRID_COUPON_HELP = __('Help', 'booki');
		$this->CHECKOUT_GRID_HOW_COUPONS_WORK = __('How coupons work', 'booki');
		$this->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP = __('Enter your coupon code. To update your cart, click on redeem.', 'booki');
		$this->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP = __('If you require a refund on an order placed using a coupon, the value of the coupon will not be refunded.', 'booki');
		$this->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP = __('The coupons are valid for a limited period of time.', 'booki');
		$this->CHECKOUT_GRID_PAYMENT_AUTHORIZED =  __('We are authorized to proceed with payment. Please review the above booking and confirm payment.', 'booki');
		$this->CHECKOUT_GRID_EMPTY_CART = __('Empty cart', 'booki');
		$this->CHECKOUT_GRID_BOOK_MORE = __('Book more', 'booki');
		$this->CHECKOUT_GRID_PROCEED = __('Proceed', 'booki');
		$this->CHECKOUT_GRID_CONFIRM_AND_PAY = __('Confirm and pay', 'booki');
		
		$this->init();
	}
	
	public function init(){
		$this->ORDER_ID_REF_LOC =  Booki_WPMLHelper::t('order_id_ref', $this->ORDER_ID_REF);
		$this->VIEW_ORDER_HISTORY_LOC = Booki_WPMLHelper::t('view_order_history', $this->VIEW_ORDER_HISTORY);
		$this->SUBTOTAL_LOC = Booki_WPMLHelper::t('subtotal', $this->SUBTOTAL);
		$this->DEPOSIT_LOC = Booki_WPMLHelper::t('deposit', $this->DEPOSIT);
		$this->DISCOUNT_LOC = Booki_WPMLHelper::t('discount', $this->DISCOUNT);
		$this->TAX_LOC = Booki_WPMLHelper::t('tax', $this->TAX);
		$this->TOTAL_LOC = Booki_WPMLHelper::t('total', $this->TOTAL);
		$this->HISTORY_LOC = Booki_WPMLHelper::t('history', $this->HISTORY);
		$this->CONGRATS_LOC = Booki_WPMLHelper::t('congrats', $this->CONGRATS);
		$this->GOT_DISCOUNT_LOC = Booki_WPMLHelper::t('got_discount', $this->GOT_DISCOUNT);
		$this->BOOK_NOW_LOC = Booki_WPMLHelper::t('book_now', $this->BOOK_NOW);
		$this->BOOK_NOW_PAY_LATER_LOC = Booki_WPMLHelper::t('book_now_pay_later', $this->BOOK_NOW_PAY_LATER);
		$this->BOOKING_FOR_LOC = Booki_WPMLHelper::t('booking_for', $this->BOOKING_FOR);
		$this->DAYS_BOOKED_LOC = Booki_WPMLHelper::t('days_booked', $this->DAYS_BOOKED);
		$this->EXTRAS_LOC = Booki_WPMLHelper::t('extras', $this->EXTRAS);
		$this->DISCOUNT_BY_PERCENTAGE_LOC = Booki_WPMLHelper::t('discount_by_percentage', $this->DISCOUNT_BY_PERCENTAGE);
		$this->COUPON_LOC = Booki_WPMLHelper::t('coupon', $this->COUPON);
		$this->PROMOTIONS_LOC = Booki_WPMLHelper::t('promotions', $this->PROMOTIONS);
		$this->FROM_LOC = Booki_WPMLHelper::t('from', $this->FROM);
		$this->TO_LOC = Booki_WPMLHelper::t('to', $this->TO);
		
		$this->BOOKING_WIZARD_BOOKING_SNATCHED_LOC = Booki_WPMLHelper::t('booking_wizard_booking_snatched', $this->BOOKING_WIZARD_BOOKING_SNATCHED);
		$this->BOOKING_WIZARD_REQUIRED_FIELD_LOC = Booki_WPMLHelper::t('booking_wizard_required_field', $this->BOOKING_WIZARD_REQUIRED_FIELD);
		$this->BOOKING_WIZARD_LAST_BOOKING_ADDED_LOC = Booki_WPMLHelper::t('booking_wizard_last_booking_added', $this->BOOKING_WIZARD_LAST_BOOKING_ADDED);
		$this->BOOKING_WIZARD_PAY_ONLY_DEPOSIT_LOC = Booki_WPMLHelper::t('booking_wizard_pay_only_deposit', $this->BOOKING_WIZARD_PAY_ONLY_DEPOSIT);
		$this->BOOKING_WIZARD_CREATE_BOOKING_LOC = Booki_WPMLHelper::t('booking_wizard_create_booking', $this->BOOKING_WIZARD_CREATE_BOOKING);
		$this->BOOKING_WIZARD_CHECK_OUT_LOC = Booki_WPMLHelper::t('booking_wizard_check_out', $this->BOOKING_WIZARD_CHECK_OUT);
		
		//checkoutgrid.php
		$this->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT_LOC = Booki_WPMLHelper::t('checkout_grid_problem_processing_payment',$this->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT);
		$this->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT_LOC = Booki_WPMLHelper::t('checkout_grid_success_processing_payment', $this->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT);
		$this->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY_LOC = Booki_WPMLHelper::t('checkout_grid_booking_confirm_shortly', $this->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY);
		$this->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE_LOC = Booki_WPMLHelper::t('checkout_grid_booking_not_available', $this->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE);
		$this->CHECKOUT_GRID_CHECKOUT_CART_HEADING_LOC = Booki_WPMLHelper::t('checkout_grid_checkout_cart_heading', $this->CHECKOUT_GRID_CHECKOUT_CART_HEADING);
		$this->CHECKOUT_GRID_BOOKINGS_EXHAUSTED_LOC = Booki_WPMLHelper::t('checkout_grid_bookings_exhausted', $this->CHECKOUT_GRID_BOOKINGS_EXHAUSTED);
		$this->CHECKOUT_GRID_COST_LOC = Booki_WPMLHelper::t('checkout_grid_cost', $this->CHECKOUT_GRID_COST);
		$this->CHECKOUT_GRID_BOOKING_DATE_LOC = Booki_WPMLHelper::t('checkout_grid_booking_date', $this->CHECKOUT_GRID_BOOKING_DATE);
		$this->CHECKOUT_GRID_BOOKING_TIME_LOC = Booki_WPMLHelper::t('checkout_grid_booking_time', $this->CHECKOUT_GRID_BOOKING_TIME);
		$this->CHECKOUT_GRID_TIMEZONE_OFFSET_LOC = Booki_WPMLHelper::t('checkout_grid_timezone_offset', $this->CHECKOUT_GRID_TIMEZONE_OFFSET);
		$this->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW_LOC = Booki_WPMLHelper::t('checkout_grid_deposit_required_now',  $this->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW);
		$this->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL_LOC = Booki_WPMLHelper::t('checkout_grid_amount_due_on_arrival',  $this->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL);
		$this->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT_LOC = Booki_WPMLHelper::t('checkout_grid_no_items_to_checkout',  $this->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT);
		$this->CHECKOUT_GRID_ENTER_COUPON_CODE_LOC = Booki_WPMLHelper::t('checkout_grid_enter_coupon_code',  $this->CHECKOUT_GRID_ENTER_COUPON_CODE);
		$this->CHECKOUT_GRID_REDEEM_LOC = Booki_WPMLHelper::t('checkout_grid_redeem', $this->CHECKOUT_GRID_REDEEM);
		$this->CHECKOUT_GRID_REMOVE_COUPON_LOC = Booki_WPMLHelper::t('checkout_grid_remove_coupon', $this->CHECKOUT_GRID_REMOVE_COUPON);
		$this->CHECKOUT_GRID_COUPON_HELP_LOC = Booki_WPMLHelper::t('checkout_grid_coupon_help', $this->CHECKOUT_GRID_COUPON_HELP);
		$this->CHECKOUT_GRID_HOW_COUPONS_WORK_LOC = Booki_WPMLHelper::t('checkout_grid_how_coupons_work', $this->CHECKOUT_GRID_HOW_COUPONS_WORK);
		$this->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP_LOC = Booki_WPMLHelper::t('checkout_grid_enter_coupon_code_help', $this->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP);
		$this->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP_LOC = Booki_WPMLHelper::t('checkout_grid_enter_coupon_refund_help', $this->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP);
		$this->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP_LOC = Booki_WPMLHelper::t('checkout_grid_enter_coupon_valid_help', $this->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP);
		$this->CHECKOUT_GRID_PAYMENT_AUTHORIZED_LOC = Booki_WPMLHelper::t('checkout_grid_payment_authorized', $this->CHECKOUT_GRID_PAYMENT_AUTHORIZED);
		$this->CHECKOUT_GRID_EMPTY_CART_LOC = Booki_WPMLHelper::t('checkout_grid_empty_cart', $this->CHECKOUT_GRID_EMPTY_CART);
		$this->CHECKOUT_GRID_BOOK_MORE_LOC = Booki_WPMLHelper::t('checkout_grid_book_more', $this->CHECKOUT_GRID_BOOK_MORE);
		$this->CHECKOUT_GRID_PROCEED_LOC = Booki_WPMLHelper::t('checkout_grid_proceed', $this->CHECKOUT_GRID_PROCEED);
		$this->CHECKOUT_GRID_CONFIRM_AND_PAY_LOC = Booki_WPMLHelper::t('checkout_grid_confirm_and_pay', $this->CHECKOUT_GRID_CONFIRM_AND_PAY);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('order_id_ref', $this->ORDER_ID_REF);
		Booki_WPMLHelper::register('view_order_history', $this->VIEW_ORDER_HISTORY);
		Booki_WPMLHelper::register('history', $this->HISTORY);
		Booki_WPMLHelper::register('subtotal', $this->SUBTOTAL);
		Booki_WPMLHelper::register('deposit', $this->DEPOSIT);
		Booki_WPMLHelper::register('discount', $this->DISCOUNT);
		Booki_WPMLHelper::register('tax', $this->TAX);
		Booki_WPMLHelper::register('total', $this->TOTAL);
		Booki_WPMLHelper::register('congrats', $this->CONGRATS);
		Booki_WPMLHelper::register('got_discount', $this->GOT_DISCOUNT);
		Booki_WPMLHelper::register('book_now', $this->BOOK_NOW);
		Booki_WPMLHelper::register('book_now_pay_later', $this->BOOK_NOW_PAY_LATER);
		Booki_WPMLHelper::register('booking_for', $this->BOOKING_FOR);
		Booki_WPMLHelper::register('days_booked', $this->DAYS_BOOKED);
		Booki_WPMLHelper::register('extras', $this->EXTRAS);
		Booki_WPMLHelper::register('discount_by_percentage', $this->DISCOUNT_BY_PERCENTAGE);
		Booki_WPMLHelper::register('coupon', $this->COUPON);
		Booki_WPMLHelper::register('promotions', $this->PROMOTIONS);
		Booki_WPMLHelper::register('from', $this->FROM);
		Booki_WPMLHelper::register('to', $this->TO);
		
		Booki_WPMLHelper::register('booking_wizard_booking_snatched', $this->BOOKING_WIZARD_BOOKING_SNATCHED);
		Booki_WPMLHelper::register('booking_wizard_required_field', $this->BOOKING_WIZARD_REQUIRED_FIELD);
		Booki_WPMLHelper::register('booking_wizard_last_booking_added', $this->BOOKING_WIZARD_LAST_BOOKING_ADDED);

		Booki_WPMLHelper::register('booking_wizard_pay_only_deposit',  $this->BOOKING_WIZARD_PAY_ONLY_DEPOSIT);
		Booki_WPMLHelper::register('booking_wizard_create_booking', $this->BOOKING_WIZARD_CREATE_BOOKING);
		Booki_WPMLHelper::register('booking_wizard_check_out', $this->BOOKING_WIZARD_CHECK_OUT);
		
		
		Booki_WPMLHelper::register('checkout_grid_problem_processing_payment', $this->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT);
		Booki_WPMLHelper::register('checkout_grid_success_processing_payment', $this->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT);
		Booki_WPMLHelper::register('checkout_grid_booking_confirm_shortly', $this->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY);
		Booki_WPMLHelper::register('checkout_grid_booking_not_available', $this->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE);
		Booki_WPMLHelper::register('checkout_grid_checkout_cart_heading', $this->CHECKOUT_GRID_CHECKOUT_CART_HEADING);
		Booki_WPMLHelper::register('checkout_grid_bookings_exhausted', $this->CHECKOUT_GRID_BOOKINGS_EXHAUSTED);
		Booki_WPMLHelper::register('checkout_grid_cost', $this->CHECKOUT_GRID_COST);
		Booki_WPMLHelper::register('checkout_grid_booking_date', $this->CHECKOUT_GRID_BOOKING_DATE);
		Booki_WPMLHelper::register('checkout_grid_booking_time', $this->CHECKOUT_GRID_BOOKING_TIME);
		Booki_WPMLHelper::register('checkout_grid_timezone_offset', $this->CHECKOUT_GRID_TIMEZONE_OFFSET);
		Booki_WPMLHelper::register('checkout_grid_deposit_required_now', $this->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW);
		Booki_WPMLHelper::register('checkout_grid_amount_due_on_arrival', $this->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL);
		Booki_WPMLHelper::register('checkout_grid_no_items_to_checkout', $this->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT);
		Booki_WPMLHelper::register('checkout_grid_enter_coupon_code', $this->CHECKOUT_GRID_ENTER_COUPON_CODE);
		Booki_WPMLHelper::register('checkout_grid_redeem', $this->CHECKOUT_GRID_REDEEM);
		Booki_WPMLHelper::register('checkout_grid_remove_coupon', $this->CHECKOUT_GRID_REMOVE_COUPON);
		Booki_WPMLHelper::register('checkout_grid_coupon_help', $this->CHECKOUT_GRID_COUPON_HELP);
		Booki_WPMLHelper::register('checkout_grid_how_coupons_work', $this->CHECKOUT_GRID_HOW_COUPONS_WORK);
		Booki_WPMLHelper::register('checkout_grid_enter_coupon_code_help', $this->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP);
		Booki_WPMLHelper::register('checkout_grid_enter_coupon_refund_help', $this->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP);
		Booki_WPMLHelper::register('checkout_grid_enter_coupon_valid_help', $this->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP);
		Booki_WPMLHelper::register('checkout_grid_payment_authorized', $this->CHECKOUT_GRID_PAYMENT_AUTHORIZED);
		Booki_WPMLHelper::register('checkout_grid_empty_cart', $this->CHECKOUT_GRID_EMPTY_CART);
		Booki_WPMLHelper::register('checkout_grid_book_more', $this->CHECKOUT_GRID_BOOK_MORE);
		Booki_WPMLHelper::register('checkout_grid_proceed', $this->CHECKOUT_GRID_PROCEED);
		Booki_WPMLHelper::register('checkout_grid_confirm_and_pay', $this->CHECKOUT_GRID_CONFIRM_AND_PAY);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('order_id_ref');
		Booki_WPMLHelper::unregister('view_order_history');
		Booki_WPMLHelper::unregister('subtotal');
		Booki_WPMLHelper::unregister('deposit');
		Booki_WPMLHelper::unregister('discount');
		Booki_WPMLHelper::unregister('tax');
		Booki_WPMLHelper::unregister('total');
		Booki_WPMLHelper::unregister('history');
		Booki_WPMLHelper::unregister('congrats');
		Booki_WPMLHelper::unregister('got_discount');
		Booki_WPMLHelper::unregister('book_now');
		Booki_WPMLHelper::unregister('book_now_pay_later');
		Booki_WPMLHelper::unregister('booking_for');
		Booki_WPMLHelper::unregister('days_booked');
		Booki_WPMLHelper::unregister('extras');
		Booki_WPMLHelper::unregister('discount_by_percentage');
		Booki_WPMLHelper::unregister('coupon');
		Booki_WPMLHelper::unregister('promotions');
		Booki_WPMLHelper::unregister('from');
		Booki_WPMLHelper::unregister('to');
		
		Booki_WPMLHelper::unregister('booking_wizard_booking_snatched');
		Booki_WPMLHelper::unregister('booking_wizard_required_field');
		Booki_WPMLHelper::unregister('booking_wizard_last_booking_added');

		Booki_WPMLHelper::unregister('booking_wizard_pay_only_deposit');
		Booki_WPMLHelper::unregister('booking_wizard_create_booking');
		Booki_WPMLHelper::unregister('booking_wizard_check_out');
		
		Booki_WPMLHelper::unregister('checkout_grid_problem_processing_payment');
		Booki_WPMLHelper::unregister('checkout_grid_success_processing_payment');
		Booki_WPMLHelper::unregister('checkout_grid_booking_confirm_shortly');
		Booki_WPMLHelper::unregister('checkout_grid_booking_not_available');
		Booki_WPMLHelper::unregister('checkout_grid_checkout_cart_heading');
		Booki_WPMLHelper::unregister('checkout_grid_bookings_exhausted');
		Booki_WPMLHelper::unregister('checkout_grid_cost');
		Booki_WPMLHelper::unregister('checkout_grid_booking_date');
		Booki_WPMLHelper::unregister('checkout_grid_booking_time');
		Booki_WPMLHelper::unregister('checkout_grid_timezone_offset');
		Booki_WPMLHelper::unregister('checkout_grid_deposit_required_now');
		Booki_WPMLHelper::unregister('checkout_grid_amount_due_on_arrival');
		Booki_WPMLHelper::unregister('checkout_grid_no_items_to_checkout');
		Booki_WPMLHelper::unregister('checkout_grid_enter_coupon_code');
		Booki_WPMLHelper::unregister('checkout_grid_redeem');
		Booki_WPMLHelper::unregister('checkout_grid_remove_coupon');
		Booki_WPMLHelper::unregister('checkout_grid_coupon_help');
		Booki_WPMLHelper::unregister('checkout_grid_how_coupons_work');
		Booki_WPMLHelper::unregister('checkout_grid_enter_coupon_code_help');
		Booki_WPMLHelper::unregister('checkout_grid_enter_coupon_refund_help');
		Booki_WPMLHelper::unregister('checkout_grid_enter_coupon_valid_help');
		Booki_WPMLHelper::unregister('checkout_grid_payment_authorized');
		Booki_WPMLHelper::unregister('checkout_grid_empty_cart');
		Booki_WPMLHelper::unregister('checkout_grid_book_more');
		Booki_WPMLHelper::unregister('checkout_grid_proceed');
		Booki_WPMLHelper::unregister('checkout_grid_confirm_and_pay');
	}
}
?>