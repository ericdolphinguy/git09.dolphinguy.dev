/**
	 @license Copyright @ 2014 Alessandro Zifiglio. All rights reserved. http://www.booki.io
*/
(function(window, $, _){
/**
		@function Augments the function object with an exports method that facilitates exposing a method out of it's contained scope to the outside.
		@description Exports the symbol out of it's contained scope and exposes it to the outside by attaching it to the window object.
		@param {string|Object} fullName The full name of the class to export outside the private context it is in.
		@param {Object=} obj The object to export. If not supplied, nothing is going to happen which is useful when wanting to trick google closure compiler from removing dead code. 
	*/
	Function.extern = function (fullName, obj) {
		var parts, current, length, i, container = window;
		if(!fullName || !obj)return;
		parts = fullName.split('.');
		length  = parts.length;
		for(i = 0; i < length; i++) {
			current = parts[i];
			if (i === (length - 1)) {
				container[current] = obj;
			}
			else if(container[current]) {
				container = container[current];
			}
			else {
				container = container[current] = {};
			}
		}
		return this;
	}
	
	/**
		@name Small
		@namespace
		@class
		@constructor
		@description Small is a tiny js library (3.86kb without gzip compression) that provides you with only the absolute necessary boiler plate code you'll end up writing when not using a heavy js library.
	*/
	var Booki = Booki || function(){};
	Function.extern('Booki', Booki);
})(window, jQuery, _);
(function(){

	Booki.OptionalsBookingMode = {
		'eachBooking' : 0
		, 'eachDay' : 1
	};
	Booki.OptionalsListingMode = {
		'checkboxList' : 0
		, 'radioButtonList' : 1
	};
	Booki.BookingWizardMode = {
		'tabs' : 0
		, 'linear' : 1
	};
/**
		@enum
		@description Represents a form element in a Booki.FormElement object.
	*/
	Booki.ElementType = {
		'textbox' : 0
		, 'textarea' : 1
		, 'dropdownlist' : 2
		, 'listbox' : 3
		, 'checkbox' : 4
		, 'radiobutton' : 5
		, 'h1' : 6
		, 'h2' : 7
		, 'h3' : 8
		, 'h4' : 9
		, 'h5' : 10
		, 'h6' : 11
		, 'plaintext' : 12
		, 'tc' : 13
	};
	
	/**
		@enum
		@description Represents a week day name used in a Booki.Calendar object.
	*/
	Booki.DayName = {
		'monday': 0
		, 'tuesday': 1
		, 'wednesday': 2
		, 'thursday': 3
		, 'friday': 4
		, 'saturday': 5
		, 'sunday': 6
	};
	
	Booki.Sidenav = {
		'detail': 0
		, 'edit': 1
	};
	
	Booki.CalendarMode = {
		'popup': 0
		, 'inline': 1
		, 'range': 2
		, 'nextDayCheckout': 3
	};
	
	Booki.BookingMode = {
		'reservation': 0
		, 'appointment': 1
	};
	
	Booki.Period = {
		'byDay': 0
		, 'byTime': 1
	};
	Booki.ProjectStatus = {
		'stopped': 0
		, 'running': 1
	};
	/**
		@enum
		@description Represents the direction up or down in a Booki.FormElements collection object.
	*/
	Booki.Direction = {
		'up': 0
		, 'down': 1
	};
	
	Booki.HourFormat = {
		'H24': 0
		, 'AMPM': 1
	};
	
	Booki.ProjectStep = {
		'bookingForm': 0
		, 'customFormFields': 1
	};
})();
(function($){
	/**
		@function
		@description pads numbers with zero
		@param {number=} size 
	*/
	Number.prototype.pad = function(size){
	  if(typeof(size) !== 'number'){size = 2;}
	  var s = String(this);
	  while (s.length < size) s = '0' + s;
	  return s;
	};
	
	Booki.rfc3986EncodeURIComponent = function (str) {  
		return encodeURIComponent(str).replace(/[!'()*]/g, escape);  
	};
})(window['jQuery']);
(function(window, url, $, _, JSON){
	$.fn.outerHTML = function() {
		var $elem = $(this),
			content;
		if ('outerHTML' in $elem[0]) {
			return $elem[0].outerHTML;
		} else {
			content = $elem.wrap('<div></div>').parent().html();
			$elem.unwrap();
			return content;
		}
	};
	
	var extern = Function.extern
	, Booki = window['Booki']
	, 
	/**
		@function
		@ignore
		@description Helper function to get a value from a Backbone object as a property or as a function.
	*/
	getValue = function(object, prop) {
		if (!(object && object[prop])) return null;
		return _.isFunction(object[prop]) ? object[prop]() : object[prop];
	}
	, 
	/**
		@function
		@ignore
		@description Throw an error when a URL is needed, and none is supplied.
	*/
	urlError = function() {
		throw new Error('A "url" property or function must be specified');
	}
	, bookiModalProgress
	, bookiModalProgressBar
	, bookiModalProgressMethod
	, bookiModalProgressUrl;

	Booki.progressBarShow = function(method, url){
		if(!bookiModalProgress){
			bookiModalProgress = $('#progressModal').modal({
			  backdrop: 'static',
			  show: false
			});
			bookiModalProgressBar = bookiModalProgress.find('.progress-bar');
			bookiModalProgressMethod = bookiModalProgress.find('.progress-method');
			bookiModalProgressUrl = bookiModalProgress.find('.progress-url');
		}
		window.console.log('showing');
		if(method && url){
			bookiModalProgressMethod.html(method);
			bookiModalProgressUrl.html(url);
		}
		bookiModalProgress.modal('show').is(':visible');
		bookiModalProgressBar.addClass('stretch');
	};
	
	Booki.progressBarHide = function(){
		if(bookiModalProgress && bookiModalProgress.hasClass('in')){
			bookiModalProgressBar.removeClass('stretch');
			bookiModalProgress.modal('hide').is(':visible');
		}
	};
	
	$(document).ajaxStop(function(e){
		Booki.progressBarHide();
	});
	/**
		@function
		@description Creates parameters for an http request.
		@param {string} method The http method name to call on the server ( in contest of booki, but could be any piece of data to send to the server to identify the request).
		@param {Object} model The model from whom to build the json string ready for transport.
		@param {Object} options 
	*/
	Booki.httpRequestParams = function(method, model, options){
		var params = {'type': 'POST', 'dataType': 'json'}/*Default JSON-request options.*/,
			action = model['action'][method];
		
		// Ensure that we have a URL.
		if (!options['url']) {
		  params['url'] = getValue(model, 'url') || urlError();
		}
		params['contentType'] = 'application/x-www-form-urlencoded';
		if(!options['preserveModels'] && (model['models'] && model['models'].length > 0)){
			model = model['models'][0];
		}

		params['data']= {
			'model': Booki.rfc3986EncodeURIComponent(window['JSON']['stringify'](model.toJSON())), 
			'action': action
		};
		
		return params;
	};
	
	/**
		@function
		@ignore
		@description Override sync method for wp. we don't have a rest service but rather using an ajax api
		because that is what wp exposes. so we override and adapt.
	*/
	Backbone.sync = function(method, model, options) {
		// Default options, unless specified.
		options || (options = {});
		
		var params = Booki.httpRequestParams(method, model, options);
		Booki.progressBarHide();
		Booki.progressBarShow(model.action[method] , model.url);
		// Make the request, allowing the user to override any Ajax options.
		return $.ajax(_.extend(params, options));
	};
	
	Booki.Admin = function(){};
	
	$(window).load(function () {
		var projectsView = new Booki.ProjectsView({
			'el': $('#projects-view')
		}),
		formElementsView,
		calendarView,
		optionalsView,
		cascadingListView,
		progressBar = '.tabbable .progress',
		createFormElementsView = function(projectId){
			if(formElementsView){
				formElementsView.undelegateEvents();
				formElementsView.collection.reset();
				formElementsView.dispose();
			}
			formElementsView = new Booki.FormElementsView({
				'el': $('#formelements-view'),
				'projectId': projectId
			});
		},
		createCalendarView = function(projectId){
			if(calendarView){
				calendarView.undelegateEvents();
				calendarView.dispose();
			}
			calendarView = new Booki.CalendarView({
				'el': $('#calendar-view'),
				'projectId': projectId
			});
		},
		createOptionalsView = function(projectId){
			if(optionalsView){
				optionalsView.undelegateEvents();
			}
			optionalsView = new Booki.OptionalsView({
				'el': $('#optionals-view'),
				'projectId': projectId
			});
		},
		createCascadingListView = function(projectId){
			if(cascadingListView){
				cascadingListView.undelegateEvents();
			}
			cascadingListView = new Booki.CascadingListView({
				'el': $('#cascadinglist-view'),
				'projectId': projectId
			});
		};
		
		$('a[data-toggle="tab"]').click(function(e){
			var target = e.srcElement || e.target,
				hash = this.hash.slice(1),
				projectId = projectsView.selectedId,
				$elem = $(this);
			e.preventDefault();
			if(typeof(projectId) !== 'number' || projectId === -1){
				return false;
			}
			
			$('.nav.nav-list a[href="#formelement"]').tab('show');//reset sub tab
			
			$elem.tab('show');
			
			switch(hash){
				case 'step1':
					break;
				case 'step2':
				case 'formelements':
					createFormElementsView(projectId);
					break;
				case 'step3':
					createCalendarView(projectId);
					break;
				case 'step4':
					createOptionalsView(projectId);
					break;
				case 'step5':
					createCascadingListView(projectId);
					break;
			}
		});
	});
	
	
})(window, window['ajaxurl'], window['jQuery'], window['_'], window['JSON']);
(function(window, url, $){
	Booki.FilteredCollectionBase = Backbone.Collection.extend({
		'initialize': function(models, options) {
			this.id = options['id'];
		},
		'sync' : function(method, model, options) {
			var params;
			options || (options = {});
			
			params = Booki.httpRequestParams(method, model, options);
			
			params['data']['model'] = window['JSON']['stringify']({'id': model['id']});
			// Make the request, allowing the user to override any Ajax options.
			return $.ajax(_.extend(params, options));
		}
	});
})(window, window['ajaxurl'], window['jQuery']);

(function(window, $, _){
	Booki.ModelBase = Backbone.Model.extend({});
})(window, jQuery, _);
(function(window, $, _){
	Booki.ViewBase = Backbone.View.extend({
		'validate': function(options){
			$(this.el).find('.booki_parsley_validated').each(function(){
				$(this).parsley(options);
			});
		},
		
		'isValid': function(){
			var isValid = true;
			$(this.el).find('.booki_parsley_validated').each(function(){
				var $elem = $(this),
					result;
				if (!$elem.is(':visible') || $elem.is(':disabled')){
					return true;
				}
				result = $(this).parsley().validate(true);
				if(result !== null && (typeof(result) === 'object' && result.length > 0)){
					isValid = false;
				}
			});
			return isValid;
		},
		
		'parseDefault': function(value, def){
			if(typeof(value) === 'undefined' || value === null){
				return typeof(def) === 'undefined' ? '' : def;
			}
			return value;
		},
		
		'tooltip': function(){
			$('[data-toggle=tooltip]').tooltip();
		},
		
		'sortIntAsc': function(a, b){
			 return a - b;
		}
	});
})(window, jQuery, _);
(function(url, moment){
	Booki.Calendar = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_insertCalendar',
			'update': 'booki_updateCalendar',
			'delete': 'booki_deleteCalendar',
			/*This is a problematic normalization,
			  so we retrieve a calendar by projectId instead.*/
			'read': 'booki_readCalendarByProject'
		}
	});
})(window['ajaxurl'], window['moment']);
(function(url, moment){
	Booki.CalendarDay = Booki.ModelBase.extend({
		'url': url 
		, 'action': {
			'create': 'booki_insertCalendarDay'
			, 'update': 'booki_updateCalendarDay'
			, 'delete': 'booki_deleteCalendarDay'
			, 'read':  'booki_readCalendarDay'
		}
	});
})(window['ajaxurl'], window['moment']);
(function(url){
	Booki.CascadingItem = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertCascadingItem'
			, 'update': 'booki_updateCascadingItem'
			, 'delete': 'booki_deleteCascadingItem'
			, 'read':   'booki_readCascadingItem'
		}
	});
})(window['ajaxurl']);
(function(url){
	Booki.CascadingList = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertCascadingList'
			, 'update': 'booki_updateCascadingList'
			, 'delete': 'booki_deleteCascadingList'
			, 'read':   'booki_readCascadingList'
		}
	});
})(window['ajaxurl']);
(function(url){
	Booki.CleanupCalendarDays = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'delete': 'booki_cleanupCalendarDay'
		}
	});
})(window['ajaxurl']);
(function(url){
	Booki.DuplicateProject = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_duplicateProject'
		}
	});
})(window['ajaxurl']);
(function(url, $){
Booki.FormElement = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_insertFormElement',
			'update': 'booki_updateFormElement',
			'delete': 'booki_deleteFormElement',
			'read':   'booki_readFormElement'
		},
		'createElement': function(){
			var elementType = this.get('elementType')
				, bindingData = this.get('bindingData')
				, value = this.get('value')
				, selectedItemValue = this.get('value')
				, label = this.get('label')
				, context = this
				, optionValue
				, name = ''
				, type
				, multiple
				, cssClass = 'form-control'
				, className = arguments.length === 0 ? this.get('className') : null
				, $elem
				, $anchor
				, i;
			
			switch(elementType){
				case 0/*Booki.ElementType.textbox*/:
					name = 'input';
					type = 'text';
				break;
				case 1/*Booki.ElementType.textarea*/:
					name = 'textarea';
				break;
				case 2/*Booki.ElementType.dropdownlist*/:
					name = 'select';
					value = null;
				break;
				case 3/*Booki.ElementType.listbox*/:
					name = 'select';
					multiple = 'multiple';
					value = null;
				break;
				case 4/*Booki.ElementType.checkbox*/:
				case 13/*Booki.ElementType.tc*/:
					name = 'input';
					type = 'checkbox';
					value = null;
					cssClass = null;
				break;
				case 5/*Booki.ElementType.radiobutton*/:
					name = 'input';
					type = 'radio';
					cssClass = null;
				break;
				case 6/*Booki.ElementType.h1*/:
					name = 'h1';
					cssClass = null;
				break;
				case 7/*Booki.ElementType.h2*/:
					name = 'h2';
					cssClass = null;
				break;
				case 8/*Booki.ElementType.h3*/:
					name = 'h3';
					cssClass = null;
				break;
				case 9/*Booki.ElementType.h4*/:
					name = 'h4';
					cssClass = null;
				break;
				case 10/*Booki.ElementType.h5*/:
					name = 'h5';
					cssClass = null;
				break;
				case 11/*Booki.ElementType.h6*/:
					name = 'h6';
					cssClass = null;
				break;
				case 12/*Booki.ElementType.plaintext*/:
					name = 'p';
					cssClass = null;
				break;
			}
			$elem = $(document.createElement(name));
			
			if(type){
				$elem.attr('type', type);
			}
			if(type === 'checkbox' || type === 'radio'){
				if(type === 'radio'){
					$elem.attr('value', label);
				}
				
				if(bindingData && bindingData[0]){
					$elem.attr('checked', true);
				}
			}else if (typeof(value) === 'string' && value.length > 0){
				if (elementType >= 6 && elementType <= 12){
					$elem.append(value);
				} else{
					$elem.attr('value', value);
				}
			}
			
			if(multiple){
				$elem.attr(multiple, multiple);
			}
			
			if(bindingData.length > 0 && (elementType === 2 || elementType === 3)){
				for(i in bindingData){
					optionValue = bindingData[i];
					$elem.append($('<option value="' + i + '"' + (optionValue === selectedItemValue ? ' selected' : '') + '>' + optionValue + '</option>'));
				}
			}
			if(cssClass){
				$elem.addClass(cssClass);
			}
			if(className){
				$elem.addClass(className);
			}
			return $elem.outerHTML();
		}
	});
})(window['ajaxurl'], window['jQuery']);
(function(url, moment){
	Booki.NamelessDays = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'delete': 'booki_deleteNamelessDays'
		}
	});
})(window['ajaxurl'], window['moment']);
(function(url){
	Booki.Optional = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertOptional'
			, 'update': 'booki_updateOptional'
			, 'delete': 'booki_deleteOptional'
			, 'read':   'booki_readOptional'
		}
	});
})(window['ajaxurl']);
(function(url, $){
	Booki.Project = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_insertProject',
			'update': 'booki_updateProject',
			'delete': 'booki_deleteProject',
			'read':   'booki_readProject'
		}
	});
})(window['ajaxurl'], window['jQuery']);
(function(url){
	Booki.TimeSlots = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'read': 'booki_createTimeSlots'
		}
	});
})(window['ajaxurl']);
(function(url){
	Booki.CalendarDays = Backbone.Collection.extend({
		'model': Booki.CalendarDay
		, 'url': url
		, 'action' : {
			'read': 'booki_readAllCalendarDays'
			, 'create': 'booki_insertCalendarDays'
			, 'update': 'booki_updateCalendarDays'
			, 'delete': 'booki_deleteCalendarDays'
		}
		, 'create': function (options) {
			options = options || {};
			options['preserveModels'] = true;
			return Backbone.sync('create', this, options);
		}
		, 'save': function (options) {
			options = options || {};
			options['preserveModels'] = true;
			return Backbone.sync('update', this, options);
		}
		, 'delete': function (options) {
			return Backbone.sync('delete', this, options);
		}
	});
})(window['ajaxurl']);
(function(url){
	Booki.CascadingItems = Backbone.Collection.extend({
		'model': Booki.CascadingItem,
		'url': url,
		'action' : {'read': 'booki_readAllCascadingItem'}
	});
})(window['ajaxurl']);

