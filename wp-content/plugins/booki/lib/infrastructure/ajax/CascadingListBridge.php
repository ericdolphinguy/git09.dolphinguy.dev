<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/CascadingListJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CascadingListBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllCascadingList', array($this, 'readAllCallback')); 
		add_action('wp_ajax_booki_readCascadingList', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertCascadingList', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateCascadingList', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteCascadingList', array($this, 'deleteCallback'));
	}
	
	public function readAllCallback(){

		echo Booki_CascadingListJSONProvider::readAll();
		
		die();
	}
	
	public function readCallback(){

		echo Booki_CascadingListJSONProvider::read();
		
		die();
	}
	
	public function insertCallback(){

		echo Booki_CascadingListJSONProvider::insert();
		
		die();
	}
	
	public function updateCallback(){

		echo Booki_CascadingListJSONProvider::update();
		
		die();
	}
	
	public function deleteCallback(){

		echo Booki_CascadingListJSONProvider::delete();
		
		die();
	}
}
?>
