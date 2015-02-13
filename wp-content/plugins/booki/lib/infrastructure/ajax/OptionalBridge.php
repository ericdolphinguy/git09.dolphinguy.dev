<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/OptionalJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_OptionalBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllOptional', array($this, 'readAllCallback')); 
		add_action('wp_ajax_booki_readOptional', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertOptional', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateOptional', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteOptional', array($this, 'deleteCallback'));
	}
	
	public function readAllCallback(){

		echo Booki_OptionalJSONProvider::readAll();
		
		die();
	}
	
	public function readCallback(){

		echo Booki_OptionalJSONProvider::read();
		
		die();
	}
	
	public function insertCallback(){

		echo Booki_OptionalJSONProvider::insert();
		
		die();
	}
	
	public function updateCallback(){

		echo Booki_OptionalJSONProvider::update();
		
		die();
	}
	
	public function deleteCallback(){

		echo Booki_OptionalJSONProvider::delete();
		
		die();
	}
}
?>
