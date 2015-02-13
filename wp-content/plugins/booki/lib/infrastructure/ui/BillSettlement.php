<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CouponRepository.php';
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';

class Booki_BillSettlement
{
	public $currency;
	public $currencySymbol;
	public $totalAmount;
	public $bookings;
	public $tax;
	public $coupon = null;
	public $formattedTotalAmountIncludingTax;
	public $formattedTotalAmount;
	public $totalAmountIncludingTax;
	public $hasBookings;
	public $dateFormat;
	public $globalSettings;
	public $timezoneInfo;
	public $timezoneString;
	public $orderId;
	public $discount = 0;
	public $hasDiscount = false;
	public $hasDeposit = false;
	public $enableBookingWithAndWithoutPayment = false;
	public $order;
	public $deposit = 0;
	public $formattedTotalAfterDeposit = 0;
	public $enableCartItemHeader = false;
	private $calendarRepository;
	private $projectRepository;
	private $timeFormat;
	private $dateTimeHelper;
	private $projectId;
	public function __construct($orderId, $couponCode = null, $projectId = null)
	{
		$this->orderId = $orderId;
		if($couponCode){
			$couponRepo = new Booki_CouponRepository();
			$this->coupon = $couponRepo->find($couponCode);
		}
		$this->projectId = $projectId;
		
		$this->dateFormat = get_option('date_format');
		$this->timeFormat = get_option('time_format');
		
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol = $localeInfo['currencySymbol'];
		
		$this->totalAmount = 0;
		$this->formattedTotalAmountIncludingTax = Booki_Helper::toMoney(0);
		
		$this->globalSettings = Booki_Helper::globalSettings();
		
		$this->bookings = array();
		
		$this->enableCartItemHeader = $this->globalSettings->enableCartItemHeader;

		$this->calendarRepository =  new Booki_CalendarRepository();
		$this->projectRepository = new Booki_ProjectRepository();
		$this->order = Booki_BookingProvider::read($orderId);
		
		if(!$this->order){
			return;
		}

		
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($this->order->timezone);
		$this->timezoneString = $this->timezoneInfo['timezone'];
		
		$this->discount = $this->order->discount;
		$this->tax = $this->order->tax;

		$this->createItemSummary($this->order->bookedDays);

		$this->sum();
		
		if($this->deposit > 0){
			$this->hasDeposit = true;
		}
	}
	
