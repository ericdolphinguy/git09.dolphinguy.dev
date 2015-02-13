(function(url, $){
	Booki.ProjectsView = Booki.ViewBase.extend({
		'events': {
			'change select[name]': 'projectSelectionChanged'
        },
		
        'initialize': function(config) {
			var $template = $('#projects-template');
			this.selectedId = -1;
            _.bindAll(this, 'render');
			if(!this.collection){
				this.collection = new Booki.Projects();
			}
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.tags = new Backbone.Collection();
			this.render();
        },
        
        'render': function(model) {
			var context = this
				, collection = this.collection
				, models
				, tags;
			if(this.el){
				collection.fetch({
					success: function(models, resp){
						context.collection.reset(resp['projects']);
						context.tags.reset(resp['tags']);
						context.output(context.collection);
						if(context.selectedId === -1){
							context.tabs();
							context.createProjectView();
						}else{
							context.tabs('enable');
						}
					}
				});
			}
			return this;
        }, 
		
		'output': function(collection){
			var $el = $(this.el),
				context = this,
				content = this.template({
					'selectedId': this.selectedId
					, 'models': collection.models
				});
			$el.html(content);

			return this;
		},
		
		'createProjectView': function(model){
			var context = this;
			if(this.projectView){
				this.projectView.undelegateEvents();
			}
			this.projectView = new Booki.ProjectView({
				'el': $('#project-view')
				, 'model': model
				, 'tags': this.tags
				, 'onDeleted': function(model){
					context.selectedId = -1;
					context.collection.remove(model);
					context.render();
					context.createProjectView();
				}
				, 'onCreated': function(model){
					var _model = context.getModel(model.get('id'));
					if(_model){
						//we are updating
						context.collection.remove(_model, {silent: true});
					}
					context.selectedId = model.get('id');
					context.collection.add(model);
					context.render();
				}
			});
		},
		
		'projectSelectionChanged': function(e){
			var val = $(e.currentTarget).val()
				, id = parseInt(val, 10)
				, model = this.getModel(id);
			this.tabs(id !== -1 ? 'enable' : '');
			this.selectedId = id;
			this.createProjectView(model);
		},
		
		'tabs': function(status){
			var $tabs = $('.booki .wizard-tab li:not(li a[href="#step1"])');
			if(status === 'enable'){
				$tabs.removeClass('disabled');
			}else{
				$tabs.addClass('disabled');
			}
		},
		
		'getModel': function(id){
			return this.collection.find(function(item){
				return item.get('id') === parseInt(id, 10);
			});
		}
    });
})(window['ajaxurl'], window['jQuery']);