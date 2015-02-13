<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_InvoiceSetting extends Booki_EntityBase{
	public $id = -1;
	public $companyName;
	public $companyNumber;
	public $address;
	public $telephone;
	public $email;
	public $additionalNote;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->companyName = func_get_arg(0);
			$this->companyNumber = func_get_arg(1);
			$this->telephone = func_get_arg(2);
			$this->email = func_get_arg(3);
			$this->address = func_get_arg(4);
			$this->additionalNote = func_get_arg(5);
			if($numArgs === 7){
				$this->id = func_get_arg(6);
			}
		}
	}
}
?>