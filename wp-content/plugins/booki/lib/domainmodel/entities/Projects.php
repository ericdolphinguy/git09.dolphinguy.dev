<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'Project.php';
class Booki_Projects extends Booki_CollectionBase
{
	public $total = 0;
    public function add($value) {
		if (! ($value instanceOf Booki_Project) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Project class.');
		}
		parent::add($value);
    }
}
?>