(function(url){
	Booki.CascadingLists = Backbone.Collection.extend({
		'model': Booki.CascadingList,
		'url': url,
		'action' : {'read': 'booki_readAllCascadingList'}
	});
})(window['ajaxurl']);

(function(url){
	Booki.FormElements = Backbone.Collection.extend({
		'model': Booki.FormElement,
		'url': url,
		'action' : {'read': 'booki_readAllFormElement'},
		'comparator': function(model) {
			return model.get('rowIndex');
		}
	});
})(window['ajaxurl']);

(function(url){
	Booki.Optionals = Backbone.Collection.extend({
		'model': Booki.Optional,
		'url': url,
		'action' : {'read': 'booki_readAllOptional'}
	});
})(window['ajaxurl']);

(function(url, $){
	Booki.Projects = Backbone.Collection.extend({
		'model': Booki.Project,
		'url': url,
		'action' : {'read': 'booki_readAllProject'}
	});
})(window['ajaxurl'], window['jQuery']);
(function(url){
	Booki.Seasons = Backbone.Collection.extend({
		'url': url
		, 'action' : {'read': 'booki_readAllSeasons'}
	});
})(window['ajaxurl']);
(function(url){
	Booki.Tags = Backbone.Collection.extend({
		'url': url, 
		'action': {
			'read': 'booki_readAllProjectTags'
		}
	});
})(window['ajaxurl']);
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
(function(url, $, moment, accounting){
	Booki.CascadingItemView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .new.btn': 'new',
			'click .add.btn': 'add',
			'click .remove.btn': 'delete',
			'change input, select[name="parentId"]': 'inputChanged',
			'change select[name="cascadingItems"]': 'listboxChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#cascadingitem-template');
			this.cascadingList = config['cascadingList']
			this.cascadingLists = config['cascadingLists'];
			this.selectedCascadingItemId = null;
            _.bindAll(this, 'render');
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.currencySymbol = Booki.localization.currencySymbol;
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			
			this.render();
		},
		
		'render': function(){
			var context = this
				, model
				, models
				, cascadingList
				, cascadingItems;

			models = new Booki.CascadingItems(this.cascadingList);
			models.fetch({'success': function(models, resp){
			
					model = models.at(0);
					
					if(!model){
						model = context.getDefaultModel();
					}
					cascadingItems = model.get('cascadingItems');
					cascadingList = new Booki.CascadingItems(cascadingItems);
					
					if(context.selectedCascadingItemId !== null){
						model = cascadingList.find(function(item){
							return item.get('id') === context.selectedCascadingItemId;
						});
					}else if (!model.isNew() || model.get('id') === null){
						model = context.getDefaultModel();
					}

					if(cascadingList.length === 0){
						cascadingList.add(model);
					}
					context.renderItems(model, cascadingList);
				}
			});
		},
		
		'renderItems': function(model, models){
			var $el,
				content;
			
			this.selectedCascadingItemId = null;
			
			this.model = model;
			this.models = models || new Booki.CascadingItems();

			if(!this.el){
				return;
			}
			$el = $(this.el);
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': models.toJSON()
				, 'cascadingLists': this.cascadingLists.toJSON()
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.validate();
			this.tooltip();
		},

		'listboxChanged': function(e){
			var target = e.srcElement || e.target
				, selectedCascadingItemId = parseInt($('option:selected', target).val(), 10)
				, model = this.models.find(function(item){
				return item.get('id') === selectedCascadingItemId;
			});
			this.renderItems(model, this.models);
		},
		'new': function(e){
			this.renderItems(this.getDefaultModel(), this.models);
		},
		
		'add': function(e){
			var context = this
				, cascadingItemId = this.model.get('id')
				, model = this.models.find(function(item){
					return item.get('id') === cascadingItemId;
				});
			
			if(!model){
				this.models.add(this.model);
			}
			
			if(!this.isValid()){
				return;
			}
			
			this.selectedCascadingItemId = this.model.get('id');
			
			this.model.save(this.model.attributes, { 'success':
				function(model, resp){
					context.render();
				} 	
			});
		},
		
		'delete': function(e){
			var context = this;
			this.model.destroy({'success': 
				function(model, resp){
					context.render();
				}
			});
		},
		
		'save': function(e){
			var cascadingItem = this.model
				, context = this;
		
			if(!this.isValid()){
				return;
			}
			this.selectedCascadingItemId = this.model.get('id');
			cascadingItem.save(cascadingItem.attributes, { 'success':
				function(model, resp){
					context.render();
				} 	
			});
		},
		
		'inputChanged': function(e){
			var target = e.srcElement || e.target,
				$target = $(target),
				name = target['name'],
				type = target['type'],
				value,
				data = {},
				silent = true,
				model;
			switch(name){
				case 'value':
				value = $target.val();
				break;
				case 'cost':
				value = $target.val();
				break;
				case 'parentId':
				value = parseInt($target.val(), 10);
				break;
				case 'lat':
				value = $target.val();
				break;
				case 'lng':
				value = $target.val();
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.CascadingItem({
				'id': null
				, 'value': ''
				, 'cost': 0
				, 'lat': 0
				, 'lng': 0
				, 'listId': this.cascadingList.get('id')
				, 'parentId': null
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);
(function(url, $, moment, accounting){
	Booki.CascadingListView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .delete.btn': 'delete',
			'change input': 'inputChanged',
			'change select': 'selectedListChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#cascadinglist-template');
			this.projectId = config['projectId'];
			this.selectedListId = null;
            _.bindAll(this, 'render');
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.currencySymbol = Booki.localization.currencySymbol;
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			
			this.render();
		},
		
		'render': function(){
			var context = this
				, model;
			
			if(this.models){
				this.models.reset();
			}
			this.models = new Booki.CascadingLists({'projectId': this.projectId});
			this.models.fetch({'reset': true, 'success': function(models, resp){

					if(context.selectedListId !== null){
						model = models.find(function(item){
							return item.get('id') === context.selectedListId;
						});
					}else{
						model = models.at(0);
					}
					if (!model){
						model = context.getDefaultModel();
					}
					context.selectedListId = null;
					context.renderLists(model, models);
				}
			});
		},
		
		'renderLists': function(model){
			var $el
				, content;
			this.model = model;
			if(!this.el){
				return;
			}
			$el = $(this.el);
			
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': this.models.toJSON()
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.createCascadingItemView();
		
			this.validate();
			this.tooltip();
		},
		
		'createNew': function(e){
			this.selectedListId = null;
			this.renderLists(this.getDefaultModel());
		},
		
		'selectedListChanged': function(e){
			var target = e.srcElement || e.target, 
			selectedListId = parseInt($('option:selected', target).val(), 10),
			model;
			if(selectedListId === -1){
				this.createNew();
				return;
			}
			model = this.models.find(function(item){
				return item.get('id') === selectedListId;
			});
			if(model){
				this.selectedListId = selectedListId;
				this.renderLists(model);
			}
		},
		
		'delete': function(e){
			var cascadingList = this.model
				, context = this;
			this.selectedListId = null;
			cascadingList.destroy({'success': 
				function(model, resp){
					context.render();
				}
			});
		},
		
		'save': function(e){
			var cascadingList = this.model
				, context = this;
			
			this.selectedListId = this.model.get('id');
			
			if(!this.isValid()){
				return;
			}
			
			cascadingList.save(cascadingList.attributes, { 'success':
				function(model, resp){
					context.render();
				} 	
			});
		},
		
		'inputChanged': function(e){
			var target = e.srcElement || e.target,
				$target = $(target),
				name = target['name'],
				type = target['type'],
				value,
				data = {},
				silent = true,
				model;
			switch(name){
				case 'label':
				value = $target.val();
				break;
				case 'isRequired':
				value = $target.is(':checked');
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'createCascadingItemView': function(){
			var args;
			if(this.cascadingItemView){
				this.cascadingItemView.undelegateEvents();
				delete this.cascadingItemView;
			}
			this.cascadingItemView = new Booki.CascadingItemView({'cascadingList': this.model, 'cascadingLists': this.models, 'el': $('#cascadingitem-view')});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.CascadingList({
				'id': null
				, 'projectId': this.projectId
				, 'label': ''
				, 'isRequired': false
				, 'cascadingItems': new Booki.CascadingItems()
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);
(function(url, $){
	Booki.FormElementsView = Booki.ViewBase.extend({
		'events': {
			'click .edit.btn': 'editFormElement'
			, 'click .delete.btn': 'deleteFormElement'
			, 'click .save.btn': 'savePendingModels'
			, 'click #deleteFormElementModal .delete-form-element': 'permanentlyDeleteFormElement'
        },
		
		'initialize': function(config) {
			var $template = $('#formelements-template'),
				model = this.model;
			
			this.projectId = config['projectId'];
			this.cols = 0;
			this.rows = 0;
            _.bindAll(this, 'render');
			
			this.createCollection();
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },

        'render': function() {
			var context = this, 
				collection = this.collection;

			collection.fetch({
				'success': function(models, resp){
					context.collection.reset(resp['formElements']);
					context.cols = parseInt(resp['cols'], 10);
					context.rows = parseInt(resp['rows'], 10);
					if(context.el){
						context.renderCollection();
					}
					return context;
				}
			});
			return this;
        },
		
		'addPendingModels': function(models){
			var i;
			if(!this.pendingModels){
				this.pendingModels = new Backbone.Collection();
			}
			for(i in models){
				this.pendingModels.add(models[i]);
			}
		},
		
		'savePendingModels': function(){
			var model, context = this;
			if(this.pendingModels.length > 0){
				model = this.pendingModels.pop();
				model.save(model.attributes, {'success': 
					function(model){
						if(context.pendingModels.length > 0){
							context.savePendingModels();
							return;
						}
						context.renderCollection();
					}
				});
			}
		},
		
		'renderCollection': function(){
			if(!this.el){
				return;
			}
			
			var content = this.template(
				{
					'context': this,
					'models': this.collection.models,
					'isDirty': (this.pendingModels && this.pendingModels.length > 0)
				});
			$(this.el).html(content);
			
			this.createModelView();
			this.createSidenavView();
		},
		
		'createCollection': function(){
			if(this.collection){
				this.collection.reset();
			}
			this.collection = new Booki.FormElements(new Backbone.Model({'id': this.projectId}));
		},
		
		'resetCollection': function(){
			this.createCollection();
			this.render();
		},
		
		'getModelById': function(id){
			return this.collection.find(function(item){
				return item.get('id') === id;
			});
		},
		
		'createSidenavView': function(){
			var value = {'count': this.collection.length};
			if(!this.sidenavView){
				this.sidenavView = new Booki.SidenavView({
					'template': $('#formelement-sidenav-template'),
					'tab': '.formbuilder-tab',
					'el': $('#formelement-sidenav-view'),
					'model': new Backbone.Model(value)
				});
				return;
			}
			this.sidenavView.model.set(value);
		},
		
		'createModelView': function(model){
			var context = this;
			if(!this.modelView){
				this.modelView = new Booki.FormElementView({
					'projectId': this.projectId
					, 'model': model
					, 'cols': this.cols
					, 'rows': this.rows
					, 'el': $('#formelement-view')
					, 'onSaved': function(model){
						context.resetCollection();
					}
				});
				return;
			}
			if(model){
				this.modelView.model.set(model.attributes);
			}
		},
		
		'editFormElement': function(e){
			var $target = $(e.currentTarget)
				, id = parseInt($target.val(), 10)
				, model = this.getModelById(id);
			
			this.createModelView(model);
			this.sidenavView.showTab(0/*Booki.Sidenav.detail*/);
		},
		
		'deleteFormElement': function(e){
			//we need a modal dialog for confirmation here
			var context = this
				, $target = $(e.currentTarget)
				, id = parseInt($target.val(), 10);
			this.modelForDelete = this.getModelById(id);
		},
		
		'permanentlyDeleteFormElement': function(e){
			var context = this;
			if(!this.body){
				this.body = $('body');
			}
			//bootstrap has a problem closing the backdrop, so helping manually
			this.body.removeClass('modal-open');
			$('.modal-backdrop').remove();
			if(this.modelForDelete){
				this.modelForDelete.destroy({'success': function() {
					context.renderCollection();
					context.sidenavView.showTab(1/*Booki.Sidenav.edit*/);
				}});
			}
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
})(window['ajaxurl'], window['jQuery']);

(function(url, $, _){
	Booki.FormElementView = Booki.ViewBase.extend({
		'events': {
			'change input': 'formElementChanged',
			'change select': 'formElementChanged',
			'keyup input[name="dataItem"]': 'testDatasourceToolbar',
			'click .btn-group.datasource .add': 'addDatasourceItem',
			'click .btn-group.datasource .remove': 'removeDatasourceItem',
			'click .btn-group.datasource .select': 'selectDatasourceItem',
			'click .btn-group.main .add': 'saveModel',
			'click .btn-group.main .update': 'saveModel',
			'click .btn-group.main .createnew': 'createNew'
        },
		
		'initialize': function(config) {
			var $template = $('#formelement-template')
				, i
				, cols = config['cols']
				, rows = config['rows'];
			this.projectId = config['projectId'];
			this.onSaved = config['onSaved'];
			this.cols = [];
			this.rows = [];
			for(i = 1; i <= cols; i++){
				this.cols.push(i);
			}
			for(i = 1; i <= rows; i++){
				this.rows.push(i);
			}
			
			if(!this.model){
				this.model = this.getDefaultModel();
			}
            _.bindAll(this, 'render');
			this.model.bind('change', this.render);
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },
		
		'render': function(model, changes){
			var $el
				, content
				, selectedColIndex
				, selectedRowIndex
				, elementType;
			
			model = model || this.model;
			
			if(this.el){
				$el = $(this.el);
				elementType = this.model.get('elementType');
				selectedColIndex = parseInt($el.find('select[name="colIndex"] option:selected').val(), 10);
				selectedRowIndex = parseInt($el.find('select[name="rowIndex"] option:selected').val(), 10);
				content = this.template(
				{
					'elementTypes': ['Textbox', 'TextArea', 'Dropdown List', 'Listbox', 'Checkbox', 'RadioButton', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PlainText', 'Terms and Conditions']
					, 'context': this
					, 'isNew': model.isNew()
					, 'model': model.toJSON()
					, 'cols': this.cols.sort(this.sortIntAsc)
					, 'rows': this.rows.sort(this.sortIntAsc)
					, 'colIndexShow': model.get('colIndex') !== selectedColIndex
					, 'rowIndexShow': model.get('rowIndex') !== selectedRowIndex
					, 'parseDefault': this.parseDefault
					, 'toBool': this.toBool
					, 'supportsValueAttribute': [0,1,2,3,4,5,13].indexOf(elementType) !== -1
				});
				$el.html(content);
				this.initDataBindingControls();
				this.testDatasourceToolbar();
				this.validate({
					'validators': {
						'minoptions': function (  ) {
							return { 
								'validate': function(val, minLength, parsleyField){
									return parsleyField.$element['0'].options.length > minLength - 1;
								}, 
								'priority': 2
							}
						}
						, 'selectone': function (  ) {
							return { 
								'validate': function(val, name, parsleyField){
									if(!name){
										return true;
									}
									return !(val === '-1' && !$el.find('input[name="' + name + '"]').val());
								}, 
								'priority': 2
							}
						}
					}
					, 'messages': {
							'minoptions': "Datasource cannot be empty."
							, 'selectone': 'Tag required. Select an existing tag or enter a new one.'
					}
				});
			}

			this.$('.btn-group.main .update.btn').prop('disabled', this.model.isNew());
			this.$('.btn-group.main .createnew.btn').prop('disabled', this.model.isNew());
			this.$('.btn-group.main .add.btn').prop('disabled', !this.model.isNew());
			this.tooltip();
		},
		'toBool': function(value){
			if(typeof(value) === 'boolean'){
				return value;
			}
			return parseInt(this.parseDefault(value, 0), 10) !== 0;
		},
		'addDatasourceItem': function(e){
			var $dataItem = $(this._dataItem)
				, $bindingData = $(this._bindingData)
				, options
				, value = $dataItem.val();
			if(value.trim().length > 0){
				$bindingData.append('<option value="' + $bindingData[0].options.length + '">' + value + '</option>');
				$dataItem.val('');
				//now trigger parsley validation
				options = $bindingData[0].options;
				$(options[options.length - 1]).attr('selected', 'selected');
				$bindingData.focus();
			}
		},
		'removeDatasourceItem': function(e){
			var bindingData = this._bindingData
				, selectedIndex = bindingData.selectedIndex
				, $option = $(bindingData.options[selectedIndex])
				, value = this.model.get('value')
				, currentValue = $option.text();

			if(value === currentValue){
				//reset the default value
				$(this._value).val('');
				this.model.set({'value': ''}, {silent: true});
			}
			
			$option.remove();
			this.testDatasourceToolbar();
		},
		
		'selectDatasourceItem': function(){
			var bindingData = this._bindingData
				, selectedIndex = bindingData.selectedIndex
				, $option = $(bindingData.options[selectedIndex])
				/**String*/
				, value = $option.text();

			$(this._value).val(value);
			this.model.set({'value': value}, {silent: true});
			this.testDatasourceToolbar();
		},
		
		'formElementChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value
				, data = {}
				, silent = true
				, groupName
				, targetValue
				, isValidationField = $target.hasClass('validation')
				, validation = this.model.get('validation')
				, targetIsChecked;
			switch(name){
				case 'label':
					value = $target.val();
				break;
				case 'colIndex':
				case 'rowIndex':
					value = this.parseSelectValue(type, $target, name);
					if(value === -1){
						return;
					}
				break;
				case 'bindingData':
					this.testDatasourceToolbar();
					return;
				break;
				case 'value':
					if(type === 'checkbox'){
						value = $target.is(':checked');
					}else{
						value = $target.val();
					}
				break;
				case 'defaultselection':
					value = [$target.is(':checked')];
					name = 'bindingData';
				break;
				case 'className':
					value = $target.val();
				break;
				case 'lineSeparator':
					value = $target.is(':checked');
				break;
				case 'once':
					value = $target.is(':checked');
				break;
				case 'capability':
					value = parseInt($target.val(), 10);
				break;
				case 'elementType':
					value = parseInt($target.val(), 10);
					this.model.set({'colIndex': 0, 'rowIndex': 0}, {'silent': true});
					silent = false;
				break;
				case 'entrytype':
					groupName = $target.find(":selected").val();
					value = this.cloneObject(validation, groupName, true); 
					$(['digits', 'number', 'alphanum']).each(function(i, prop){
						if (prop !== groupName){
							value[prop] = null;
						}
					});
					name = 'validation';
				break;
				default:
					if(isValidationField){
						targetIsChecked = $target.is(':checked');
						if(name === 'email'){
							silent = false;
							if(!targetIsChecked){
								data['capability'] = 0;
							}
						}
						value = this.cloneObject(validation, name, type === 'checkbox' ? targetIsChecked : $target.val());
						name = 'validation';
					}
			}
			
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		'cloneObject': function(o, key, value){
			var x
				, result = {};
			for(x in o){
				if(o.hasOwnProperty(x)){
					result[x] = key === x ? value : o[x];
				}
			}
			return result;
		},
		'parseSelectValue': function(type, $target, name){
			var value
				, $field = $(this.el).find('.form-group.new' + name);
			if (type === 'select-one'){
				value = $target.find('option:selected').val();
				if(value !== '-1'){
					$field.addClass('hide');
				}
				else{
					$field.removeClass('hide');
					value = $target.val();
				}
			} else{
				$(this.el).find('select[name="' + name + '"]').parsley( 'destroy' );
				value = $target.val();
			}
			return parseInt(value, 10);
		},
		'testDatasourceToolbar': function(){
			if(!this._bindingData){
				return;
			}
			var $el = $(this.el), 
				bindingData = this._bindingData, 
				selectedIndex = bindingData.selectedIndex,
				selectedValue = $(bindingData.options[selectedIndex]).text(),
				$addButton = $el.find('.btn-group.datasource .add'),
				$removeButton = $el.find('.btn-group.datasource .remove'),
				$defaultSelectionButton = $el.find('.btn-group.datasource .select'),
				disabled = bindingData.options.length === 0 || selectedIndex === -1;
			
			$addButton.prop('disabled', $(this._dataItem).val().length === 0);
			$removeButton.prop('disabled', disabled);
			$defaultSelectionButton.prop('disabled', disabled || $(this._value).val() === selectedValue);
		},
	
		'initDataBindingControls': function(){
			var el = $(this.el);
			this._bindingData = el.find('select[name="bindingData"]')[0];
			this._value = el.find('input[name="value"]')[0];
			this._dataItem = el.find('input[name="dataItem"]')[0];
		},
		
		'saveModel': function(e){
			var context = this
				, model = this.model
				, wasNew = model.isNew()
				, bindingData
				, onSaved = this.onSaved
				, validation = model.get('validation')
				, colIndex = model.get('colIndex')
				, rowIndex = model.get('rowIndex');
				
			this.initDataBindingControls();
			
			if(this._bindingData){
				bindingData	= $(this._bindingData.options).map(function(){ 
					return $(this).text();
				}).get();
				model.set({'bindingData': bindingData}, {'silent': true});
			}
			
			if(!this.isValid()){
				return;
			}
			
			if(!_.contains(this.cols, colIndex)){
				this.cols.push(colIndex);
			}
			
			if(!_.contains(this.rows, rowIndex)){
				this.rows.push(rowIndex);
			}
			
			if(!this.toBool(validation.digits) && !this.toBool(validation.number)){
				validation['max'] = 0;
				validation['min'] = 0;
			}
			if (this.toBool(validation.digits) || this.toBool(validation.number)){
				validation['maxLength'] = 0;
				validation['minLength'] = 0;
			}
			model.set({'validation': validation}, {'silent': true});
			
			model.save(model.attributes, { 'success':
				function(model, resp){
					context.model = model;
					if(wasNew){
						context.resetModel();
					}
					if(onSaved){
						onSaved(model);
					}
				}
			});
		},
		
		'createNew': function(e){
			this.resetModel();
		},
		
		'getDefaultModel': function(){
			return new Booki.FormElement({
				'id': null
				, 'projectId': this.projectId
				, 'label': ''
				, 'elementType': Booki.ElementType.textbox
				, 'lineSeparator': false
				, 'rowIndex': 0
				, 'colIndex': 0
				, 'className': ''
				, 'value': ''
				, 'bindingData': []
				, 'once': false
				, 'capability': 0
				, 'validation': {
					'required': null
					, 'notBlank': null
					, 'minLength': null
					, 'maxLength': null
					, 'min': null
					, 'max': null
					, 'regex': null
					, 'email': null
					, 'url': null
					, 'digits': null
					, 'number': null
					, 'alphanum': null
					, 'dateIso': null
				}
			});
		},
		
		'resetModel': function(){
			this.model = this.getDefaultModel();
			this.model.bind('change', this.render);
			this.render();
		}
	});
})(window['ajaxurl'], window['jQuery'], window['_']);
(function(url, $, moment, accounting){
	Booki.OptionalsView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .delete.btn': 'delete',
			'click .createnew.btn': 'createNew',
			'click .add.btn': 'save',
			'change input': 'inputChanged',
			'change select': 'listboxChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#optionals-template');
			this.projectId = config['projectId'];
			
            _.bindAll(this, 'render');
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.currencySymbol = Booki.localization.currencySymbol;
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			
			this.render();
		},
		
		'render': function(){
			var context = this,
				projectId = this.projectId,
				selectedId = this.selectedId,
				model,
				models;

			models = new Booki.Optionals({'projectId': projectId});
			models.fetch({'success': function(models, resp){
					model = models.find(function(item){
						return item.get('id') === selectedId;
					});
					if (!model){
						model = context.getDefaultModel();
					}
					context.renderOptionals(model, models);
				}
			});
		},
		
		'renderOptionals': function(model, models){
			var $el,
				content;
			
			this.model = model;
			this.models = models || new Booki.Optionals();
			
			this.models.bind('remove', this.render);
			if(model){
				model.bind('change', this.render);
			}

			if(!this.el){
				return;
			}
			$el = $(this.el);
			
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': models.models
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.$('.createnew.btn').prop('disabled', model.isNew());
			this.$('.update.btn').prop('disabled', model.isNew());
			this.$('.delete.btn').prop('disabled', model.isNew());
			this.$('.add.btn').prop('disabled', !model.isNew());
			
			this.validate();
			this.tooltip();
		},
		
		'createNew': function(e){
			this.selectedId = null;
			this.renderOptionals(this.getDefaultModel(), this.models);
		},
		
		'listboxChanged': function(e){
			var models = this.models, 
				target = e.srcElement || e.target, 
				selectedOption = parseInt($('option:selected', target).val(), 10),
				model = models.find(function(item){
						return item.get('id') === selectedOption;
				});
			if(model){
				this.renderOptionals(model, models);
			}
		},
		
		'delete': function(e){
			var optional = this.model;
			
			optional.destroy({'success': 
				function(model, resp){}
			});
		},
		
		'save': function(e){
			var optional = this.model;
			
			if(!this.isValid()){
				return;
			}
			
			optional.save(optional.attributes, { 'success':
				function(model, resp){} 	
			});
		},
		
		'inputChanged': function(e){
			var target = e.srcElement || e.target,
				$target = $(target),
				name = target['name'],
				type = target['type'],
				value,
				data = {},
				silent = true,
				model;
			switch(name){
				case 'name':
				value = $target.val();
				break;
				case 'cost':
				value = $target.val();
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.Optional({
				'id': null,
				'projectId': this.projectId,
				'name': '',
				'cost': ''
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);
(function(url, $){
	Booki.ProjectsView = Booki.ViewBase.extend({
		'events': {
			'change select[name]': 'projectSelectionChanged'
        },
		
        'initialize': function(config) {
			var $template = $('#projects-template');
			this.selectedId = -1;
            _.bindAll(this, 'render');
			if(!this.collection){
				this.collection = new Booki.Projects();
			}
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.tags = new Backbone.Collection();
			this.render();
        },
        
        'render': function(model) {
			var context = this
				, collection = this.collection
				, models
				, tags;
			if(this.el){
				collection.fetch({
					success: function(models, resp){
						context.collection.reset(resp['projects']);
						context.tags.reset(resp['tags']);
						context.output(context.collection);
						if(context.selectedId === -1){
							context.tabs();
							context.createProjectView();
						}else{
							context.tabs('enable');
						}
					}
				});
			}
			return this;
        }, 
		
		'output': function(collection){
			var $el = $(this.el),
				context = this,
				content = this.template({
					'selectedId': this.selectedId
					, 'models': collection.models
				});
			$el.html(content);

			return this;
		},
		
		'createProjectView': function(model){
			var context = this;
			if(this.projectView){
				this.projectView.undelegateEvents();
			}
			this.projectView = new Booki.ProjectView({
				'el': $('#project-view')
				, 'model': model
				, 'tags': this.tags
				, 'onDeleted': function(model){
					context.selectedId = -1;
					context.collection.remove(model);
					context.render();
					context.createProjectView();
				}
				, 'onCreated': function(model){
					var _model = context.getModel(model.get('id'));
					if(_model){
						//we are updating
						context.collection.remove(_model, {silent: true});
					}
					context.selectedId = model.get('id');
					context.collection.add(model);
					context.render();
				}
			});
		},
		
		'projectSelectionChanged': function(e){
			var val = $(e.currentTarget).val()
				, id = parseInt(val, 10)
				, model = this.getModel(id);
			this.tabs(id !== -1 ? 'enable' : '');
			this.selectedId = id;
			this.createProjectView(model);
		},
		
		'tabs': function(status){
			var $tabs = $('.booki .wizard-tab li:not(li a[href="#step1"])');
			if(status === 'enable'){
				$tabs.removeClass('disabled');
			}else{
				$tabs.addClass('disabled');
			}
		},
		
		'getModel': function(id){
			return this.collection.find(function(item){
				return item.get('id') === parseInt(id, 10);
			});
		}
    });
})(window['ajaxurl'], window['jQuery']);
(function(url, $, _){
	Booki.ProjectView = Booki.ViewBase.extend({
		'events': {
			'change input[name], textarea[name], select[name="calendarMode"], select[name="defaultStep"], select[name="bookingMode"], select[name="tag"], select[name="bookingWizardMode"]': 'projectChanged'
			, 'click .duplicateProject': 'duplicate'
			, 'click .delete': 'delete'
			, 'click .update': 'update'
			, 'click .preview-thumbnail button': 'thumbnailCloseClick'
        },
		
        'initialize': function(config) {
			var $template = $('#project-template');
            _.bindAll(this, 'render');
			this.onDeleted = config['onDeleted'];
			this.onCreated = config['onCreated'];
			this.tags = config['tags'];
			if(!this.model){
				this.model = this.getDefaultModel();
			}
			this.model.bind('change', this.render);
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },
		
        'render': function() {
			var context = this 
				, $el = $(this.el)
				, model = this.model
				, isNew = model.isNew()
				, content;
				
			if(this.el){
				$el = $(this.el);
				content = this.template({
					'isNew': isNew
					, 'model': model.toJSON()
					, 'tags': this.tags.models
				});
				$el.html(content);

				this.tooltip();
				this.$imagePickerDialog = $('#imageGalleryModal .modal-body');
				this.$image = $el.find('div.preview-thumbnail');
				this.$imageDefault = $el.find('div.preview-thumbnail-default');
				this.attachImageGalleryHandlers();

				this.validate({
					'validators': {
						'selectone': function (  ) {
							return { 
								'validate': function(val, name, parsleyField){
									if(!name){
										return true;
									}
									return !(val === '-1' && !$el.find('input[name="' + name +'"]').val());
								}, 
								'priority': 2
							}
						}
					}
					, 'messages': {
							'selectone': 'Tag required. Select an existing tag or enter a new one.'
					}
				});
			}
			return this;
        },
		
		'projectChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value = $target['val']()
				, data = {}
				, selectFields = ['calendarMode', 'bookingMode', 'defaultStep', 'bookingWizardMode']
				, $newTagField = $(this.el).find('.form-group.newtag')
				, silent = true;
			if(type === 'checkbox'){
				value = $target.is(':checked');
				if(name === 'enableMonthSelection'){
					silent = false;
					if(!value){
						data['monthsCount'] = 1;
					}
				}
			}else if($.inArray(name, selectFields) > -1){
				value = parseInt($target.find('option:selected').val(), 10);
				if(name === 'calendarMode'){
					silent = false;
					data['enableMonthSelection'] = false;
					data['monthsCount'] = 1;
				}
			} else if (name === 'tag' && type === 'select-one'){
				value = $target.find('option:selected').val();
				if(value !== '-1'){
					$newTagField.addClass('hide');
				}
				else{
					$newTagField.removeClass('hide');
					value = $target['val']();
				}
			} else if (name === 'tag'){
				$(this.el).find('select[name="tag"]').parsley( 'destroy' );
			} else if (name === 'optionalsListingMode'){
				value = parseInt($target.val(), 10);
				if(value === 1){
					data['optionalsMinimumSelection'] = 0;
				}
				silent = false;
			}else if (name === 'optionalsBookingMode' || name === 'optionalsMinimumSelection'){
				value = parseInt($target.val(), 10);
			}
			data[name] = value;
			if(!silent && this.model.isNew()){
				data['tag'] = '';
			}
			this.model.set(data, {'silent': silent});
		},
		'duplicate': function(e){
			var context = this
				, name = this.model.get('duplicateProjectName')
				, id = this.model.get('id')
				, model = new Booki.DuplicateProject({'projectId': id, 'projectName': name})
				, attributes;
			
			if($.trim(name).length === 0){
				return;
			}
			
			attributes = model.attributes;
			attributes['projectName'] = name;
			attributes['projectId'] = id;
			
			model.save(attributes, {'success': 
				function(result){
					context.model.set({'id': result['id'], 'name': name});
					if(context.onCreated){
						context.onCreated(context.model);
					}
					window.console.log(context.model);
				}
			});
		},
		'update': function(e){
			this.save();
		},
		'save': function(){
			var context = this
				, model = this.model
				, tag = model.get('tag')
				, containsTag = this.tags.find(function(item){
					return item.get('name') === tag;
				});
			
			if(!this.isValid()){
				return;
			}
			
			if(!containsTag && tag){
				this.tags.add(new Backbone.Model({'name': tag}));
			}
			model.save(model.attributes, { 'success':
				function(model){
					if(context.onCreated){
						context.onCreated(model);
					}
				}
			});
		},
		
		'delete': function(e){
			var context = this;
			if(this.model){
				this.model.destroy({
					success: function(model){
						if(context.onDeleted){
							context.onDeleted(model);
						}
					}
				});
			}
		},
		'thumbnailCloseClick': function(e){
			e.preventDefault();
			this.updateThumbnail('');
			return false;
		},
		'updateThumbnail': function(previewUrl){
			this.model.set({'previewUrl': previewUrl}, {silent: true});
			if(previewUrl){
				this.$imageDefault.addClass('hide');
				this.$image.css('background-image', 'url("' + previewUrl + '")');
				this.$image.removeClass('hide');
				return;
			}
			this.$image.addClass('hide');
			this.$imageDefault.removeClass('hide');
		},
		'imageGalleryPagerClick': function(e){
            var href = e.target.href
				, callbackUrl
				, data
				, context = this;
            if(href.length === 0){return;}
            
            e.preventDefault();
            this.detachImageGalleryHandlers();
            
            callbackUrl = url + href.substr(href.indexOf('?'));
            
            data = {
				'action': 'mediaLibraryPaging'
			};
			
            $['post']( callbackUrl, data, function(response) {
               context.$imagePickerDialog.html(response);
               context.attachImageGalleryHandlers();
            });
        },
		
        'attachImageGalleryHandlers': function(){
            $('a.booki-first-page, a.booki-prev-page, a.booki-next-page, a.booki-last-page')['on']('click', $.proxy( this.imageGalleryPagerClick, this));
            $('a.image-item-selected')['on']('click', $.proxy( this.imagePickerSelectionChanged, this));
			$('.pager-indicator-textbox').attr('readonly', true);
        },
		
		'detachImageGalleryHandlers': function(){
            $('a.first-page, a.prev-page, a.next-page, a.last-page')['off']();
            $('a.image-item-selected')['off']();
        },
		
		'imagePickerSelectionChanged': function(e){
            var previewUrl
				, href = e.currentTarget.href;
				
            e.preventDefault();
            if(href.length === 0){return;}
            
            previewUrl = href.substr(href.indexOf('#') + 1);

            if(previewUrl.length > 0){
				this.updateThumbnail(previewUrl);
            }
        },
		
		'getDefaultModel': function(){
			return new Booki.Project({
				'id': null
				, 'status': Booki.ProjectStatus.running
				, 'name': ''
				, 'duplicateProjectName': ''
				, 'bookingDaysMinimum': 0
				, 'bookingDaysLimit': 1
				, 'calendarMode': Booki.CalendarMode.popup
				, 'bookingMode': Booki.BookingMode.reservation
				, 'description': ''
				, 'previewUrl': ''
				, 'tag': ''
				, 'notifyUserEmailList': ''
				, 'optionalsBookingMode': 0
				, 'optionalsListingMode': 0
				, 'optionalsMinimumSelection': 0
				, 'defaultStep': Booki.ProjectStep.bookingForm
				, 'bookingTabLabel': Booki.resx.PROJECT_TAB_BOOKING_TAB_LABEL_DEFAULT
				, 'customFormTabLabel': Booki.resx.PROJECT_TAB_CUSTOM_FORM_TAB_LABEL_DEFAULT
				, 'availableDaysLabel': Booki.resx.PROJECT_TAB_AVAILABLE_DAYS_LABEL_DEFAULT
				, 'selectedDaysLabel': Booki.resx.PROJECT_TAB_SELECTED_DAYS_LABEL_DEFAULT
				, 'bookingTimeLabel': Booki.resx.PROJECT_TAB_BOOKING_TIME_LABEL_DEFAULT
				, 'optionalItemsLabel': Booki.resx.PROJECT_TAB_OPTIONAL_ITEM_LABEL_DEFAULT
				, 'nextLabel': Booki.resx.PROJECT_TAB_NEXT_LABEL_DEFAULT
				, 'prevLabel': Booki.resx.PROJECT_TAB_PREV_LABEL_DEFAULT
				, 'addToCartLabel': Booki.resx.PROJECT_TAB_ADD_TO_CART_LABEL_DEFAULT
				, 'fromLabel': Booki.resx.PROJECT_TAB_FROM_LABEL_DEFAULT
				, 'toLabel': Booki.resx.PROJECT_TAB_TO_LABEL_DEFAULT
				, 'proceedToLoginLabel' : Booki.resx.PROJECT_TAB_PROCEED_TO_LOGIN_LABEL_DEFAULT
				, 'makeBookingLabel': Booki.resx.PROJECT_TAB_MAKE_BOOKING_LABEL_DEFAULT
				, 'bookingLimitLabel': Booki.resx.PROJECT_TAB_BOOKING_LIMIT_LABEL_DEFAULT
				, 'contentTop': ''
				, 'contentBottom': ''
				, 'bookingWizardMode': Booki.BookingWizardMode.tabs
				, 'hideSelectedDays': false
			});
		}
    });
})(window['ajaxurl'], window['jQuery'], window['_']);
(function(url, $){
	Booki.SidenavView = Booki.ViewBase.extend({
		'initialize': function(config){
			var $template = config['template'];
			this.disableHyperLinkDelegate = function(e){
				e.preventDefault();
				return false;
			};
			_.bindAll(this, 'render');
			
			if(this.model){
				this.model.bind('change', this.render);
			}
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
			this.tabId = config['tab'];
		},
		
		'render': function(){
			var content;
			if(this.el){
				content = this.template(
				{
					'model': this.model
				});
				$(this.el).html(content);
			}
			return this;
		},
		
		'showTab': function(index){
			var tabs = $(this.tabId).find('a');
			$(tabs[index]).tab('show');
		},
		
		'disableTab': function(index, disable){
			var tabs = $(this.tabId).find('a'),
				$tab = $(tabs[index]),
				del = this.disableHyperLinkDelegate;

			if(disable){
				$tab.bind('click', del);
				$tab.parent().addClass('disabled');
				$tab.removeAttr('data-toggle');
				$tab.css('cursor', 'not-allowed');
			}
			else{
				$tab.unbind('click', del);
				$tab.parent().removeClass('disabled');
				$tab.attr('data-toggle', 'tab');
				$tab.css('cursor', '');
			}
		},
		
		'dispose': function(){
			var $tab = $(this.tabId)
				, del = this.disableHyperLinkDelegate
				, tabs;
			if(!$tab || !this.el){
				return;
			}
			tabs = $tab.find('a');
			$.each(tabs, function(i, tab){
				$(tab).unbind('click', del);
			});
			//reset default tab
			$(tabs[0]).tab('show');
		}
	});
})(window['ajaxurl'], window['jQuery']);

