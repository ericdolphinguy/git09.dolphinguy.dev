<?php
require_once dirname(__FILE__) . '/../utils/Helper.php';
require_once 'Admin.php';
class Booki_ResetAppJob{
	public function __construct()
	{
		if ( !wp_next_scheduled('Booki_ResetAppJobEventHook'))
		{
			wp_schedule_event( time(), 'hourly', 'Booki_ResetAppJobEventHook' );
		}
	}
	public static function init(){
		if(BOOKI_RESTRICTED_MODE){
			Booki_Admin::clearDatabase();
		}
	}
}
add_action('Booki_ResetAppJobEventHook', array('Booki_ResetAppJob', 'init'));

?>