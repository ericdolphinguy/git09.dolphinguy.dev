(function(url, $, _){
	Booki.FormElementView = Booki.ViewBase.extend({
		'events': {
			'change input': 'formElementChanged',
			'change select': 'formElementChanged',
			'keyup input[name="dataItem"]': 'testDatasourceToolbar',
			'click .btn-group.datasource .add': 'addDatasourceItem',
			'click .btn-group.datasource .remove': 'removeDatasourceItem',
			'click .btn-group.datasource .select': 'selectDatasourceItem',
			'click .btn-group.main .add': 'saveModel',
			'click .btn-group.main .update': 'saveModel',
			'click .btn-group.main .createnew': 'createNew'
        },
		
		'initialize': function(config) {
			var $template = $('#formelement-template')
				, i
				, cols = config['cols']
				, rows = config['rows'];
			this.projectId = config['projectId'];
			this.onSaved = config['onSaved'];
			this.cols = [];
			this.rows = [];
			for(i = 1; i <= cols; i++){
				this.cols.push(i);
			}
			for(i = 1; i <= rows; i++){
				this.rows.push(i);
			}
			
			if(!this.model){
				this.model = this.getDefaultModel();
			}
            _.bindAll(this, 'render');
			this.model.bind('change', this.render);
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },
		
		'render': function(model, changes){
			var $el
				, content
				, selectedColIndex
				, selectedRowIndex
				, elementType;
			
			model = model || this.model;
			
			if(this.el){
				$el = $(this.el);
				elementType = this.model.get('elementType');
				selectedColIndex = parseInt($el.find('select[name="colIndex"] option:selected').val(), 10);
				selectedRowIndex = parseInt($el.find('select[name="rowIndex"] option:selected').val(), 10);
				content = this.template(
				{
					'elementTypes': ['Textbox', 'TextArea', 'Dropdown List', 'Listbox', 'Checkbox', 'RadioButton', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'PlainText', 'Terms and Conditions']
					, 'context': this
					, 'isNew': model.isNew()
					, 'model': model.toJSON()
					, 'cols': this.cols.sort(this.sortIntAsc)
					, 'rows': this.rows.sort(this.sortIntAsc)
					, 'colIndexShow': model.get('colIndex') !== selectedColIndex
					, 'rowIndexShow': model.get('rowIndex') !== selectedRowIndex
					, 'parseDefault': this.parseDefault
					, 'toBool': this.toBool
					, 'supportsValueAttribute': [0,1,2,3,4,5,13].indexOf(elementType) !== -1
				});
				$el.html(content);
				this.initDataBindingControls();
				this.testDatasourceToolbar();
				this.validate({
					'validators': {
						'minoptions': function (  ) {
							return { 
								'validate': function(val, minLength, parsleyField){
									return parsleyField.$element['0'].options.length > minLength - 1;
								}, 
								'priority': 2
							}
						}
						, 'selectone': function (  ) {
							return { 
								'validate': function(val, name, parsleyField){
									if(!name){
										return true;
									}
									return !(val === '-1' && !$el.find('input[name="' + name + '"]').val());
								}, 
								'priority': 2
							}
						}
					}
					, 'messages': {
							'minoptions': "Datasource cannot be empty."
							, 'selectone': 'Tag required. Select an existing tag or enter a new one.'
					}
				});
			}

			this.$('.btn-group.main .update.btn').prop('disabled', this.model.isNew());
			this.$('.btn-group.main .createnew.btn').prop('disabled', this.model.isNew());
			this.$('.btn-group.main .add.btn').prop('disabled', !this.model.isNew());
			this.tooltip();
		},
		'toBool': function(value){
			if(typeof(value) === 'boolean'){
				return value;
			}
			return parseInt(this.parseDefault(value, 0), 10) !== 0;
		},
		'addDatasourceItem': function(e){
			var $dataItem = $(this._dataItem)
				, $bindingData = $(this._bindingData)
				, options
				, value = $dataItem.val();
			if(value.trim().length > 0){
				$bindingData.append('<option value="' + $bindingData[0].options.length + '">' + value + '</option>');
				$dataItem.val('');
				//now trigger parsley validation
				options = $bindingData[0].options;
				$(options[options.length - 1]).attr('selected', 'selected');
				$bindingData.focus();
			}
		},
		'removeDatasourceItem': function(e){
			var bindingData = this._bindingData
				, selectedIndex = bindingData.selectedIndex
				, $option = $(bindingData.options[selectedIndex])
				, value = this.model.get('value')
				, currentValue = $option.text();

			if(value === currentValue){
				//reset the default value
				$(this._value).val('');
				this.model.set({'value': ''}, {silent: true});
			}
			
			$option.remove();
			this.testDatasourceToolbar();
		},
		
		'selectDatasourceItem': function(){
			var bindingData = this._bindingData
				, selectedIndex = bindingData.selectedIndex
				, $option = $(bindingData.options[selectedIndex])
				/**String*/
				, value = $option.text();

			$(this._value).val(value);
			this.model.set({'value': value}, {silent: true});
			this.testDatasourceToolbar();
		},
		
		'formElementChanged': function(e){
			var target = e.srcElement || e.target
				, $target = $(target)
				, name = target['name']
				, type = target['type']
				, value
				, data = {}
				, silent = true
				, groupName
				, targetValue
				, isValidationField = $target.hasClass('validation')
				, validation = this.model.get('validation')
				, targetIsChecked;
			switch(name){
				case 'label':
					value = $target.val();
				break;
				case 'colIndex':
				case 'rowIndex':
					value = this.parseSelectValue(type, $target, name);
					if(value === -1){
						return;
					}
				break;
				case 'bindingData':
					this.testDatasourceToolbar();
					return;
				break;
				case 'value':
					if(type === 'checkbox'){
						value = $target.is(':checked');
					}else{
						value = $target.val();
					}
				break;
				case 'defaultselection':
					value = [$target.is(':checked')];
					name = 'bindingData';
				break;
				case 'className':
					value = $target.val();
				break;
				case 'lineSeparator':
					value = $target.is(':checked');
				break;
				case 'once':
					value = $target.is(':checked');
				break;
				case 'capability':
					value = parseInt($target.val(), 10);
				break;
				case 'elementType':
					value = parseInt($target.val(), 10);
					this.model.set({'colIndex': 0, 'rowIndex': 0}, {'silent': true});
					silent = false;
				break;
				case 'entrytype':
					groupName = $target.find(":selected").val();
					value = this.cloneObject(validation, groupName, true); 
					$(['digits', 'number', 'alphanum']).each(function(i, prop){
						if (prop !== groupName){
							value[prop] = null;
						}
					});
					name = 'validation';
				break;
				default:
					if(isValidationField){
						targetIsChecked = $target.is(':checked');
						if(name === 'email'){
							silent = false;
							if(!targetIsChecked){
								data['capability'] = 0;
							}
						}
						value = this.cloneObject(validation, name, type === 'checkbox' ? targetIsChecked : $target.val());
						name = 'validation';
					}
			}
			
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		'cloneObject': function(o, key, value){
			var x
				, result = {};
			for(x in o){
				if(o.hasOwnProperty(x)){
					result[x] = key === x ? value : o[x];
				}
			}
			return result;
		},
		'parseSelectValue': function(type, $target, name){
			var value
				, $field = $(this.el).find('.form-group.new' + name);
			if (type === 'select-one'){
				value = $target.find('option:selected').val();
				if(value !== '-1'){
					$field.addClass('hide');
				}
				else{
					$field.removeClass('hide');
					value = $target.val();
				}
			} else{
				$(this.el).find('select[name="' + name + '"]').parsley( 'destroy' );
				value = $target.val();
			}
			return parseInt(value, 10);
		},
		'testDatasourceToolbar': function(){
			if(!this._bindingData){
				return;
			}
			var $el = $(this.el), 
				bindingData = this._bindingData, 
				selectedIndex = bindingData.selectedIndex,
				selectedValue = $(bindingData.options[selectedIndex]).text(),
				$addButton = $el.find('.btn-group.datasource .add'),
				$removeButton = $el.find('.btn-group.datasource .remove'),
				$defaultSelectionButton = $el.find('.btn-group.datasource .select'),
				disabled = bindingData.options.length === 0 || selectedIndex === -1;
			
			$addButton.prop('disabled', $(this._dataItem).val().length === 0);
			$removeButton.prop('disabled', disabled);
			$defaultSelectionButton.prop('disabled', disabled || $(this._value).val() === selectedValue);
		},
	
		'initDataBindingControls': function(){
			var el = $(this.el);
			this._bindingData = el.find('select[name="bindingData"]')[0];
			this._value = el.find('input[name="value"]')[0];
			this._dataItem = el.find('input[name="dataItem"]')[0];
		},
		
		'saveModel': function(e){
			var context = this
				, model = this.model
				, wasNew = model.isNew()
				, bindingData
				, onSaved = this.onSaved
				, validation = model.get('validation')
				, colIndex = model.get('colIndex')
				, rowIndex = model.get('rowIndex');
				
			this.initDataBindingControls();
			
			if(this._bindingData){
				bindingData	= $(this._bindingData.options).map(function(){ 
					return $(this).text();
				}).get();
				model.set({'bindingData': bindingData}, {'silent': true});
			}
			
			if(!this.isValid()){
				return;
			}
			
			if(!_.contains(this.cols, colIndex)){
				this.cols.push(colIndex);
			}
			
			if(!_.contains(this.rows, rowIndex)){
				this.rows.push(rowIndex);
			}
			
			if(!this.toBool(validation.digits) && !this.toBool(validation.number)){
				validation['max'] = 0;
				validation['min'] = 0;
			}
			if (this.toBool(validation.digits) || this.toBool(validation.number)){
				validation['maxLength'] = 0;
				validation['minLength'] = 0;
			}
			model.set({'validation': validation}, {'silent': true});
			
			model.save(model.attributes, { 'success':
				function(model, resp){
					context.model = model;
					if(wasNew){
						context.resetModel();
					}
					if(onSaved){
						onSaved(model);
					}
				}
			});
		},
		
		'createNew': function(e){
			this.resetModel();
		},
		
		'getDefaultModel': function(){
			return new Booki.FormElement({
				'id': null
				, 'projectId': this.projectId
				, 'label': ''
				, 'elementType': Booki.ElementType.textbox
				, 'lineSeparator': false
				, 'rowIndex': 0
				, 'colIndex': 0
				, 'className': ''
				, 'value': ''
				, 'bindingData': []
				, 'once': false
				, 'capability': 0
				, 'validation': {
					'required': null
					, 'notBlank': null
					, 'minLength': null
					, 'maxLength': null
					, 'min': null
					, 'max': null
					, 'regex': null
					, 'email': null
					, 'url': null
					, 'digits': null
					, 'number': null
					, 'alphanum': null
					, 'dateIso': null
				}
			});
		},
		
		'resetModel': function(){
			this.model = this.getDefaultModel();
			this.model.bind('change', this.render);
			this.render();
		}
	});
})(window['ajaxurl'], window['jQuery'], window['_']);