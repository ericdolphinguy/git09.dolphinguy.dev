<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/EmailSetting.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/EmailSettingRepository.php';
class Booki_EmailSettingController extends Booki_BaseController
{
	private $repo;
	public function __construct($createCallback = null, $updateCallback = null, $deleteCallback = null){
		if(!isset($_POST['booki_emailtemplates'])){
			return;
		}
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$templateName = $this->getPostValue('templateName');
		if(!$templateName){
			return;
		}
		$this->repo = new Booki_EmailSettingRepository($templateName);
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$setting = $this->process();
		$result = $this->repo->insert($setting);
		$this->executeCallback($callback);
	}
	
	public function update($callback){
		$id = (int)$this->getPostValue('booki_update');
		$setting = $this->process($id);
		$result = $this->repo->update($setting);
		$this->executeCallback($callback);
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('booki_delete');
		$result = $this->repo->delete($id);
		$this->executeCallback($callback);
	}
	
	protected function process($id = -1) {
		$content = $this->getPostValue('content');
		$senderName = $this->getPostValue('senderName');
		$senderEmail = $this->getPostValue('senderEmail');
		$subject = $this->getPostValue('subject');
		$enable = $this->getBoolPostValue('enable');
		return new Booki_EmailSetting(
			$content
			, $senderName
			, $senderEmail
			, $subject
			, $enable
			, $id
		);
	}
}
?>