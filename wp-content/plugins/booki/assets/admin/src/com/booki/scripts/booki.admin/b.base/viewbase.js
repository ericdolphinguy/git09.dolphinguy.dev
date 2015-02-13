(function(window, $, _){
	Booki.ViewBase = Backbone.View.extend({
		'validate': function(options){
			$(this.el).find('.booki_parsley_validated').each(function(){
				$(this).parsley(options);
			});
		},
		
		'isValid': function(){
			var isValid = true;
			$(this.el).find('.booki_parsley_validated').each(function(){
				var $elem = $(this),
					result;
				if (!$elem.is(':visible') || $elem.is(':disabled')){
					return true;
				}
				result = $(this).parsley().validate(true);
				if(result !== null && (typeof(result) === 'object' && result.length > 0)){
					isValid = false;
				}
			});
			return isValid;
		},
		
		'parseDefault': function(value, def){
			if(typeof(value) === 'undefined' || value === null){
				return typeof(def) === 'undefined' ? '' : def;
			}
			return value;
		},
		
		'tooltip': function(){
			$('[data-toggle=tooltip]').tooltip();
		},
		
		'sortIntAsc': function(a, b){
			 return a - b;
		}
	});
})(window, jQuery, _);