(function(url, $, moment, accounting){
	Booki.CascadingItemView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .new.btn': 'new',
			'click .add.btn': 'add',
			'click .remove.btn': 'delete',
			'change input, select[name="parentId"]': 'inputChanged',
			'change select[name="cascadingItems"]': 'listboxChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#cascadingitem-template');
			this.cascadingList = config['cascadingList']
			this.cascadingLists = config['cascadingLists'];
			this.selectedCascadingItemId = null;
            _.bindAll(this, 'render');
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.currencySymbol = Booki.localization.currencySymbol;
			this.thousandsSep = Booki.localization.thousandsSep;
			this.decimalPoint = Booki.localization.decimalPoint;
			
			this.render();
		},
		
		'render': function(){
			var context = this
				, model
				, models
				, cascadingList
				, cascadingItems;

			models = new Booki.CascadingItems(this.cascadingList);
			models.fetch({'success': function(models, resp){
			
					model = models.at(0);
					
					if(!model){
						model = context.getDefaultModel();
					}
					cascadingItems = model.get('cascadingItems');
					cascadingList = new Booki.CascadingItems(cascadingItems);
					
					if(context.selectedCascadingItemId !== null){
						model = cascadingList.find(function(item){
							return item.get('id') === context.selectedCascadingItemId;
						});
					}else if (!model.isNew() || model.get('id') === null){
						model = context.getDefaultModel();
					}

					if(cascadingList.length === 0){
						cascadingList.add(model);
					}
					context.renderItems(model, cascadingList);
				}
			});
		},
		
		'renderItems': function(model, models){
			var $el,
				content;
			
			this.selectedCascadingItemId = null;
			
			this.model = model;
			this.models = models || new Booki.CascadingItems();

			if(!this.el){
				return;
			}
			$el = $(this.el);
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': models.toJSON()
				, 'cascadingLists': this.cascadingLists.toJSON()
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.validate();
			this.tooltip();
		},

		'listboxChanged': function(e){
			var target = e.srcElement || e.target
				, selectedCascadingItemId = parseInt($('option:selected', target).val(), 10)
				, model = this.models.find(function(item){
				return item.get('id') === selectedCascadingItemId;
			});
			this.renderItems(model, this.models);
		},
		'new': function(e){
			this.renderItems(this.getDefaultModel(), this.models);
		},
		
		'add': function(e){
			var context = this
				, cascadingItemId = this.model.get('id')
				, model = this.models.find(function(item){
					return item.get('id') === cascadingItemId;
				});
			
			if(!model){
				this.models.add(this.model);
			}
			
			if(!this.isValid()){
				return;
			}
			
			this.selectedCascadingItemId = this.model.get('id');
			
			this.model.save(this.model.attributes, { 'success':
				function(model, resp){
					context.render();
				} 	
			});
		},
		
		'delete': function(e){
			var context = this;
			this.model.destroy({'success': 
				function(model, resp){
					context.render();
				}
			});
		},
		
		'save': function(e){
			var cascadingItem = this.model
				, context = this;
		
			if(!this.isValid()){
				return;
			}
			this.selectedCascadingItemId = this.model.get('id');
			cascadingItem.save(cascadingItem.attributes, { 'success':
				function(model, resp){
					context.render();
				} 	
			});
		},
		
		'inputChanged': function(e){
			var target = e.srcElement || e.target,
				$target = $(target),
				name = target['name'],
				type = target['type'],
				value,
				data = {},
				silent = true,
				model;
			switch(name){
				case 'value':
				value = $target.val();
				break;
				case 'cost':
				value = $target.val();
				break;
				case 'parentId':
				value = parseInt($target.val(), 10);
				break;
				case 'lat':
				value = $target.val();
				break;
				case 'lng':
				value = $target.val();
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.CascadingItem({
				'id': null
				, 'value': ''
				, 'cost': 0
				, 'lat': 0
				, 'lng': 0
				, 'listId': this.cascadingList.get('id')
				, 'parentId': null
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);