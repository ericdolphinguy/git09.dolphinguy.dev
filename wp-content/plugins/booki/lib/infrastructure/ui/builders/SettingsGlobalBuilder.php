<?php
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/SettingsGlobalRepository.php';

class Booki_SettingsGlobalBuilder{
	public $result;
	public function __construct(){
		$repo = new Booki_SettingsGlobalRepository();
		$this->result = $repo->read();
	}
}
?>