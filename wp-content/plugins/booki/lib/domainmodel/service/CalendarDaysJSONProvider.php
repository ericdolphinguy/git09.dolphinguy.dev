<?php
require_once  dirname(__FILE__) . '/../repository/CalendarDayRepository.php';
require_once  dirname(__FILE__) . '/../entities/CalendarDay.php';
require_once  dirname(__FILE__) . '/base/ProviderBase.php';

class Booki_CalendarDaysJSONProvider extends Booki_ProviderBase{
		private static $instance;
		protected function __construct()
		{
		}
		public static function repository()
		{
			if (!isset(self::$instance)) {
				self::$instance = new Booki_CalendarDayRepository();
			}
			return self::$instance;
		}
		
		public static function readAll(){
			$model = self::json_decode_request($_POST['model']);
			$calendarDays = self::repository()->readAll($model->calendarId);
			$result = $calendarDays->toArray();
			return $result;
			//return self::json_encode_response($result);
		}
		public static function readAllSeasons(){
			$model = self::json_decode_request($_POST['model']);
			$seasons = self::repository()->readAllSeasons($model->calendarId);
			$calendarDays = self::readAll();
			$result = array('seasons'=>$seasons, 'calendarDays'=>$calendarDays);
			return self::json_encode_response($result);
		}
		
		public static function readAllBySeason(){
			$model = self::json_decode_request($_POST['model']);
			$calendarDays = self::repository()->readAllBySeason($model->calendarId, $model->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}
		
		public static function insert(){
			$models = self::json_decode_request($_POST['model']);
			foreach($models as $model){
				$result = self::repository()->insert( new Booki_CalendarDay(
					$model->calendarId
					, new Booki_DateTime($model->day)
					, $model->timeExcluded
					, $model->hours
					, $model->minutes
					, $model->cost
					, $model->hourStartInterval
					, $model->minuteStartInterval
					, $model->seasonName
					, $model->minNumDaysDeposit
					, $model->deposit
				));
			}
			$calendarDays = self::repository()->readAllBySeason($models[0]->calendarId, $models[0]->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}
		
		public static function update(){
			$models = self::json_decode_request($_POST['model']);
			$calendarId = $models[0]->calendarId;
			foreach($models as $model){
				$result = self::repository()->update( new Booki_CalendarDay(
					$model->calendarId
					, new Booki_DateTime($model->day)
					, $model->timeExcluded
					, $model->hours
					, $model->minutes
					, $model->cost
					, $model->hourStartInterval
					, $model->minuteStartInterval
					, $model->seasonName
					, $model->minNumDaysDeposit
					, $model->deposit
					, $model->id
				));
			}
			$calendarDays = self::repository()->readAllBySeason($models[0]->calendarId, $models[0]->seasonName);
			$result = $calendarDays->toArray();
			return self::json_encode_response($result);
		}

		public static function delete(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->deleteBySeason($model->seasonName);
			return self::json_encode_response($result, 'affectedRecords');
		}
		
		public static function deleteNamelessDays(){
			$model = self::json_decode_request($_POST['model']);
			$result = self::repository()->deleteNamelessDays($model->calendarId);
			return self::json_encode_response($result, 'affectedRecords');
		}
}
?>