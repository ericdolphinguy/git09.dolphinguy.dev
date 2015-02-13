(function(url){
	Booki.CascadingItem = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertCascadingItem'
			, 'update': 'booki_updateCascadingItem'
			, 'delete': 'booki_deleteCascadingItem'
			, 'read':   'booki_readCascadingItem'
		}
	});
})(window['ajaxurl']);