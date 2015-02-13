<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'CascadingItem.php';
class Booki_CascadingItems extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_CascadingItem) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_CascadingItem class.');
		}
        parent::add($value);
    }
}
?>