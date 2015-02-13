<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../infrastructure/actions/Admin.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_UninstallController extends Booki_BaseController{

	public function __construct($clearCallback, $deleteCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_uninstall')){
				return;
		}
		
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		
		if(!current_user_can('delete_plugins')){
			return;
		}
		if (array_key_exists('clear', $_POST)){
			$this->clear($clearCallback);
		}
		
		if (array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}
	}
	
	public function clear($callback){
		Booki_Admin::clearDatabase();
		$this->executeCallback($callback, array());
	}
	
	public function delete($callback){
		Booki_Admin::uninstall();
		$this->executeCallback($callback, array());
	}
}
?>