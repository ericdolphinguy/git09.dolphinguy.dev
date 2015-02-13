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