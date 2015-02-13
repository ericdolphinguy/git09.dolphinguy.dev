<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'CascadingList.php';
class Booki_CascadingLists extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_CascadingList) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_CascadingList class.');
		}
        parent::add($value);
    }
}
?>