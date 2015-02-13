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