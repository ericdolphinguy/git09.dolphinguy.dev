(function($){
	$['fn']['BookiModalPopup'] = function(method) {
		var $this
			, methods = {
			'init': function(options){
				var settings
					, $confirmButton;
				$this = this;
				$(document).ready(function(){
					settings = $.extend({}, options);
					$confirmButton = $this.find('.booki-confirm');
					$this.on('show.bs.modal', function (e) {
						var $target = $(e.relatedTarget)
							, id = $target.data('bookiId');
						$confirmButton.attr('value', id);
					});
				});
			}
			, 'destroy' : function( ) {
				$this.off();
			}
		};
		
		if ( methods[method] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiModalPopup' );
		}
	};
	
})(window['jQuery']);