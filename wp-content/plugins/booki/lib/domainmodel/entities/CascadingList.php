<?php
require_once  dirname(__FILE__) . '/../base/EntityBookingElementBase.php';
require_once 'CascadingItems.php';
class Booki_CascadingList extends Booki_EntityBookingElementBase {
	public $id;
	public $projectId;
	public $label;
	public $label_loc;
	public $isRequired;
	public $cascadingItems;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->label = func_get_arg(1);
			$this->isRequired = func_get_arg(2);
			if($numArgs === 4){
				$this->id = func_get_arg(3);
			}
		}
		$this->updateResources();
		$this->init();
		$this->cascadingItems = new Booki_CascadingItems();
	}
	
	public function toArray(){
		return array(
			'projectId'=>$this->projectId
			, 'label'=>$this->label
			, 'isRequired'=>$this->isRequired
			, 'cascadingItems'=>$this->cascadingItems->toArray()
			, 'id'=>$this->id
		);
	}
	
	protected function init(){
		$this->label_loc = Booki_WPMLHelper::t('cascading_list_' . $this->label . '_label', $this->label);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('cascading_list_' . $this->label . '_label', $this->label);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('cascading_list_' . $this->label . '_label');
	}
}
?>