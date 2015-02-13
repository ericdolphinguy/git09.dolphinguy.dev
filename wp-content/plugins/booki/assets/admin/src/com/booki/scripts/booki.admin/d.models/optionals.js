(function(url){
	Booki.Optionals = Backbone.Collection.extend({
		'model': Booki.Optional,
		'url': url,
		'action' : {'read': 'booki_readAllOptional'}
	});
})(window['ajaxurl']);
