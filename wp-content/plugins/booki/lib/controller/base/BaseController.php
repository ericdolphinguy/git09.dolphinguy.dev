<?php

class Booki_BaseController{
	public function __construct($createCallback = null, $updateCallback = null, $deleteCallback = null){
		if (array_key_exists('booki_create', $_POST)){
			$this->create($createCallback);
		}else if(array_key_exists('booki_update', $_POST)){
			$this->update($updateCallback);
		}else if(array_key_exists('booki_delete', $_POST)){
			$this->delete($deleteCallback);
		}
	}
	public function create($callback){

	}
	public function update($updateCallback){
		
	}
	public function delete($deleteCallback){
		
	}
	
	public function executeCallback($callback, $param = null){
		if (is_callable($callback)) {
			if($param === null){
				$param = array();
			}
			call_user_func_array($callback, $param);
		}
	}
	
	protected function getPostValue($key, $default = null){
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}
	
	protected function getBoolPostValue($key){
		return isset($_POST[$key]) ? true : false;
	}

	protected function getBoolValue($key){
		return isset($_POST[$key]) ? true : false;
	}
	public function encode($value){
		return htmlspecialchars($value, ENT_QUOTES);
	}
}
?>