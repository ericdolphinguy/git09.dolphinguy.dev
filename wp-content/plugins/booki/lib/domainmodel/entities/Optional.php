<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';
class Booki_Optional extends Booki_EntityBase{
	public $id;
	public $projectId;
	public $name;
	public $name_loc;
	public $cost;
	public $status;
	//count for BookedOptional
	public $count = 0;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->projectId = func_get_arg(0);
			$this->name = func_get_arg(1);
			$this->cost = func_get_arg(2);
			if($numArgs === 4){
				$this->id = func_get_arg(3);
			}
		}
		$this->updateResources();
		$this->init();
	}
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('optional_item_' . $this->name . '_name_project' . $this->projectId, $this->name);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('optional_item_' . $this->name . '_name_project' . $this->projectId);
	}
}
?>