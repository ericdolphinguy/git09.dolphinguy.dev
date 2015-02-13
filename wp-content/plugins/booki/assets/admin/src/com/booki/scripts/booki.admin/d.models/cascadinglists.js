(function(url){
	Booki.CascadingLists = Backbone.Collection.extend({
		'model': Booki.CascadingList,
		'url': url,
		'action' : {'read': 'booki_readAllCascadingList'}
	});
})(window['ajaxurl']);
