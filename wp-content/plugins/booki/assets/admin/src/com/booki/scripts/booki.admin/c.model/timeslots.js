(function(url){
	Booki.TimeSlots = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'read': 'booki_createTimeSlots'
		}
	});
})(window['ajaxurl']);