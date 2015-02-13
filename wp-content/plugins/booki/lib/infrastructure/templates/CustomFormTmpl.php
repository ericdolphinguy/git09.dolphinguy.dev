<?php
require_once  dirname(__FILE__) . '/../ui/builders/CustomFormBuilder.php';
require_once  dirname(__FILE__) . '/../../domainmodel/entities/ElementType.php';

class Booki_CustomFormTmpl{
	public $data;
	public $errors = array();
	public $colIndex = 0;
	public $projectId;
	public function __construct(){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		$builder = new Booki_CustomFormBuilder($this->projectId);
		$this->data = $builder->result;
	}
}
?>