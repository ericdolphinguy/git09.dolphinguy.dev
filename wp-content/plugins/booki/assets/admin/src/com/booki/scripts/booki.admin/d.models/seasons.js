(function(url){
	Booki.Seasons = Backbone.Collection.extend({
		'url': url
		, 'action' : {'read': 'booki_readAllSeasons'}
	});
})(window['ajaxurl']);