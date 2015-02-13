(function(url, $, moment, accounting){
	Booki.CalendarDayView = Booki.ViewBase.extend({
		'events': {
			'click .create.btn': 'create'
			, 'click .update.btn': 'save'
			, 'click .delete.btn': 'delete'
			, 'click .delete.btn.delete-nameless-days': 'deleteNamelessDays'
			, 'change input:not([type="checkbox"].calendardayweek)': 'inputChanged'
			, 'change select:not([multiple="multiple"])': 'inputChanged'
			, 'change [type="checkbox"].calendardayweek': 'weekDayChanged'
			, 'click .remove-time.btn': 'removeSelectedTime'
			, 'click .reset-time.btn': 'resetExcludedTime'
			, 'change select.time-list': 'timeListChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#calendar-day-template');
			this.calendarId = config['calendarId'];
			this.minDate = config['minDate'];
			this.maxDate = config['maxDate'];
			this.selectedEndDate = this.minDate;
			this.selectedStartDate = this.maxDate;
			this.weekDaysExcluded = config['weekDaysExcluded'];
			this.daysExcluded = config['daysExcluded'];
			this.hours = config['hours'];
			this.minutes = config['minutes'];
			this.timeSlots = config['timeSlots'];
			this.period = config['period'];
			this.cost = config['cost'];
			this.enableSingleHourMinuteFormat = config['enableSingleHourMinuteFormat'];
			this.selectedSeason = '0';/*createnew*/
			this.dateFormat = 'MM/DD/YYYY';
			this.datePickerDateFormat = 'mm/dd/yy';
			this.currencySymbol = Booki.localization.currencySymbol;
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			this.hourFormat = Booki.HourFormat.H24;
			this.weekdays = [];
            _.bindAll(this, 'render');
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			
			this.cleanupCalendarDays = new Booki.CleanupCalendarDays({'id': this.calendarId});
			
			this.render();
        },
		
		'render': function(attrs){
			var context = this
				, calendarId = this.calendarId
				, model = this.model
				, seasons
				, minDate;
			if(attrs){
				if(attrs['startDate']){
					this.minDate = attrs['startDate'];
					if(model && typeof(model.get('id')) !== 'number' ){
						//we dont have a model yet, using default:
						model.set({'day': this.minDate}, {'silent': true})
					}
				}
				if(attrs['endDate']){
					this.maxDate = attrs['endDate'];
				}
				if(attrs['weekDaysExcluded']){
					this.weekDaysExcluded = attrs['weekDaysExcluded'];
				}
				if(attrs['daysExcluded']){
					this.daysExcluded = attrs['daysExcluded'];
				}
				if(attrs['timeslots']){
					this.timeSlots = attrs['timeSlots'];
				}
				if(attrs['hours']){
					this.hours = attrs['hours'];
				}
				if(attrs['minutes']){
					this.hours = attrs['minutes'];
				}
				if(attrs['cost']){
					this.hours = attrs['cost'];
				}
				if(typeof(attrs['period']) === 'number'){
					this.period = attrs['period'];
				}
			}
			
			seasons = new Booki.Seasons({'calendarId': calendarId});
			seasons.fetch({'success': function(collection, resp){
					context.seasons = new Backbone.Collection(resp['seasons']);
					context.calendarDays = new Backbone.Collection(resp['calendarDays']);
					context.collection = new Booki.CalendarDays({'calendarId': calendarId, 'seasonName': context.selectedSeason === '-1' ? null : context.selectedSeason});
					context.collection.fetch({'success': function(collection, resp){
							if(collection.length > 0){
								context.model = collection.at(0);
							}else{
								context.selectedStartDate = context.getUnusedMinDate();
								context.selectedEndDate = context.selectedStartDate;
								context.model = context.getDefaultModel();
								context.collection.add(context.model);
							}
							context.renderCalendarDay();
							context.validate();
						}
					});
				}
			});
		},
		
		'renderCalendarDay': function(){
			var $el
				, content
				, hours = []
				, minutes = []
				, i = 0
				, model
				, models = this.collection
				, hasUnnamedSeasons = this.calendarDays.find(function(item){
						return !item.get('seasonName') && !item.isNew();
				});
				
			if(!this.el){
				return;
			}	
			
			model = this.model;
			
			
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
				 'hours': hours
				, 'minutes': minutes
				, 'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': models.toJSON()
				, 'hasChanges': model.hasChanged()
				, 'disableStatusTimeResetButton': model.get('timeExcluded').length === 0 ? 'disabled="disabled"' : ''
				, 'disableStatusDeleteButton': model.isNew() ? 'disabled="disabled"' : ''
				, 'localization': Booki.localization
				, 'accounting': accounting
				, 'disableStatusDaysResetButton': models.length > 0
				, 'selectedSeason': this.selectedSeason
				, 'seasons': this.seasons.toJSON()
				, 'hasUnnamedSeasons': hasUnnamedSeasons ? true : false
			});
			
			$el.html(content);
			this.createDatePickers();
			this.tooltip();
		},
		'weekDayAvailable': function(weekday){
			var result = false;
			this.collection.each(function(model) {
				var day = model.get('day');
				if(weekday === moment(day).weekday()){
					result = true;
					return false;
				}
			});
			return result;
		},
		'weekDaysCount': function(weekday){
			var start
				, end
				, m
				, available
				, result = 0;
				
			if(this.selectedStartDate && this.selectedEndDate){
				start =  moment(this.selectedStartDate);
				end = moment(this.selectedEndDate);
				for (m = start; m.isBefore(end) || m.isSame(end); m.add('days', 1)) {
					if(m.weekday() === weekday){
						available = this.dateAvailable(m._d);
						if(available){
							++result;
						}
					}
				}
			}
			return result;
		},
		'weekDayChanged': function(e){
			var $target = $(e.target)
				, weekday = parseInt($target.attr('name'), 10)
				, $el = $(this.el)
				, weekDays = $el.find('[type="checkbox"].weekday:checked').map(function(){ 
					return parseInt(this['name'], 10)
				}).get()
				, $disabledItems = $el.find('[type="checkbox"].weekday:disabled');
			if(this.weekdays.indexOf(weekday) === -1){
				this.weekdays.push(weekday);
			}else{
				this.weekdays = _.without(this.weekdays, weekday);
			}
			this.resetCollection();
			this.renderCalendarDay();
		},
		'formatDate': function(value){
			return moment(value).format(this.dateFormat);
		},
		'createDatePickers': function(){
			var context = this
				, $el = $(this.el)
				, collection = this.calendarDays
				, dateFormat = this.datePickerDateFormat
				, minDate = this.minDate
				, maxDate = this.maxDate
				, datepickerArgs = {
				'defaultDate': new Date(this.model.get('day')),
				'dateFormat': dateFormat,
				'changeMonth': true,
				'changeYear': true,
				'minDate': new Date(minDate),
				'maxDate': new Date(maxDate),
				'beforeShowDay': function(day) {
					var title
						, d = context.formatDate(day)
						, weekDay = day.getDay()
						, selectable = context.dateAvailable(day);
					
					if(!selectable){
						return [false/*date is non-selectable*/];
					}
					
					return [true, '', ''];
				}
			};
			this.$startDate = $el.find('#cdstartdate');
			this.$endDate = $el.find('#cdenddate');
			
			this.$startDate.datepicker(datepickerArgs);
			this.$endDate.datepicker(datepickerArgs);
			this.$startDate.datepicker('setDate', this.selectedStartDate);
			this.$endDate.datepicker('setDate', this.selectedEndDate);
		},
		
		'delete': function(e){
			var context = this;

			this.collection['delete']({ 'success':
				function(models, resp){
					context.selectedSeason = '0';/*createnew*/
					context.render();
				} 	
			});
		},
		'deleteNamelessDays': function(e){
			var namelessDays = new Booki.NamelessDays(this.calendarId)
				, context = this;
			namelessDays.destroy({ 'success':
				function(_model, resp){
					context.model = null;
					context.render();
				} 	
			});
		},
		'create': function(e){
			var context = this
				, seasons;
			if(!this.isValid()){
				return;
			}
			
			this.collection['create']({ 'success':
				function(models, resp){
					context.collection.reset(models);
					context.model = context.collection.at(0);
					context.selectedSeason = context.model.get('seasonName');
					seasons = new Booki.Seasons({'calendarId': context.calendarId});
					seasons.fetch({'success': function(collection, resp){
							context.seasons = new Backbone.Collection(resp['seasons']);
							context.calendarDays = new Backbone.Collection(resp['calendarDays']);
							context.renderCalendarDay();
							context.validate();
						}
					});
				} 	
			});
		},
		'save': function(e){
			var context = this;
			if(!this.isValid()){
				return;
			}
			
			this.collection['save']({ 'success':
				function(models, resp){
					context.selectedSeason = context.model.get('seasonName');
					context.render();
				} 	
			});
		},
		'inputChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value
				, data = {}
				, model
				, flag1
				, timeSlots
				, context = this;
			switch(name){
				case 'selectedSeason':
					this.selectedSeason = $target.find('option:selected').val();
					this.render();
					return;
				break;
				case 'seasonName':
					value = $target.val();
				break;
				case 'day':
					value = $target.val();
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
				case 'cdstartdate':
				case 'cdenddate':
					this.resetCollection();
					this.selectedStartDate = this.$startDate.val();
					this.selectedEndDate = this.$endDate.val();
					this.weekdays = [];
					this.renderCalendarDay();
					return;
				break;
				case 'minNumDaysDeposit':
					value = parseInt($target.val(), 10);
				break;
				case 'deposit':
					value = $target.val();
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': true});
			this.update();
			if(flag1){
				this.fetchTimeSlots();
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
				, 'enableSingleHourMinuteFormat': this.enableSingleHourMinuteFormat
			});
			timeSlots.fetch({'success': function(model, resp){
				context.model.set({'timeSlots': resp['result']}, {'silent': true});
				context.renderCalendarDay();
			}});
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
		
		'resetExcludedTime': function(e){
			$(this.el).find('.reset-time.btn').attr('disabled', true);
			this.model.set({'timeExcluded': []}, {'silent': true});
			this.renderCalendarDay();
		},
		'getUnusedMinDate': function(){
			var result = this.minDate
				, start =  moment(this.minDate)
				, end = moment(this.maxDate)
				, available
				, m;
			for (m = start; m.isBefore(end) || m.isSame(end); m.add('days', 1)) {
				available = this.dateAvailable(m._d);
				if(available){
					result = m.format(this.dateFormat);
					break;
				}
			}
			return result;
		},
		'resetCollection': function(){
			var start
				, end
				, m
				, model
				, available
				, $el = $(this.el)
				, weekDays = $el.find('[type="checkbox"].calendardayweek:checked').map(function(){ 
					return parseInt(this['name'], 10)
				}).get()
				, weekDay;
				
			if(this.$startDate && this.$endDate){
				this.collection.reset();
				start =  moment(this.$startDate.val());
				end = moment(this.$endDate.val());
				for (m = start; m.isBefore(end) || m.isSame(end); m.add('days', 1)) {
					available = this.dateAvailable(m._d);
					if(available && weekDays.indexOf(m.weekday()) === -1){
						model = this.getDefaultModel(this.model, m.format(this.dateFormat));
						this.collection.add(model);
					}
				}
				this.collection.comparator = function( model ) {
				  return model.get( 'day' );
				}

				// Resort collection
				this.collection.sort();
			}
		},
		'dateAvailable': function(day){
			var context = this
				, result = true
				, weekDay = day.getDay()
				, d = context.formatDate(day);
			$(this.daysExcluded).each(function(i, item){
				if(context.formatDate(item) === d){
					result = false;
					return false;
				}
			});
			
			if(!result){
				return result;
			}
			
			this.calendarDays.each(function(item){
				if(context.formatDate(item.get('day')) == d){
					result = false;
					return false;
				}
			});
			
			if(!result){
				return result;
			}
			
			$(this.weekDaysExcluded).each(function(i, item){
				if(weekDay === item){
					result = false;
					return false;
				}
			});
			
			return result;
		},
		'update': function(){
			var context = this;
			this.collection.each(function(item) {
				var model = context.model.clone();
				model.set({'id': item.get('id'), 'day': item.get('day')}, {'silent': true});
				item.set(model.attributes, {'silent': true});
			});
		},
		'getAllSeasons': function(collection){
			var result = []
				, seasonName;
			
			collection.each(function(model) {
				seasonName = model.get('seasonName');
				if(seasonName && result.indexOf(seasonName) === -1){
					result.push(seasonName);
				}
			});
			return result;
		},
		'getDefaultModel': function(model, day){
			var seasonName =  model ? model.get('seasonName') : ''
				, timeExcluded = model ? model.get('timeExcluded') : []
				, daysExcluded = model ? model.get('daysExcluded') : this.daysExcluded
				, weekDaysExcluded = model ? model.get('weekDaysExcluded') : this.weekDaysExcluded
				, hours = model ? model.get('hours') : this.hours
				, minutes = model ? model.get('minutes') : this.minutes
				, cost = model ? model.get('cost') : this.cost
				, timeSlots = model ? model.get('timeSlots') : this.timeSlots
				, hourStartInterval = model ? model.get('hourStartInterval') : 0
				, minuteStartInterval = model ? model.get('minuteStartInterval') : 0
				, minNumDaysDeposit = model ? model.get('minNumDaysDeposit') : 0
				, deposit = model ? model.get('deposit') : 0;
			day = day || this.selectedStartDate;
			return new Booki.CalendarDay({
				'id': null
				, 'calendarId': this.calendarId
				, 'day': this.formatDate(day)
				, 'seasonName': seasonName
				, 'timeExcluded': timeExcluded
				, 'daysExcluded': daysExcluded
				, 'weekDaysExcluded': weekDaysExcluded
				, 'hours': hours
				, 'minutes': minutes
				, 'cost': cost
				, 'timeSlots': timeSlots
				, 'hourStartInterval': hourStartInterval
				, 'minuteStartInterval': minuteStartInterval
				, 'minNumDaysDeposit': minNumDaysDeposit
				, 'deposit': deposit
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);