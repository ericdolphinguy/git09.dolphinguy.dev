(function(url, $, moment, accounting){
	Booki.OptionalsView = Booki.ViewBase.extend({
		'events': {
			'click .update.btn': 'save',
			'click .delete.btn': 'delete',
			'click .createnew.btn': 'createNew',
			'click .add.btn': 'save',
			'change input': 'inputChanged',
			'change select': 'listboxChanged'
        },
		
		'initialize': function(config) {
			var $template = $('#optionals-template');
			this.projectId = config['projectId'];
			
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
			var context = this,
				projectId = this.projectId,
				selectedId = this.selectedId,
				model,
				models;

			models = new Booki.Optionals({'projectId': projectId});
			models.fetch({'success': function(models, resp){
					model = models.find(function(item){
						return item.get('id') === selectedId;
					});
					if (!model){
						model = context.getDefaultModel();
					}
					context.renderOptionals(model, models);
				}
			});
		},
		
		'renderOptionals': function(model, models){
			var $el,
				content;
			
			this.model = model;
			this.models = models || new Booki.Optionals();
			
			this.models.bind('remove', this.render);
			if(model){
				model.bind('change', this.render);
			}

			if(!this.el){
				return;
			}
			$el = $(this.el);
			
			content = this.template(
			{
				'context': this
				, 'isNew': model.isNew()
				, 'model': model.toJSON()
				, 'models': models.models
				, 'localization': Booki.localization
				, 'accounting': accounting
			});
			
			$el.html(content);
			
			this.$('.createnew.btn').prop('disabled', model.isNew());
			this.$('.update.btn').prop('disabled', model.isNew());
			this.$('.delete.btn').prop('disabled', model.isNew());
			this.$('.add.btn').prop('disabled', !model.isNew());
			
			this.validate();
			this.tooltip();
		},
		
		'createNew': function(e){
			this.selectedId = null;
			this.renderOptionals(this.getDefaultModel(), this.models);
		},
		
		'listboxChanged': function(e){
			var models = this.models, 
				target = e.srcElement || e.target, 
				selectedOption = parseInt($('option:selected', target).val(), 10),
				model = models.find(function(item){
						return item.get('id') === selectedOption;
				});
			if(model){
				this.renderOptionals(model, models);
			}
		},
		
		'delete': function(e){
			var optional = this.model;
			
			optional.destroy({'success': 
				function(model, resp){}
			});
		},
		
		'save': function(e){
			var optional = this.model;
			
			if(!this.isValid()){
				return;
			}
			
			optional.save(optional.attributes, { 'success':
				function(model, resp){} 	
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
				case 'name':
				value = $target.val();
				break;
				case 'cost':
				value = $target.val();
				break;
			}
			data[name] = value;
			this.model.set(data, {'silent': silent});
		},
		
		'getDefaultModel': function(){ 
			return new Booki.Optional({
				'id': null,
				'projectId': this.projectId,
				'name': '',
				'cost': ''
			});
		}
	});
})(window['ajaxurl'], window['jQuery'], window['moment'], window['accounting']);