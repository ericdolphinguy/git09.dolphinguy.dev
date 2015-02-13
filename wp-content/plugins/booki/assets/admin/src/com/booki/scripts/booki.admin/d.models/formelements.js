(function(url){
	Booki.FormElements = Backbone.Collection.extend({
		'model': Booki.FormElement,
		'url': url,
		'action' : {'read': 'booki_readAllFormElement'},
		'comparator': function(model) {
			return model.get('rowIndex');
		}
	});
})(window['ajaxurl']);
