<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/FormElementJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_FormElementBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllFormElement', array($this, 'readAllCallback')); 
		add_action('wp_ajax_booki_readFormElement', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertFormElement', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateFormElement', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteFormElement', array($this, 'deleteCallback'));  
	}
	
	public function readAllCallback(){

		echo Booki_FormElementJSONProvider::readAll();
		
		die();
	}
	
	public function readCallback(){

		echo Booki_FormElementJSONProvider::read();
		
		die();
	}
	
	public function insertCallback(){
	
		echo Booki_FormElementJSONProvider::insert();
		
		die();
	}
	
	public function updateCallback(){

		echo Booki_FormElementJSONProvider::update();
		
		die();
	}
	
	public function deleteCallback(){

		echo Booki_FormElementJSONProvider::delete();
		
		die();
	}
}
?>
