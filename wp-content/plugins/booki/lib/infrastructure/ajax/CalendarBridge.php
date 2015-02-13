<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/CalendarJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CalendarBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readCalendarByProject', array($this, 'readByProjectCallback')); 
		add_action('wp_ajax_booki_readCalendar', array($this, 'readCallback')); 
		add_action('wp_ajax_booki_insertCalendar', array($this, 'insertCallback')); 
		add_action('wp_ajax_booki_updateCalendar', array($this, 'updateCallback')); 
		add_action('wp_ajax_booki_deleteCalendar', array($this, 'deleteCallback')); 
	}
	
	public  function readByProjectCallback(){

		echo Booki_CalendarJSONProvider::readByProject();
		
		die();
	}
	
	public  function readCallback(){
		
		echo Booki_CalendarJSONProvider::read();
		
		die();
	}
	
	public  function insertCallback(){
		
		echo Booki_CalendarJSONProvider::insert();
		
		die();
	}
	
	public  function updateCallback(){
		
		echo Booki_CalendarJSONProvider::update();
		
		die();
	}
	
	public  function deleteCallback(){
		
		echo Booki_CalendarJSONProvider::delete();
		
		die();
	}
}
?>
