<?php
	require_once 'BaseController.php';
	require_once dirname(__FILE__) . '/../utils/BookingFormParser.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/SettingsGlobalRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/entities/CheckoutMode.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/CouponRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
	
	require_once  dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/repository/BookedDaysRepository.php';

	require_once dirname(__FILE__) . '/../../infrastructure/session/OrderLogger.php';
	require_once dirname(__FILE__) . '/../../infrastructure/session/Cart.php';
	require_once dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
	require_once dirname(__FILE__) . '/../../infrastructure/payment/gateways/PPGateway.php';
	require_once dirname(__FILE__) . '/../../infrastructure/emails/NotificationEmailer.php';
	require_once dirname(__FILE__) . '/../../infrastructure/emails/AgentsNotificationEmailer.php';

class Booki_BaseCartController extends Booki_BaseController{
	protected $projectId;
	protected $bookings;
	protected $cart;
	protected $errors = null;
	public function __construct(){
		$this->projectId = isset($_POST['projectid']) ? (int)$_POST['projectid'] : -1;
		$this->cart = new Booki_Cart();
		$this->bookings = $this->cart->getBookings();
	}
	
	public function redeemCoupon($callback){
		$couponCode = trim($this->getPostValue('booki_couponcode'));
		if($couponCode){
			$repo = new Booki_CouponRepository();
			$coupon = $repo->find($couponCode);
			$message = null;
			if($coupon){
				$totalAmount = $this->cart->getTotalAmount();
				if($this->bookings->coupon){
					$message = __('You can only apply 1 coupon at a time.', 'booki');
				}
				else if($totalAmount < $coupon->orderMinimum){
					$message = sprintf(__('Coupon is valid only on bookings that exceed %s.', 'booki'),  Booki_Helper::getCurrencySymbol() . Booki_Helper::toMoney($coupon->orderMinimum));
				}else if($coupon->isValid()){
					$flag = true;
					if($coupon->projectId !== -1){
						foreach($this->bookings as $booking){
							if($booking->projectId !== $coupon->projectId){
								$flag = false;
								break;
							}
						}
					}
					if(!$flag){
						$message = __('Coupon code is invalid and has been removed. You are trying to apply a coupon code that is not valid on one of the bookings in your cart. Provide a valid coupon for discount or proceed without.', 'booki');
					}else{
						$this->cart->setCoupon($coupon);
					}
				}else{
					$message = __('Coupon has expired and is not valid anymore. No discount applied.', 'booki');
				}
			}else{
				$message = __('Coupon is not valid. No discount applied.', 'booki');
			}
			$this->executeCallback($callback, array($message));
		}
	}
	
	public function cancelCoupon(){
		if($this->bookings->coupon){
			$this->cart->removeCoupon();
		}
	}
	
	public function addToCart($callback = null){
		$this->errors = array();
		if($this->projectId === -1 || $this->projectId === null){
			return;
		}
		if(!Booki_NonceHelper::isHuman($this->projectId)){
			return false;
		}
		$this->validateCart();
		$result = Booki_BookingFormParser::populateBookingFromPostData($this->projectId, $this->bookings);
		$this->bookings = $result['bookings'];
		$this->errors = $result['errors'];
		$globalSettings = Booki_Helper::globalSettings();
		if(count($this->errors) === 0 && !$globalSettings->addToCart){
			$url = Booki_Helper::appendReferrer(Booki_Helper::getUrl(Booki_PageNames::CART));
			wp_redirect($url);
		}
		add_filter('booki_custom_form_errors', array($this, 'customFormErrors'));
		do_action('booki_new_item_in_cart', null);

		$params = array($this->cart, $this->projectId, $this->errors);
		if($callback){
			$this->executeCallback($callback, $params);
		}
		return $params;
	}
	
	public function customFormErrors(){
		return $this->errors;
	}
	
	public function removeOptional($callback = null){
		if(!isset($_POST['booki_remove_optional'])){
			return;
		}
		$value = explode(':', $_POST['booki_remove_optional']);

		$bookingId = intval($value[0]);
		$optionalId = intval($value[1]);
		foreach($this->bookings as $booking){
			if($booking->id === $bookingId){
				foreach($booking->optionals as $optional){
					if ($optional->id === $optionalId){
						$booking->optionals->remove_item($optional);
						break 2;
					}
				}
			}
		}
		$this->executeCallback($callback, array($this->cart, $optionalId));
	}
	
