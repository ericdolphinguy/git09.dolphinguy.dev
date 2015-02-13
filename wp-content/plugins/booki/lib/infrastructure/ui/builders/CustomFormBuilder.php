<?php
require_once  dirname(__FILE__) . '/../CustomFormElements.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/FormElementRepository.php';
class Booki_CustomFormBuilder{
	private $projectId;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		
		$formElementRepository = new Booki_FormElementRepository();
		$formElements = $formElementRepository->readAll($projectId);
		
		$this->result = new Booki_CustomFormElements($projectId, $formElements);
	}
}
?>