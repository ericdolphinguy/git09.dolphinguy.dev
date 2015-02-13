<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/InvoiceSetting.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/InvoiceSettingRepository.php';
class Booki_InvoiceSettingController extends Booki_BaseController
{
	private $repo;
	private $id;
	public function __construct($createCallback, $updateCallback, $deleteCallback){
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		$this->repo = new Booki_InvoiceSettingRepository();
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback){
		$setting = $this->process();
		$result = $this->repo->insert($setting);
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$id = (int)$this->getPostValue('booki_update');
		$setting = $this->process($id);
		$result = $this->repo->update($setting);
		$this->executeCallback($callback, array($result));
	}
	
	public function delete($callback){
		$id = (int)$this->getPostValue('booki_delete');
		$result = $this->repo->delete($id);
		$this->executeCallback($callback, array($result));
	}
	
	protected function process($id = -1) {
		$companyName = $this->getPostValue('companyName');
		$address = $this->getPostValue('address');
		$telephone = $this->getPostValue('telephone');
		$email = $this->getPostValue('email');
		$companyNumber = $this->getPostValue('companyNumber');
		$additionalNote = $this->getPostValue('additionalNote');
		
		return new Booki_InvoiceSetting(
			$companyName
			, $companyNumber
			, $telephone
			, $email
			, $address
			, $additionalNote
			, $id
		);
	}
}
?>