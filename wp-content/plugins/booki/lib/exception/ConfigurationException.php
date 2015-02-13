<?php
class Booki_ConfigurationException extends Exception {

	public function __construct($message=null,$code=0) {
		parent::__construct($message, $code);
	}
}