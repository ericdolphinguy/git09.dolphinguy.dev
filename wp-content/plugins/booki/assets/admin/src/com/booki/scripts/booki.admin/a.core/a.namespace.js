/**
	 @license Copyright @ 2014 Alessandro Zifiglio. All rights reserved. http://www.booki.io
*/
(function(window, $, _){
/**
		@function Augments the function object with an exports method that facilitates exposing a method out of it's contained scope to the outside.
		@description Exports the symbol out of it's contained scope and exposes it to the outside by attaching it to the window object.
		@param {string|Object} fullName The full name of the class to export outside the private context it is in.
		@param {Object=} obj The object to export. If not supplied, nothing is going to happen which is useful when wanting to trick google closure compiler from removing dead code. 
	*/
	Function.extern = function (fullName, obj) {
		var parts, current, length, i, container = window;
		if(!fullName || !obj)return;
		parts = fullName.split('.');
		length  = parts.length;
		for(i = 0; i < length; i++) {
			current = parts[i];
			if (i === (length - 1)) {
				container[current] = obj;
			}
			else if(container[current]) {
				container = container[current];
			}
			else {
				container = container[current] = {};
			}
		}
		return this;
	}
	
	/**
		@name Small
		@namespace
		@class
		@constructor
		@description Small is a tiny js library (3.86kb without gzip compression) that provides you with only the absolute necessary boiler plate code you'll end up writing when not using a heavy js library.
	*/
	var Booki = Booki || function(){};
	Function.extern('Booki', Booki);
})(window, jQuery, _);