(function(url, $, _){
	Booki.ProjectView = Booki.ViewBase.extend({
		'events': {
			'change input[name], textarea[name], select[name="calendarMode"], select[name="defaultStep"], select[name="bookingMode"], select[name="tag"], select[name="bookingWizardMode"]': 'projectChanged'
			, 'click .duplicateProject': 'duplicate'
			, 'click .delete': 'delete'
			, 'click .update': 'update'
			, 'click .preview-thumbnail button': 'thumbnailCloseClick'
        },
		
        'initialize': function(config) {
			var $template = $('#project-template');
            _.bindAll(this, 'render');
			this.onDeleted = config['onDeleted'];
			this.onCreated = config['onCreated'];
			this.tags = config['tags'];
			if(!this.model){
				this.model = this.getDefaultModel();
			}
			this.model.bind('change', this.render);
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },
		
        'render': function() {
			var context = this 
				, $el = $(this.el)
				, model = this.model
				, isNew = model.isNew()
				, content;
				
			if(this.el){
				$el = $(this.el);
				content = this.template({
					'isNew': isNew
					, 'model': model.toJSON()
					, 'tags': this.tags.models
				});
				$el.html(content);

				this.tooltip();
				this.$imagePickerDialog = $('#imageGalleryModal .modal-body');
				this.$image = $el.find('div.preview-thumbnail');
				this.$imageDefault = $el.find('div.preview-thumbnail-default');
				this.attachImageGalleryHandlers();

				this.validate({
					'validators': {
						'selectone': function (  ) {
							return { 
								'validate': function(val, name, parsleyField){
									if(!name){
										return true;
									}
									return !(val === '-1' && !$el.find('input[name="' + name +'"]').val());
								}, 
								'priority': 2
							}
						}
					}
					, 'messages': {
							'selectone': 'Tag required. Select an existing tag or enter a new one.'
					}
				});
			}
			return this;
        },
		
		'projectChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value = $target['val']()
				, data = {}
				, selectFields = ['calendarMode', 'bookingMode', 'defaultStep', 'bookingWizardMode']
				, $newTagField = $(this.el).find('.form-group.newtag')
				, silent = true;
			if(type === 'checkbox'){
				value = $target.is(':checked');
				if(name === 'enableMonthSelection'){
					silent = false;
					if(!value){
						data['monthsCount'] = 1;
					}
				}
			}else if($.inArray(name, selectFields) > -1){
				value = parseInt($target.find('option:selected').val(), 10);
				if(name === 'calendarMode'){
					silent = false;
					data['enableMonthSelection'] = false;
					data['monthsCount'] = 1;
				}
			} else if (name === 'tag' && type === 'select-one'){
				value = $target.find('option:selected').val();
				if(value !== '-1'){
					$newTagField.addClass('hide');
				}
				else{
					$newTagField.removeClass('hide');
					value = $target['val']();
				}
			} else if (name === 'tag'){
				$(this.el).find('select[name="tag"]').parsley( 'destroy' );
			} else if (name === 'optionalsListingMode'){
				value = parseInt($target.val(), 10);
				if(value === 1){
					data['optionalsMinimumSelection'] = 0;
				}
				silent = false;
			}else if (name === 'optionalsBookingMode' || name === 'optionalsMinimumSelection'){
				value = parseInt($target.val(), 10);
			}
			data[name] = value;
			if(!silent && this.model.isNew()){
				data['tag'] = '';
			}
			this.model.set(data, {'silent': silent});
		},
		'duplicate': function(e){
			var context = this
				, name = this.model.get('duplicateProjectName')
				, id = this.model.get('id')
				, model = new Booki.DuplicateProject({'projectId': id, 'projectName': name})
				, attributes;
			
			if($.trim(name).length === 0){
				return;
			}
			
			attributes = model.attributes;
			attributes['projectName'] = name;
			attributes['projectId'] = id;
			
			model.save(attributes, {'success': 
				function(result){
					context.model.set({'id': result['id'], 'name': name});
					if(context.onCreated){
						context.onCreated(context.model);
					}
					window.console.log(context.model);
				}
			});
		},
		'update': function(e){
			this.save();
		},
		'save': function(){
			var context = this
				, model = this.model
				, tag = model.get('tag')
				, containsTag = this.tags.find(function(item){
					return item.get('name') === tag;
				});
			
			if(!this.isValid()){
				return;
			}
			
			if(!containsTag && tag){
				this.tags.add(new Backbone.Model({'name': tag}));
			}
			model.save(model.attributes, { 'success':
				function(model){
					if(context.onCreated){
						context.onCreated(model);
					}
				}
			});
		},
		
		'delete': function(e){
			var context = this;
			if(this.model){
				this.model.destroy({
					success: function(model){
						if(context.onDeleted){
							context.onDeleted(model);
						}
					}
				});
			}
		},
		'thumbnailCloseClick': function(e){
			e.preventDefault();
			this.updateThumbnail('');
			return false;
		},
		'updateThumbnail': function(previewUrl){
			this.model.set({'previewUrl': previewUrl}, {silent: true});
			if(previewUrl){
				this.$imageDefault.addClass('hide');
				this.$image.css('background-image', 'url("' + previewUrl + '")');
				this.$image.removeClass('hide');
				return;
			}
			this.$image.addClass('hide');
			this.$imageDefault.removeClass('hide');
		},
		'imageGalleryPagerClick': function(e){
            var href = e.target.href
				, callbackUrl
				, data
				, context = this;
            if(href.length === 0){return;}
            
            e.preventDefault();
            this.detachImageGalleryHandlers();
            
            callbackUrl = url + href.substr(href.indexOf('?'));
            
            data = {
				'action': 'mediaLibraryPaging'
			};
			
            $['post']( callbackUrl, data, function(response) {
               context.$imagePickerDialog.html(response);
               context.attachImageGalleryHandlers();
            });
        },
		
        'attachImageGalleryHandlers': function(){
            $('a.booki-first-page, a.booki-prev-page, a.booki-next-page, a.booki-last-page')['on']('click', $.proxy( this.imageGalleryPagerClick, this));
            $('a.image-item-selected')['on']('click', $.proxy( this.imagePickerSelectionChanged, this));
			$('.pager-indicator-textbox').attr('readonly', true);
        },
		
		'detachImageGalleryHandlers': function(){
            $('a.first-page, a.prev-page, a.next-page, a.last-page')['off']();
            $('a.image-item-selected')['off']();
        },
		
		'imagePickerSelectionChanged': function(e){
            var previewUrl
				, href = e.currentTarget.href;
				
            e.preventDefault();
            if(href.length === 0){return;}
            
            previewUrl = href.substr(href.indexOf('#') + 1);

            if(previewUrl.length > 0){
				this.updateThumbnail(previewUrl);
            }
        },
		
		'getDefaultModel': function(){
			return new Booki.Project({
				'id': null
				, 'status': Booki.ProjectStatus.running
				, 'name': ''
				, 'duplicateProjectName': ''
				, 'bookingDaysMinimum': 0
				, 'bookingDaysLimit': 1
				, 'calendarMode': Booki.CalendarMode.popup
				, 'bookingMode': Booki.BookingMode.reservation
				, 'description': ''
				, 'previewUrl': ''
				, 'tag': ''
				, 'notifyUserEmailList': ''
				, 'optionalsBookingMode': 0
				, 'optionalsListingMode': 0
				, 'optionalsMinimumSelection': 0
				, 'defaultStep': Booki.ProjectStep.bookingForm
				, 'bookingTabLabel': Booki.resx.PROJECT_TAB_BOOKING_TAB_LABEL_DEFAULT
				, 'customFormTabLabel': Booki.resx.PROJECT_TAB_CUSTOM_FORM_TAB_LABEL_DEFAULT
				, 'availableDaysLabel': Booki.resx.PROJECT_TAB_AVAILABLE_DAYS_LABEL_DEFAULT
				, 'selectedDaysLabel': Booki.resx.PROJECT_TAB_SELECTED_DAYS_LABEL_DEFAULT
				, 'bookingTimeLabel': Booki.resx.PROJECT_TAB_BOOKING_TIME_LABEL_DEFAULT
				, 'optionalItemsLabel': Booki.resx.PROJECT_TAB_OPTIONAL_ITEM_LABEL_DEFAULT
				, 'nextLabel': Booki.resx.PROJECT_TAB_NEXT_LABEL_DEFAULT
				, 'prevLabel': Booki.resx.PROJECT_TAB_PREV_LABEL_DEFAULT
				, 'addToCartLabel': Booki.resx.PROJECT_TAB_ADD_TO_CART_LABEL_DEFAULT
				, 'fromLabel': Booki.resx.PROJECT_TAB_FROM_LABEL_DEFAULT
				, 'toLabel': Booki.resx.PROJECT_TAB_TO_LABEL_DEFAULT
				, 'proceedToLoginLabel' : Booki.resx.PROJECT_TAB_PROCEED_TO_LOGIN_LABEL_DEFAULT
				, 'makeBookingLabel': Booki.resx.PROJECT_TAB_MAKE_BOOKING_LABEL_DEFAULT
				, 'bookingLimitLabel': Booki.resx.PROJECT_TAB_BOOKING_LIMIT_LABEL_DEFAULT
				, 'contentTop': ''
				, 'contentBottom': ''
				, 'bookingWizardMode': Booki.BookingWizardMode.tabs
				, 'hideSelectedDays': false
			});
		}
    });
})(window['ajaxurl'], window['jQuery'], window['_']);