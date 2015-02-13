<?php
require_once 'Helper.php';
class Booki_ThemeHelper{
	public static function includeTemplate($fileName){
		if(file_exists(dirname( __FILE__ ) . "/$fileName")){
			include $fileName ;
		}else {
			include BOOKI_ROOT . "/lib/views/templates/$fileName";
		}
	}
	
	public static function getHistoryPage(){
		$globalSettings = Booki_Helper::globalSettings();
		if($globalSettings->useDashboardHistoryPage){
			return admin_url() . 'admin.php?page=booki/userhistory.php';
		}
		return Booki_Helper::getUrl(Booki_PageNames::HISTORY_PAGE);
	}
	
	public static function getTemplateFilePath($fileName){
		$path = get_template_directory() . DIRECTORY_SEPARATOR . $fileName;
		if(!file_exists($path)){
			//oh ok, get template directly from plugin
			$path = dirname(__FILE__) . '/../../views/templates/' . $fileName;
		}
		return $path;
	}
	
	public static function getTemplateRender($path){
		ob_start();
		include($path);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}
?>