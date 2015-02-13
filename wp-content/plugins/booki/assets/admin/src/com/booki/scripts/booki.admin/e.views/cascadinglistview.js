(function(url, $, moment, accounting){
	Booki.CascadingListView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .delete.btn': 'delete',
			'change input': 'inputChanged',
			'change select': 'selectedListChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#cascadinglist-template');
			this.projectId = config['projectId'];
			this.selectedListId = null;
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
				, model;
			
			if(this.models){
				this.models.reset();
			}
			this.models = new Booki.CascadingLists({'projectId': this.projectId});
			this.models.fetch({'reset': true, 'success': function(models, resp){

					if(context.selectedListId !== null){
						model = models.find(function(item){
							return item.get('id') === context.selectedListId;
						});
					}else{
						model = models.at(0);
					}
					if (!model){
						model = context.getDefaultModel();
					}
					context.selectedListId = null;
					context.renderLists(model, models);
				}
			});
		},
		
		'renderLists': function(model){
			var $el
				, content;
			this.model = model;
			if(!this.el){
				return;
			}
			$el = $(this.el);
			
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': this.models.toJSON()
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.createCascadingItemView();
		
			this.validate();
			this.tooltip();
		},
		
		'createNew': function(e){
			this.selectedListId = null;
			this.renderLists(this.getDefaultModel());
		},
		
		'selectedListChanged': function(e){
			var target = e.srcElement || e.target, 
			selectedListId = parseInt($('option:selected', target).val(), 10),
			model;
			if(selectedListId === -1){
				this.createNew();
				return;
			}
			model = this.models.find(function(item){
				return item.get('id') === selectedListId;
			});
			if(model){
				this.selectedListId = selectedListId;
				this.renderLists(model);
			}
		},
		
		'delete': function(e){
			var cascadingList = this.model
				, context = this;
			this.selectedListId = null;
			cascadingList.destroy({'success': 
				function(model, resp){
					context.render();
				}
			});
		},
		
		'save': function(e){
			var cascadingList = this.model
				, context = this;
			
			this.selectedListId = this.model.get('id');
			
			if(!this.isValid()){
				return;
			}
			
			cascadingList.save(cascadingList.attributes, { 'success':
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
				case 'label':
				value = $target.val();
				break;
				case 'isRequired':
				value = $target.is(':checked');
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'createCascadingItemView': function(){
			var args;
			if(this.cascadingItemView){
				this.cascadingItemView.undelegateEvents();
				delete this.cascadingItemView;
			}
			this.cascadingItemView = new Booki.CascadingItemView({'cascadingList': this.model, 'cascadingLists': this.models, 'el': $('#cascadingitem-view')});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.CascadingList({
				'id': null
				, 'projectId': this.projectId
				, 'label': ''
				, 'isRequired': false
				, 'cascadingItems': new Booki.CascadingItems()
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);