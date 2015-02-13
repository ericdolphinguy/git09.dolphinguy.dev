(function(window, url, $, _, JSON){
	$.fn.outerHTML = function() {
		var $elem = $(this),
			content;
		if ('outerHTML' in $elem[0]) {
			return $elem[0].outerHTML;
		} else {
			content = $elem.wrap('<div></div>').parent().html();
			$elem.unwrap();
			return content;
		}
	};
	
	var extern = Function.extern
	, Booki = window['Booki']
	, 
	/**
		@function
		@ignore
		@description Helper function to get a value from a Backbone object as a property or as a function.
	*/
	getValue = function(object, prop) {
		if (!(object && object[prop])) return null;
		return _.isFunction(object[prop]) ? object[prop]() : object[prop];
	}
	, 
	/**
		@function
		@ignore
		@description Throw an error when a URL is needed, and none is supplied.
	*/
	urlError = function() {
		throw new Error('A "url" property or function must be specified');
	}
	, bookiModalProgress
	, bookiModalProgressBar
	, bookiModalProgressMethod
	, bookiModalProgressUrl;

	Booki.progressBarShow = function(method, url){
		if(!bookiModalProgress){
			bookiModalProgress = $('#progressModal').modal({
			  backdrop: 'static',
			  show: false
			});
			bookiModalProgressBar = bookiModalProgress.find('.progress-bar');
			bookiModalProgressMethod = bookiModalProgress.find('.progress-method');
			bookiModalProgressUrl = bookiModalProgress.find('.progress-url');
		}
		if(method && url){
			bookiModalProgressMethod.html(method);
			bookiModalProgressUrl.html(url);
		}
		bookiModalProgress.modal('show').is(':visible');
		bookiModalProgressBar.addClass('stretch');
	};
	
	Booki.progressBarHide = function(){
		if(bookiModalProgress && bookiModalProgress.hasClass('in')){
			bookiModalProgressBar.removeClass('stretch');
			bookiModalProgress.modal('hide').is(':visible');
		}
	};
	
	$(document).ajaxStop(function(e){
		Booki.progressBarHide();
	});
	/**
		@function
		@description Creates parameters for an http request.
		@param {string} method The http method name to call on the server ( in contest of booki, but could be any piece of data to send to the server to identify the request).
		@param {Object} model The model from whom to build the json string ready for transport.
		@param {Object} options 
	*/
	Booki.httpRequestParams = function(method, model, options){
		var params = {'type': 'POST', 'dataType': 'json'}/*Default JSON-request options.*/,
			action = model['action'][method];
		
		// Ensure that we have a URL.
		if (!options['url']) {
		  params['url'] = getValue(model, 'url') || urlError();
		}
		params['contentType'] = 'application/x-www-form-urlencoded';
		if(!options['preserveModels'] && (model['models'] && model['models'].length > 0)){
			model = model['models'][0];
		}

		params['data']= {
			'model': Booki.rfc3986EncodeURIComponent(window['JSON']['stringify'](model.toJSON())), 
			'action': action
		};
		
		return params;
	};
	
	/**
		@function
		@ignore
		@description Override sync method for wp. we don't have a rest service but rather using an ajax api
		because that is what wp exposes. so we override and adapt.
	*/
	Backbone.sync = function(method, model, options) {
		// Default options, unless specified.
		options || (options = {});
		
		var params = Booki.httpRequestParams(method, model, options);
		Booki.progressBarHide();
		Booki.progressBarShow(model.action[method] , model.url);
		// Make the request, allowing the user to override any Ajax options.
		return $.ajax(_.extend(params, options));
	};
	
	Booki.Admin = function(){};
	
	$(window).load(function () {
		var projectsView = new Booki.ProjectsView({
			'el': $('#projects-view')
		}),
		formElementsView,
		calendarView,
		optionalsView,
		cascadingListView,
		progressBar = '.tabbable .progress',
		createFormElementsView = function(projectId){
			if(formElementsView){
				formElementsView.undelegateEvents();
				formElementsView.collection.reset();
				formElementsView.dispose();
			}
			formElementsView = new Booki.FormElementsView({
				'el': $('#formelements-view'),
				'projectId': projectId
			});
		},
		createCalendarView = function(projectId){
			if(calendarView){
				calendarView.undelegateEvents();
				calendarView.dispose();
			}
			calendarView = new Booki.CalendarView({
				'el': $('#calendar-view'),
				'projectId': projectId
			});
		},
		createOptionalsView = function(projectId){
			if(optionalsView){
				optionalsView.undelegateEvents();
			}
			optionalsView = new Booki.OptionalsView({
				'el': $('#optionals-view'),
				'projectId': projectId
			});
		},
		createCascadingListView = function(projectId){
			if(cascadingListView){
				cascadingListView.undelegateEvents();
			}
			cascadingListView = new Booki.CascadingListView({
				'el': $('#cascadinglist-view'),
				'projectId': projectId
			});
		};
		
		$('a[data-toggle="tab"]').click(function(e){
			var target = e.srcElement || e.target,
				hash = this.hash.slice(1),
				projectId = projectsView.selectedId,
				$elem = $(this);
			e.preventDefault();
			if(typeof(projectId) !== 'number' || projectId === -1){
				return false;
			}
			
			$('.nav.nav-list a[href="#formelement"]').tab('show');//reset sub tab
			
			$elem.tab('show');
			
			switch(hash){
				case 'step1':
					break;
				case 'step2':
				case 'formelements':
					createFormElementsView(projectId);
					break;
				case 'step3':
					createCalendarView(projectId);
					break;
				case 'step4':
					createOptionalsView(projectId);
					break;
				case 'step5':
					createCascadingListView(projectId);
					break;
			}
		});
	});
	
	
})(window, window['ajaxurl'], window['jQuery'], window['_'], window['JSON']);