	public function removeCascadingListItem($callback = null){
		if(!isset($_POST['booki_remove_cascadingitem'])){
			return;
		}
		$value = explode(':', $_POST['booki_remove_cascadingitem']);

		$bookingId = intval($value[0]);
		$cascadingItemId = intval($value[1]);
		foreach($this->bookings as $booking){
			if($booking->id === $bookingId){
				foreach($booking->cascadingItems as $cascadingItem){
					if ($cascadingItem->id === $cascadingItemId){
						$booking->cascadingItems->remove_item($cascadingItem);
						break 2;
					}
				}
			}
		}
		$this->executeCallback($callback, array($this->cart, $cascadingItemId));
	}
	
	public function removeDate($callback = null){
		if(!isset($_POST['booki_remove_date'])){
			return;
		}
		$temp = $value = explode(',', $_POST['booki_remove_date']);
		foreach($temp as $t){
			$value = explode(':', $t);
			$id = intval($value[0]);
			$dateValue = trim($value[1]);
			
			foreach($this->bookings as $booking){
				if($booking->id === $id){
					foreach($booking->dates as $key => $date){
						if($date === $dateValue){
							unset($booking->dates[$key]);
							break 2;
						}
					}
				}
			}
		}
		if($this->bookings->count() > 0){
			$this->executeCallback($callback, array($this->cart, $id));
		}
	}
	
	public function removeOrder($callback = null){
		if(!isset($_POST['booki_remove_order'])){
			return;
		}
		$id = intval($_POST['booki_remove_order']);
		foreach($this->bookings as $booking){
			if($booking->id === $id){
				$this->bookings->remove_item($booking);
				break;
			}
		}
		if($this->bookings->count() > 0){
			$this->executeCallback($callback, array($this->cart, $id));
		}
	}

