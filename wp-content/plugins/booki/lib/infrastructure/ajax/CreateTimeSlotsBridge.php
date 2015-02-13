<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../../domainmodel/base/EntityBase.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_CreateTimeSlotsBridge extends Booki_BridgeBase{

	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_createTimeSlots', array($this, 'getTimeSlotsCallback')); 
	}
	
	public  function getTimeSlotsCallback(){

		$model = Booki_Helper::json_decode_request($_POST['model']);
		$entityBase = new Booki_EntityBase();
		$result = $entityBase->createTimeSlots($model->hours, $model->minutes, $model->hourStartInterval, $model->minuteStartInterval, $model->enableSingleHourMinuteFormat);
		echo Booki_Helper::json_encode_response($result, 'result');

		die();
	}
}
?>
