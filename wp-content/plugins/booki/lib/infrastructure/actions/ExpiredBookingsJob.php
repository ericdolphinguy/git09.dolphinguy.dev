<?php
require_once dirname(__FILE__) . '/../ui/builders/SettingsGlobalBuilder.php';
require_once dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';

class Booki_ExpiredBookingsJob{
	public function __construct()
	{
		if ( !wp_next_scheduled('Booki_ExpiredBookingsJobEventHook'))
		{
			wp_schedule_event( time(), 'hourly', 'Booki_ExpiredBookingsJobEventHook' );
		}
	}
	public static function init(){
		$builder = new Booki_SettingsGlobalBuilder();
		$globalSettings = $builder->result;
		if($globalSettings->unpaidOrderExpiry > 0){
			$result = Booki_BookingProvider::deleteExired($globalSettings->unpaidOrderExpiry);
		}
	}
}

add_action( 'Booki_ExpiredBookingsJobEventHook', array('Booki_ExpiredBookingsJob', 'init') );


?>