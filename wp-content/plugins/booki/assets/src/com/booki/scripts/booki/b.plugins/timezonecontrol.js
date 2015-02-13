(function($, jstz){
	$['fn']['BookiTimezoneControl'] = function(method) {
		var $headerCaption
			, $regionSelect
			, $timezoneContainer
			, $timezoneSelect
			, $autoDetect
			, $progressBar
			, $loadOnStart
			, $collapseTimezone
			, $timezoneManualSelection
			, ajaxUrl
			, cookieName = 'BOOKITIMEZONE'
			, flag
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, result;

				$(document).ready(function(){
					settings = $.extend({}, options);
					$regionSelect = $this.find(settings['region']);
					$timezoneContainer = $this.find(settings['timezone']);
					$timezoneSelect = $timezoneContainer.find('select[name="timezone"]');
					$autoDetect = $this.find('input[name="autodetect"]');
					$headerCaption = $this.find(settings['headerCaption']);
					$loadOnStart = $(settings['loadOnStart']);
					$timezoneManualSelection = $(settings['timezoneManualSelection']);

					$collapseTimezone = $this.find('.collapseTimezone');
					$progressBar = $this.find('.progress');
					ajaxUrl = settings['ajaxurl'];
					
					$timezoneSelect.on('change', function(){
						if(flag){
							flag = false;
							return;
						}
						var $sel = $(this).find(':selected');
						$headerCaption.html($sel.text());
						$autoDetect.prop('checked', false);
						methods['saveState'](false, $sel.val());
						$collapseTimezone.collapse('hide');
					});

					$regionSelect.on('change', function(){
						var value = $(this).val();
						methods['timezoneChoice'](value, null);
					});

					if($autoDetect.length > 0){
						$autoDetect.on('click', function(){
							var isChecked = $(this).is(':checked')
								, result
								, selectedZone;
							if( isChecked ) {
								selectedZone = methods['guessTimezone'](null, true);
							}else{
								result = methods['readState']();
								selectedZone = result['selectedZone'];
							}
							methods['saveState'](isChecked, selectedZone);
						});
					}
					result = methods['getDefaultTimezone']();
					$autoDetect.prop('checked', result['autoRun']);
					if($timezoneManualSelection.length > 0){
						result['selectedZone'] = $timezoneManualSelection.val();
						$this.find('.autodetect').addClass('hide');
					}
					
					methods['saveState'](result['autoRun'], result['selectedZone']);
					
					if($headerCaption.length > 0){
						$headerCaption[0].title = '';
						$headerCaption.html(result['selectedZone']);
					}
				});
				
				$(document).ajaxStop(function(e){
					if($progressBar){
						$progressBar.addClass('hide');
					}
				});
			}
			, 'getDefaultTimezone': function(){
				var state = $().BookiCookie('read', cookieName)
					, values
					, zone;
				if(!state){
					zone = jstz['determine']()
					return {
						'autoRun': true
						, 'selectedZone': zone['name']()
					};
				}
				values = state.split(':');
				return {
					'autoRun': values[0] === 'true'
					, 'selectedZone': values[1] === 'null' ? null : values[1]
				};
			}
			, 'parseSavedState': function(state, timeZone){
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
			, 'readState': function(saveState){
				if($timezoneManualSelection.length > 0){
					return methods['parseSavedState']();
				}
				saveState = typeof(saveState) === 'undefined' ? true : saveState;
				var result = $().BookiCookie('read', cookieName)
					, newValue = $autoDetect.is(':checked')
					, selectedZone;
				if(!result && saveState){
					selectedZone = methods['guessTimezone']();
					result = methods['saveState'](newValue, selectedZone);
				}
				return methods['parseSavedState'](result);
			}
			, 'saveState': function(newValue, timezoneValue){
				if($timezoneManualSelection.length > 0){
					return methods['parseSavedState']();
				}
				var result = $().BookiCookie('read', cookieName)
					, value = newValue + ':' + timezoneValue;
				if(value !== result){
					$().BookiCookie('erase', cookieName);
					$().BookiCookie('create', cookieName, value, 30);
					return value;
				}
				return result;
			}
			, 'guessTimezone': function(selectedZone, triggerChange){
				var guessedTimezone = jstz['determine']()
					, region;
				selectedZone = (typeof(selectedZone) === 'undefined' || selectedZone === null) ? guessedTimezone['name']() : selectedZone;
				if(!selectedZone){
					return;
				}
				region = selectedZone.substr(0, selectedZone.indexOf('/'));
				if(!region){
					region = selectedZone;
				}
				methods['timezoneChoice'](region, selectedZone, triggerChange);
				return selectedZone;
			}
			, 'timezoneChoice': function(region, selectedZone, triggerChange){
				if(region === '-1'){
					$timezoneContainer.addClass('hide');
					$timezoneSelect.empty();
					return;
				}
				$progressBar.removeClass('hide');
				$.post(ajaxUrl, {
					'model': {
						'region': region
						, 'selectedZone': selectedZone
					}
					, 'action': 'booki_timezoneChoice'
				}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result']
						, options = result ? result['options'] : null
						, $option;
					if(options){
						if($regionSelect.length > 0){
							$regionSelect.val(region);
						}
						if($timezoneContainer.length > 0){
							$timezoneSelect.html(options);
							$timezoneContainer.removeClass('hide');
							if(selectedZone){
								$option = $timezoneSelect.find(":selected");
								if($headerCaption.length > 0){
									$headerCaption[0].title = $option.text();
									$headerCaption.html($option.val());
								}
							}
							if($loadOnStart.length > 0 && triggerChange){
								window.setTimeout(function(){
									flag = true;
									$timezoneSelect.change();
								}, 500);
								return;
							}
							if(selectedZone){
								$collapseTimezone.collapse('hide');
							}
						}
					}
				});
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
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiTimezoneControl' );
		}    
	};
	
})(window['jQuery'], window['jstz']);