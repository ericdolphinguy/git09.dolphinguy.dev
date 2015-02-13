<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_User extends Booki_EntityBase{
	public $username;
	public $email;
	public $firstname;
	public $lastname;
	public $bookingsCount;
	public $id;
	public function __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0){
			$this->username = func_get_arg(0);
			$this->email = func_get_arg(1);
			$this->firstname = func_get_arg(2);
			$this->lastname = func_get_arg(3);
			$this->bookingsCount = func_get_arg(4);
			if($numArgs === 6){
				$this->id = func_get_arg(5);
			}
		}
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'username'=>$this->username
			, 'email'=>$this->email
			, 'firstname'=>$this->firstname
			, 'lastname'=>$this->lastname
			, 'bookingsCount'=>$this->bookingsCount
		);
	}
}
?>