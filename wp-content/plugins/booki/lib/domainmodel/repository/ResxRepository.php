<?php
require_once  dirname(__FILE__) . '/../entities/Resx.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_ResxRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $settings_table_name;
	private $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->settingName = 'Resx';
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
			$data->init();
			return $data;
		}
		$resx = new Booki_Resx();
		return $resx;
	}
	
	public function insert($settings){
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($settings)
		  ), array('%s', '%s'));
		  
		 if($settings !== false){
			$settings->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($settings){
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($settings)
		), array('id'=>$settings->id), array('%s'));
		$settings->updateResources();
		return $result;
	}
	
	public function delete($id){
		$settings = self::read();
		$settings->deleteResources();
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
}
?>