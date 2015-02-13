(function(url, moment){
	Booki.NamelessDays = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'delete': 'booki_deleteNamelessDays'
		}
	});
})(window['ajaxurl'], window['moment']);