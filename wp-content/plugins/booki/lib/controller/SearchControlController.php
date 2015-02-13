<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../infrastructure/ui/lists/ProjectList.php';
class Booki_SearchControlController extends Booki_BaseController{
	private $tags;
	private $isWidget;
	private $headingLength;
	private $descriptionLength;
	private $fullPager;
	private $perPage;
	private $projectId;
	public function __construct($listArgs, $callback){
		$this->listArgs = $listArgs;
		$this->search($callback);
	}
	public function search($callback){
		$result = new Booki_ProjectList($this->listArgs);
		$result->bind();
		$this->executeCallback($callback, array($result));
	}
}
?>