(function(url, $, moment, accounting){
	Booki.CalendarView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save'
			, 'click .cancel.btn': 'discardChanges'
			, 'change input': 'inputChanged'
			, 'change select[name="hours"]': 'inputChanged'
			, 'change select[name="minutes"]': 'inputChanged'
			, 'change select[name="hourStartInterval"]': 'inputChanged'
			, 'change select[name="minuteStartInterval"]': 'inputChanged'
			, 'click .remove-day.btn': 'removeSelectedDays'
			, 'click .remove-time.btn': 'removeSelectedTime'
			, 'click .reset-day.btn': 'resetExcludedDays'
			, 'click .reset-time.btn': 'resetExcludedTime'
			, 'change select.days-list': 'daysListChanged'
			, 'change select.time-list': 'timeListChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#calendar-template');
			this.projectId = config['projectId'];
			this.dateFormat = 'MM/DD/YYYY';
			this.datePickerDateFormat = 'mm/dd/yy';
			this.hourFormat = Booki.HourFormat.H24;
			if(!this.model){
				this.model = this.getDefaultModel();
			}
            _.bindAll(this, 'render');
			this.model.bind('change', this.render);
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			
			this.render();
		},
		
		'render': function(){
			var context = this
				, projectId = this.projectId
				, model = new Booki.Calendar({'projectId': projectId});
				
			model.fetch({'success': function(model, resp){
					if(typeof(model.get('id')) === 'number' ){
						context.model.set(model.attributes);
					}
					context.renderCalendar(true);
				}
			});
		},
		
		'renderCalendar': function(refreshCalendarDay){
			var $el
				, content
				, hours = []
				, minutes = []
				, i = 0
				, model = this.model
				, startDate = new Date(this.formatDate(model.get('startDate')))
				, endDate = new Date(this.formatDate(model.get('endDate')))
				, isDirty = model.isNew() || model.hasChanged();


			if(!this.el){
				return;
			}
			
			$el = $(this.el);

			while(i < 24){
				hours.push(i.pad());
				++i;
			}
			i = 0;
			while(i <= 60){
				minutes.push(i.pad());
				++i;
			}
			
			content = this.template(
			{
				'days': this.getDays(startDate, endDate)
				, 'hours': hours
				, 'minutes': minutes
				, 'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'hasChanges': model.hasChanged()
				, 'disableStatusDaysResetButton': (model.get('daysExcluded').length > 0 || model.get('weekDaysExcluded').length > 0) ? '': 'disabled="disabled"'
				, 'disableStatusTimeResetButton': (model.get('timeExcluded').length === 0 ? 'disabled="disabled"' : '')
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.createDatePickers();
			this.createSidenavView();
			if(!isDirty){
				this.createModelView(refreshCalendarDay);
			}
			this.sidenavView.showTab(0/*Booki.Sidenav.detail*/);
			this.sidenavView.disableTab(1, isDirty);
			
			this.validate();
			this.tooltip();
		},
		'createSidenavView': function(){
			if(!this.sidenavView){
				this.sidenavView = new Booki.SidenavView({
					'template': $('#calendar-sidenav-template'),
					'tab': '.calendar-tab',
					'el': $('#calendar-sidenav-view')
				});
				return;
			}
		},
		
		'createModelView': function(refresh){
			var context = this
				, minDate = this.formatDate(this.model.get('startDate'))
				, maxDate = this.formatDate(this.model.get('endDate'))
				, weekDaysExcluded = this.model.get('weekDaysExcluded')
				, daysExcluded = this.model.get('daysExcluded')
				, attrs = this.model.attributes;
			if(!this.modelView){
				this.modelView = new Booki.CalendarDayView({
					'calendarId': this.model.get('id')
					, 'el': $('#calendar-day-view')
					, 'minDate': minDate
					, 'maxDate': maxDate
					, 'weekDaysExcluded': weekDaysExcluded
					, 'daysExcluded': daysExcluded
					, 'period': this.model.get('period')
					, 'cost': this.model.get('cost')
					, 'hours': this.model.get('hours')
					, 'minutes': this.model.get('minutes')
					, 'timeSlots': this.model.get('timeSlots')
					, 'enableSingleHourMinuteFormat': this.model.get('enableSingleHourMinuteFormat')
				});
				return;
			}
			if(refresh){
				this.modelView.render(attrs);
			}
		},
		
		'formatDate': function(value){
			return moment(value).format(this.dateFormat);
		},
		
		'createDatePickers': function(){
			var model = this.model
				, dateFormat = this.datePickerDateFormat
				, context = this
				, $startDate = $('#startdate')
				, $endDate = $('#enddate');

			//make dateformat retrievable from settings. Allow flexibility.
			$startDate.datepicker(
			{
				'defaultDate': new Date(model.get('startDate'))
				, 'dateFormat': dateFormat
				, 'changeMonth': true
				, 'changeYear': true
				, 'onSelect': function(dateText, inst){
					model.set({'startDate': dateText}, {'silent': true});
					context.renderCalendar();
					$endDate.datepicker( 'option', 'minDate', dateText );
				}
			});
			
			$endDate.datepicker(
			{
				'defaultDate': new Date(model.get('endDate'))
				, 'dateFormat': dateFormat
				, 'changeMonth': true
				, 'changeYear': true
				, 'numberOfMonths': 3
				, 'minDate': new Date(model.get('startDate'))
				, 'onSelect': function(dateText, inst){
					model.set({'endDate': dateText}, {'silent': true});
					context.renderCalendar();
					$startDate.datepicker( 'option', 'minDate', dateText );
				}
			});
		},
		'daysListChanged': function(e){
			var target = e.srcElement || e.target
				, $el = $(this.el)
				, $removeButton = $el.find('.remove-day.btn');
			$removeButton.removeAttr('disabled');
		},
		'timeListChanged': function(e){
			var target = e.srcElement || e.target
				, $el = $(this.el)
				, $removeButton = $el.find('.remove-time.btn');
			if($(target).find('option').length > 1){
				$removeButton.removeAttr('disabled');
			}else{
				$removeButton.attr('disabled', 'disabled');
			}
		},
		'save': function(e){
			var calendar = this.model;
			
			if(!this.isValid()){
				return;
			}
			
			calendar.save(calendar.attributes, { 'success':
				function(model, resp){
					
				} 	
			});
		},
		'discardChanges': function(e){
			this.render();
		},
		'inputChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value
				, data = {}
				, model = this.model
				, flag1
				, flag2
				, timeSlots
				, context = this;
			switch(name){
				case 'weekday':
				value = $('[type="checkbox"].weekday:checked').map(function(){ 
					return parseInt(this['value'], 10); 
				}).get();
				name = 'weekDaysExcluded';
				flag2 = true;
				break;
				case 'hours':
				case 'minutes':
					value = parseInt($target.val(), 10);
					if(value === 0){
						if((name === 'minutes' && this.model.get('hours') === 0) ||
							(name === 'hours' && this.model.get('minutes') === 0)){
							value = 1;
						}
					}
					flag1 = true;
				break;
				case 'hourStartInterval':
				case 'minuteStartInterval':
					value = parseInt($target.val(), 10);
					flag1 = true;
				break;
				case 'cost':
					value = $target.val();
				break;
				case 'period':
					value = parseInt($target.val(), 10);
					if(value === Booki.Period.byDay && model.get('timeSlots').length > 1){
						data = {'hours': 23, 'minutes': 60, 'timeSlots': []};
					}
					flag2 = true;
				break;
				case 'bookingLimit':
					value = parseInt($target.val(), 10);
				break;
				case 'displayCounter':
					value = $target.is(':checked');
				break;
				case 'minNumDaysDeposit':
					value = parseInt($target.val(), 10);
				break;
				case 'deposit':
					value = $target.val();
				break;
				case 'bookingStartLapse':
					value = parseInt($target.val(), 10);
				break;
				case 'enableSingleHourMinuteFormat':
					value = $target.is(':checked');
					flag1 = true;
				break;
			}
			data[name] = value;
			model.set(data, {'silent': true});
			if(flag1){
				this.fetchTimeSlots();
			}else if (flag2){
				this.renderCalendar(true);
			}
		},
		'fetchTimeSlots': function(){
			var context = this
				, timeSlots;
			timeSlots = new Booki.TimeSlots({
				'hours': this.model.get('hours')
				, 'minutes': this.model.get('minutes')
				, 'hourStartInterval': this.model.get('hourStartInterval')
				, 'minuteStartInterval': this.model.get('minuteStartInterval')
				, 'enableSingleHourMinuteFormat': this.model.get('enableSingleHourMinuteFormat')
			});
			timeSlots.fetch({'success': function(model, resp){
				context.model.set({'timeSlots': resp['result']}, {'silent': true});
				context.renderCalendar(true);
			}});
		},
		'removeSelectedDays': function(e){
			var $el = $(this.el)
				, $removeButton = $el.find('.remove-day.btn')
				, $resetButton = $el.find('.reset-day.btn')
				, model = this.model
				, days = model.get('daysExcluded');
				
			$('select[name="dayslist"] option:selected').each(function(i, item){
				var value = $(item).val();
				if(days.indexOf(value) === -1){
					days.push(value);
				}
				$(this).remove();
			});
			model.set({'daysExcluded': days}, {'silent': true});
			
			$removeButton.attr('disabled', true);
			
			if(days.length === 0){
				$resetButton.attr('disabled');
			}else{
				$resetButton.removeAttr('disabled');
			}
		},
		'removeSelectedTime': function(e){
			var $el = $(this.el)
				, $removeButton = $el.find('.remove-time.btn')
				, $resetButton = $el.find('.reset-time.btn')
				, model = this.model
				, timeSlots = model.get('timeExcluded');
				
			$('select[name="timelist"] option:selected').each(function(i, item){
				var value = $(item).val();
				if(timeSlots.indexOf(value) === -1){
					timeSlots.push(value);
				}
				$(this).remove();
			});
			model.set({'timeExcluded': timeSlots}, {'silent': true});

			$removeButton.attr('disabled', true);

			if(timeSlots.length === 0){
				$resetButton.attr('disabled');
			}else{
				$resetButton.removeAttr('disabled');
			}
		},
		'resetExcludedDays': function(e){
			$(this.el).find('.reset-day.btn').attr('disabled', true);
			this.model.set({'daysExcluded': [], 'weekDaysExcluded': []}, {'silent': true});
			this.renderCalendar(true);
		},
		'resetExcludedTime': function(e){
			$(this.el).find('.reset-time.btn').attr('disabled', true);
			this.model.set({'timeExcluded': []}, {'silent': true});
			this.renderCalendar(true);
		},
		'getDays': function(start, end){
			var newDate
				, days = [];
			while (start <= end) {
				days.push(this.formatDate(start));
				newDate = start.setDate(start.getDate() + 1);
				start = new Date(newDate);
			}
			days = this.filterDaysExcluded(this.model.get('daysExcluded'), days, function(a, b){
				return (a === b);
			});
			return this.filterDaysExcluded(this.model.get('weekDaysExcluded'), days, function(a, b){
				return (parseInt(a, 10) === new Date(b).getDay());
			});
		},
		'filterDaysExcluded': function(list, days, func){
			days = $.grep(days, function(day){
				var invert = true
					, i
					, result;
				for(i in list){
					result = func(list[i], day);
					if(result){
						invert = false;
						break;
					}
				}
				return invert;
			});
			return days;
		},
		
		'getDefaultModel': function(){
			var startDate = this.formatDate(moment()); 
			return new Booki.Calendar({
				'id': null
				, 'projectId': this.projectId
				, 'startDate': startDate
				, 'endDate': startDate
				, 'daysExcluded': []
				, 'timeExcluded': []
				, 'weekDaysExcluded': []
				, 'hours': 23
				, 'minutes': 60
				, 'cost': 0
				, 'timeSlots': []
				, 'period': Booki.Period.byDay
				, 'discount': 0
				, 'bookingMinimumDiscount': 0
				, 'hourStartInterval': 0
				, 'minuteStartInterval': 0
				, 'bookingLimit': 0
				, 'displayCounter': false
				, 'currentBookingCount': 0
				, 'minNumDaysDeposit': 0
				, 'deposit': 0
				, 'bookingStartLapse': 0
				, 'enableSingleHourMinuteFormat': false
			});
		},
		
		'dispose': function(){
			if(this.sidenavView){
				this.sidenavView.undelegateEvents();
				this.sidenavView.dispose();
			}
			if(this.modelView){
				this.modelView.undelegateEvents();
			}
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);