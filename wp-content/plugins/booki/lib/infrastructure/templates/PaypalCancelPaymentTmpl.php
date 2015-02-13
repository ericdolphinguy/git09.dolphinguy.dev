<?php
require_once dirname(__FILE__) . '/../../controller/PaypalCancelPaymentController.php';

class Booki_PaypalCancelPaymentTmpl{
	public $success;
	public function __construct(){
		new Booki_PaypalCancelPaymentController(array($this, 'onCancel'));
	}

	public function onCancel($result){
		$this->success = true;
	}
}
?>