<?php
	require_once dirname(__FILE__) . '/../../domainmodel/repository/SettingsGlobalRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/service/EventsLogProvider.php';
	if(!class_exists('MailChimp')){
		require_once  BOOKI_MAILCHIMP . 'Mailchimp.php';
	}
class Booki_MailChimp{
	private $setting;
	private $mailChimp;
	public $apiKey;
	public function __construct(){
		$repo =  new Booki_SettingsGlobalRepository();
		$this->setting = $repo->read();
		$this->apiKey = $this->setting->mailChimpKey;

		if($this->apiKey){
			$this->mailChimp = new MailChimp($this->apiKey, array('verifySSL'=>false));
		}
	}
	public function getCachedList(){
		if(isset($_SESSION['Booki_MailChimpList'])){
			return $_SESSION['Booki_MailChimpList'];
		}
		return null;
	}
	
	public function getList(){
		if(isset($_SESSION['Booki_MailChimpList'])){
			return $_SESSION['Booki_MailChimpList'];
		}
		
		$result = array();
		if($this->mailChimp){
			try{
				$mcList = new Mailchimp_Lists($this->mailChimp);
				$result = $mcList->getList();
			}catch(Exception $ex){
				Booki_EventsLogProvider::insert($ex);
			}
		}
		$_SESSION['Booki_MailChimpList'] = $result;
		return $_SESSION['Booki_MailChimpList'];
	}
	
	public function refreshList(){
		if(isset($_SESSION['Booki_MailChimpList'])){
			unset($_SESSION['Booki_MailChimpList']);
		}
		return $this->getList();
	}
	
	public function batchSubscribe($id, $batch, $doubleOptin=true, $updateExisting=true, $replaceInterests=true){
		$mcList = new Mailchimp_Lists($this->mailChimp);
		try{
			$result = $mcList->batchSubscribe($id, $batch, $doubleOptin, $updateExisting, $replaceInterests);
		}catch(Exception $ex){
			Booki_EventsLogProvider::insert($ex);
		}
	}
	
	public function mergeVarsAdd($id, $vars){
		$mcList = new Mailchimp_Lists($this->mailChimp);
		$mergeVars = $mcList->mergeVars(array($id));
		$data = $mergeVars['data'];
		$existingFields = isset($data[0]) ? $data[0]['merge_vars'] : array();
		$results = array();
		foreach($vars as $var){
			$continue = false;
			foreach($existingFields as $field){
				if($field['tag'] == $var['tag']){
					$continue = true;
					break;
				}
			}
			if($continue){
				continue;
			}
			$options = isset($var['options']) ? $var['options'] : array();
			try{
				$result = $mcList->mergeVarAdd($id, $var['tag'], $var['name'], $options);
			}catch(Exception $ex){
				Booki_EventsLogProvider::insert($ex);
			}
			array_push($results, $result);
		}
		return $results;
	}
}
?>