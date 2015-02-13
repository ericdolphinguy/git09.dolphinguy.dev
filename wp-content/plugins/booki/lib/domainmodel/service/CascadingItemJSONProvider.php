<?php
require_once  dirname(__FILE__) . '/../entities/CascadingList.php';
require_once  dirname(__FILE__) . '/../entities/CascadingItem.php';
require_once  dirname(__FILE__) . '/../repository/CascadingListRepository.php';
require_once  dirname(__FILE__) . '/base/ProviderBase.php';

class Booki_CascadingItemJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_CascadingListRepository();
		}
		return self::$instance;
	}
	
	public static function readAll(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readItemsByList(new Booki_CascadingList($model->projectId, $model->label, $model->isRequired, $model->id));
		if($result){
			$result = $result->toArray();
		}
		return self::json_encode_response($result);
	}
	
	public static function readCascadingItemsByListId(){
		$model = isset($_POST['model']) ? $_POST['model'] : array('id'=>-1);
		$listId = $model['id'];
		$cascadingList = self::repository()->readList($listId);
		$result = self::repository()->readItemsByList($cascadingList);
		if($result){
			$result = $result->toArray();
		}
		return self::json_encode_response($result);
	}
	
	public static function read(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->readItem($model->id);
		if($result){
			$result = $result->toArray();
		}
		return self::json_encode_response($result);
	}
	
	public static function insert(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insertItem(new Booki_CascadingItem((array)$model));
		return self::json_encode_response($result, 'id');
	}
	
	public static function update(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->updateItem(new Booki_CascadingItem((array)$model));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function delete(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->deleteByItem($model->id);
		return self::json_encode_response($result, 'affectedRecords');
	}
	
}
?>