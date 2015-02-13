<?php
require_once dirname(__FILE__) . '/base/ProviderBase.php';
require_once dirname(__FILE__) . '/../entities/BookedDay.php';
require_once dirname(__FILE__) . '/../entities/BookedDays.php';
require_once dirname(__FILE__) . '/../repository/BookedDaysRepository.php';
require_once dirname(__FILE__) . '/../entities/BookedFormElement.php';
require_once dirname(__FILE__) . '/../entities/BookedFormElements.php';
require_once dirname(__FILE__) . '/../repository/OrderRepository.php';
require_once dirname(__FILE__) . '/../repository/BookedFormElementsRepository.php';
require_once dirname(__FILE__) . '/../repository/SettingsGlobalRepository.php';
require_once dirname(__FILE__) . '/../repository/ProjectRepository.php';
require_once dirname(__FILE__) . '/../repository/FormElementRepository.php';
require_once dirname(__FILE__) . '/../repository/CalendarRepository.php';
require_once dirname(__FILE__) . '/../repository/CalendarDayRepository.php';
require_once dirname(__FILE__) . '/../entities/BookedOptional.php';
require_once dirname(__FILE__) . '/../entities/BookedOptionals.php';
require_once dirname(__FILE__) . '/../repository/BookedOptionalsRepository.php';
require_once dirname(__FILE__) . '/../../infrastructure/emails/NotificationEmailer.php';
require_once dirname(__FILE__) . '/../../infrastructure/emails/OrderCancelNotificationEmailer.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/TimeHelper.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/DateHelper.php';

class Booki_BookingProvider
{
	private static $orderRepo;
	private static $bookedDaysRepo;
	private static $bookedCascadingItemsRepo;
	private static $bookedFormElementsRepo;
	private static $bookedOptionalsRepo;
	protected function __construct()
	{
	}
	public static function orderRepository()
	{
		if (!isset(self::$orderRepo)) 
		{
			self::$orderRepo = new Booki_OrderRepository();
		}
		return self::$orderRepo;
	}
	
	public static function bookedDaysRepository()
	{
		if (!isset(self::$bookedDaysRepo)) 
		{
			self::$bookedDaysRepo = new Booki_BookedDaysRepository();
		}
		return self::$bookedDaysRepo;
	}
	
	public static function bookedFormElementsRepository()
	{
		if (!isset(self::$bookedFormElementsRepo)) 
		{
			self::$bookedFormElementsRepo = new Booki_BookedFormElementsRepository();
		}
		return self::$bookedFormElementsRepo;
	}
	
	public static function bookedCascadingItemsRepository()
	{
		if (!isset(self::$bookedCascadingItemsRepo)) 
		{
			self::$bookedCascadingItemsRepo = new Booki_BookedCascadingItemsRepository();
		}
		return self::$bookedCascadingItemsRepo;
	}
	
	public static function bookedOptionalsRepository()
	{
		if (!isset(self::$bookedOptionalsRepo)) 
		{
			self::$bookedOptionalsRepo = new Booki_BookedOptionalsRepository();
		}
		return self::$bookedOptionalsRepo;
	}
	
	protected static function filter($formElements, $capability){
		if($formElements){
			foreach($formElements as $formElement){
				if($formElement->capability === $capability){
					return $formElement;
				}
			}
		}
		return null;
	}
	
