(function($, jstz){
	$['fn']['BookiTimezoneControlState'] = function(method) {
		var cookieName = 'BOOKITIMEZONE'
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, result
					, selectedZone
					, url;

				settings = $.extend({}, options);
				result = methods['readState']();
				$this.each(function(){
					var $a = $(this);
					url = $a.prop('href');
					url = methods['updateQueryString'](url, 'timezone', result['selectedZone']);
					$a.prop('href', url);
				});
			}
			, 'parseSavedState': function(state){
				var values;
				if(!state){
					return {
						'autoRun': true
						, 'selectedZone': null
					};
				}
				values = state.split(':');
				return {
					'autoRun': values[0] === 'true'
					, 'selectedZone': values[1] === 'null' ? null : values[1]
				};
			}
			, 'readState': function(){
				var result = $().BookiCookie('read', cookieName)
					, newValue = true
					, selectedZone;
				if(!result){
					selectedZone = methods['guessTimezone']();
					result = methods['saveState'](newValue, selectedZone);
				}
				return methods['parseSavedState'](result);
			}
			, 'saveState': function(newValue, timezoneValue){
				var result = $().BookiCookie('read', cookieName)
					, value = newValue + ':' + timezoneValue;
				if(value !== result){
					$().BookiCookie('erase', cookieName);
					$().BookiCookie('create', cookieName, value, 30);
					return value;
				}
				return result;
			}
			, 'guessTimezone': function(){
				var guessedTimezone = jstz['determine']();
				return guessedTimezone['name']();
			}
			, 'updateQueryString': function(url, param, value){
				var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))')
					, parts = url.toString().split('#')
					, hash = parts[1]
					, qstring = /\?.+$/
					, newURL;
					
				url = parts[0];
				newURL = url;
				
				if (val.test(url))
				{
					newURL = url.replace(val, '$1' + param + '=' + value);
				}
				else if (qstring.test(url))
				{
					newURL = url + '&' + param + '=' + value;
				}
				else
				{
					newURL = url + '?' + param + '=' + value;
				}
				if (hash)
				{
					newURL += '#' + hash;
				}
				return newURL;
			}
			, 'destroy' : function( ) {
				return this.each(function(){
					//clean up
				});
			}
		};
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiTimezoneControlState' );
		}   
	};
	
})(window['jQuery'], window['jstz']);