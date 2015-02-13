<?php
require_once  dirname(__FILE__) . '/../session/Cart.php';
class Booki_CustomFormElements{
	public $formElements;
	public $rows;
	public $projectId;
	public $hasCustomFormFields;
	public function __construct($projectId, $formElements){
		$this->projectId = $projectId;	
		$this->rows = array();
		$this->formElements = $formElements;
		
		foreach($this->formElements as $el){
			if(!isset($this->rows[$el->rowIndex])){
				$this->rows[$el->rowIndex] = array();
			}
			array_push($this->rows[$el->rowIndex], $el);
			usort($this->rows[$el->rowIndex], array($this, 'sortByColIndex'));
		}
		$this->hasCustomFormFields = count($this->rows) > 0;
	}
	
	public function sortByColIndex($a, $b){
		return $a->colIndex > $b->colIndex;
	}
	
	public function fieldStatus($formElement){
		$display = $this->displayField($formElement);
		$attributes = array(
				sprintf('title="%s"', __('Value not required on multiple bookings for the same item', 'booki'))
				, 'disabled'
		);
		if(!$display){
			return implode(" ", $attributes);
		}
		return '';
	}
	public function displayField($formElement){
		$cart = new Booki_Cart();
		if(!$formElement->once){
			return true;
		}
		$bookings = $cart->getBookings();
		$globalSettings = Booki_Helper::globalSettings();
		//if we're booking project multiple times, then bailing out.
		foreach($bookings as $booking){
			if($globalSettings->oneForm || $this->projectId == $booking->projectId){
				return false;
			}
		}
		return true;
	}
	
	public function getAttributes($formElement){
		$enabled = $this->displayField($formElement);
		if($enabled){
			return $formElement->attributes;
		}
		return '';
	}
}
?>