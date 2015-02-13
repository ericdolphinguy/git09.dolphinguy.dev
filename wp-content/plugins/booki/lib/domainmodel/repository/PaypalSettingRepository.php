<?php
require_once  dirname(__FILE__) . '/../entities/PaypalSetting.php';
require_once  dirname(__FILE__) . '/../../infrastructure/actions/ConfigManager.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_PaypalSettingRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $settings_table_name;
	private $settingName;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->settingName = 'Paypal';
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
			return $data;
		}
		
		$configManager = Booki_ConfigManager::getInstance();
		$accountSection = $configManager->getSection('Account');
		$settingSection = $configManager->getSection('Settings');
		//default sandbox
		return new Booki_PaypalSetting(
			$accountSection['acct1.AppId']
			, $accountSection['acct1.UserName']
			, $accountSection['acct1.Password']
			, $accountSection['acct1.Signature']
			, false
			, $settingSection['settings.Currency']
			, $settingSection['settings.BrandName']
			, $settingSection['settings.CustomPageStyle']
			, $settingSection['settings.Logo']
			, $settingSection['settings.HeaderImage']
			, $settingSection['settings.HeaderBorderColor']
			, $settingSection['settings.HeaderBackColor']
			, $settingSection['settings.PayFlowColor']
			, $settingSection['settings.CartBorderColor']
			, $settingSection['settings.AllowBuyerNote']
			, 'Physical'
		);
	}
	
	public function insert($settings){
		 $result = $this->wpdb->insert($this->settings_table_name,  array(
			'name'=>$this->settingName
			, 'data'=>serialize($settings)
		  ), array('%s', '%s'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}

	public function update($settings){
		$result = $this->wpdb->update($this->settings_table_name,  array(
			'data'=>serialize($settings)
		), array('id'=>$settings->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->settings_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id));
		return $rows_affected;
	}
}
?>