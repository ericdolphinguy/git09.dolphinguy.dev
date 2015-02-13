<?php
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/OptionalRepository.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/ProjectRepository.php';
require_once  dirname(__FILE__) . '/../OptionalElements.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';
class Booki_OptionalsFormBuilder{
	public $projectId;
	public $optionals;
	public $result;
	public function __construct($projectId){
		$this->projectId = $projectId;
		$optionalRepository = new Booki_OptionalRepository();
		$optionals = $optionalRepository->readAll($projectId);
		$projectRepository = new Booki_ProjectRepository();
		$project = $projectRepository->read($projectId);
		$localeInfo = Booki_Helper::getLocaleInfo();
		
		$currency = $localeInfo['currency'];
		$currencySymbol = $localeInfo['currencySymbol'];
		
		$this->result = new Booki_OptionalElements($optionals, $project, $currency, $currencySymbol);
	}
}
?>