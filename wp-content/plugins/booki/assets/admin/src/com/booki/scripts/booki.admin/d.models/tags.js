(function(url){
	Booki.Tags = Backbone.Collection.extend({
		'url': url, 
		'action': {
			'read': 'booki_readAllProjectTags'
		}
	});
})(window['ajaxurl']);