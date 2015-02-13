<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_TimezoneControlBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_timezoneChoice', array($this, 'timezoneChoiceCallback')); 
		add_action('wp_ajax_nopriv_booki_timezoneChoice', array($this, 'timezoneChoiceCallback')); 
	}
	
	public  function timezoneChoiceCallback(){

		$model = $_POST['model'];
		$options = $this->timezoneChoice($model['region'], $model['selectedZone']);
		$result = array( 'options'=>$options);
		
		echo Booki_Helper::json_encode_response($result, 'result');

		die();
	}
	
	public function timezoneChoice( $region, $selectedZone = null ) {
		$utc = new DateTimeZone('UTC');
		$dt = new Booki_DateTime('now', $utc);

		$timezones = DateTimeZone::listIdentifiers();

		sort($timezones);
		$result = array(sprintf('<option value="-1">%s</option>', __('Select new timezone', 'booki')));

		foreach($timezones as $timezone) {
			$pair = explode('/', $timezone, 2);
			$currentRegion = $pair[0];

			if($currentRegion != trim($region)){
				continue;
			}
			
			$transition = Booki_TimeHelper::getTimezoneAbbrOffset($timezone);
			$timezoneInfo = Booki_TimeHelper::formatTimezoneTransition($transition);

			$selected = ($selectedZone === $timezone) ? 'selected ' : '';
			
			array_push($result, '<option ' . $selected . 'value="' . $timezone . '">' . $timezone . ' [' . $timezoneInfo . ']</option>');
		}

		return join( "\n", $result );
	}
}
?>
