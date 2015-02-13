(function(url){
	Booki.CleanupCalendarDays = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'delete': 'booki_cleanupCalendarDay'
		}
	});
})(window['ajaxurl']);