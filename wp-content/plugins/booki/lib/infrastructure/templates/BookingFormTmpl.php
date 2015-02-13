<?php
require_once  dirname(__FILE__) . '/../ui/builders/BookingFormBuilder.php';

class Booki_BookingFormTmpl{
	public $projectId;
	public $data;
	public $isBackEnd;
	public $uniqueKey;
	public function __construct(){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		$this->isBackEnd = apply_filters( 'booki_is_backend', null);
		
		$builder = new Booki_BookingFormBuilder($this->projectId);
		$this->data = $builder->result;
		//using the uniquekey in the datepickers labels "for" value which 
		//helps when multiple calendars are present on the page 
		//to pop the calendar when the append part containing the calendar icon is clicked.
		$this->uniqueKey = uniqid();
	}
}

?>