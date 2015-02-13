(function(url){
	Booki.Optional = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertOptional'
			, 'update': 'booki_updateOptional'
			, 'delete': 'booki_deleteOptional'
			, 'read':   'booki_readOptional'
		}
	});
})(window['ajaxurl']);