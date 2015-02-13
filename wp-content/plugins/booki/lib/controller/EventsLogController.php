<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/EventsLogRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/EventLog.php';

class Booki_EventsLogController extends Booki_BaseController{
	private $repo;
	public function __construct($deleteCallback, $deleteAllCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_eventslog')){
			return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_EventsLogRepository();

		if(array_key_exists('delete', $_POST)){
			$this->delete($deleteCallback);
		}else if(array_key_exists('deleteall', $_POST)){
			$this->deleteAll($deleteAllCallback);
		}
	}
	
	
	public function delete($callback){
		$id = isset($_POST['delete']) ? intval($_POST['delete']) : null;
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	
	public function deleteAll($callback){
		$result = $this->repo->deleteAll();
		$this->executeCallback($callback, array($result));
	}
}
?>