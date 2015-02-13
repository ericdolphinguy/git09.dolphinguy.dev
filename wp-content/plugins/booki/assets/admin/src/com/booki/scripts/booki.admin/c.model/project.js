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