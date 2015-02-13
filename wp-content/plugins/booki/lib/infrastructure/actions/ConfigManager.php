<?php
require_once  dirname(__FILE__) . '/../../exception/ConfigurationException.php';
/**
 * BKConfigManager loads the paypal merchant SDK configuration file and
 * allows reading/writing to it.
 */
 class Booki_ConfigManager {

	private $config;
	private $configFile;
	/**
	 * @var BKConfigManager
	 */
	private static $instance;

	private function __construct($configFile){
		if($configFile){
			$this->configFile = $configFile;
		}
		else{
			$this->configFile = constant('BOOKI_PAYPAL_MERCHANT_SDK') . 'config/sdk_config.ini';
		}
		$this->load($this->configFile);
	}

	// create singleton object for BKConfigManager
	public static function getInstance($configFile = null)
	{
		if ( !isset(self::$instance) || $configFile !== self::$instance->configFile) {
			self::$instance = new Booki_ConfigManager($configFile);
		}
		return self::$instance;
	}

	//used to load the file
	private function load($fileName) {
		$this->config = @parse_ini_file($fileName, true);
		if($this->config == NULL || count($this->config) == 0) {
			throw new Booki_ConfigurationException("Config file $fileName not found","303");
		}
	}

	/**
	 * simple getter for configuration sections
	 * If an exact match for key is not found,
	 * does a "contains" search on the key
	 */
	public function getSection($searchKey){

		if(array_key_exists($searchKey, $this->config))
		{
			return $this->config[$searchKey];
		}
		else {
			$arr = array();
			foreach ($this->config as $k => $v){
				if(strstr($k, $searchKey)){
					$arr[$k] = $v;
				}
			}
			
			return $arr;
		}

	}
	
	/**
	 * simple setter for configuration params
	 * Searches for a section, if found, looks for the searchkey in the section
	 * and sets the value.
	 */
	public function setSectionItem($sectionName, $searchKey, $value){

		if(array_key_exists($sectionName, $this->config))
		{
			if(array_key_exists($searchKey, $this->config[$sectionName])){
				$this->config[$sectionName][$searchKey] = $value;
				$this->save();
			}
		}
	}
	/**
		sets a value in the Account section
	*/
	public function setAccountItem($username, $password, $signature)
	{
		$this->setSectionItem('Account', 'acct1.UserName', $username);
		$this->setSectionItem('Account', 'acct1.Password', $password);
		$this->setSectionItem('Account', 'acct1.Signature', $signature);
	}
	/**
		sets a value in the Settings section
	*/
	public function setSettingItem($currency, $brandName, $pageStyle, $headerImage, 
			$headerImage, $headerBorderColor, $headerBackColor, $payFlowColor, $cartBorderColor, $allowBuyerNote, $logo)
	{
		$this->setSectionItem('Settings', 'settings.Currency', $currency);
		$this->setSectionItem('Settings', 'settings.BrandName', $brandName);
		$this->setSectionItem('Settings', 'settings.CustomPageStyle', $pageStyle);
		$this->setSectionItem('Settings', 'settings.HeaderImage', $headerImage);
		$this->setSectionItem('Settings', 'settings.HeaderBorderColor', $headerBorderColor);
		$this->setSectionItem('Settings', 'settings.HeaderBackColor', $headerBackColor);
		$this->setSectionItem('Settings', 'settings.PayFlowColor', $payFlowColor);
		$this->setSectionItem('Settings', 'settings.CartBorderColor', $cartBorderColor);
		$this->setSectionItem('Settings', 'settings.AllowBuyerNote', $allowBuyerNote);
		$this->setSectionItem('Settings', 'settings.Logo', $logo);
	}
	private function save() { 
		$content = ""; 
		foreach ($this->config as $key=>$elem) { 
			$content .= "[".$key."]\n"; 
			foreach ($elem as $key2=>$elem2) { 
				if(is_array($elem2)) 
				{ 
					for($i=0;$i<count($elem2);$i++) 
					{ 
						$content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
					} 
				} 
				else if($elem2=="") $content .= $key2." = \n"; 
				else $content .= $key2." = \"".$elem2."\"\n"; 
			} 
		} 

		if (!$handle = fopen($this->configFile, 'w')) { 
			return false; 
		}
		if (!fwrite($handle, $content)) { 
			return false; 
		}
		//save successful, re-load configFile
		$this->load($this->configFile);
		
		fclose($handle); 
		return true; 
	}
}