	public static function insert($order, $notificationEmailCallback = null)
	{
		$formElements = null;
		
		$orderId = self::orderRepository()->insert($order);

		if($order->bookedDays)
		{
			foreach($order->bookedDays as $bookedDay)
			{
				self::bookedDaysRepository()->insert($orderId, $bookedDay);
			}
		}
		
		if($order->bookedOptionals)
		{
			foreach($order->bookedOptionals as $optional)
			{
				self::bookedOptionalsRepository()->insert($orderId, $optional);
			}
		}
		
		if($order->bookedCascadingItems){
			foreach($order->bookedCascadingItems as $cascadingItem){
				self::bookedCascadingItemsRepository()->insert($orderId, $cascadingItem);
			}
		}
		
		if($order->bookedFormElements)
		{
			$projects = array();
			foreach($order->bookedFormElements as $formElement)
			{
				if(!in_array($formElement->projectId, $projects) && $formElement->elementType === Booki_ElementType::TEXTBOX){
					array_push($projects, $formElement->projectId);
				}
				self::bookedFormElementsRepository()->insert($orderId, $formElement);
			}
		}
		
		if(!$order->userIsRegistered && $order->bookedFormElements)
		{
			$contactInfo = self::getNonRegContactInfo($orderId);
			if($contactInfo){
				$firstName = $contactInfo['firstname'];
				$lastName = $contactInfo['lastname'];
				$email = $contactInfo['email'];
				if($contactInfo['hasAutoRegEmail']){
					$result = Booki_Helper::createUserIfNotExists($email, $firstName, $lastName);
					$order->userId = $result['userId'];
					$order->userIsRegistered = true;
					$order->id = $orderId;
					self::update($order);
				}else{
					call_user_func_array($notificationEmailCallback, array($email, $firstName, $lastName));
				}
			}
		}

		return $orderId;
	}
	
	public static function getNonRegContactInfo($orderId){
		$capabilities = implode(',', array(
			Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG
			, Booki_FormElementCapability::EMAIL_NOTIFICATION
			, Booki_FormElementCapability::FIRST_NAME
			, Booki_FormElementCapability::LAST_NAME
		));
		$formElements = self::bookedFormElementsRepository()->readOrderByCapability($orderId, Booki_ElementType::TEXTBOX, $capabilities);
		$formElementEmailNotification = self::filter($formElements, Booki_FormElementCapability::EMAIL_NOTIFICATION);
		$formElementEmailAutoReg = self::filter($formElements, Booki_FormElementCapability::EMAIL_NOTIFICATION_AUTOREG);
		$formElementFirstName = self::filter($formElements, Booki_FormElementCapability::FIRST_NAME);
		$formElementLastName = self::filter($formElements, Booki_FormElementCapability::LAST_NAME);
		$firstName = $formElementFirstName ? $formElementFirstName->value : null;
		$lastName = $formElementLastName ? $formElementLastName->value : null;
		$email = null;
		$hasAutoRegEmail = false;
		
		if($formElementEmailAutoReg){
			$email = $formElementEmailNotification->value;
			$hasAutoRegEmail = true;
		}else if($formElementEmailNotification){
			$email = $formElementEmailNotification->value;
		}
		
		if(!$email){
			return null;
		}
		
		if($firstName && !$lastName){
			$lastName = '';
			$parts = explode(' ', $firstName);
			if(count($parts) > 1){
				$lastName = $parts[1];
			}
		}
		$fullName = $firstName . ' ' . $lastName;
		return array('email'=>$email, 'firstname'=>$firstName, 'lastname'=>$lastName, 'hasAutoRegEmail'=>$hasAutoRegEmail, 'name'=>$fullName);
	}
	
	public static function update($order)
	{
		return self::orderRepository()->update($order);
	}
	
	public static function read($orderId)
	{
		$order = self::orderRepository()->read($orderId);
		if($order){
			$order->bookedDays = self::bookedDaysRepository()->readByOrder($orderId);
			$order->bookedOptionals = self::bookedOptionalsRepository()->readByOrder($orderId);
			$order->bookedFormElements = self::bookedFormElementsRepository()->readByOrder($orderId);
			$order->bookedCascadingItems = self::bookedCascadingItemsRepository()->readByOrder($orderId);
		}
		return $order;
	}
	
	public static function approveAll($orderId){
		
	}
	public static function deleteExired($days){
		$today = new Booki_DateTime();
		$newDate = date(BOOKI_DATEFORMAT, strtotime($today->format(BOOKI_DATEFORMAT) . " - $days days"));
		
		return self::orderRepository()->deleteExpired($newDate);
	}
	
	public static function delete($orderId){
		return self::orderRepository()->delete($orderId);
	}
	
