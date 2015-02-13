<?php
require_once dirname(__FILE__) . '/Cart.php';
require_once dirname(__FILE__) . '/../utils/Helper.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/Order.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedDay.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedOptional.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedFormElement.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedDays.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedOptionals.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedCascadingItem.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/BookedFormElements.php';
require_once dirname(__FILE__) . '/../../domainmodel/entities/PaymentStatus.php';
require_once dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
require_once dirname(__FILE__) . '/../../domainmodel/repository/CouponRepository.php';
require_once dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
require_once dirname(__FILE__) . '/../ui/OrderSummary.php';

class Booki_OrderLogger{
	private $globalSettings;
	public $orderSummary;
	public $bookings;
	public $order;
	public $userId;
	public $coupon;
	public $unRegisteredUserInfo;
	private $userIsRegistered = true;
	public function __construct(Booki_Bookings $bookings = null, $userId = null, $coupon = null){

		$this->globalSettings = Booki_Helper::globalSettings();
		$localeInfo = Booki_Helper::getLocaleInfo();
		
		if(!$bookings){
			$cart = new Booki_Cart();
			$bookings = $cart->getBookings();
		}
		
		$this->coupon = $coupon ? $coupon : $bookings->coupon;
		$this->orderSummary = new Booki_OrderSummary($bookings, $localeInfo['currency'], $localeInfo['currencySymbol'], $bookings->timezone);
		$this->bookings = $this->orderSummary->bookings;
		if($userId !== null){
			$this->userId = $userId;
		}else if(is_user_logged_in()){
			$this->userId = get_current_user_id();
		} else {
			$this->userId = $this->globalSettings->adminUserId;
			$this->userIsRegistered = false;
		}
		
		$this->init();
	}
	
	protected function init(){

		$this->order = new Booki_Order(array(
			'orderDate'=>new Booki_DateTime()
			, 'userId'=>$this->userId
			, 'status'=>Booki_PaymentStatus::UNPAID
			, 'totalAmount'=>$this->orderSummary->totalAmount
			, 'isRegistered'=>$this->userIsRegistered
			, 'timezone'=>$this->orderSummary->timezoneString
			, 'tax'=>$this->globalSettings->tax
		));

		$status = (!$this->globalSettings->enablePayments && $this->globalSettings->autoApproveBooking)
						? Booki_BookingStatus::APPROVED : Booki_BookingStatus::PENDING_APPROVAL;
		//by default admin is owner
		$handlerUserId = $this->globalSettings->adminUserId;
		$projectId = null;
		$calendarRepository =  new Booki_CalendarRepository();
		foreach($this->bookings as $booking){
			if($projectId !== $booking->projectId){
				$calendar = $calendarRepository->readByProject($booking->projectId);
				$projectId = $booking->projectId;
			}
			foreach($booking->dates as $date){
				$this->order->bookedDays->add(new Booki_BookedDay(array(
					'projectId'=>$booking->projectId
					, 'bookingDate'=>$date['date']
					, 'hourStart'=>$date['hourStart']
					, 'minuteStart'=>$date['minuteStart']
					, 'hourEnd'=>$date['hourEnd']
					, 'minuteEnd'=>$date['minuteEnd']
					, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
					, 'cost'=>$date['cost']
					, 'deposit'=>$date['deposit']
					, 'status'=>$status
					, 'handlerUserId'=>$handlerUserId
					, 'projectName'=>$booking->projectName
				)));				
			}
			
			foreach( $booking->optionals as $optional ){
				$this->order->bookedOptionals->add(new Booki_BookedOptional(
					$booking->projectId
					, $optional['name']
					, $optional['cost']
					, $optional['deposit']
					, $status
					, null
					, $handlerUserId
					, null
					, $booking->projectName
					, $optional['count']
				));
			}
			

			foreach( $booking->cascadingItems as $cascadingItem ){
				$this->order->bookedCascadingItems->add(new Booki_BookedCascadingItem(
					$booking->projectId
					, $cascadingItem['value']
					, $cascadingItem['cost']
					, $cascadingItem['deposit']
					, $status
					, null
					, $handlerUserId
					, null
					, $booking->projectName
					, $cascadingItem['count']
				));
			}
			
			foreach($booking->formElements as $formElement){
				$this->order->bookedFormElements->add(new Booki_BookedFormElement(
					$booking->projectId
					, $formElement->label
					, $formElement->elementType
					, $formElement->rowIndex
					, $formElement->colIndex
					, $formElement->value
					, $formElement->capability
				));
			}
		}
	}
	
	public function setUserId($userId){
		$this->order->userId = $userId;
	}
	
	public function log(){
		$this->order->id = Booki_BookingProvider::insert($this->order, array($this, 'emailNotificationCallback'));
		$cart = new Booki_Cart();
		$cart->clear();
		
		$this->sendNotifications();
			
		return $this->order;
	}
	
	public function applyCouponAndLog($coupon){
		if($coupon){
			if($coupon->code && $coupon->couponType === Booki_CouponType::REGULAR){
				$coupon->expire();
				$couponRepository = new Booki_CouponRepository();
				$couponRepository->update($coupon);
			}
			$this->order->discount = $coupon->discount;
		}
		return $this->log();
	}
	
	protected function sendNotifications(){
		if($this->globalSettings->notifyBookingReceivedSuccessfully){
			$notificationEmailer = null;
			if($this->order->userIsRegistered){
				$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_RECEIVED_SUCCESSFULLY, $this->order->id);
			} else if ($this->unRegisteredUserInfo){
				$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::BOOKING_RECEIVED_SUCCESSFULLY, $this->order->id, null, null, null, 0, $this->unRegisteredUserInfo);
			}
			if($notificationEmailer){
				$notificationEmailer->send();
			}
		}

		if($this->globalSettings->autoNotifyAdminNewBooking && (!$this->globalSettings->enablePayments || $this->globalSettings->enableBookingWithAndWithoutPayment)){
			$notificationToUserInfo = Booki_Helper::getUserInfoByEmail($this->globalSettings->notificationEmailTo);
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_ADMIN, $this->order->id, null, null, null, 0, $notificationToUserInfo);
			$notificationEmailer->send();
			
			//notifies also agents if projects in booking have agents
			$notificationEmailer = new Booki_AgentsNotificationEmailer(Booki_EmailType::NEW_BOOKING_RECEIVED_FOR_AGENTS, $this->order->id);
			$notificationEmailer->send();
		}
	}
	
	public function emailNotificationCallback($email, $firstName, $lastName){
		$name = '';
		if($firstName){
			$name = $firstName;
		}
		if($lastName){
			$name .= strlen($name) > 0 ? ' ' . $lastName : $lastName;
		}
		if(!$name){
			$parts = explode('@', $email);
			$name = $parts[0];
		}
		$this->unRegisteredUserInfo = array('email'=>$email, 'name'=>$name);
	}
}
?>