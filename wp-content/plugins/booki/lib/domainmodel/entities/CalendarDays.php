<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'CalendarDay.php';
class Booki_CalendarDays extends Booki_CollectionBase{
	public function add($value) {
		if (! ($value instanceOf Booki_CalendarDay) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_CalendarDay class.');
		}
        parent::add($value);
    }
}
?>