	public static function summary($orderId){
		$order = self::read($orderId);
		if(!$order){
			return null;
		}
		$timeFormat = get_option('time_format');
		$globalTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$timezoneInfo = Booki_TimeHelper::timezoneInfo($order->timezone);
		
		$dateTime = new Booki_DateTime();
		$dateTime->setTimeZone(new DateTimeZone($globalTimezoneInfo['timezone']));
		
		$dateTimezone = new DateTimeZone($timezoneInfo['timezone']);
		$timezoneOffset = $dateTimezone->getOffset($dateTime);
		
		$globalSettingsRepo = new Booki_SettingsGlobalRepository();
		$globalSettings = $globalSettingsRepo->read();
		
		$localeInfo = Booki_Helper::getLocaleInfo();
		$cost = 0;
		
		$item = new stdClass();
		$item->orderId = $orderId;
		$item->userId = $order->userId;
		$item->orderDate = $order->orderDate;
		$item->status = $order->status;
		$item->dates = array();
		$item->optionals = array();
		$item->cascadingItems = array();
		$item->enablePayments = $globalSettings->enablePayments;
		$item->formElements = $order->bookedFormElements;	
		$item->currencySymbol = $localeInfo['currencySymbol'];
		$item->currency = $localeInfo['currency'];
		$item->settings = $globalSettings;
		$item->timezoneInfo = $timezoneInfo;
		$item->discount = $order->discount;
		$item->userIsRegistered = $order->userIsRegistered;
		
		foreach($order->bookedDays as $day){
			$cost += $day->cost;
			$formattedTime = '';
			if($day->hasTime()){
				$dateTime->setTime($day->hourStart, $day->minuteStart);
				$formattedTime = date($timeFormat, $dateTime->format('U') + $timezoneOffset);
			}
			
			if($day->hasEndTime()){
				$dateTime->setTime($day->hourEnd, $day->minuteEnd);
				$formattedTime .= ' - ' . date($timeFormat, $dateTime->format('U') + $timezoneOffset);
			}

			array_push($item->dates, array(
				'date'=>$day->bookingDate
				, 'projectId'=>$day->projectId
				, 'projectName'=>$day->projectName
				, 'formattedDate'=>Booki_Helper::formatDate( $day->bookingDate)
				, 'cost'=>$day->cost
				, 'deposit'=>$day->deposit
				, 'formattedCost'=>$item->currencySymbol . Booki_Helper::toMoney($day->cost)
				, 'hourStart'=>$day->hourStart
				, 'minuteStart'=>$day->minuteStart
				, 'hourEnd'=>$day->hourEnd
				, 'minuteEnd'=>$day->minuteEnd
				, 'formattedTime'=>$formattedTime
				, 'notifyUserEmailList'=>$day->notifyUserEmailList
			));
		}
		
		foreach($order->bookedOptionals as $optional){
			array_push($item->optionals, array(
				'name'=>$optional->getName()
				, 'projectId'=>$optional->projectId
				, 'projectName'=>$optional->projectName
				, 'cost'=>$optional->getCalculatedCost()
				, 'formattedCost'=>$item->currencySymbol . Booki_Helper::toMoney($optional->getCalculatedCost())
				, 'notifyUserEmailList'=>$optional->notifyUserEmailList
			));
			$cost += $optional->getCalculatedCost();
		}
		
		foreach($order->bookedCascadingItems as $cascadingItem){
			array_push($item->cascadingItems, array(
				'value'=>$cascadingItem->getName()
				, 'projectId'=>$cascadingItem->projectId
				, 'projectName'=>$cascadingItem->projectName
				, 'cost'=>$cascadingItem->getCalculatedCost()
				, 'formattedCost'=>$item->currencySymbol . Booki_Helper::toMoney($cascadingItem->getCalculatedCost())
				, 'notifyUserEmailList'=>$cascadingItem->notifyUserEmailList
			));
			$cost += $cascadingItem->getCalculatedCost();
		}
		
		$item->hasBookings = $cost > 0;
		$item->totalAmount = Booki_Helper::toMoney($cost);
		$item->formattedTotalAmount = $item->currencySymbol . $item->totalAmount;
		
		$discount = 0;
		if($order->discount > 0){
			$discount = Booki_Helper::percentage($order->discount, $item->totalAmount);
		}
		
		$item->discount = $discount;
		$cost -= $discount;
		
		$item->tax = $globalSettings->tax;
		$item->formattedTotalAmountIncludingTax = Booki_Helper::toMoney(Booki_Helper::percentage($globalSettings->tax, $cost) + $cost);
		return $item;
	}

