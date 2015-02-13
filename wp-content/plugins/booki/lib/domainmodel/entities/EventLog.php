<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_EventLog extends Booki_EntityBase{
	public $id;
	public $entryDate;
	public $data;
	public function __construct($data = null, $entryDate = null, $id = null){
		$this->data = $data;
		$this->entryDate = $entryDate;
		$this->id = $id;
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'entryDate'=>$this->entryDate
			, 'data'=>$this->data
		);
	}
}
?>