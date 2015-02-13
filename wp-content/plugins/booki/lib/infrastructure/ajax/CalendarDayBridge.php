<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/CalendarDayJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CalendarDayBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readCalendarDay', array($this, 'read')); 
		add_action('wp_ajax_booki_insertCalendarDay', array($this, 'insert')); 
		add_action('wp_ajax_booki_updateCalendarDay', array($this, 'update'));
		add_action('wp_ajax_booki_deleteCalendarDay', array($this, 'delete')); 
		add_action('wp_ajax_booki_cleanupCalendarDay', array($this, 'cleanup')); 
	}
	
	public function readAll(){

		echo Booki_CalendarDayJSONProvider::readAll();
		
		die();
	}
	
	
	
	public function read(){

		echo Booki_CalendarDayJSONProvider::read();
		
		die();
	}
	
	public function insert(){

		echo Booki_CalendarDayJSONProvider::insert();
		
		die();
	}
	
	public function update(){

		echo Booki_CalendarDayJSONProvider::update();
		
		die();
	}
	
	public function cleanup(){

		echo Booki_CalendarDayJSONProvider::cleanup();
		
		die();
	}

	
	public function delete(){

		echo Booki_CalendarDayJSONProvider::delete();
		
		die();
	}
}
?>
