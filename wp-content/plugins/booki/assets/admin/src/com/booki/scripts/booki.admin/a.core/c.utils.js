(function($){
	/**
		@function
		@description pads numbers with zero
		@param {number=} size 
	*/
	Number.prototype.pad = function(size){
	  if(typeof(size) !== 'number'){size = 2;}
	  var s = String(this);
	  while (s.length < size) s = '0' + s;
	  return s;
	};
	
	Booki.rfc3986EncodeURIComponent = function (str) {  
		return encodeURIComponent(str).replace(/[!'()*]/g, escape);  
	};
})(window['jQuery']);