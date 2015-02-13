<?php
require_once  dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarDayRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/BookedDaysRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/SettingsGlobalRepository.php';
require_once  dirname(__FILE__) . '/../session/Bookings.php';
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
require_once  dirname(__FILE__) . '/../utils/DateHelper.php';
class Booki_OrderSummary
{
	
	public $currency;
	public $currencySymbol;
	public $totalAmount;
	public $bookings;
	public $tax;
	public $formattedTotalAmountIncludingTax;
	public $hasBookings;
	public $dateFormat;
	public $globalSettings;
	public $hasBookedElements = false;
	public $timezoneInfo;
	public $timezoneString;
	public $bookingsCount = 0;
	public $hasDiscount = false;
	public $hasDeposit = false;
	public $discount;
	public $discounted = false;
	public $bookingMinimumDiscount;
	public $enableBookingWithAndWithoutPayment;
	private $items;
	private $timeFormat;
	private $dateTimeHelper;
	
	public function __construct(Booki_Bookings $items, $currency, $currencySymbol, $timezone = null)
	{
		$this->dateFormat = get_option('date_format');
		$this->items = $items;
		$this->currency = $currency;
		$this->currencySymbol = $currencySymbol;
		$this->totalAmount = 0;
		$this->bookings = array();
		
		$globalSettingsRepo = new Booki_SettingsGlobalRepository();
		$this->globalSettings = $globalSettingsRepo->read();
		
		$this->tax = $this->globalSettings->tax;
		$this->enableCartItemHeader = $this->globalSettings->enableCartItemHeader;
		
		if(!$this->globalSettings->autoTimezoneDetection){
			$timezone = null;
		}
		
		$this->discount = $this->globalSettings->discount;
		$this->bookingMinimumDiscount = $this->globalSettings->bookingMinimumDiscount;
		$this->enableBookingWithAndWithoutPayment = $this->globalSettings->enableBookingWithAndWithoutPayment;
		$this->hasDiscount = ($this->discount > 0 && ($this->bookingMinimumDiscount == 0 || $this->items->count() >= $this->bookingMinimumDiscount));

		$this->timeFormat = get_option('time_format');
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		$this->timezoneString = $this->timezoneInfo['timezone'];
		$this->createSummary();
		$this->enablePayments = $this->globalSettings->enablePayments && $this->totalAmount > 0;
		if($this->items->count() === 0){
			$this->disableDiscounts();
		}
	}
	
