<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
require_once  dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';

class Booki_OrderDetails{
	public $order;
	public $bookedDays;
	public $bookedOptionals;
	public $bookedFormElements;
	public $bookedCascadingItems;
	public $currency;
	public $currencySymbol;
	public $totalCost;
	public $tax;
	public $formattedTotalAmountIncludingTax;
	public $globalTimezoneInfo;
	public $hasUserDefinedTimezone = false;
	public $userDefinedTimezone;
	public $depositSubTotalFormatted;
	public $deposit;
	public $discount = 0;
	public $timezone;
	public $globalSettings;
	public $depositsByProjectGroup;
	public function __construct($orderId){
		if($orderId === null){
			return;
		}
		
		$this->globalSettings = Booki_Helper::globalSettings();
		$this->globalTimezoneInfo = Booki_TimeHelper::timezoneInfo();
		$this->timezone = $this->globalTimezoneInfo['timezone'];	
		
		$this->order = Booki_BookingProvider::orderRepository()->read($orderId);
		if(!$this->order){
			return;
		}
		$this->discount = $this->order->discount;
		$this->tax = $this->order->tax;
		
		if($this->order->timezone){
			$this->userDefinedTimezone = $this->order->timezone;
			$this->hasUserDefinedTimezone = true;
		}else{
			$this->userDefinedTimezone = $this->timezone;
		}
		
		$order = Booki_BookingProvider::read($orderId);
		$this->bookedDays = $order->bookedDays;
		$this->bookedOptionals = $order->bookedOptionals;
		$this->bookedCascadingItems = $order->bookedCascadingItems;
		$this->bookedFormElements = $order->bookedFormElements;
		
		$this->currency = $this->order->currency;
		$this->currencySymbol = Booki_Helper::toCurrencySymbol($this->currency);
		
		$this->init();
	}
	
	
	protected function init(){
		$this->totalCost = 0;
		$this->refundTotal = 0;
		if($this->bookedDays){
			foreach($this->bookedDays as $bookedDay){
				if($bookedDay->status === Booki_BookingStatus::REFUNDED){
					$this->refundTotal += $this->calcDeposit($bookedDay->deposit, $bookedDay->cost);
				}
				$this->totalCost += $bookedDay->cost;
				if($bookedDay->deposit > 0){
					$this->deposit += $this->calcDeposit($bookedDay->deposit, $bookedDay->cost);
				}
			}
		}
		
		if($this->bookedOptionals){
			foreach($this->bookedOptionals as $bookedOptional){
				if($bookedOptional->status === Booki_BookingStatus::REFUNDED){
					$this->refundTotal += $this->calcDeposit($bookedOptional->deposit, $bookedOptional->getCalculatedCost());
				}
				$this->totalCost += $bookedOptional->getCalculatedCost();
				if($bookedOptional->deposit > 0){
					$this->deposit += $this->calcDeposit($bookedOptional->deposit, $bookedOptional->getCalculatedCost());
				}
			}
		}
		
		if($this->bookedCascadingItems){
			foreach($this->bookedCascadingItems as $bookedCascadingItem){
				if($bookedCascadingItem->status === Booki_BookingStatus::REFUNDED){
					$this->refundTotal += $this->calcDeposit($bookedCascadingItem->deposit, $bookedCascadingItem->getCalculatedCost());
				}
				$this->totalCost += $bookedCascadingItem->getCalculatedCost();
				if($bookedCascadingItem->deposit > 0){
					$this->deposit += $this->calcDeposit($bookedCascadingItem->deposit, $bookedCascadingItem->getCalculatedCost());
				}
			}
		}

		$totalCost = $this->totalCost;

		$discount = 0;
		if($this->discount > 0){
			$totalCost = Booki_Helper::calcDiscount($this->discount, $totalCost);
		}
		
		$tax = 0;
		
		if($this->deposit > 0){
			$totalCost -= $this->deposit;
			$this->depositSubTotalFormatted = Booki_Helper::toMoney($this->deposit);
			if($this->tax > 0){
				$depositTax = Booki_Helper::percentage($this->tax, $this->deposit);
				$this->deposit = Booki_Helper::toMoney($depositTax + $this->deposit);
			}
		}else if($this->tax > 0){
			$tax = Booki_Helper::percentage($this->tax, $totalCost);
		}
		$this->formattedTotalAmountIncludingTax = Booki_Helper::toMoney($tax + $totalCost);
	}
	
	public function formatCost($val){
		return $this->currencySymbol . Booki_Helper::toMoney($val);
	}
	
	public function formatDate($val){
		return $val->format(get_option('date_format'));
	}
	
	public function formatTime($bookedDay, $timezone = null){
		if(!$timezone){
			$timezone = $this->timezone;
		}
		return Booki_TimeHelper::formatTime($bookedDay, $timezone, $bookedDay->enableSingleHourMinuteFormat);
	}
	
	protected function calcDeposit($deposit, $cost){
		if($deposit > 0){
			return ($cost/100)*$deposit;
		}
		return $cost;
	}
}
?>