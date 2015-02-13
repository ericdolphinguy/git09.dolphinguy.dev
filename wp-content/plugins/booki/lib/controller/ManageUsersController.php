<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/UserRepository.php';
class Booki_ManageUsersController extends Booki_BaseController{
	private $exportsPerPage;
	private $userRepository;
	public function __construct($deleteCallback, $exportCallback, $exportsPerPage){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_manageusers')){
				return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->exportsPerPage = $exportsPerPage;
		$this->userRepository = new Booki_UserRepository();
		parent::__construct(null, null, $deleteCallback);
		if (array_key_exists('export', $_POST)){
			$this->export($exportCallback);
		}
	}
	public function delete($callback){
		$userId = (int)$this->getPostValue('booki_delete');
		$result = $this->userRepository->delete($userId);
		$this->executeCallback($callback, array($result));
	}
	public function export($callback){
		$pageIndex = (int)$_POST['pageindex'];
		$result = $this->userRepository->readAll($pageIndex, $this->exportPerPage);
		$this->executeCallback($callback, array($pageIndex));
	}
}
?>