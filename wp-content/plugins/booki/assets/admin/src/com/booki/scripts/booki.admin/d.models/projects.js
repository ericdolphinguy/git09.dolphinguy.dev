(function(url, $){
	Booki.Projects = Backbone.Collection.extend({
		'model': Booki.Project,
		'url': url,
		'action' : {'read': 'booki_readAllProject'}
	});
})(window['ajaxurl'], window['jQuery']);