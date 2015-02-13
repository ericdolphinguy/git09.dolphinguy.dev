<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'BookedOptional.php';
class Booki_BookedOptionals extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_BookedOptional) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_BookedOptional class.');
		}
        parent::add($value);
    }
}
?>