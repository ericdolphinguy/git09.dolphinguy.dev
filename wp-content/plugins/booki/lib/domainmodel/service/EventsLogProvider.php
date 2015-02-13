<?php
require_once  dirname(__FILE__) . '/../entities/EventLog.php';
require_once  dirname(__FILE__) . '/../repository/EventsLogRepository.php';
require_once  dirname(__FILE__) . '/base/ProviderBase.php';

class Booki_EventsLogProvider
{
	private static $eventsLogRepo;

	protected function __construct()
	{
	}
	public static function eventsLogRepository()
	{
		if (!isset(self::$eventsLogRepo)) 
		{
			self::$eventsLogRepo = new Booki_EventsLogRepository();
		}
		return self::$eventsLogRepo;
	}
	
	public static function insert($data)
	{
		return self::eventsLogRepository()->insert(new Booki_EventLog($data, new Booki_DateTime()));
	}
	
}
?>