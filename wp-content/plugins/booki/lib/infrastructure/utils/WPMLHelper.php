<?php
require_once 'Helper.php';

class Booki_WPMLHelper{
	public static function register($name, $value){
		if(function_exists('icl_register_string')){
			icl_register_string('booki', $name, stripcslashes($value));
		}
		return stripcslashes($value);
	}
	
	public static function unregister($name){
		if(function_exists('icl_unregister_string')){
			icl_unregister_string('booki', $name);
		}
	}
	
	public static function t($name, $value){
		if(function_exists('icl_t')){
			return icl_t('booki', $name, stripcslashes($value));
		}
		return stripcslashes($value);
	}
	
	public static function registerEmailResource(){
		$templateNames = Booki_Helper::systemEmails();
		foreach($templateNames as $templateName){
			$name = 'email_template_' . str_replace(' ', '_', $templateName);
			$result = Booki_WPMLHelper::t($name, '');
			if($result){
				//already registered, bailout
				break;
			}
			
			$emailSettingRepository = new Booki_EmailSettingRepository($templateName);
			$emailSetting = $emailSettingRepository->read();
			$content = '';
			if(!$emailSetting){
				$content = Booki_Helper::readEmailTemplate($templateName);
			}else{
				$content = $emailSetting->content;
			}
			self::register($name, $content);
		}
	}
}
?>