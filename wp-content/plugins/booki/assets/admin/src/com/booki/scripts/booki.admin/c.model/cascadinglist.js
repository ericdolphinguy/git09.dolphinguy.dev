(function(url){
	Booki.CascadingList = Booki.ModelBase.extend({
		'url': url
		, 'action': {
			'create': 'booki_insertCascadingList'
			, 'update': 'booki_updateCascadingList'
			, 'delete': 'booki_deleteCascadingList'
			, 'read':   'booki_readCascadingList'
		}
	});
})(window['ajaxurl']);