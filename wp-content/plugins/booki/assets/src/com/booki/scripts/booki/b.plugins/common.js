(function($){
	$(document).ready(function(){
		$('[data-toggle=tooltip]').tooltip();
		$('[data-toggle=popover]').popover({container: 'body'});
	});
})(window['jQuery']);