	protected function createItemSummary($bookedDays){
		if(is_array($bookedDays)){
			$day = $bookedDays[0];
		}else{
			$day = $bookedDays->item(0);
		}
		
		if(!$day){
			return;
		}
		
		$this->hasBookings = true;
		
		$project = $this->projectRepository->read($day->projectId);	
		$calendar = $this->calendarRepository->readByProject($day->projectId);
		
		$item = new stdClass();
		$item->dates = array();
		$item->optionals = array();
		$item->cascadingItems = array();
		$item->enablePayments = $this->globalSettings->enablePayments;
		$item->projectName = $project->name_loc;
		$item->calendarMode = $project->calendarMode;
		$item->deposit = 0;
		$item->subTotal = 0;
		$item->total = 0;
		foreach($bookedDays as $day){
			if($this->projectId !== null && $day->projectId !== $this->projectId){
				continue;
			}

			$item->subTotal += $day->cost;
			if($day->deposit > 0){
				$item->deposit += $this->calcDeposit($day->deposit, $day->cost);
			}
			
			$formattedTime = '';

			if($calendar->period === Booki_CalendarPeriod::BY_TIME){
				$formattedTime = Booki_TimeHelper::formatTime($day, $this->timezoneString, $day->enableSingleHourMinuteFormat, $this->timeFormat);
			}
			
			array_push($item->dates, array(
				'date'=>$day->bookingDate
				, 'id'=>$day->id
				, 'formattedDate'=>$day->bookingDate->format($this->dateFormat)
				, 'cost'=>$day->cost
				, 'deposit'=>$day->deposit
				, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($day->cost)
				, 'hourStart'=>$day->hourStart
				, 'minuteStart'=>$day->minuteStart
				, 'hourEnd'=>$day->hourEnd
				, 'minuteEnd'=>$day->minuteEnd
				, 'formattedTime'=>$formattedTime
				, 'projectName'=>$day->projectName
				, 'notifyUserEmailList'=>$day->notifyUserEmailList
				, 'isBooked'=>false
				, 'isRequired'=>false
			));
		}
		
		foreach($this->order->bookedOptionals as $optional){
			if($this->projectId !== null && $optional->projectId !== $this->projectId){
				continue;
			}

			$calculatedCost = $optional->cost;
			$calculatedName = $optional->name_loc;
			if($optional->count > 0){
				$calculatedCost =  $optional->cost * $optional->count;
				$calculatedName .= ' x ' . $optional->count;
			}
			
			array_push($item->optionals, array(
				'name'=>$optional->name_loc
				, 'id'=>$optional->id
				, 'cost'=>$optional->cost
				, 'deposit'=>$optional->deposit
				, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($optional->cost)
				, 'count'=>$optional->count
				, 'calculatedCost'=>$calculatedCost
				, 'formattedCalculatedCost'=>$this->currencySymbol . Booki_Helper::toMoney($calculatedCost)
				, 'calculatedName'=>$calculatedName
				, 'projectName'=>$optional->projectName
				, 'notifyUserEmailList'=>$optional->notifyUserEmailList
				, 'isBooked'=>false
				, 'isRequired'=>false
			));
			$item->subTotal += $calculatedCost;
			if($optional->deposit > 0){
				$item->deposit += $this->calcDeposit($optional->deposit, $calculatedCost);
			}
		}
		
		foreach($this->order->bookedCascadingItems as $cascadingItem){
			if($this->projectId !== null && $cascadingItem->projectId !== $this->projectId){
				continue;
			}

			$calculatedCost = $cascadingItem->cost;
			$calculatedName = $cascadingItem->value_loc;
			if($cascadingItem->count > 0){
				$calculatedCost =  $cascadingItem->cost * $cascadingItem->count;
				$calculatedName .= ' x ' . $cascadingItem->count;
			}
			array_push($item->cascadingItems, array(
				'value'=>$cascadingItem->value_loc
				, 'id'=>$cascadingItem->id
				, 'cost'=>$cascadingItem->cost
				, 'deposit'=>$cascadingItem->deposit
				, 'formattedCost'=>$this->currencySymbol . Booki_Helper::toMoney($cascadingItem->cost)
				, 'count'=>$cascadingItem->count
				, 'calculatedCost'=>$calculatedCost
				, 'formattedCalculatedCost'=>$this->currencySymbol . Booki_Helper::toMoney($calculatedCost)
				, 'calculatedName'=>$calculatedName
				, 'projectName'=>$cascadingItem->projectName
				, 'notifyUserEmailList'=>$cascadingItem->notifyUserEmailList
				, 'isBooked'=>false
				, 'isRequired'=>false
			));
			$item->subTotal += $calculatedCost;
			if($cascadingItem->deposit > 0){
				$item->deposit += $this->calcDeposit($cascadingItem->deposit, $calculatedCost);
			}
		}
		
		$item->total = $item->subTotal;
		
		if($item->deposit > 0){
			$item->total -= $item->deposit;
		}
		array_push($this->bookings, $item);
	}
	
	protected function sum(){
		$totalAmount = 0;
		$totalAfterDeposit = $totalAmount;
		foreach($this->bookings as $item){
			if($item->deposit > 0){
				$totalAmount += $item->deposit;
				$this->deposit += $item->deposit;
				$totalAfterDeposit += $item->total;
			}else{
				$totalAmount += $item->total;
			}
		}
		$this->totalAmount = $totalAmount;
		$this->formattedTotalAmount = Booki_Helper::toMoney($totalAmount);
		
		if($this->discount > 0){
			$this->hasDiscount = true;
			$totalAmount = Booki_Helper::calcDiscount($this->discount, $totalAmount);
		}
		
		$tax = Booki_Helper::percentage($this->tax, $totalAmount);
		$this->totalAmountIncludingTax = $tax + $totalAmount;
		$this->formattedTotalAmountIncludingTax = Booki_Helper::toMoney($this->totalAmountIncludingTax);
		$this->formattedTotalAfterDeposit = Booki_Helper::toMoney($totalAfterDeposit);
	}
	
	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
}
?>