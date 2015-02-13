(function(window, url, $){
	Booki.FilteredCollectionBase = Backbone.Collection.extend({
		'initialize': function(models, options) {
			this.id = options['id'];
		},
		'sync' : function(method, model, options) {
			var params;
			options || (options = {});
			
			params = Booki.httpRequestParams(method, model, options);
			
			params['data']['model'] = window['JSON']['stringify']({'id': model['id']});
			// Make the request, allowing the user to override any Ajax options.
			return $.ajax(_.extend(params, options));
		}
	});
})(window, window['ajaxurl'], window['jQuery']);