	public function checkOut($callback = null, $cartCallback = null){
		$checkoutMode = (int)$this->getPostValue('booki_checkout');
		$message = null;
		$globalSettings = Booki_Helper::globalSettings();
		
		if(!$globalSettings->useCartSystem){
			if($this->projectId === -1 || $this->projectId === null){
				return;
			}
			if(!Booki_NonceHelper::isHuman($this->projectId)){
				return false;
			}
			$this->validateCart();
			$result = Booki_BookingFormParser::populateBookingFromPostData($this->projectId, $this->bookings);
			$this->bookings = $result['bookings'];
			$this->errors = $result['errors'];
			if(count($this->errors) > 0){
				add_filter('booki_custom_form_errors', array($this, 'customFormErrors'));
				do_action('booki_new_item_in_cart', null);
				$params = array($this->cart, $this->projectId, $this->errors);
				if($cartCallback){
					$this->executeCallback($cartCallback, $params);
				}
				return;
			}
		}
		
		if($this->bookings->count() === 0){
			//why are we still here ? perhaps a spam bot.
			return false;
		}
		
		$isValid = $this->validateDays();

		if(!$isValid){
			$this->executeCallback($callback, array($message));
			return;
		}
		
		//discounts & coupons
		$couponCode = trim($this->getPostValue('booki_couponcode'));
		$discount = $globalSettings->discount;
		$bookingMinimumDiscount = $globalSettings->bookingMinimumDiscount;
		$hasDiscount = ($discount > 0 && ($bookingMinimumDiscount == 0 || $this->bookings->count() >= $bookingMinimumDiscount));
		
		if($hasDiscount){
			$calendarRepository = new Booki_CalendarRepository();
			//do not allow discount if we have a deposit
			foreach($this->bookings as $booking){
				$calendar = $calendarRepository->readByProject($booking->projectId);
				if($calendar->deposit > 0){
					$hasDiscount = false;
					break;
				}
			}
		}
		$coupon = null;
		if($hasDiscount){
			$expirationDate = new Booki_DateTime();
			$coupon = new Booki_Coupon(array('discount'=>$discount, 'orderMinimum'=>$bookingMinimumDiscount, 'expirationDate'=>$expirationDate->format(BOOKI_DATEFORMAT)));
		}else if($couponCode){
			$repo = new Booki_CouponRepository();
			$coupon = $repo->find($couponCode);
			if(!$coupon || !$coupon->isValid()){
				$this->cancelCoupon();
				$message = __('Coupon code is invalid and has been removed. Provide a valid coupon for discount or proceed without.', 'booki');
				$this->executeCallback($callback, array($message));
				return;
			}else if($coupon->projectId !== -1){
				$flag = true;
				foreach($this->bookings as $booking){
					if($booking->projectId !== $coupon->projectId){
						$flag = false;
						break;
					}
				}
				if(!$flag){
					$this->cancelCoupon();
					$message = __('Coupon code is invalid and has been removed. You are trying to apply a coupon code that is not valid on one of the bookings in your cart. Provide a valid coupon for discount or proceed without.', 'booki');
					$this->executeCallback($callback, array($message));
					return;
				}
			}
		}
		
		if($checkoutMode === Booki_CheckoutMode::PAY_NOW){
			$paypalGateway = new Booki_PPGateway(null, $coupon);
			$paypalGateway->checkout();
			return;
		}
		
		$orderLogger = new Booki_OrderLogger();
		$order = $orderLogger->applyCouponAndLog($coupon);

		$message = __('We received your booking successfully. We shall contact you shortly. Thanks!', 'booki');
		if($globalSettings->autoInvoiceNotification && $order->userIsRegistered){
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::INVOICE, $order->id);
			$result = $notificationEmailer->send();
			$message = sprintf(__('We received your booking successfully. We tried sending you an invoice but apparently we are not able to 
			send emails at this time. To expedite contact us with your orderId {%d}. Thanks!', 'booki'), $order->id);
			if($result){
				++$order->invoiceNotification;
				Booki_BookingProvider::orderRepository()->update($order);
				$message = __('We received your booking successfully. An invoice has been emailed to you. Please check your inbox or your spam folder!', 'booki');
			}
		}
		
		if($globalSettings->autoApproveBooking){
			Booki_BookingProvider::approveOrderAndNotifyUser($order->id, $order->userIsRegistered, $orderLogger->unRegisteredUserInfo);
		}
		
		$this->executeCallback($callback, array($message, $order->id, $couponCode));
	}
	
	/**
		@description Redirects to either continue url set in the querystring or 
		the continueBookingUrl set in global settings if one exists.
		Otherwise it will just reload the current page, i.e. the cart page doing nothing.
	*/
	public function continueBooking(){
		$returnUrl = null;
		$repo =  new Booki_SettingsGlobalRepository();
		$setting = $repo->read();
		if($setting->continueBookingUrl){
			$returnUrl = $setting->continueBookingUrl ? $setting->continueBookingUrl : null;
		}else if(isset($_GET['booki_continue'])){
			$returnUrl = urldecode($_GET['booki_continue']);
		} 
		
		if($returnUrl){
			wp_redirect(Booki_Helper::appendScheme($returnUrl));
		}
	}
	
	protected function validateDays()
	{
		$projectRepository = new Booki_ProjectRepository();
		$bookedDaysRepository = new Booki_BookedDaysRepository();
		$calendarRepository = new Booki_CalendarRepository();
		$projectId = null;
		$project = null;
		$calendar = null;
		$bookedDays = false;
		$calendarExhausted = false;
		foreach($this->bookings as $booking){
			if($booking->projectId !== $projectId){ 
				$projectId = $booking->projectId;
				$project = $projectRepository->read($projectId);
				$calendar = $calendarRepository->readByProject($projectId);
			}
			
			if($calendar->exhausted()){
				$calendarExhausted = true;
				return;
			}
			
			if($project->bookingMode !== Booki_BookingMode::APPOINTMENT){
				continue;
			}
			
			if(!$bookedDays){
				$bookedDays = $bookedDaysRepository->readByDays($booking->dates, $projectId);
			}
			
			if(!$bookedDays){
				return;
			}
			
			foreach($booking->dates as $date){
				$currentDate = Booki_DateHelper::parseFormattedDateString($date);
				foreach($bookedDays as $bookedDay){
					$areEqual = Booki_DateHelper::daysAreEqual($bookedDay->bookingDate, $currentDate);
					if($areEqual){
						if($calendar->period === Booki_CalendarPeriod::BY_TIME && 
							($booking->hasTime() && $bookedDay->compareTime($booking))){
							return false;
						} else if ($calendar->period === Booki_CalendarPeriod::BY_DAY){
							return false;
						}
					}
				}
			}
		}
		if($calendarExhausted){
			return false;
		}
		return true;
	}
	
	protected function validateCart(){
		//cart rules: we currently allow
		//1. Only one item in cart if it has deposits, otherwise gets confusing and unnatural.
		$depositField = 'deposit_field';
		$deposit = isset($_POST[$depositField]) ? (double)$_POST[$depositField] : null;
		foreach($this->bookings as $booking){
			if($booking->deposit > 0 || ($deposit !== null && $deposit > 0)){
				$this->cart->clear();
				$this->cart = new Booki_Cart();
				$this->bookings = $this->cart->getBookings();
				break;
			}
		}
	}
}
?>