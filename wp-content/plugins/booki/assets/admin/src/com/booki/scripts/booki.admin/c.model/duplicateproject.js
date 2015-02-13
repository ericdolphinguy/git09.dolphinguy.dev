(function(url){
	Booki.DuplicateProject = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_duplicateProject'
		}
	});
})(window['ajaxurl']);