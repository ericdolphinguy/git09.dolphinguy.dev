<?php
require_once  dirname(__FILE__) . '/../base/EntityBookingElementBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';

class Booki_CascadingItem extends Booki_EntityBookingElementBase {
	public $id = -1;
	public $listId = -1;
	public $parentId = -1;
	public $value;
	public $value_loc;
	public $cost = 0;
	public $lat = 0;
	public $lng = 0;
	//count is a helper for BookedCascadingItem
	public $count;
	public $isRequired;
	public function __construct($args){
		if($this->keyExists('value', $args)){
			$this->value = $this->decode((string)$args['value']);
		}
		if($this->keyExists('cost', $args)){
			$this->cost = (double)$args['cost'];
		}
		if($this->keyExists('lat', $args)){
			$this->lat = (double)$args['lat'];
		}
		if($this->keyExists('lng', $args)){
			$this->lng = (double)$args['lng'];
		}
		if($this->keyExists('listId', $args)){
			$this->listId = (int)$args['listId'];
		}
		if($this->keyExists('parentId', $args) && isset($args['parentId'])){
			$this->parentId = (int)$args['parentId'];
		}
		if($this->keyExists('isRequired', $args)){
			$this->isRequired = (bool)$args['isRequired'];
		}
		if($this->keyExists('id', $args)){
			$this->id = (int)$args['id'];
		}
		$this->updateResources();
		$this->init();
	}
	
	public function toArray(){
		return array(
			'value'=>$this->value
			,'cost'=>$this->cost
			, 'listId'=>$this->listId
			, 'parentId'=>$this->parentId
			, 'lat'=>$this->lat
			, 'lng'=>$this->lng
			, 'id'=>$this->id
		);
	}
	
	public function getValuePlusFormattedCost($currency, $currencySymbol){
		if($this->cost > 0 && $this->parentId === -1){
			return $this->value_loc . '&nbsp;&nbsp;' . $currencySymbol . Booki_Helper::toMoney($this->cost) . ' ' . $currency;
		}
		return $this->value_loc;
	}
	
	protected function init(){
		$this->value_loc = Booki_WPMLHelper::t('cascading_item_' . $this->value . '_value', $this->value);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('cascading_item_' . $this->value . '_value', $this->value);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('cascading_item_' . $this->value . '_value');
	}
}
?>