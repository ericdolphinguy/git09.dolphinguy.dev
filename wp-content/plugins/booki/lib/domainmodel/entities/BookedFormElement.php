<?php
require_once  dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_BookedFormElement extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $orderId;
	public $label;
	public $label_loc;
	public $elementType;
	public $rowIndex;
	public $colIndex;
	public $value;
	public $capability;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->label = func_get_arg(1);
			$this->elementType = func_get_arg(2);
			$this->rowIndex = func_get_arg(3);
			$this->colIndex = func_get_arg(4);
			$this->value = func_get_arg(5);
			$this->capability = func_get_arg(6);
			if($numArgs >= 8){
				$this->orderId = func_get_arg(7);
			}
			if($numArgs === 9){
				$this->id = func_get_arg(8);
			}
		}
	}
	
	protected function init(){
		$this->label_loc = Booki_WPMLHelper::t('form_field_' . $this->label . '_label_project' . $this->projectId, $this->label);
	}
}
?>