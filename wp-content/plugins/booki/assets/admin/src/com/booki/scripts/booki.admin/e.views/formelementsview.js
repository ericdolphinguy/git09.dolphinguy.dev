(function(url, $){
	Booki.FormElementsView = Booki.ViewBase.extend({
		'events': {
			'click .edit.btn': 'editFormElement'
			, 'click .delete.btn': 'deleteFormElement'
			, 'click .save.btn': 'savePendingModels'
			, 'click #deleteFormElementModal .delete-form-element': 'permanentlyDeleteFormElement'
        },
		
		'initialize': function(config) {
			var $template = $('#formelements-template'),
				model = this.model;
			
			this.projectId = config['projectId'];
			this.cols = 0;
			this.rows = 0;
            _.bindAll(this, 'render');
			
			this.createCollection();
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
        },

        'render': function() {
			var context = this, 
				collection = this.collection;

			collection.fetch({
				'success': function(models, resp){
					context.collection.reset(resp['formElements']);
					context.cols = parseInt(resp['cols'], 10);
					context.rows = parseInt(resp['rows'], 10);
					if(context.el){
						context.renderCollection();
					}
					return context;
				}
			});
			return this;
        },
		
		'addPendingModels': function(models){
			var i;
			if(!this.pendingModels){
				this.pendingModels = new Backbone.Collection();
			}
			for(i in models){
				this.pendingModels.add(models[i]);
			}
		},
		
		'savePendingModels': function(){
			var model, context = this;
			if(this.pendingModels.length > 0){
				model = this.pendingModels.pop();
				model.save(model.attributes, {'success': 
					function(model){
						if(context.pendingModels.length > 0){
							context.savePendingModels();
							return;
						}
						context.renderCollection();
					}
				});
			}
		},
		
		'renderCollection': function(){
			if(!this.el){
				return;
			}
			
			var content = this.template(
				{
					'context': this,
					'models': this.collection.models,
					'isDirty': (this.pendingModels && this.pendingModels.length > 0)
				});
			$(this.el).html(content);
			
			this.createModelView();
			this.createSidenavView();
		},
		
		'createCollection': function(){
			if(this.collection){
				this.collection.reset();
			}
			this.collection = new Booki.FormElements(new Backbone.Model({'id': this.projectId}));
		},
		
		'resetCollection': function(){
			this.createCollection();
			this.render();
		},
		
		'getModelById': function(id){
			return this.collection.find(function(item){
				return item.get('id') === id;
			});
		},
		
		'createSidenavView': function(){
			var value = {'count': this.collection.length};
			if(!this.sidenavView){
				this.sidenavView = new Booki.SidenavView({
					'template': $('#formelement-sidenav-template'),
					'tab': '.formbuilder-tab',
					'el': $('#formelement-sidenav-view'),
					'model': new Backbone.Model(value)
				});
				return;
			}
			this.sidenavView.model.set(value);
		},
		
		'createModelView': function(model){
			var context = this;
			if(!this.modelView){
				this.modelView = new Booki.FormElementView({
					'projectId': this.projectId
					, 'model': model
					, 'cols': this.cols
					, 'rows': this.rows
					, 'el': $('#formelement-view')
					, 'onSaved': function(model){
						context.resetCollection();
					}
				});
				return;
			}
			if(model){
				this.modelView.model.set(model.attributes);
			}
		},
		
		'editFormElement': function(e){
			var $target = $(e.currentTarget)
				, id = parseInt($target.val(), 10)
				, model = this.getModelById(id);
			
			this.createModelView(model);
			this.sidenavView.showTab(0/*Booki.Sidenav.detail*/);
		},
		
		'deleteFormElement': function(e){
			//we need a modal dialog for confirmation here
			var context = this
				, $target = $(e.currentTarget)
				, id = parseInt($target.val(), 10);
			this.modelForDelete = this.getModelById(id);
		},
		
		'permanentlyDeleteFormElement': function(e){
			var context = this;
			if(!this.body){
				this.body = $('body');
			}
			//bootstrap has a problem closing the backdrop, so helping manually
			this.body.removeClass('modal-open');
			$('.modal-backdrop').remove();
			if(this.modelForDelete){
				this.modelForDelete.destroy({'success': function() {
					context.renderCollection();
					context.sidenavView.showTab(1/*Booki.Sidenav.edit*/);
				}});
			}
		},
		
		'dispose': function(){
			if(this.sidenavView){
				this.sidenavView.undelegateEvents();
				this.sidenavView.dispose();
			}
			if(this.modelView){
				this.modelView.undelegateEvents();
			}
		}
	});
})(window['ajaxurl'], window['jQuery']);
