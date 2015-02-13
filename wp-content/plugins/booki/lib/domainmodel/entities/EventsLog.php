<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'EventLog.php';
class Booki_EventsLog extends Booki_CollectionBase{
	public $total = 0;
	public function add($value) {
		if (! ($value instanceOf Booki_EventLog) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_EventLog class.');
		}
        parent::add($value);
    }
}
?>