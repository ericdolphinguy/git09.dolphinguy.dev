/**
	 @license Copyright @ 2014 Alessandro Zifiglio. All rights reserved. http://www.booki.io
*/
(function($){
	var methods = {
		'create': function(name,value,days) {
			var date
				, expires;
			if (days) {
				date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				expires = "; expires="+date.toGMTString();
			}
			else {
				expires = "";
			}
			document.cookie = name+"="+value+expires+"; path=/";
		}
		, 'read': function(name) {
			var nameEQ = name + "="
				, ca = document.cookie.split(';')
				, c
				, i;
			for(i=0;i < ca.length;i++) {
				c = ca[i];
				while (c.charAt(0)==' '){ 
					c = c.substring(1,c.length);
				}
				if (c.indexOf(nameEQ) == 0){ 
					return c.substring(nameEQ.length,c.length);
				}
			}
			return null;
		}
		, 'erase': function(name) {
			methods['create'](name,"",-1);
		}
		, 'destroy' : function( ) {
			return this.each(function(){
				//clean up
			});
		}
	};
		
	$['fn']['BookiCookie'] = function(method) {
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiCookie' );
		}    
	};
	
})(window['jQuery']);
(function($, moment, accounting){
	$['fn']['Booki'] = function(method) {
		var $elem
		, dateFormatString
		, calendarPeriod
		, calendar
		, calendarDays
		, minDate
		, maxDate
		, $optionals
		, $popupCalendar
		, $fromPopupCalendar
		, $toPopupCalendar
		, $selectionLimit
		, $datePickerMinDateSelection
		, $singleDatePickerAddon
		, $selectedDatesContainer
		, $selectedDateInput
		, $timeSlotsDropDownList
		, $timezoneDropDownList
		, $totalsLabel
		, $autoDetect
		, $addToCartButton
		, $checkoutButton
		, contractStartDate
		, bookingDaysMinimum
		, $minimumDaysRequired
		, bookingDaysLimit
		, hours
		, minutes
		, timeSlotsCache
		, decimalPoint
		, thousandsSep
		, currencySymbol
		, ajaxUrl
		, $progressTimeslots
		, currentDate
		, timezone = ''
		, altFormat
		, bookingMode
		, calendarMode
		, autoTimezoneDetection
		, usedSlots
		, exhaustedSlots = []
		, timeSelector
		, $daysExhausted
		, $timeSlotsExhausted
		, $timeSlotsBooked
		, discount
		, bookingMinimumDiscount
		, bookedItemsCount
		, includeBookingPrice
		, $subTotalContainer
		, $subTotalLabel
		, $depositContainer
		, $depositLabel
		, $discountContainer
		, $discountLabel
		, $optionalsCount
		, $progressCascades
		, $deposit
		, calendarCssClasses
		, calendarFirstDay
		, showCalendarButtonPanel
		, displayBookedTimeSlots
		, hourStartInterval
		, minuteStartInterval
		, oldHours
		, oldMinutes
		, oldHourStartInterval
		, oldMinuteStartInterval
		, optionalsBookingMode
		, optionalsListingMode
		, optionalsMinimumSelection
		, highlightSelectedOptionals
		, $cascadingLists
		, defaultCascadingListSelectionLabel
		, hideSelectedDays
		/*method shortcuts*/
		, calculateSelectedDaysCost
		, getCost
		, updateTotal
		, beforeShowDay
		, dateSelected
		, adjustByAvailability
		, datePicker
		, getTimeSlots
		, addSelectedDates
		, selectedDateRemove
		, onSelectedDateRemoveClick
		, disableBackspace
		, testAddToCartButton
		, getSelectedDates
		, timezoneChanged
		, renderTimeslots
		, addDatesFromRange
		, onRangeClose
		, ensureTimeSlotSelection
		, formatDate
		, formatMoment
		, parseDouble
		, cascadingListItemSelected
		, getFormGroup
		, createCascadingList
		, cascadingItemSelected
		, monthSelectionFieldsChanged
		, applyDeposit
		, allowAdvanceBooking
		, minimumDaysCheck
		, timeSlotsChanged
		, bookingDaysLimitCheck
		, validateRangeDiff
		, methods = {
			'init': function(options){
				$elem = this;
				var elem = this[0]
					, settings
					, hasBookings;

				$(document).ready(function(){
					methods['bootstrap']();
					
					$('.booki_parsley_validated').submit(function(e){
						return this.parsley('validate');
					});
					settings = $.extend({}, options);
					calendarPeriod = settings['calendarPeriod'];
					dateFormatString = settings['dateFormat'];
					altFormat = settings['altFormat'];
					calendar = settings['calendar'];
					calendarDays = settings['calendarDays'];
					minDate = settings['minDate'];
					maxDate = settings['maxDate'];
					currencySymbol = settings['currencySymbol'];
					decimalPoint = settings['decimalPoint'];
					thousandsSep = settings['thousandsSep'];
					timezone = settings['timezone'];
					ajaxUrl = settings['ajaxurl'];
					$daysExhausted = $elem.find('.booki-days-exhausted');
					$timeSlotsExhausted = $elem.find('.booki-time-slots-exhausted');
					$timeSlotsBooked = $elem.find('.booki-time-slots-booked');
					$optionals = $elem.find('.booki-optional');
					$popupCalendar = $elem.find('.booki-single-datepicker');
					$fromPopupCalendar = $elem.find('.booki-datepicker-from');
					$toPopupCalendar = $elem.find('.booki-datepicker-to');
					$selectionLimit = $elem.find('.booki-booking-limit');
					$datePickerMinDateSelection = $elem.find('.booki-datepicker-min-date-selection');
					$singleDatePickerAddon = $elem.find('.booki-single-datepicker-group .input-group-addon');
					$selectedDateInput = $elem.find('.booki-selected-date');
					bookingDaysMinimum = settings['bookingDaysMinimum'];
					bookingDaysLimit = settings['bookingDaysLimit'];
					$totalsLabel = $elem.find('.booki-totals-label');
					$timeSlotsDropDownList = $elem.find('select[name="time[]"]');
					$timezoneDropDownList = $elem.find('select[name="timezone"]');
					$autoDetect = $elem.find('input[name="autodetect"]');
					bookingMode = settings['bookingMode'];	
					calendarMode = settings['calendarMode'];
					usedSlots = settings['usedSlots'];
					autoTimezoneDetection = settings['autoTimezoneDetection'];
					timeSelector = settings['timeSelector'];
					discount = settings['discount'];
					bookingMinimumDiscount = settings['bookingMinimumDiscount'];
					bookedItemsCount = settings['bookedItemsCount'];
					includeBookingPrice = settings['includeBookingPrice'];
					calendarCssClasses = settings['calendarCssClasses'];
					calendarFirstDay = settings['calendarFirstDay'];
					showCalendarButtonPanel = settings['showCalendarButtonPanel'];
					displayBookedTimeSlots = settings['displayBookedTimeSlots'];
					$subTotalContainer = $elem.find('.booki-sub-total');
					$depositContainer = $elem.find('.booki-deposit');
					$depositLabel = $elem.find('.booki-deposit-label');
					$subTotalLabel = $elem.find('.booki-sub-total-label');
					$discountContainer = $elem.find('.booki-discount');
					$discountLabel = $elem.find('.booki-discount-label');
					$optionalsCount = $elem.find('.booki_optionals_count');
					$progressTimeslots = $elem.find('.progress.booki-time-progress');
					optionalsBookingMode = settings['optionalsBookingMode'];
					optionalsListingMode = settings['optionalsListingMode'];
					optionalsMinimumSelection = settings['optionalsMinimumSelection'];
					defaultCascadingListSelectionLabel = settings['defaultCascadingListSelectionLabel']
					highlightSelectedOptionals = settings['highlightSelectedOptionals'];
					$cascadingLists = $elem.find('.booki-cascading-list');
					$progressCascades = $elem.find('.progress.booki-progress-cascades');
					hideSelectedDays = settings['hideSelectedDays'];
					$minimumDaysRequired = $elem.find('.booki-minimum-days-required');
					$deposit = $elem.find('input[name="deposit_field"]');
					$checkoutButton = $elem.find('button[name="booki_checkout"][value="1"]');
					$autoDetect.on('click', function(){
						if($(this).is(':checked')){
							getTimeSlots(formatDate($selectedDateInput.val()));
						}
					});
					
					$selectedDatesContainer = $elem.find('.booki-dates');
					$addToCartButton = $elem.find('button[name="booki_add_cart"]');
					
					$selectedDateInput.val(formatDate(minDate));
					if(bookingDaysLimit > 1 && calendarPeriod === 0/*by_day*/){
						$selectedDatesContainer.addClass('booki-outer-border');
					}
					
					//readonly, so disable backspace.
					$popupCalendar.on('keydown', disableBackspace);
					$fromPopupCalendar.on('keydown', disableBackspace);
					$toPopupCalendar.on('keydown', disableBackspace);
					
					$timezoneDropDownList.change(timezoneChanged);
					
					$(usedSlots).each(function(i, item){
						if(item['slotsExhausted']){
							exhaustedSlots.push(item['day']);
						}
					});
					
					hasBookings = datePicker();
					if(!hasBookings){
						$optionals.attr('disabled', true);
						$addToCartButton.attr('disabled', true);
					}else{
						$optionals.change($.proxy(updateTotal, elem));
					}
					
					if(timeSelector === 1/*LISTBOX*/){
						$timeSlotsDropDownList.change($.proxy(timeSlotsChanged, elem));
					}
					$cascadingLists.on('change', cascadingListItemSelected);
					updateTotal();
				});
				
				$(document).ajaxStop(function(e){
					if($progressTimeslots){
						$progressTimeslots.hide();
					}
				});
				$(document).ajaxStop(function(e){
					if($progressCascades){
						$progressCascades.hide();
					}
				});
			}
			, 'timeSlotsChanged': function(e){
				var result = bookingDaysLimitCheck()
					, options = $timeSlotsDropDownList.find('option:selected')
					, i;
				if (!result){
					for(i = 0; i < options.length; i++){
						if (i >= bookingDaysLimit){
							$(options[i]).removeAttr('selected');
						}
					}
				}
				updateTotal();
			}
			, 'bookingDaysLimitCheck': function(diff){
				var dates = getSelectedDates()
					, result = true
					, length = calendarPeriod === 0/*by_day*/ ? dates.length : $timeSlotsDropDownList.find('option:selected').length;
				if(typeof(diff) !== 'undefined'){
					length = diff;
				}
				if(bookingDaysLimit > 1 && length > bookingDaysLimit){
					$selectionLimit.removeClass('hide');
					result = false;
				}else{
					$selectionLimit.addClass('hide');
				}
				return result;
			}
			, 'minimumDaysCheck': function(){
				var dates = getSelectedDates()
					, result = true
					, length = calendarPeriod === 0/*by_day*/ ? dates.length : $timeSlotsDropDownList.find('option:selected').length;
				if(bookingDaysMinimum && length < bookingDaysMinimum){
					result = false;
					$minimumDaysRequired.removeClass('hide');
					$addToCartButton.attr('disabled', true);
				}else{
					$minimumDaysRequired.addClass('hide');
					$addToCartButton.removeAttr('disabled');
				}
				
				if(result){
					testAddToCartButton();
				}
				return result;
			}
			, 'getSelectedDates': function(){
				var val = $selectedDateInput.val()
				, dates = val ? val.split(',') : [];
				return dates;
			}
			, 'addSelectedDates': function(selectedDate){
				var cost = accounting.formatMoney(getCost(selectedDate), currencySymbol, 2, thousandsSep, decimalPoint)
					, $removeButtons = $selectedDatesContainer.find('li')
					, removeButton = '<i class="glyphicon glyphicon-trash"></i>'
					, priceBadge = (includeBookingPrice ? '<span class="badge">' + cost + '</span>' : '')
					, removeButtonContainer = '<span class="booki-remove-button-container">' + priceBadge
					, dates = getSelectedDates()
					, $item
					, exit = false
					, $li
					, $i;
					
				$removeButtons.each(function(i, item){
					var date = $(item).data('value');
					if(date === selectedDate){
						exit = true;
						return false;
					}
				});
				
				if (exit){
					return;
				}
				
				if(dates.length === 1){
					removeButton = '';
				}
				if(bookingDaysLimit === 1 || calendarPeriod === 1/*by_time*/){
					$selectedDatesContainer.empty();
				}
				$selectedDatesContainer.append('<li ' + 'data-value="' + selectedDate + '"><span class="pull-left">' + selectedDate + '</span>' + removeButtonContainer + removeButton + '</span></li>');
				$li = $selectedDatesContainer.find('[data-value="' + selectedDate + '"]');
				if(dates.length > 1){
					$li.find('i').on('click', onSelectedDateRemoveClick);
					$item = $selectedDatesContainer.find('[data-value="' + dates[0] + '"] span.booki-remove-button-container');
					$i = $item.find('i');
					if($i.length === 0){
						$item.append(removeButton);
						$item.find('i').on('click', onSelectedDateRemoveClick);
					}
				}
				window.setTimeout(function(){
					$li.addClass('booki-slide-in');
				}, 50);
			}
			, 'onSelectedDateRemoveClick': function(e){
				var $this = $(this)
					, selectedDate = $this.parent().parent().data('value');
				selectedDateRemove(selectedDate);
				bookingDaysLimitCheck();
				updateTotal();
			}
			, 'selectedDateRemove': function(selectedDate){
				var $item
					, dates = getSelectedDates()
					, i
					, d
					, startDate
					, endDate;
				
				if(dates.length > 1){
					for(i in dates){
						if(dates[i] === selectedDate){
							dates.splice(i, 1);
							break;
						}
					}
					if(!hideSelectedDays){
						$item = $elem.find('[data-value="' + selectedDate + '"]');
						$item.removeClass('booki-slide-in');
						
						(function ($item) {
						  window.setTimeout(function(){
							$item.remove();
						  }, 1000);
						})($item);
					}
				}
				
				$selectedDateInput.val(dates.join());
				
				if(dates.length === 1 && !hideSelectedDays){
					$elem.find('[data-value="' + dates[0] + '"]').find('i').remove();
				}
				
				if(dates.length > 0){
					startDate = formatMoment(dates[0]);
					endDate = formatMoment(dates[dates.length - 1]);
					for(i in dates){
						d = formatMoment(dates[i]);
						if(d.isAfter(endDate)){
							endDate = d;
						}
						if(d.isBefore(startDate)){
							startDate = d;
						}
					}
					
					(function (startDate, endDate) {
						  window.setTimeout(function(){
							if($popupCalendar.length > 0){
								$popupCalendar.datepicker('setDate', formatDate(endDate));
							}else{
								$fromPopupCalendar.datepicker('setDate', formatDate(startDate));
								$toPopupCalendar.datepicker('setDate', formatDate(endDate));
							}
						  }, 50);
					})(startDate, endDate);
				}
			}
			, 'testAddToCartButton': function(){
				var disabled = !$selectedDateInput.val();
				$addToCartButton.prop("disabled", disabled);
				return !disabled;
			}
			, 'formatDate': function(value){
				return moment(value).format(dateFormatString);
			}
			, 'formatMoment': function(value){
				return moment(value, dateFormatString);
			}
			, 'calculateSelectedDaysCost': function(){
				var dates = getSelectedDates()
				, length = dates.length
				, date
				, i
				, total = 0;

				for(i = 0; i < length; i++){
					date = dates[i];
					total += getCost(date);
				}
				return total;
			}
			, 'getCost': function(dateText){
				var i
					, length = calendarDays.length
					, calendarDay;
				for(i = 0; i < length; i++){
					calendarDay = calendarDays[i];
					if(dateText === calendarDay.day){
						return parseDouble(calendarDay.cost);
					}
				}
				return parseDouble(calendar.cost);
			}
			, 'updateTotal': function(){
				testAddToCartButton();
				if(!includeBookingPrice){
					return;
				}
				var total = 0
					, slots = $timeSlotsDropDownList.find('option:selected').length
					, dates = getSelectedDates()
					, selectedDaysCost = calculateSelectedDaysCost()
					, hasDiscount = (discount > 0 && (bookedItemsCount >= bookingMinimumDiscount || bookingMinimumDiscount === 0))
					, count = timeSelector === 1/*LISTBOX*/&& slots > 1 ? slots : dates.length
					, optionalItemCostFormatted
					, totalFormatted
					, cascadingItemCost
					, deposit = applyDeposit()
					, depositValue;
				
				if(!count){
					count = 1;
				}
				$('.booki-cascading-list option').each(function(i, item){
					var $item = $(item)
						, cascadingItemCost = parseDouble($item.data('bookiCost'))
						, parentId = parseInt($item.data('bookiParent'), 10)
						, originalValue = $item.data('bookiOriginalValue');
					if((cascadingItemCost !== 0 && !isNaN(cascadingItemCost)) && parentId === -1){
						if(optionalsBookingMode === 1/*apply to each day or time slot*/){
							cascadingItemCost = cascadingItemCost * count;
							$item.html(originalValue + '&nbsp;&nbsp;x ' +  count + ' = ' + accounting.formatMoney(cascadingItemCost, currencySymbol, 2, thousandsSep, decimalPoint));
						}
						if($item.is(':selected')){
							total += cascadingItemCost;
						}
					}
				});
				
				$optionals.each(function(i, item){
					var $item = $(item)
						, optionalItemCost = parseDouble($item.data('cost'))
						, $parent = $($item.parent())
						, $costContainer = $parent.find('.booki_optionals_cost');
					if(optionalsBookingMode === 1/*apply to each day or time slot*/){
						optionalItemCost = optionalItemCost * count;
						$optionalsCount.html(' x ' + count);
					}
					if($item.is(':checked')){
						total += optionalItemCost;
						if(highlightSelectedOptionals){
							$parent.addClass('active');
						}
					}else {
						if(highlightSelectedOptionals){
							$parent.removeClass('active');
						}
					}
					optionalItemCostFormatted = accounting.formatMoney(optionalItemCost, currencySymbol, 2, thousandsSep, decimalPoint);
					$costContainer.html(optionalItemCostFormatted);
				});
				
				
				if(timeSelector === 1/*LISTBOX*/&& slots > 1){
					selectedDaysCost = selectedDaysCost * slots;
				}
				
				ensureTimeSlotSelection();
				
				total += selectedDaysCost;

				if(hasDiscount || deposit > 0){
					$subTotalContainer.removeClass('hide');
					$subTotalLabel.html(accounting.formatMoney(total, currencySymbol, 2, thousandsSep, decimalPoint));
				}
				else{
					$subTotalContainer.addClass('hide');
				}
				if(hasDiscount){
					$discountContainer.removeClass('hide');
					$discountLabel.html(-discount + '%');
					total -= ((discount / 100) * total);
				}else{
					$discountContainer.addClass('hide');
				}
				if(deposit > 0){
					depositValue = (total/100)*deposit;
					total -= depositValue;
					$depositContainer.removeClass('hide');
					$depositLabel.html(accounting.formatMoney(depositValue, currencySymbol, 2, thousandsSep, decimalPoint));
				}else{
					$depositContainer.addClass('hide');
				}
				totalFormatted = accounting.formatMoney(total, currencySymbol, 2, thousandsSep, decimalPoint);
				$totalsLabel.html(totalFormatted);
				
				if(total > 0){
					$checkoutButton.removeClass('hide');
				}else{
					$checkoutButton.addClass('hide');
				}
				minimumDaysCheck();
			}
			, 'allowAdvanceBooking': function(dateText, resetTime){
				var d = formatMoment(dateText)
					, today = moment()
					, diff;
					if(resetTime){
						today.startOf('day');
					}
				diff = d.diff(today, 'days');
				if(calendar.bookingStartLapse !== 0 && diff < calendar.bookingStartLapse){
					return false;
				}
				return true;
			}
			, 'beforeShowDay': function(dateText) {
				var weekDay = dateText.getDay()
					, selectable = true
					, dates = getSelectedDates()
					, highlight = false
					, formattedDay = formatDate(dateText);
				
				if(!allowAdvanceBooking(formattedDay, true)){
					return [false];
				}
				
				$(calendar.daysExcluded).each(function(i, item){
					if(item === formattedDay){
						selectable = false;
						return false;
					}
				});
				
				if(bookingMode === 1/*1 = Appointment*/){
					$(usedSlots).each(function(i, item){
						if(item['day'] === formattedDay && item['slotsExhausted']){
							selectable = false;
							return false;
						}
					});
				}
				
				if(!selectable){
					return [selectable];
				}
				
				$(calendar.weekDaysExcluded).each(function(i, item){
					if(weekDay === item){
						selectable = false;
						return false;
					}
				});
				
				if(!selectable){
					return [selectable];
				}
				
				$(dates).each(function(i, item){
					if (formattedDay === item){
						highlight = true;
						return false;
					}
				});
				
				if(highlight){
					if(calendarMode === 0/*popup*/ || calendarMode === 1/*inline*/){
						if(!hideSelectedDays){
							addSelectedDates(formattedDay);
						}
						if(calendarPeriod === 1/*BY_TIME*/ && $timeSlotsDropDownList.find('options').length > 0){
							//loading timeslots when picker is created, so only load
							//if timeslotsdropdown already has items i.e. not loading the first time.
							getTimeSlots(formattedDay);
						}
					}
					return [selectable, ' highlighted-day', '..' ]
				}
				
				return [true, formattedDay, ''];
			}
			, 'dateSelected': function(dateText){
				var $this = $(this)
					, dates = getSelectedDates()
					, length = dates.length
					, date
					, cost;
				if(calendarPeriod){
					getTimeSlots(dateText);	
				}
				currentDate = dateText;
				
				if($.inArray(dateText, dates) !== -1){
					selectedDateRemove(dateText);
					bookingDaysLimitCheck();
				}else{
					if(bookingDaysLimit <= 1 || calendarPeriod === 1/*by_time*/){
						$selectedDateInput.val(dateText);
						if(includeBookingPrice){
							cost = accounting.formatMoney(getCost(dateText), currencySymbol, 2, thousandsSep, decimalPoint)
							$singleDatePickerAddon.html(cost);
						}
					}
					else if (length < bookingDaysLimit){
						dates.push(dateText);
						$selectedDateInput.val(dates.join());
						if(!hideSelectedDays){
							addSelectedDates(dateText);
						}
					}else{
						if(length > 0){
							(function (dates, length) {
							  window.setTimeout(function(){
								$popupCalendar.datepicker('setDate', dates[length - 1])
							  }, 50);
							})(dates, length);
							if(length === bookingDaysLimit){
								$selectionLimit.removeClass('hide');
							}
						}
					}
				}
				updateTotal();
			}
			, 'disableBackspace': function(e){
				if(e.keyCode === 8){
					e.preventDefault();
				}
			}
			, 'adjustByAvailability': function(startDate){
				var usedSlot
					, usedSlotDay
					, diff
					, i
					, j;
					
				usedSlots.sort(function(a,b){
					return formatMoment(a['day']) - formatMoment(b['day']);
				});
				
				for(j in usedSlots){
					usedSlot = usedSlots[j];
					usedSlotDay = formatMoment(usedSlot['day']);
					diff = startDate.diff(usedSlotDay, 'days');
					if((startDate.isBefore(usedSlotDay) && diff === 1) || startDate.isSame(usedSlotDay)){
						if(usedSlot['slotsExhausted']){
							startDate = startDate.add('days', 1);
						}
					}
				}
				
				return startDate;
			}
			, 'applyDeposit': function(){
				var dates = getSelectedDates()
					, dateText
					, i
					, j
					, length = calendarDays.length
					, calendarDay
					, cd
					, now = moment()
					, diff
					, deposit;
				
				dates.sort(function(a,b){
					return formatMoment(a) - formatMoment(b);
				});
				
				for(i = 0; i < dates.length; i++){
					dateText = dates[i];
					for(j = 0; j < calendarDays.length; j++){
						calendarDay = calendarDays[j];
						if(dateText === calendarDay.day){
							cd = formatMoment(calendarDay.day);
							diff = cd.diff(now, 'days');
							if(diff > calendarDay.minNumDaysDeposit){
								deposit = calendarDay.deposit;
								break;
							}
						}
					}
					if(deposit > 0){
						break;
					}
				}
				
				if(!deposit){
					cd = formatMoment(dates[0]);
					diff = cd.diff(now, 'days');
					if(calendar.minNumDaysDeposit === 0 || diff > calendar.minNumDaysDeposit){
						deposit = calendar.deposit;
					}
				}
				
				$deposit.val(deposit);
				
				return deposit;
			}
			, 'datePicker': function(){
				var bookedDate
					, startDate = formatMoment(minDate)
					, endDate = formatMoment(maxDate)
					, contractEndDate
					, cost
					, args;
				
				adjustByAvailability(startDate);
				
				while((startDate.isBefore(endDate) || startDate.isSame(endDate) ) && calendar.weekDaysExcluded.indexOf(startDate.weekday() ) > -1){
					startDate.add('days', 1);
					adjustByAvailability(startDate);
				}
				
				calendar.daysExcluded.sort(function(a,b){
					return formatMoment(a) - formatMoment(b);
				});
				
				while((startDate.isBefore(endDate) || startDate.isSame(endDate) ) && calendar.daysExcluded.indexOf(formatDate(startDate)) > -1){
					startDate.add('days', 1);
					adjustByAvailability(startDate);
				}
				
				while(!allowAdvanceBooking(startDate, true) && startDate.isBefore(endDate)){
					startDate.add('days', 1);
					adjustByAvailability(startDate);
				}
				
				if(startDate.isAfter(endDate)){
					$selectedDateInput.val('');
					$daysExhausted.removeClass('hide');
					$timeSlotsExhausted.removeClass('hide');
					$popupCalendar.addClass('booki-readonly-field');
					$fromPopupCalendar.addClass('booki-readonly-field');
					$toPopupCalendar.addClass('booki-readonly-field');
					$timeSlotsDropDownList.attr('disabled', true);
					if(includeBookingPrice){
						cost = accounting.formatMoney(0, currencySymbol, 2, thousandsSep, decimalPoint)
						$singleDatePickerAddon.html(cost);
					}
					return false;
				}
				
				contractStartDate = formatDate(startDate);
				$selectedDateInput.val(contractStartDate);
				args = {
					'dateFormat': altFormat
					, 'defaultDate': startDate._d
					, 'minDate': startDate._d
					, 'maxDate': endDate._d
					, 'beforeShowDay': beforeShowDay
					, 'hideIfNoPrevNext': true
					, 'showButtonPanel': showCalendarButtonPanel
				};

				if(calendarFirstDay < 7){
					args['firstDay'] = calendarFirstDay;
				}
				
				if($popupCalendar.length > 0){
					args['onSelect'] = dateSelected;
					$popupCalendar.datepicker(args);
					if(includeBookingPrice){
						cost = accounting.formatMoney(getCost(contractStartDate), currencySymbol, 2, thousandsSep, decimalPoint)
						$singleDatePickerAddon.html(cost);
					}
					$popupCalendar.datepicker('setDate', contractStartDate);
					$selectedDateInput.val(contractStartDate);
					if(calendarPeriod === 1/*BY_TIME*/){
						getTimeSlots(contractStartDate);
					}
					if(calendarCssClasses){
						$popupCalendar.addClass(calendarCssClasses);
					}
				}
				
				if ($fromPopupCalendar.length > 0){
					args['onClose'] = function( selectedDate ) {
						var dates = getSelectedDates();
						if($toPopupCalendar.length > 0){
							$toPopupCalendar.datepicker( 'option', 'minDate', selectedDate );
						}
						//only if we have a range, go.
						if(dates.length > 0){
							onRangeClose();
						}
					}
					$fromPopupCalendar.datepicker(args);
					$fromPopupCalendar.datepicker('setDate', contractStartDate);
					if(calendarCssClasses){
						$fromPopupCalendar.addClass(calendarCssClasses);
					}
				}
				
				if($toPopupCalendar.length > 0){
					args['onClose'] = function(dateText) {
						if($fromPopupCalendar.length > 0){
							$fromPopupCalendar.datepicker( 'option', 'maxDate', dateText);
						}
						onRangeClose();
					}
					$toPopupCalendar.datepicker(args);
					$toPopupCalendar.datepicker('setDate', contractStartDate);
					if(calendarCssClasses){
						$toPopupCalendar.addClass(calendarCssClasses);
					}
				}
				
				addDatesFromRange();
				
				if(calendarCssClasses){
					$('#ui-datepicker-div').addClass(calendarCssClasses);
				}
				
				return true;
			}
			, 'onRangeClose': function(){
				addDatesFromRange();
			}
			, 'timezoneChanged': function(e){
				var $this = $(this)
					, selectedValue = $(this).find(':selected').val();
					
				getTimeSlots(currentDate, selectedValue);
				timezone = selectedValue;
			}
			, 'getTimeSlots': function(day, tz){
				var i
					, length = calendarDays.length
					, calendarDay
					, timeExcluded
					, result;
				
				currentDate = day;
				
				$timeSlotsBooked.addClass('hide');
				
				if(typeof(tz) === 'undefined'){
					result = $().BookiTimezoneControlState('readState');
					tz = result['selectedZone'];
					if(!autoTimezoneDetection){
						tz = timezone;
					}
				}
				
				hours = calendar['hours'];
				minutes = calendar['minutes'];
				hourStartInterval = calendar['hourStartInterval'];
				minuteStartInterval = calendar['minuteStartInterval'];
				
				timeExcluded = calendar['timeExcluded'];
				for(i = 0; i < length; i++){
					calendarDay = calendarDays[i];
					if(calendarDay['day'] == day){
						hours = calendarDay['hours'];
						minutes = calendarDay['minutes'];
						hourStartInterval = calendarDay['hourStartInterval'];
						minuteStartInterval = calendarDay['minuteStartInterval'];
						timeExcluded = calendarDay['timeExcluded'];
						break;
					}
				}
				
				if(((hours === oldHours && minutes === oldMinutes) && 
								(hourStartInterval === oldHourStartInterval && 
											minuteStartInterval === oldMinuteStartInterval))&& 
														timezone === tz){
					renderTimeslots(timeExcluded);
					return;
				}
				
				oldHours = hours;
				oldMinutes = minutes;
				oldHourStartInterval = hourStartInterval;
				oldMinuteStartInterval = minuteStartInterval;
				
				timezone = tz;
				$progressTimeslots.removeClass('hide');
				$progressTimeslots.show();
				
				(function(timeExcluded){
					$.post(ajaxUrl, {
						'model': {
							'hours': hours
							, 'minutes': minutes
							, 'hourStartInterval': hourStartInterval
							, 'minuteStartInterval': minuteStartInterval
							, 'enableSingleHourMinuteFormat': calendar.enableSingleHourMinuteFormat
							, 'timezone': timezone
						}
						, 'action': 'booki_getTimeSlots'}
						, function(data) {
							var r = $.parseJSON(data)
								, result = r['result']
								, timezoneInfo = result ? result['timezoneInfo'] : null;
							
							timeSlotsCache = result ? result['timeslots'] : null;
							
							if(!timeSlotsCache){
								return;
							}
							renderTimeslots(timeExcluded);
					});
				})(timeExcluded);
			}
			, 'renderTimeslots': function(timeExcluded){
				var i
					, val
					, state
					, option
					, match
					, s
					, cd
					, length = timeSlotsCache.length
					, now = moment()
					, validSlots = [];
				$timeSlotsDropDownList.empty();
				$timeSlotsDropDownList.prop('disabled', false);
				for(i = 0; i < length;i++){
					val = timeSlotsCache[i]['value'].split(',');
					state = '';
					if(timeExcluded.indexOf(val[0]) !== -1){
						if(!displayBookedTimeSlots){
							continue;
						}
						state = ' disabled class="booki-option-disabled"';
					}
					validSlots.push(timeSlotsCache[i]);
					s = timeSlotsCache[i]['rawFrom'].split(':');
					cd = formatMoment(currentDate).hour(s[0]).minute(s[1]);

					if(cd < now){
						continue;
					}
					if(allowAdvanceBooking(cd, false)){
						option = '<option value="' + val + '"' + state +'>' + timeSlotsCache[i]['text'] + '</option>';
						$timeSlotsDropDownList.append(option);
					}
				}
				if($timeSlotsDropDownList.find('option').length === 0 || $timeSlotsDropDownList.find('option:not(:disabled)').length === 0){
					$timeSlotsDropDownList.prop('disabled', true);
					$timeSlotsBooked.removeClass('hide');
				}
				ensureTimeSlotSelection();
			}
			, 'ensureTimeSlotSelection': function(){
				var match
					, selections;
				if(timeSelector === 1/*LISTBOX*/){
					selections = $timeSlotsDropDownList.find('option:selected');
					if(selections.length > 0){
						return;
					}
					match = $timeSlotsDropDownList.find('option').not('[disabled]');
					if(match.length > 0){
						$(match[0]).attr('selected', true);
					}
				}
			}
			, 'addDatesFromRange': function(){
				var dates = []
					, date
					, fromDate = formatMoment(contractStartDate)
					, toDate = formatMoment(contractStartDate)
					, diff
					, i
					, months
					, result;
				
				if (calendarMode === 2/*range*/ || calendarMode === 3/*nextDayCheckout*/){
					if($fromPopupCalendar.length > 0){
						fromDate = moment($fromPopupCalendar.datepicker('getDate'));
					}
					if($toPopupCalendar.length > 0){
						toDate = moment($toPopupCalendar.datepicker('getDate'));
					}
				}else if (bookingDaysLimit > 1 && calendarPeriod === 0/*by_day*/){
					if($toPopupCalendar.length > 0){
						toDate = moment($toPopupCalendar.datepicker('getDate'));
					}
				}else{
					return;
				}
				
				if(calendarMode === 3/*next day checkout*/){
					diff = toDate.diff(fromDate, 'days');
				}else{
					diff = toDate.diff(fromDate, 'days') + 1;
				}
				diff = validateRangeDiff(fromDate, diff);
				result = bookingDaysLimitCheck(diff);
				if(!result){
					dates = getSelectedDates();
					date = dates[dates.length - 1];
					$toPopupCalendar.datepicker('setDate', date);
					$toPopupCalendar.datepicker( 'option', 'minDate', date);
					return;
				}
				
				$selectedDatesContainer.empty();
				
				if(diff === 0 && calendarMode === 3/*next day checkout*/){
					dates.push(fromDate.format(dateFormatString));
				}else{
					//add also the current selection
					fromDate = fromDate.subtract('days', 1);
					for(i = 0; i < diff; i++){
						fromDate = fromDate.add('days', 1);
						date = fromDate.format(dateFormatString);
						if(calendar.weekDaysExcluded.indexOf(fromDate.weekday()) > -1){
							continue;
						}
						if(calendar.daysExcluded.indexOf(date) > -1){
							continue;
						}
						if(exhaustedSlots.indexOf(date) > -1){
							continue;
						}
						dates.push(date);
					}
				}
				$selectedDateInput.val(dates.join());
				if(!hideSelectedDays){
					for(date in dates){
						addSelectedDates(dates[date]);
					}
				}
				
				updateTotal();
			}
			, 'validateRangeDiff': function(fromDate, diff){
				var i
					, date
					, result = 0;
				fromDate = fromDate.clone().subtract('days', 1);
				for(i = 0; i < diff; i++){
					fromDate = fromDate.add('days', 1);
					date = fromDate.format(dateFormatString);
					if(calendar.weekDaysExcluded.indexOf(fromDate.weekday()) > -1){
						continue;
					}
					if(calendar.daysExcluded.indexOf(date) > -1){
						continue;
					}
					if(exhaustedSlots.indexOf(date) > -1){
						continue;
					}
					++result;
				}
				return result;
			}
			, 'cascadingListItemSelected': function(e){
				var target = e.srcElement || e.target
					, $optionItem = $('option:selected', target)
					, itemId = parseInt($optionItem.val(), 10)
					, parentId = $optionItem.data('bookiParent')
					, placeHolder = $(target).data('bookiPlaceholder');
				
				updateTotal();
				
				if(typeof(parentId) === 'undefined' || parentId === ''){
					return;
				}
				
				parentId = parseInt(parentId, 10);
				
				if(parentId === -1){
					return;
				}
				
				$progressCascades.removeClass('hide');
				$progressCascades.show();
				
				(function(parentId, placeHolder){
					$.post(ajaxUrl, {
						'model': { 'id': parentId }
						, 'action': 'booki_readCascadingItemsByListId'}
						, function(data) {
							var result = $.parseJSON(data);
							createCascadingList(result, placeHolder);
					});
				})(parentId, placeHolder);
			}
			, 'createCascadingList': function(cascadingList, placeHolder){
				var i
					, item
					, cascadingItems = cascadingList['cascadingItems']
					, name = 'booki_cascadingdropdown_' + cascadingList['id']
					, $selectList = $('<select></select>').attr('id', name).attr('name', name).addClass('form-control booki-cascading-list booki_parsley_validated')
					, $option
					, formGroup = getFormGroup(name, cascadingList['label'])
					, containerId = '#' + name + '_container'
					, formattedValue;
				
				if(cascadingList['isRequired']){
					$selectList.attr('data-parsley-trigger', 'change');
					$selectList.attr('data-parsley-required', true);
				}
				
				$option = $('<option></option>').attr('value', '').html(defaultCascadingListSelectionLabel);
				$selectList.append($option);
				
				for(i in cascadingItems){
					item = cascadingItems[i];
					formattedValue = item['value'];
					if(item['cost'] > 0 && item['parentId'] === -1){
						formattedValue += '&nbsp;&nbsp;' + accounting.formatMoney(item['cost'], currencySymbol, 2, thousandsSep, decimalPoint)
					}
					$option = $('<option></option>').attr('value', item['id']).attr('data-booki-parent', item['parentId']).attr('data-booki-cost', item['cost']).attr('data-booki-original-value', item['value']).html(formattedValue);
					$selectList.append($option);
				}
				$(placeHolder).empty().html(formGroup);
				$(containerId).append($selectList);
				$selectList.on('change', cascadingItemSelected);
				updateTotal();
			}
			, 'getFormGroup': function(name, label){
				var result = '<div class="form-group">';
					result +=	'<label class="col-lg-4 control-label" for="' + name + '">';
					result +=	label;
					result +=	'</label>';
					result +=	'<div class="col-lg-8">';
					result += 		'<div id="' + name + '_container"></div>';
					result += 	'</div>';
					result += 	'</div>';
				return result;
			}
			, 'cascadingItemSelected': function(e){
				updateTotal();
			}
			, 'parseDouble': function(value){
				return parseFloat((''+value).replace(/,/g,''));
			}
			, 'bootstrap': function(){
				formatDate = methods['formatDate'];
				formatMoment = methods['formatMoment'];
				calculateSelectedDaysCost = methods['calculateSelectedDaysCost'];
				getCost = methods['getCost'];
				updateTotal = methods['updateTotal'];
				beforeShowDay = methods['beforeShowDay'];
				dateSelected = methods['dateSelected'];
				datePicker = methods['datePicker'];
				adjustByAvailability = methods['adjustByAvailability'];
				getTimeSlots = methods['getTimeSlots'];
				addSelectedDates = methods['addSelectedDates'];
				selectedDateRemove = methods['selectedDateRemove'];
				onSelectedDateRemoveClick = methods['onSelectedDateRemoveClick'];
				testAddToCartButton = methods['testAddToCartButton'];
				getSelectedDates = methods['getSelectedDates'];
				timezoneChanged = methods['timezoneChanged'];
				renderTimeslots = methods['renderTimeslots'];
				addDatesFromRange = methods['addDatesFromRange'];
				onRangeClose = methods['onRangeClose'];
				disableBackspace = methods['disableBackspace'];
				ensureTimeSlotSelection = methods['ensureTimeSlotSelection'];
				parseDouble = methods['parseDouble'];
				cascadingListItemSelected = methods['cascadingListItemSelected'];
				getFormGroup = methods['getFormGroup'];
				createCascadingList = methods['createCascadingList'];
				cascadingItemSelected = methods['cascadingItemSelected'];
				monthSelectionFieldsChanged = methods['monthSelectionFieldsChanged'];
				applyDeposit = methods['applyDeposit'];
				allowAdvanceBooking = methods['allowAdvanceBooking'];
				minimumDaysCheck = methods['minimumDaysCheck'];
				timeSlotsChanged = methods['timeSlotsChanged'];
				bookingDaysLimitCheck = methods['bookingDaysLimitCheck'];
				validateRangeDiff = methods['validateRangeDiff'];
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.Booki' );
		}    
	};
	
})(window['jQuery'], window['moment'], window['accounting']);
(function($, moment){
	$['fn']['BookiBookingWizard'] = function(method) {
		var projectId
		, $bookingButton
		, $nextButton
		, $backButton
		, $step1
		, $tabs
		, $el
		, $errorContainer
		, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings;

				$(document).ready(function(){
					$el = $this;
					settings = $.extend({}, options);
					
					if(settings['projectId'] !== undefined){
						projectId = settings['projectId'];
					}
					
					if(settings['bookingButton']){
						$bookingButton = typeof(settings['bookingButton']) === 'string' ? 
											$this.find(settings['bookingButton']) : settings['bookingButton'];
					}
					
					if(settings['nextButton']){
						$nextButton = typeof(settings['nextButton']) === 'string' ? 
											$this.find(settings['nextButton']) : settings['nextButton'];
					}
					
					if(settings['backButton']){
						$backButton = typeof(settings['backButton']) === 'string' ? 
											$this.find(settings['backButton']) : settings['backButton'];
					}
					
					if(settings['step1']){
						$step1 = typeof(settings['step1']) === 'string' ? 
											$this.find(settings['step1']) : settings['step1'];
					}
					
					if(settings['tabs']){
						$tabs = typeof(settings['tabs']) === 'string' ? 
											$this.find(settings['tabs']) : settings['tabs'];
					}
					
					$errorContainer = $(settings['errorContainer']);
					if($tabs.length < 2){
						if($step1){
							$step1.tab('show');
						}
						$nextButton.addClass('hide');
						$backButton.addClass('hide');
						return;
					}
					
					$nextButton.on('click', methods['wizardButtonClick']);
					$backButton.on('click', methods['wizardButtonClick']);
					
					methods['toggleButtons'](0);
					
					$tabs.on('click', methods['tabsClick']);
					$('[data-toggle=tooltip]').tooltip();
				});
			}
			, 'validate': function(){
				if(!methods['isValid']()){
					$errorContainer.removeClass('hide');
				}else{
					$errorContainer.addClass('hide');
				}
			}
			, 'tabsClick': function (e) {
				var $activeTab = $(this);
				e.preventDefault();
				methods['validate']();
				methods['toggleButtons']($activeTab.data('step'));
			}
			, 'toggleButtons': function (selectedStep){
				if(parseInt(selectedStep, 10) === 0){
					$bookingButton.addClass('hide');
					$nextButton.removeClass('hide');
					$backButton.addClass('hide');
				}else{
					$bookingButton.removeClass('hide');
					$nextButton.addClass('hide');
					$backButton.removeClass('hide');
				}
				$('.booki' + projectId + '.nav.nav-tabs a[data-step="' + selectedStep + '"]').tab('show');
			}
			, 'wizardButtonClick': function (e){
				var $currentTarget = $(e.currentTarget);
				methods['validate']();
				methods['toggleButtons']($currentTarget.data('step'));
			}
			, 'isValid': function(){
				var isValid = true
					, $validators = $el.find('.booki_parsley_validated');
				$validators.each(function(){
					var $elem = $(this),
						result;
					if (!$elem.is(':visible') || $elem.is(':disabled')){
						return true;
					}
					result = $elem.parsley().validate(true);
					if(result !== null && (typeof(result) === 'object' && result.length > 0)){
						isValid = false;
					}
				});
				return isValid;
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiBookingWizard' );
		}    
	};
	
})(window['jQuery'], window['moment']);
(function($){
	$(document).ready(function(){
		$('[data-toggle=tooltip]').tooltip();
		$('[data-toggle=popover]').popover({container: 'body'});
	});
})(window['jQuery']);
(function($){
	$['fn']['BookiModalPopup'] = function(method) {
		var $this
			, methods = {
			'init': function(options){
				var settings
					, $confirmButton;
				$this = this;
				$(document).ready(function(){
					settings = $.extend({}, options);
					$confirmButton = $this.find('.booki-confirm');
					$this.on('show.bs.modal', function (e) {
						var $target = $(e.relatedTarget)
							, id = $target.data('bookiId');
						$confirmButton.attr('value', id);
					});
				});
			}
			, 'destroy' : function( ) {
				$this.off();
			}
		};
		
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiModalPopup' );
		}
	};
	
})(window['jQuery']);
(function($, moment){
	$['fn']['BookiSearchFilter'] = function(method) {
		var methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, $fromDate
					, $toDate
					, fromDefaultDate = null
					, toDefaultDate = null
					, dateFormat
					, altFormat
					, argsFrom
					, argsTo
					, calendarFirstDay
					, showCalendarButtonPanel
					, calendarCssClasses;

				$(document).ready(function(){
					settings = $.extend({}, options);
					if(settings['fromDefaultDate']){
						fromDefaultDate = moment(settings['fromDefaultDate']);
					}
					if(settings['toDefaultDate']){
						toDefaultDate = moment(settings['toDefaultDate']);
					}
					$fromDate = $this.find(settings['fromDateElem']);
					$toDate = $this.find(settings['toDateElem']);
					calendarFirstDay = settings['calendarFirstDay'];
					showCalendarButtonPanel = settings['showCalendarButtonPanel'];
					calendarCssClasses = settings['calendarCssClasses'];
					
					altFormat = settings['altFormat'];
					dateFormat = settings['dateFormat'];
					
					argsFrom = {
						'dateFormat': altFormat
						, 'changeMonth': true
						, 'changeYear': true
						, 'showButtonPanel': showCalendarButtonPanel
					};
					
					if(fromDefaultDate){
						argsFrom['defaultDate'] = fromDefaultDate._d;
					}
					argsTo = {
						'dateFormat': altFormat
						, 'changeMonth': true
						, 'changeYear': true
						, 'showButtonPanel': showCalendarButtonPanel
					}
					if(toDefaultDate){
						argsTo['defaultDate'] = toDefaultDate._d;
					}
					if(calendarFirstDay < 7){
						argsTo['firstDay'] = calendarFirstDay;
						argsFrom['firstDay'] = calendarFirstDay;
					}
					if($fromDate.length > 0){
						$fromDate.datepicker(argsFrom);
						if(calendarCssClasses){
							$fromDate.addClass(calendarCssClasses);
						}
					}
					
					if($toDate.length > 0){
						$toDate.datepicker(argsTo);
						if(calendarCssClasses){
							$toDate.addClass(calendarCssClasses);
						}
					}
					
					if(calendarCssClasses){
						$('#ui-datepicker-div').addClass(calendarCssClasses);
					}
				});
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiSearchFilter' );
		}    
	};
	
})(window['jQuery'], window['moment']);
(function($, jstz){
	$['fn']['BookiTimezoneControl'] = function(method) {
		var $headerCaption
			, $regionSelect
			, $timezoneContainer
			, $timezoneSelect
			, $autoDetect
			, $progressBar
			, $loadOnStart
			, $collapseTimezone
			, $timezoneManualSelection
			, ajaxUrl
			, cookieName = 'BOOKITIMEZONE'
			, flag
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, result;

				$(document).ready(function(){
					settings = $.extend({}, options);
					$regionSelect = $this.find(settings['region']);
					$timezoneContainer = $this.find(settings['timezone']);
					$timezoneSelect = $timezoneContainer.find('select[name="timezone"]');
					$autoDetect = $this.find('input[name="autodetect"]');
					$headerCaption = $this.find(settings['headerCaption']);
					$loadOnStart = $(settings['loadOnStart']);
					$timezoneManualSelection = $(settings['timezoneManualSelection']);

					$collapseTimezone = $this.find('.collapseTimezone');
					$progressBar = $this.find('.progress');
					ajaxUrl = settings['ajaxurl'];
					
					$timezoneSelect.on('change', function(){
						if(flag){
							flag = false;
							return;
						}
						var $sel = $(this).find(':selected');
						$headerCaption.html($sel.text());
						$autoDetect.prop('checked', false);
						methods['saveState'](false, $sel.val());
						$collapseTimezone.collapse('hide');
					});

					$regionSelect.on('change', function(){
						var value = $(this).val();
						methods['timezoneChoice'](value, null);
					});

					if($autoDetect.length > 0){
						$autoDetect.on('click', function(){
							var isChecked = $(this).is(':checked')
								, result
								, selectedZone;
							if( isChecked ) {
								selectedZone = methods['guessTimezone'](null, true);
							}else{
								result = methods['readState']();
								selectedZone = result['selectedZone'];
							}
							methods['saveState'](isChecked, selectedZone);
						});
					}
					result = methods['getDefaultTimezone']();
					$autoDetect.prop('checked', result['autoRun']);
					if($timezoneManualSelection.length > 0){
						result['selectedZone'] = $timezoneManualSelection.val();
						$this.find('.autodetect').addClass('hide');
					}
					
					methods['saveState'](result['autoRun'], result['selectedZone']);
					
					if($headerCaption.length > 0){
						$headerCaption[0].title = '';
						$headerCaption.html(result['selectedZone']);
					}
				});
				
				$(document).ajaxStop(function(e){
					if($progressBar){
						$progressBar.addClass('hide');
					}
				});
			}
			, 'getDefaultTimezone': function(){
				var state = $().BookiCookie('read', cookieName)
					, values
					, zone;
				if(!state){
					zone = jstz['determine']()
					return {
						'autoRun': true
						, 'selectedZone': zone['name']()
					};
				}
				values = state.split(':');
				return {
					'autoRun': values[0] === 'true'
					, 'selectedZone': values[1] === 'null' ? null : values[1]
				};
			}
			, 'parseSavedState': function(state, timeZone){
				var values;
				if(!state){
					return {
						'autoRun': true
						, 'selectedZone': null
					};
				}
				values = state.split(':');
				return {
					'autoRun': values[0] === 'true'
					, 'selectedZone': values[1] === 'null' ? null : values[1]
				};
			}
			, 'readState': function(saveState){
				if($timezoneManualSelection.length > 0){
					return methods['parseSavedState']();
				}
				saveState = typeof(saveState) === 'undefined' ? true : saveState;
				var result = $().BookiCookie('read', cookieName)
					, newValue = $autoDetect.is(':checked')
					, selectedZone;
				if(!result && saveState){
					selectedZone = methods['guessTimezone']();
					result = methods['saveState'](newValue, selectedZone);
				}
				return methods['parseSavedState'](result);
			}
			, 'saveState': function(newValue, timezoneValue){
				if($timezoneManualSelection.length > 0){
					return methods['parseSavedState']();
				}
				var result = $().BookiCookie('read', cookieName)
					, value = newValue + ':' + timezoneValue;
				if(value !== result){
					$().BookiCookie('erase', cookieName);
					$().BookiCookie('create', cookieName, value, 30);
					return value;
				}
				return result;
			}
			, 'guessTimezone': function(selectedZone, triggerChange){
				var guessedTimezone = jstz['determine']()
					, region;
				selectedZone = (typeof(selectedZone) === 'undefined' || selectedZone === null) ? guessedTimezone['name']() : selectedZone;
				if(!selectedZone){
					return;
				}
				region = selectedZone.substr(0, selectedZone.indexOf('/'));
				if(!region){
					region = selectedZone;
				}
				methods['timezoneChoice'](region, selectedZone, triggerChange);
				return selectedZone;
			}
			, 'timezoneChoice': function(region, selectedZone, triggerChange){
				if(region === '-1'){
					$timezoneContainer.addClass('hide');
					$timezoneSelect.empty();
					return;
				}
				$progressBar.removeClass('hide');
				$.post(ajaxUrl, {
					'model': {
						'region': region
						, 'selectedZone': selectedZone
					}
					, 'action': 'booki_timezoneChoice'
				}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result']
						, options = result ? result['options'] : null
						, $option;
					if(options){
						if($regionSelect.length > 0){
							$regionSelect.val(region);
						}
						if($timezoneContainer.length > 0){
							$timezoneSelect.html(options);
							$timezoneContainer.removeClass('hide');
							if(selectedZone){
								$option = $timezoneSelect.find(":selected");
								if($headerCaption.length > 0){
									$headerCaption[0].title = $option.text();
									$headerCaption.html($option.val());
								}
							}
							if($loadOnStart.length > 0 && triggerChange){
								window.setTimeout(function(){
									flag = true;
									$timezoneSelect.change();
								}, 500);
								return;
							}
							if(selectedZone){
								$collapseTimezone.collapse('hide');
							}
						}
					}
				});
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiTimezoneControl' );
		}    
	};
	
})(window['jQuery'], window['jstz']);
(function($, jstz){
	$['fn']['BookiTimezoneControlState'] = function(method) {
		var cookieName = 'BOOKITIMEZONE'
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, result
					, selectedZone
					, url;

				settings = $.extend({}, options);
				result = methods['readState']();
				$this.each(function(){
					var $a = $(this);
					url = $a.prop('href');
					url = methods['updateQueryString'](url, 'timezone', result['selectedZone']);
					$a.prop('href', url);
				});
			}
			, 'parseSavedState': function(state){
				var values;
				if(!state){
					return {
						'autoRun': true
						, 'selectedZone': null
					};
				}
				values = state.split(':');
				return {
					'autoRun': values[0] === 'true'
					, 'selectedZone': values[1] === 'null' ? null : values[1]
				};
			}
			, 'readState': function(){
				var result = $().BookiCookie('read', cookieName)
					, newValue = true
					, selectedZone;
				if(!result){
					selectedZone = methods['guessTimezone']();
					result = methods['saveState'](newValue, selectedZone);
				}
				return methods['parseSavedState'](result);
			}
			, 'saveState': function(newValue, timezoneValue){
				var result = $().BookiCookie('read', cookieName)
					, value = newValue + ':' + timezoneValue;
				if(value !== result){
					$().BookiCookie('erase', cookieName);
					$().BookiCookie('create', cookieName, value, 30);
					return value;
				}
				return result;
			}
			, 'guessTimezone': function(){
				var guessedTimezone = jstz['determine']();
				return guessedTimezone['name']();
			}
			, 'updateQueryString': function(url, param, value){
				var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))')
					, parts = url.toString().split('#')
					, hash = parts[1]
					, qstring = /\?.+$/
					, newURL;
					
				url = parts[0];
				newURL = url;
				
				if (val.test(url))
				{
					newURL = url.replace(val, '$1' + param + '=' + value);
				}
				else if (qstring.test(url))
				{
					newURL = url + '&' + param + '=' + value;
				}
				else
				{
					newURL = url + '?' + param + '=' + value;
				}
				if (hash)
				{
					newURL += '#' + hash;
				}
				return newURL;
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiTimezoneControlState' );
		}   
	};
	
})(window['jQuery'], window['jstz']);
(function($){	
	$['fn']['BookiUserInfo'] = function(method) {
		var $userEmailInfo
			, $progressBar
			, $userIdField
			, ajaxUrl
			, $triggerButton
			, successCallback
			, userFoundMessage
			, userNotFoundMessage
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, $userEmailTextbox = $('#useremail');

				$(document).ready(function(){
					settings = $.extend({}, options);
					if(settings['triggerButton']){
						$triggerButton = typeof(settings['triggerButton']) === 'string' ? 
											$this.find(settings['triggerButton']) : settings['triggerButton'];
					}
					if(settings['userIdField']){
						$userIdField = typeof(settings['userIdField']) === 'string' ?
											$this.find(settings['userIdField']) : settings['userIdField'];
					}
					if(settings['userFoundMessage']){
						userFoundMessage = settings['userFoundMessage'];
					}
					if(settings['userNotFoundMessage']){
						userNotFoundMessage = settings['userNotFoundMessage'];
					}
					successCallback = settings['success'];
					ajaxUrl = settings['ajaxUrl'];
					$userEmailInfo = $('.useremail-info');
					$progressBar = $('.progress.booki-useremail');

					if($triggerButton){
						$triggerButton.click(function(){
							methods['getUserInfo']($userEmailTextbox.val());
							return false;
						});
						return;
					}
					$userEmailTextbox.change(function(){
						methods['getUserInfo']($(this).val());
					});
				});
				
				$(document).ajaxStop(function(e){
					$progressBar.addClass('hide');
				});
			}
			, 'getUserInfo': function(email){
				var result = $( '#useremail' ).parsley().validate(true);
				if(result !== true){
					return;
				}
				$progressBar.removeClass('hide');
				$.post(ajaxUrl, {
					'model': {
						'email': email
					}
					, 'action': 'booki_getUserByEmail'
				}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result']
						, userName
						, firstName
						, lastName
						, profilePageUrl;
					if(result){
						userName = result['userName'];
						firstName = result['firstName'];
						lastName = result['lastName'];
						profilePageUrl =  result['profilePageUrl'];
						
						if($userIdField){
							$userIdField.val(result['id']);
						}
						
						if(firstName){
							userName = firstName;
							if(lastName){
								userName += (' ' + lastName);
							}
						}
						
						$userEmailInfo.removeClass('hide');
						if(userName){
							$userEmailInfo.html(userFoundMessage + ': ' + userName);
							if(successCallback){
								successCallback();
							} else if($triggerButton){
								$triggerButton.off();
								$triggerButton.click();
							}
						}else{
							$userEmailInfo.html(userNotFoundMessage);
						}
					}
				});
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiUserInfo' );
		}    
	};
	
})(window['jQuery']);
