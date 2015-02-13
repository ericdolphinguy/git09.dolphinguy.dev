(function(url, $){
Booki.FormElement = Booki.ModelBase.extend({
		'url': url, 
		'action': {
			'create': 'booki_insertFormElement',
			'update': 'booki_updateFormElement',
			'delete': 'booki_deleteFormElement',
			'read':   'booki_readFormElement'
		},
		'createElement': function(){
			var elementType = this.get('elementType')
				, bindingData = this.get('bindingData')
				, value = this.get('value')
				, selectedItemValue = this.get('value')
				, label = this.get('label')
				, context = this
				, optionValue
				, name = ''
				, type
				, multiple
				, cssClass = 'form-control'
				, className = arguments.length === 0 ? this.get('className') : null
				, $elem
				, $anchor
				, i;
			
			switch(elementType){
				case 0/*Booki.ElementType.textbox*/:
					name = 'input';
					type = 'text';
				break;
				case 1/*Booki.ElementType.textarea*/:
					name = 'textarea';
				break;
				case 2/*Booki.ElementType.dropdownlist*/:
					name = 'select';
					value = null;
				break;
				case 3/*Booki.ElementType.listbox*/:
					name = 'select';
					multiple = 'multiple';
					value = null;
				break;
				case 4/*Booki.ElementType.checkbox*/:
				case 13/*Booki.ElementType.tc*/:
					name = 'input';
					type = 'checkbox';
					value = null;
					cssClass = null;
				break;
				case 5/*Booki.ElementType.radiobutton*/:
					name = 'input';
					type = 'radio';
					cssClass = null;
				break;
				case 6/*Booki.ElementType.h1*/:
					name = 'h1';
					cssClass = null;
				break;
				case 7/*Booki.ElementType.h2*/:
					name = 'h2';
					cssClass = null;
				break;
				case 8/*Booki.ElementType.h3*/:
					name = 'h3';
					cssClass = null;
				break;
				case 9/*Booki.ElementType.h4*/:
					name = 'h4';
					cssClass = null;
				break;
				case 10/*Booki.ElementType.h5*/:
					name = 'h5';
					cssClass = null;
				break;
				case 11/*Booki.ElementType.h6*/:
					name = 'h6';
					cssClass = null;
				break;
				case 12/*Booki.ElementType.plaintext*/:
					name = 'p';
					cssClass = null;
				break;
			}
			$elem = $(document.createElement(name));
			
			if(type){
				$elem.attr('type', type);
			}
			if(type === 'checkbox' || type === 'radio'){
				if(type === 'radio'){
					$elem.attr('value', label);
				}
				
				if(bindingData && bindingData[0]){
					$elem.attr('checked', true);
				}
			}else if (typeof(value) === 'string' && value.length > 0){
				if (elementType >= 6 && elementType <= 12){
					$elem.append(value);
				} else{
					$elem.attr('value', value);
				}
			}
			
			if(multiple){
				$elem.attr(multiple, multiple);
			}
			
			if(bindingData.length > 0 && (elementType === 2 || elementType === 3)){
				for(i in bindingData){
					optionValue = bindingData[i];
					$elem.append($('<option value="' + i + '"' + (optionValue === selectedItemValue ? ' selected' : '') + '>' + optionValue + '</option>'));
				}
			}
			if(cssClass){
				$elem.addClass(cssClass);
			}
			if(className){
				$elem.addClass(className);
			}
			return $elem.outerHTML();
		}
	});
})(window['ajaxurl'], window['jQuery']);