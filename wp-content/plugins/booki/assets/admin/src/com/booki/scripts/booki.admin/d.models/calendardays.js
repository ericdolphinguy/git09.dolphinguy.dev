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