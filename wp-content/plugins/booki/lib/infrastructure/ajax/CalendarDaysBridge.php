<?php
require_once  dirname(__FILE__) . '/../../domainmodel/service/CalendarDaysJSONProvider.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CalendarDaysBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_readAllSeasons', array($this, 'readAllSeasons')); 
		add_action('wp_ajax_booki_readAllCalendarDays', array($this, 'readAll'));
		add_action('wp_ajax_booki_insertCalendarDays', array($this, 'insert')); 
		add_action('wp_ajax_booki_updateCalendarDays', array($this, 'update')); 
		add_action('wp_ajax_booki_deleteCalendarDays', array($this, 'delete'));
		add_action('wp_ajax_booki_deleteNamelessDays', array($this, 'deleteNamelessDays'));
	}
	public function readAllSeasons(){

		echo Booki_CalendarDaysJSONProvider::readAllSeasons();
		
		die();
	}
	public function readAll(){

		echo Booki_CalendarDaysJSONProvider::readAllBySeason();
		
		die();
	}

	public function insert(){

		echo Booki_CalendarDaysJSONProvider::insert();
		
		die();
	}
	
	public function update(){

		echo Booki_CalendarDaysJSONProvider::update();
		
		die();
	}
	
	public function delete(){

		echo Booki_CalendarDaysJSONProvider::delete();
		
		die();
	}
	
		
	public function deleteNamelessDays(){

		echo Booki_CalendarDaysJSONProvider::deleteNamelessDays();
		
		die();
	}
	
}
?>
