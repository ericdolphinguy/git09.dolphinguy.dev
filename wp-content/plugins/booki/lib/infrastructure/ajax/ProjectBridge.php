<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/ProjectJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_ProjectBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllProject', array($this, 'readAllCallback')); 
		add_action('wp_ajax_booki_readAllProjectTags', array($this, 'readAllTagsCallback')); 
		add_action('wp_ajax_booki_readProject', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_duplicateProject', array($this, 'duplicateCallback')); 
		add_action('wp_ajax_booki_insertProject', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateProject', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteProject', array($this, 'deleteCallback')); 
	}
	
	public function readAllCallback(){

		echo Booki_ProjectJSONProvider::readAll($includeTags = true);
		
		die();
	}
	
	public function readAllTagsCallback(){

		echo Booki_ProjectJSONProvider::readAllTags();
		
		die();
	}
	
	public function readCallback(){

		echo Booki_ProjectJSONProvider::read();
		
		die();
	}
	
	public function duplicateCallback(){

		echo Booki_ProjectJSONProvider::duplicateProject();
		
		die();
	}
	
	public function insertCallback(){

		echo Booki_ProjectJSONProvider::insert();
		
		die();
	}
	
	public function updateCallback(){

		echo Booki_ProjectJSONProvider::update();
		
		die();
	}
	
	public function deleteCallback(){

		echo Booki_ProjectJSONProvider::delete();
		
		die();
	}
}
?>
