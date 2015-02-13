<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
require_once  dirname(__FILE__) . '/../utils/DateHelper.php';
class Booki_BookingForm{
	const calendar = '{
					"startDate": "%s"
					, "endDate": "%s"
					, "daysExcluded": [%s]
					, "timeExcluded": [%s]
					, "weekDaysExcluded": [%s]
					, "hours": %d
					, "minutes": %d
					, "cost": "%01.2f"
					, "hourStartInterval": %d
					, "minuteStartInterval": %d
					, "minNumDaysDeposit": %d
					, "deposit":"%01.2f"
					, "bookingStartLapse": %d
				}';
	
	const calendarDays = '{
					"day": "%s"
					, "timeExcluded": [%s]
					, "hours": %d
					, "minutes": %d
					, "cost": "%01.2f"
					, "hourStartInterval": %d
					, "minuteStartInterval": %d
					, "minNumDaysDeposit": %d
					, "deposit":"%01.2f"
				}';
	
	
	public $firstDay;
	public $showCalendarButtonPanel;
	public $dateFormat;
	public $altFormat;
	public $calendar;
	public $calendarDays = array();
	public $currency;
	public $locale;
	public $decialPoint;
	public $thousandsSep;
	public $currencySymbol;
	public $bookingDaysMinimum;
	public $bookingDaysLimit;
	public $hideSelectedDays;
	public $calendarMode;
	public $calendarPeriod;
	public $minDate;
	public $startDate;
	public $endDate;
	public $projectId;
	public $timezoneInfo;
	public $timezoneString;
	public $timeFormat;
	public $timeSlots = array();
	public $availableDaysLabel;
	public $selectedDaysLabel;
	public $bookingTimeLabel;
	public $proceedToLoginLabel;
	public $makeBookingLabel;
	public $fromLabel;
	public $toLabel;
	public $bookingLimitLabel;
	public $calendarStyles = array();
	public $bookingMode;
	public $autoTimezoneDetection;
	public $usedSlots = array();
	public $timeSelector;
	public $discount;
	public $bookingMinimumDiscount;
	public $hasDiscount;
	public $globalSettings;
	public $bookedItemsCount;
	public $displayBookedTimeSlots;
	public $optionalsBookingMode;
	public $optionalsListingMode;
	public $highlightSelectedOptionals;
	public $bookingLimit;
	public $bookingsExhausted = false;
	public $deposit;
	public $enableItemHeading;
	public $projectName;
	private $hours;
	private $minutes;
	public function __construct($calendar, $calendarDays, $bookedDays, $project, $currency, $currencySymbol, $locale, $bookings){
		$this->bookedItemsCount = $bookings->count();
		$timezone = $bookings->timezone;
		$this->currency = $currency;
		$this->currencySymbol = $currencySymbol;
		$this->locale = $locale;
		$this->bookingDaysLimit = $project->bookingDaysLimit;
		$this->hideSelectedDays = $project->hideSelectedDays;
		$this->calendarMode = $project->calendarMode;
		$this->availableDaysLabel = $project->availableDaysLabel_loc;
		$this->selectedDaysLabel = $project->selectedDaysLabel_loc;
		$this->bookingTimeLabel = $project->bookingTimeLabel_loc;
		$this->optionalsBookingMode = $project->optionalsBookingMode;
		$this->optionalsListingMode = $project->optionalsListingMode;
		$this->fromLabel = $project->fromLabel_loc;
		$this->toLabel = $project->toLabel_loc;
		$this->proceedToLoginLabel = $project->proceedToLoginLabel_loc;
		$this->makeBookingLabel = $project->makeBookingLabel_loc;
		$this->bookingLimitLabel = $project->bookingLimitLabel_loc;
		$this->bookingDaysMinimum = $project->bookingDaysMinimum;
		$this->projectName = $project->name_loc;
		
		$this->projectId = $calendar->projectId;
		$this->calendarPeriod = $calendar->period;
		$this->bookingLimit = $calendar->bookingLimit;
		$this->currentBookingCount = $calendar->bookingLimit - $calendar->currentBookingCount;
		$this->bookingsExhausted = $calendar->exhausted();
		$this->displayCurrentBookingsCount = $calendar->displayCounter && $this->bookingLimit > 0;
		$this->deposit = $calendar->deposit;
		$this->globalSettings = Booki_Helper::globalSettings();
		$this->bookingMode = $project->bookingMode;
		$this->autoTimezoneDetection = $this->globalSettings->autoTimezoneDetection;
		$this->timeSelector = $this->globalSettings->timeSelector;
		$this->calendarFirstDay = $this->globalSettings->calendarFirstDay;
		$this->showCalendarButtonPanel = $this->globalSettings->showCalendarButtonPanel;
		$this->discount = $this->globalSettings->discount;
		$this->bookingMinimumDiscount = $this->globalSettings->bookingMinimumDiscount;
		$this->displayBookedTimeSlots = $this->globalSettings->displayBookedTimeSlots;
		$this->highlightSelectedOptionals = $this->globalSettings->highlightSelectedOptionals;
		$this->hasDiscount = ($this->discount > 0 && ($this->bookingMinimumDiscount == 0 || $bookings->count() >= $this->bookingMinimumDiscount));
		$this->enableItemHeading = isset($_GET['enableitemheading']) ?  filter_var($_GET['enableitemheading'], FILTER_VALIDATE_BOOLEAN) : false;
		
		if(!$this->autoTimezoneDetection){
			$timezone = null;
		}
		
		if($this->globalSettings->calendarFlatStyle){
			array_push($this->calendarStyles, 'booki-flat');
		}
		if($this->globalSettings->calendarBorderlessStyle){
			array_push($this->calendarStyles, 'booki-borderless');
		}
		
		$this->dateFormat = $this->globalSettings->shorthandDateFormat;
		$this->altFormat = Booki_DateHelper::getJQueryCalendarFormat($this->dateFormat);
		
		$this->timezoneInfo = Booki_TimeHelper::timezoneInfo($timezone);
		
		$this->timezoneString = $this->timezoneInfo['timezone'];
		
		if(!$this->bookingDaysLimit){
			$this->bookingDaysLimit = 1;
		}
		if(!$this->bookingDaysLimit || $this->calendarPeriod === Booki_CalendarPeriod::BY_TIME){
			if($this->calendarMode !== 0/*popup*/ && $this->calendarMode !== 1/*inline*/){
				$this->calendarMode = Booki_CalendarMode::POPUP;
			}
		}
		
		$this->decimalPoint = $locale['decimal_point'];
		$this->thousandsSep = $locale['thousands_sep'];
		
		$this->init($calendar, $calendarDays, $bookedDays);
	}
	
	protected function init($calendar, $calendarDays, $bookedDays){
		if(!$calendar){
			return '';
		}
		
		$today = new Booki_DateTime();
		$today->setTime(0, 0, 0);
		$this->startDate = Booki_DateHelper::formatString($calendar->startDate);
		
		$calendar->startDate->setTime(0, 0, 0);
		$calendar->endDate->setTime(0, 0, 0);
		
		if($calendar->startDate < $today && $calendar->endDate >= $today){
			$this->startDate = Booki_DateHelper::formatString($today);
		} 

		$this->endDate = Booki_DateHelper::formatString($calendar->endDate);
		
		Booki_DateHelper::fillBookings($calendar, $calendarDays, $bookedDays);
		

		if($this->bookingMode === Booki_BookingMode::APPOINTMENT){
			$result = Booki_DateHelper::availabilityInRange($calendar, $calendarDays, $bookedDays);
			foreach($result['usedDays'] as $usedDay){
				$timeSlots = array(
					'day'=>$usedDay['day']
					, 'slotsExhausted'=>$usedDay['slotsExhausted']
				);
				array_push($this->usedSlots, $timeSlots);
			}
		}
		
		$this->calendar = array(
					'startDate'=>$this->startDate
					, 'endDate'=>$this->endDate
					, 'daysExcluded'=>$this->formatExcludedDays($calendar->daysExcluded)
					, 'timeExcluded'=>$calendar->timeExcluded
					, 'weekDaysExcluded'=>$calendar->weekDaysExcluded
					, 'hours'=>$calendar->hours
					, 'minutes'=>$calendar->minutes
					, 'cost'=>$calendar->cost
					, 'hourStartInterval'=>$calendar->hourStartInterval
					, 'minuteStartInterval'=>$calendar->minuteStartInterval
					, 'minNumDaysDeposit'=>$calendar->minNumDaysDeposit
					, 'deposit'=>$calendar->deposit
					, 'bookingStartLapse'=>$calendar->bookingStartLapse
					, 'enableSingleHourMinuteFormat'=>$calendar->enableSingleHourMinuteFormat
		);
		
		if($calendar->deposit > 0){
			$this->hasDiscount = false;
			$this->discount = 0;
			$this->bookingMinimumDiscount = 0;
		}
		
		foreach($calendarDays as $calendarDay){
			if($calendarDay->deposit > 0){
				$this->hasDiscount = false;
				$this->discount = 0;
				$this->bookingMinimumDiscount = 0;
			}
			$dayStringFormat = Booki_DateHelper::formatString($calendarDay->day);
			$exhausted = false;
			foreach($this->usedSlots as $usedSlot){
				if($dayStringFormat === $usedSlot['day'] && $usedSlot['slotsExhausted']){
					$exhausted = true;
					break;
				}
			}
			
			if($exhausted){
				continue;
			}
			array_push($this->calendarDays, array(
					'day'=>$dayStringFormat
					, 'timeExcluded'=>$calendarDay->timeExcluded
					, 'hours'=>$calendarDay->hours
					, 'minutes'=>$calendarDay->minutes
					, 'cost'=>$calendarDay->cost
					, 'hourStartInterval'=>$calendarDay->hourStartInterval
					, 'minuteStartInterval'=>$calendarDay->minuteStartInterval
					, 'minNumDaysDeposit'=>$calendarDay->minNumDaysDeposit
					, 'deposit'=>$calendarDay->deposit
			));
		}

		$this->cost = $calendar->cost;
		$this->formattedCost = Booki_Helper::toMoney($calendar->cost);
	}
	
	public function toJson(){
		$result = array(
			'calendarPeriod'=>$this->calendarPeriod
			, 'calendar'=>$this->calendar
			, 'calendarDays'=>$this->calendarDays
			, 'minDate'=>$this->startDate
			, 'maxDate'=>$this->endDate
			, 'bookingDaysMinimum'=>$this->bookingDaysMinimum
			, 'bookingDaysLimit'=>$this->bookingDaysLimit
			, 'hideSelectedDays'=>$this->hideSelectedDays
			, 'altFormat'=>$this->altFormat
			, 'dateFormat'=>$this->dateFormat
			, 'decimalPoint'=>$this->decimalPoint
			, 'thousandsSep'=>$this->thousandsSep
			, 'currencySymbol'=>$this->currencySymbol
			, 'ajaxurl'=>admin_url('admin-ajax.php') 
			, 'timezone'=>$this->timezoneString 
			, 'timeSelector'=>$this->timeSelector
			, 'discount'=>(double)Booki_Helper::toMoney($this->discount)
			, 'bookingMinimumDiscount'=>$this->bookingMinimumDiscount
			, 'bookedItemsCount'=>$this->bookedItemsCount
			, 'bookingMode'=>$this->bookingMode
			, 'includeBookingPrice'=>$this->globalSettings->includeBookingPrice
			, 'calendarMode'=>$this->calendarMode
			, 'usedSlots'=>$this->usedSlots
			, 'autoTimezoneDetection'=>$this->autoTimezoneDetection
			, 'calendarFirstDay'=>$this->calendarFirstDay
			, 'showCalendarButtonPanel'=>$this->showCalendarButtonPanel
			, 'displayBookedTimeSlots'=>$this->displayBookedTimeSlots
			, 'calendarCssClasses'=>implode(' ', $this->calendarStyles)
			, 'optionalsBookingMode'=>$this->optionalsBookingMode
			, 'optionalsListingMode'=>$this->optionalsListingMode
			, 'highlightSelectedOptionals'=>$this->highlightSelectedOptionals
			, 'defaultCascadingListSelectionLabel'=>__('Select an item', 'booki')
			, 'paymentOnArrivalLabel'=>__('Payment due on arrival','booki')
		);
		return json_encode($result);
	}
	
	protected function formatExcludedDays($daysExcluded){
		$result = array();
		foreach($daysExcluded as $day){
			array_push($result, Booki_DateHelper::fromDefaultToAdminSelectedFormat($day));
		}
		return $result;
	}
}
?>