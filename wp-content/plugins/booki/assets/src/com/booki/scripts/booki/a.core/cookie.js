/**
	 @license Copyright @ 2014 Alessandro Zifiglio. All rights reserved. http://www.booki.io
*/
(function($){
	var methods = {
		'create': function(name,value,days) {
			var date
				, expires;
			if (days) {
				date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				expires = "; expires="+date.toGMTString();
			}
			else {
				expires = "";
			}
			document.cookie = name+"="+value+expires+"; path=/";
		}
		, 'read': function(name) {
			var nameEQ = name + "="
				, ca = document.cookie.split(';')
				, c
				, i;
			for(i=0;i < ca.length;i++) {
				c = ca[i];
				while (c.charAt(0)==' '){ 
					c = c.substring(1,c.length);
				}
				if (c.indexOf(nameEQ) == 0){ 
					return c.substring(nameEQ.length,c.length);
				}
			}
			return null;
		}
		, 'erase': function(name) {
			methods['create'](name,"",-1);
		}
		, 'destroy' : function( ) {
			return this.each(function(){
				//clean up
			});
		}
	};
		
	$['fn']['BookiCookie'] = function(method) {
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiCookie' );
		}    
	};
	
})(window['jQuery']);