	public static function getBookingPeriod($projectId, $bookings = null){
		$projectRepository = new Booki_ProjectRepository();
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		
		$result = new stdClass();
		$result->project = $projectRepository->read($projectId);
		$result->calendar = $calendarRepository->readByProject($projectId);
		$result->calendarDays = $calendarDayRepository->readAll($result->calendar->id);
		$result->bookedDays = new Booki_BookedDays();
		if($result->project->bookingMode === Booki_BookingMode::APPOINTMENT){
			$bookedDaysRepository = new Booki_BookedDaysRepository();
			$result->bookedDays = $bookedDaysRepository->readByProject($projectId);
			
			if($bookings){
				foreach($bookings as $booking){
					if($booking->projectId !== $projectId){
						continue;
					}
					if($result->calendar->bookingLimit > 0){
						++$result->calendar->currentBookingCount;
					}
					foreach($booking->dates as $date){
						if($result->calendar->period === Booki_CalendarPeriod::BY_TIME && $booking->hasTime()){
							$time = $booking->hourStart . ':' . $booking->minuteStart;
							if(!in_array($time, $result->calendar->timeExcluded)){
								$result->calendarDays->add(new Booki_CalendarDay(
									-1
									, Booki_DateHelper::parseFormattedDateString($date)
									, $time ? array_merge($result->calendar->timeExcluded, array($time)) : array()
									, $result->calendar->hours
									, $result->calendar->minutes
									, $result->calendar->cost
									, $result->calendar->hourStartInterval
									, $result->calendar->minuteStartInterval
									, 'Temp Season'
									, null
									, null
								));
							}
						}
						
						$result->bookedDays->add(new Booki_BookedDay(array(
							'projectId'=>$booking->projectId
							, 'bookingDate'=>Booki_DateHelper::parseFormattedDateString($date)
							, 'hourStart'=>$booking->hourStart
							, 'minuteStart'=>$booking->minuteStart
							, 'hourEnd'=>$booking->hourEnd
							, 'minuteEnd'=>$booking->minuteEnd
							, 'enableSingleHourMinuteFormat'=>$result->calendar->enableSingleHourMinuteFormat
						)));
					}
				}
			}
			
		}
		return $result;
	}
	
	public static function approveOrderAndNotifyUser($orderId, $userIsRegistered = true, $unRegisteredUserInfo = null){
		self::bookedDaysRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		self::bookedOptionalsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		self::bookedCascadingItemsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::APPROVED);
		$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::ORDER_CONFIRMATION, $orderId, null, null, null, 0, $unRegisteredUserInfo);
		$notificationEmailer->send();
	}
	
	public static function cancelOrderAndNotifyAdmin($orderId){
		self::bookedDaysRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		self::bookedOptionalsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		self::bookedCascadingItemsRepository()->updateStatusByOrderId($orderId, Booki_BookingStatus::USER_REQUEST_CANCEL);
		
		$notificationEmailer = new Booki_OrderCancelNotificationEmailer($orderId);
		$notificationEmailer->send();
	}
	public static function hasAvailability($projectId){
		$result = self::getBookingPeriod($projectId);
		Booki_DateHelper::fillBookings($result->calendar, $result->calendarDays, $result->bookedDays);
		$result = Booki_DateHelper::availabilityInRange($result->calendar, $result->calendarDays, $result->bookedDays);
		return count($result['availableDays']) > 0;
	}
}
?>