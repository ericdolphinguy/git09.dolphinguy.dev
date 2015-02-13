<?php
require_once dirname(__FILE__) . '/base/PPBase.php';

class Booki_PPHandler extends Booki_PPBase{
	public function __construct(){
		parent::__construct();
		$this->checkout();
	}
}
?>