	protected function createSummary()
	{
		$flag = false;
		
		$projectRepository = new Booki_ProjectRepository();
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		$bookedDaysRepository = new Booki_BookedDaysRepository();
		
		$projectId = null;
		$project = null;
		$calendar = null;
		$calendarDays = null;
		$tempDate = array();
		$tempTime = array();
		foreach($this->items as $booking){
			if($booking->projectId !== $projectId){ 
				$projectId = $booking->projectId;
				$project = $projectRepository->read($projectId);
				if(!$project){
					continue;
				}
				$calendar = $calendarRepository->readByProject($projectId);
				$calendarDays = $calendarDayRepository->readAll($calendar->id);
			}
			
			if($calendar->deposit > 0){
				$this->disableDiscounts();
				$this->hasDeposit = true;
			}
			
			if($projectId === null || !$project){
				continue;
			}
			
			$item = new stdClass();
			$item->dates = array();
			$item->optionals = array();
			$item->cascadingItems = array();
			$item->projectId = $projectId;
			$item->projectName = $project->name_loc;
			$item->calendarId = $calendar->id;
			$item->formElements = $booking->formElements;
			$item->hasBookingLimit = $calendar->bookingLimit > 0;
			$item->bookingExhausted = false;
			$item->calendarMode = $project->calendarMode;
			$item->deposit = 0;
			$item->subTotal = 0;
			$item->total = 0;
			$item->rangeDates = null;
			$singleBookingTotal = 0;
			$bookedDays = null;
			$lengthDays = 0;
			$isBooked = false;
			if($project->bookingMode === Booki_BookingMode::APPOINTMENT ||
				$calendar->bookingLimit > 0){
				$bookedDays = $bookedDaysRepository->readByDays($booking->dates, $projectId);
			}
			foreach($booking->dates as $dateKey=>$date){
				$slotCost = 0;
				++$lengthDays;
				$currentDate = Booki_DateHelper::parseFormattedDateString($date);
				$isBooked = false;
				if($bookedDays){
					foreach($bookedDays as $bookedDay){
						if($calendar->exhausted()){
							$item->bookingExhausted = true;
							$isBooked = true;
						}
						$areEqual = Booki_DateHelper::daysAreEqual($bookedDay->bookingDate, $currentDate);
						if(!$isBooked && $areEqual){
							if($calendar->period === Booki_CalendarPeriod::BY_TIME && 
								($booking->hasTime() && !$bookedDay->compareTime($booking))){
								continue;
							}
							$isBooked = true;
						}
						if($isBooked){
							unset($booking->dates[$dateKey]);
							$this->hasBookedElements = true;
							break;
						}
					}
				}
				
				foreach($calendarDays as $calendarDay){
					if($calendarDay->day->format($this->dateFormat) === $currentDate->format($this->dateFormat)){
						if(!$isBooked){
							$item->subTotal += $calendarDay->cost;
						}
						$slotCost = $calendarDay->cost;
						$flag = true;
						break;
					}
				}
				
				if(!$flag){
					if(!$isBooked){
						$item->subTotal += $calendar->cost;
					}
					$slotCost = $calendar->cost;
				}
				
				$flag = false;
				$formattedTime = '';
				
				$item->rangeCost += $slotCost;

				if($calendar->period === Booki_CalendarPeriod::BY_TIME){
					$formattedTime = Booki_TimeHelper::formatTime($booking, $this->timezoneString, $calendar->enableSingleHourMinuteFormat, $this->timeFormat);
				}
				
				array_push($item->dates, array(
					'rawDate'=>$date
					, 'date'=>$currentDate
					, 'formattedDate'=>$currentDate->format($this->dateFormat)
					, 'bookingId'=>$booking->id
					, 'cost'=>$slotCost
					, 'deposit'=>$booking->deposit
					, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($slotCost)
					, 'hourStart'=>$booking->hourStart
					, 'minuteStart'=>$booking->minuteStart
					, 'hourEnd'=>$booking->hourEnd
					, 'minuteEnd'=>$booking->minuteEnd
					, 'formattedTime'=>$formattedTime
					, 'isBooked'=>$isBooked
					, 'isRequired'=>$project->bookingDaysMinimum > 0 && $lengthDays <= $project->bookingDaysMinimum
				));
			}

			if(!$isBooked){
				++$this->bookingsCount;
			}
			$removeBooking = count($item->dates) === 0 && $isBooked;
			
			$lengthOptionals = 0;
			foreach($booking->optionals as $optional){
				++$lengthOptionals;
				$calculatedCost = $optional->cost;
				$calculatedName = $optional->name_loc;
				if($optional->count > 0){
					$calculatedCost =  $optional->cost * $optional->count;
					$calculatedName .= ' x ' . $optional->count;
				}
				array_push($item->optionals, array(
					'name'=>$optional->name_loc
					, 'id'=>$optional->id
					, 'bookingId'=>$booking->id
					, 'cost'=>$optional->cost
					, 'deposit'=>$booking->deposit
					, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($optional->cost)
					, 'isBooked'=>$removeBooking
					, 'count'=>$optional->count
					, 'calculatedCost'=>$calculatedCost
					, 'formattedCalculatedCost'=>$this->currencySymbol . Booki_Helper::toMoney($calculatedCost)
					, 'calculatedName'=>$calculatedName
					, 'projectName'=>$item->projectName
					, 'isRequired'=>$project->optionalsMinimumSelection > 0 && $lengthOptionals <= $project->optionalsMinimumSelection
				));
				if(!$removeBooking){
					$item->subTotal += $calculatedCost;
				}
			}

			foreach($booking->cascadingItems as $cascadingItem){
				$calculatedCost = $cascadingItem->cost;
				$calculatedName = $cascadingItem->value_loc;
				if($cascadingItem->count > 0){
					$calculatedCost =  $cascadingItem->cost * $cascadingItem->count;
					$calculatedName .= ' x ' . $cascadingItem->count;
				}

				array_push($item->cascadingItems, array(
					'value'=>$cascadingItem->value_loc
					, 'id'=>$cascadingItem->id
					, 'bookingId'=>$booking->id
					, 'cost'=>$cascadingItem->cost
					, 'deposit'=>$booking->deposit
					, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($cascadingItem->cost)
					, 'isBooked'=>$removeBooking
					, 'count'=>$cascadingItem->count
					, 'calculatedCost'=>$calculatedCost
					, 'formattedCalculatedCost'=>$this->currencySymbol . Booki_Helper::toMoney($calculatedCost)
					, 'calculatedName'=>$calculatedName
					, 'projectName'=>$item->projectName
					, 'isRequired'=>$cascadingItem->isRequired
				));
				if(!$removeBooking){
					$item->subTotal += $calculatedCost;
				}
			}
			
			if($removeBooking){
				$this->items->remove_item($booking);
			}
			
			if($booking->deposit > 0){
				$item->deposit = $this->calcDeposit($booking->deposit, $item->subTotal);
				$item->total = $item->subTotal - $item->deposit;
			}else{
				$item->total = $item->subTotal;
			}
			array_push($this->bookings, $item);
		}

		$this->hasBookings = count($this->bookings) > 0;
		$totalAmount = 0;
		foreach($this->bookings as $item){
			if($item->deposit > 0){
				$totalAmount += $item->deposit;
			}else{
				$totalAmount += $item->total;
			}
		}
		$this->totalAmount = $totalAmount;
		$this->formattedTotalAmount = Booki_Helper::toMoney($totalAmount);
		
		if($this->discount > 0){
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $totalAmount);
		}else if($this->items->coupon){
			$totalAmount = Booki_Helper::calcDiscount($this->items->coupon->discount, $totalAmount);
		}
		
		$tax = Booki_Helper::percentage($this->tax, $totalAmount);
		$this->formattedTotalAmountIncludingTax = Booki_Helper::toMoney($tax + $totalAmount);
	}
	
	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
	
	protected function disableDiscounts(){
		$this->hasDiscount = false;
		$this->discount = 0;
		$this->bookingMinimumDiscount = 0;
	}
}
?>