<?php
require_once  dirname(__FILE__) . '/../entities/Optional.php';
require_once  dirname(__FILE__) . '/../repository/OptionalRepository.php';
require_once  dirname(__FILE__) . '/base/ProviderBase.php';

class Booki_OptionalJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_OptionalRepository();
		}
		return self::$instance;
	}
	
	public static function readAll(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readAll($model->projectId);
		return self::json_encode_response($result);
	}
	
	public static function read(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->read($model->id);
		return self::json_encode_response($result);
	}

	public static function insert(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insert(new Booki_Optional($model->projectId, $model->name, $model->cost));
		return self::json_encode_response($result, 'id');
	}
	
	public static function update(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->update(new Booki_Optional($model->projectId, $model->name, $model->cost, $model->id));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function delete(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->delete($model->id);
		return self::json_encode_response($result, 'affectedRecords');
	}
	
}
?>