(function(url){
	Booki.CascadingItems = Backbone.Collection.extend({
		'model': Booki.CascadingItem,
		'url': url,
		'action' : {'read': 'booki_readAllCascadingItem'}
	});
})(window['ajaxurl']);
