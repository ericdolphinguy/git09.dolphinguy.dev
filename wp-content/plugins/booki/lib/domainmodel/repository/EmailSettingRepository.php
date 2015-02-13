<?php
require_once  dirname(__FILE__) . '/../entities/EmailSetting.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_EmailSettingRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $settings_table_name;
	private $settingName;
	public function __construct($templateName){
		global $wpdb;
		$this->wpdb = &$wpdb;
		if(!$templateName){
			 throw new Exception('TemplateName parameter must be provided.');
		}
		$this->settingName = $templateName;
		$this->settings_table_name = $wpdb->prefix . 'booki_settings';
	}

	public function read(){
		$sql = "SELECT id, name, data
				FROM $this->settings_table_name
				WHERE name = '$this->settingName'";
		$result = $this->wpdb->get_results($sql);
		if($result){
			$r = $result[0];
			$data = unserialize($r->data);
			$data->id = (int)$r->id;
			$data->templateName = str_replace(' ', '_', $this->settingName);
			return $data;
		}
		return false;
	}
	
	public function insert($emailSetting){
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($emailSetting)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($emailSetting){
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($emailSetting)
		), array('id'=>$emailSetting->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
}
?>