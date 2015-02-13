(function(url, $){
	Booki.SidenavView = Booki.ViewBase.extend({
		'initialize': function(config){
			var $template = config['template'];
			this.disableHyperLinkDelegate = function(e){
				e.preventDefault();
				return false;
			};
			_.bindAll(this, 'render');
			
			if(this.model){
				this.model.bind('change', this.render);
			}
			
			if($template.length > 0){
				this.template = _.template($template.html());
			}
			this.render();
			this.tabId = config['tab'];
		},
		
		'render': function(){
			var content;
			if(this.el){
				content = this.template(
				{
					'model': this.model
				});
				$(this.el).html(content);
			}
			return this;
		},
		
		'showTab': function(index){
			var tabs = $(this.tabId).find('a');
			$(tabs[index]).tab('show');
		},
		
		'disableTab': function(index, disable){
			var tabs = $(this.tabId).find('a'),
				$tab = $(tabs[index]),
				del = this.disableHyperLinkDelegate;

			if(disable){
				$tab.bind('click', del);
				$tab.parent().addClass('disabled');
				$tab.removeAttr('data-toggle');
				$tab.css('cursor', 'not-allowed');
			}
			else{
				$tab.unbind('click', del);
				$tab.parent().removeClass('disabled');
				$tab.attr('data-toggle', 'tab');
				$tab.css('cursor', '');
			}
		},
		
		'dispose': function(){
			var $tab = $(this.tabId)
				, del = this.disableHyperLinkDelegate
				, tabs;
			if(!$tab || !this.el){
				return;
			}
			tabs = $tab.find('a');
			$.each(tabs, function(i, tab){
				$(tab).unbind('click', del);
			});
			//reset default tab
			$(tabs[0]).tab('show');
		}
	});
})(window['ajaxurl'], window['jQuery']);
