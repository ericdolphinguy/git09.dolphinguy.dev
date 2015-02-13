<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';
require_once 'FormElementCapability.php';

class Booki_FormElement extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $label;
	public $label_loc;
	public $elementType;
	public $lineSeparator;
	public $rowIndex;
	public $colIndex;
	public $className;
	public $value;
	public $bindingData;
	public $once;
	public $validation = array();
	public $attributes;
	public $capability;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->label = func_get_arg(1);
			$this->elementType = func_get_arg(2);
			$this->lineSeparator = func_get_arg(3);
			$this->rowIndex = func_get_arg(4);
			$this->colIndex = func_get_arg(5);
			$this->className = func_get_arg(6);
			$this->value = func_get_arg(7);
			$this->bindingData = func_get_arg(8);
			$this->once = func_get_arg(9);
			$this->capability = func_get_arg(10);
			if($numArgs >= 12){
				$this->validation = func_get_arg(11);
			}
			if($numArgs === 13){
				$this->id = func_get_arg(12);
			}
		}
		$this->attributes = $this->getValidationAttributes();
		$this->updateResources();
		$this->init();
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'projectId'=>$this->projectId
			, 'label'=>$this->label
			, 'elementType'=>$this->elementType
			, 'lineSeparator'=>$this->lineSeparator
			, 'rowIndex'=>$this->rowIndex
			, 'colIndex'=>$this->colIndex
			, 'className'=>$this->className
			, 'value'=>$this->value
			, 'bindingData'=>$this->bindingData
			, 'once'=>$this->once
			, 'capability'=>$this->capability
			, 'validation'=>$this->validation
		);
	}
	
	protected function getValidationAttributes(){
		$result = array();
		if(!$this->validation){
			return $result;
		}
		foreach($this->validation as $key=>$val){
			if($val !== null){
				switch($key){
					case 'required':
						if($val){
							array_push($result, 'data-parsley-required="true"');
						}
					break;
					case 'minLength':
						if($val){
							array_push($result, sprintf('data-parsley-minlength="%s"', $val));
						}
					break;
					case 'maxLength':
						if($val){
							array_push($result, sprintf('data-parsley-maxlength="%s"', $val));
						}
					break;
					case 'min':
						if($val){
							array_push($result, sprintf('data-parsley-min="%s"', $val));
						}
					break;
					case 'max':
						if($val){
							array_push($result, sprintf('data-parsley-max="%s"', $val));
						}
					break;
					case 'regex':
						if($val){
							array_push($result, sprintf('data-parsley-pattern="%s"', $val));
						}
					break;
					case 'email':
						if($val){
							array_push($result, 'data-parsley-type="email"');
						}
					break;
					case 'url':
						if($val){
							array_push($result, 'data-parsley-type="url"');
						}
					break;
					case 'digits':
						if($val){
							array_push($result, 'data-parsley-type="digits"');
						}
					break;
					case 'number':
						if($val){
							array_push($result, 'data-parsley-type="number"');
						}
					break;
					case 'alphanum':
						if($val){
							array_push($result, 'data-parsley-type="alphanum"');
						}
					break;
					case 'dateIso':
						if($val){
							array_push($result, 'data-parsley-type="dateIso"');
						}
					break;
				}
			}
		}
		if(count($result) > 0){
			array_push($result, 'data-parsley-trigger="change"');
		}
		return implode(' ', $result);
	}
	
	protected function init(){
		$this->label_loc = Booki_WPMLHelper::t('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('form_field_' . $this->label . '_label_project' . $this->projectId);
	}
}
?>