<?php
require_once  dirname(__FILE__) . '/../repository/FormElementRepository.php';
require_once  dirname(__FILE__) . '/../entities/FormElement.php';
require_once  dirname(__FILE__) . '/base/ProviderBase.php';

class Booki_FormElementJSONProvider extends Booki_ProviderBase{
	private static $instance;
	protected function __construct()
	{
	}
	public static function repository()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Booki_FormElementRepository();
		}
		return self::$instance;
	}
	public static function readAll(){
		$model = self::json_decode_request($_POST['model']);
		$result = null;
		$formElements = self::repository()->readAll($model->id);
		if($formElements){
			$result = array('formElements'=>$formElements->toArray(), 'cols'=>$formElements->cols, 'rows'=>$formElements->rows);
		}
		return self::json_encode_response($result);
	}
	
	public static function read(){
		$model = self::json_decode_request($_POST['model']);
		$result = null;
		$formElement = self::repository()->read($model->id);
		if($formElement){
			$result = $formElement->toArray();
		}
		return self::json_encode_response($result);
	}
	
	public static function insert(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->insert(new Booki_FormElement(
			$model->projectId
			, $model->label
			, $model->elementType
			, $model->lineSeparator
			, $model->rowIndex
			, $model->colIndex
			, $model->className
			, $model->value
			, $model->bindingData
			, $model->once
			, $model->capability
			, $model->validation
		));
		return self::json_encode_response($result, 'id');
	}
	
	public static function update(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->update(new Booki_FormElement(
			$model->projectId
			, $model->label
			, $model->elementType
			, $model->lineSeparator
			, $model->rowIndex
			, $model->colIndex
			, $model->className
			, $model->value
			, $model->bindingData
			, $model->once
			, $model->capability
			, $model->validation
			, $model->id
		));
		return self::json_encode_response($result, 'affectedRecords');
	}
	
	public static function delete(){
		$model = self::json_decode_request($_POST['model']);
		$result = self::repository()->delete($model->id);
		return self::json_encode_response( $result, 'affectedRecords');
	}
}
?>