<?php
require_once 'OrderSummaryBuilder.php';
class Booki_MiniCartBuilder{
	public $data;
	public function __construct(){
		$orderBuilder = new Booki_OrderSummaryBuilder();
		$this->data = $orderBuilder->result;
	}
}
?>