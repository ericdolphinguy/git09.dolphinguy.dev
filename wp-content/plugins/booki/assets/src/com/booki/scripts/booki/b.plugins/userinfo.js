(function($){	
	$['fn']['BookiUserInfo'] = function(method) {
		var $userEmailInfo
			, $progressBar
			, $userIdField
			, ajaxUrl
			, $triggerButton
			, successCallback
			, userFoundMessage
			, userNotFoundMessage
			, methods = {
			'init': function(options){
				var $this = this
					, elem = $this[0]
					, settings
					, $userEmailTextbox = $('#useremail');

				$(document).ready(function(){
					settings = $.extend({}, options);
					if(settings['triggerButton']){
						$triggerButton = typeof(settings['triggerButton']) === 'string' ? 
											$this.find(settings['triggerButton']) : settings['triggerButton'];
					}
					if(settings['userIdField']){
						$userIdField = typeof(settings['userIdField']) === 'string' ?
											$this.find(settings['userIdField']) : settings['userIdField'];
					}
					if(settings['userFoundMessage']){
						userFoundMessage = settings['userFoundMessage'];
					}
					if(settings['userNotFoundMessage']){
						userNotFoundMessage = settings['userNotFoundMessage'];
					}
					successCallback = settings['success'];
					ajaxUrl = settings['ajaxUrl'];
					$userEmailInfo = $('.useremail-info');
					$progressBar = $('.progress.booki-useremail');

					if($triggerButton){
						$triggerButton.click(function(){
							methods['getUserInfo']($userEmailTextbox.val());
							return false;
						});
						return;
					}
					$userEmailTextbox.change(function(){
						methods['getUserInfo']($(this).val());
					});
				});
				
				$(document).ajaxStop(function(e){
					$progressBar.addClass('hide');
				});
			}
			, 'getUserInfo': function(email){
				var result = $( '#useremail' ).parsley().validate(true);
				if(result !== true){
					return;
				}
				$progressBar.removeClass('hide');
				$.post(ajaxUrl, {
					'model': {
						'email': email
					}
					, 'action': 'booki_getUserByEmail'
				}
				, function(data) {
					var r = $.parseJSON(data)
						, result = r['result']
						, userName
						, firstName
						, lastName
						, profilePageUrl;
					if(result){
						userName = result['userName'];
						firstName = result['firstName'];
						lastName = result['lastName'];
						profilePageUrl =  result['profilePageUrl'];
						
						if($userIdField){
							$userIdField.val(result['id']);
						}
						
						if(firstName){
							userName = firstName;
							if(lastName){
								userName += (' ' + lastName);
							}
						}
						
						$userEmailInfo.removeClass('hide');
						if(userName){
							$userEmailInfo.html(userFoundMessage + ': ' + userName);
							if(successCallback){
								successCallback();
							} else if($triggerButton){
								$triggerButton.off();
								$triggerButton.click();
							}
						}else{
							$userEmailInfo.html(userNotFoundMessage);
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
		  $.error( 'Method ' +  method + ' does not exist on jQuery.BookiUserInfo' );
		}    
	};
	
})(window['jQuery']);