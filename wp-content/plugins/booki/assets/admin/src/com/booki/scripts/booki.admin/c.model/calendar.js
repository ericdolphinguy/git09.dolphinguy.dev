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