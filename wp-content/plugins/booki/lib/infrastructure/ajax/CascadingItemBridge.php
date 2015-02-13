<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/CascadingItemJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CascadingItemBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllCascadingItem', array($this, 'readAllCallback'));
		add_action('wp_ajax_booki_readCascadingItemsByListId', array($this, 'readCascadingItemsByListIdCallback'));
		add_action('wp_ajax_nopriv_booki_readCascadingItemsByListId', array($this, 'readCascadingItemsByListIdCallback')); 		
		add_action('wp_ajax_booki_readCascadingItem', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertCascadingItem', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateCascadingItem', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteCascadingItem', array($this, 'deleteCallback'));
	}
	
	public function readAllCallback(){

		echo Booki_CascadingItemJSONProvider::readAll();
		
		die();
	}
	
	public function readCascadingItemsByListIdCallback(){

		echo Booki_CascadingItemJSONProvider::readCascadingItemsByListId();
		
		die();
	}
	
	public function readCallback(){

		echo Booki_CascadingItemJSONProvider::read();
		
		die();
	}
	
	public function insertCallback(){

		echo Booki_CascadingItemJSONProvider::insert();
		
		die();
	}
	
	public function updateCallback(){

		echo Booki_CascadingItemJSONProvider::update();
		
		die();
	}
	
	public function deleteCallback(){

		echo Booki_CascadingItemJSONProvider::delete();
		
		die();
	}
}
?>
