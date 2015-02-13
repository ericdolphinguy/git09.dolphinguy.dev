(function($, moment){
	$['fn']['BookiBookingWizard'] = function(method) {
		var projectId
		, $bookingButton
		, $nextButton
		, $backButton
		, $step1
		, $tabs
		, $el
		, $errorContainer
		, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings;

				$(document).ready(function(){
					$el = $this;
					settings = $.extend({}, options);
					
					if(settings['projectId'] !== undefined){
						projectId = settings['projectId'];
					}
					
					if(settings['bookingButton']){
						$bookingButton = typeof(settings['bookingButton']) === 'string' ? 
											$this.find(settings['bookingButton']) : settings['bookingButton'];
					}
					
					if(settings['nextButton']){
						$nextButton = typeof(settings['nextButton']) === 'string' ? 
											$this.find(settings['nextButton']) : settings['nextButton'];
					}
					
					if(settings['backButton']){
						$backButton = typeof(settings['backButton']) === 'string' ? 
											$this.find(settings['backButton']) : settings['backButton'];
					}
					
					if(settings['step1']){
						$step1 = typeof(settings['step1']) === 'string' ? 
											$this.find(settings['step1']) : settings['step1'];
					}
					
					if(settings['tabs']){
						$tabs = typeof(settings['tabs']) === 'string' ? 
											$this.find(settings['tabs']) : settings['tabs'];
					}
					
					$errorContainer = $(settings['errorContainer']);
					if($tabs.length < 2){
						if($step1){
							$step1.tab('show');
						}
						$nextButton.addClass('hide');
						$backButton.addClass('hide');
						return;
					}
					
					$nextButton.on('click', methods['wizardButtonClick']);
					$backButton.on('click', methods['wizardButtonClick']);
					
					methods['toggleButtons'](0);
					
					$tabs.on('click', methods['tabsClick']);
					$('[data-toggle=tooltip]').tooltip();
				});
			}
			, 'validate': function(){
				if(!methods['isValid']()){
					$errorContainer.removeClass('hide');
				}else{
					$errorContainer.addClass('hide');
				}
			}
			, 'tabsClick': function (e) {
				var $activeTab = $(this);
				e.preventDefault();
				methods['validate']();
				methods['toggleButtons']($activeTab.data('step'));
			}
			, 'toggleButtons': function (selectedStep){
				if(parseInt(selectedStep, 10) === 0){
					$bookingButton.addClass('hide');
					$nextButton.removeClass('hide');
					$backButton.addClass('hide');
				}else{
					$bookingButton.removeClass('hide');
					$nextButton.addClass('hide');
					$backButton.removeClass('hide');
				}
				$('.booki' + projectId + '.nav.nav-tabs a[data-step="' + selectedStep + '"]').tab('show');
			}
			, 'wizardButtonClick': function (e){
				var $currentTarget = $(e.currentTarget);
				methods['validate']();
				methods['toggleButtons']($currentTarget.data('step'));
			}
			, 'isValid': function(){
				var isValid = true
					, $validators = $el.find('.booki_parsley_validated');
				$validators.each(function(){
					var $elem = $(this),
						result;
					if (!$elem.is(':visible') || $elem.is(':disabled')){
						return true;
					}
					result = $elem.parsley().validate(true);
					if(result !== null && (typeof(result) === 'object' && result.length > 0)){
						isValid = false;
					}
				});
				return isValid;
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
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiBookingWizard' );
		}    
	};
	
})(window['jQuery'], window['moment']);