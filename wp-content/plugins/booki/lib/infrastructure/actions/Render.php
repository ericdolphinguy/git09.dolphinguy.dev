<?php
require_once dirname(__FILE__) . '/../utils/Helper.php';
require_once dirname(__FILE__) . '/../utils/PageNames.php';
require_once dirname(__FILE__) . '/../../controller/CartController.php';
class Booki_Render{
	public $projectId;
	public $listArgs;
	public function __construct()
	{
		$this->projectId = null;
		$this->listArgs = null;
	}
	
	public function booking($id) {
		$this->projectId = intval($id);
		if($id === -1){
			return;
		}
		$this->disableCache();
		add_filter( 'booki_shortcode_id', array($this, 'getProjectId'));
		$templatePath = Booki_ThemeHelper::getTemplateFilePath('master.php');
		return Booki_ThemeHelper::getTemplateRender($templatePath);
	}
	
	public function bookingList($listArgs) {
		if(!$listArgs || count($listArgs) === 0){
			return;
		}
		$this->listArgs = $listArgs;
		add_filter( 'booki_list', array($this, 'getListArgs'));
		$templatePath = Booki_ThemeHelper::getTemplateFilePath('list.php');
		$result =  @Booki_ThemeHelper::getTemplateRender($templatePath);
		return str_replace(array("\r\n", "\n\r", "\n", "\r"), "", $result);
	}
	
	public function basket(){
		$templatePath = Booki_ThemeHelper::getTemplateFilePath('minicart.php');
		$result = Booki_ThemeHelper::getTemplateRender($templatePath);
		return str_replace(array("\r\n", "\n\r", "\n", "\r"), "", $result);
	}
	
	public function cart(){
		$this->disableCache();
		Booki_ThemeHelper::includeTemplate('cartpartial.php');
	}
	
	public function payPalBillSettlement(){
		$this->disableCache();
		Booki_ThemeHelper::includeTemplate('paypalbillsettlementpartial.php');
	}
	
	public function payPalPaymentConfirmation(){
		$this->disableCache();
		Booki_ThemeHelper::includeTemplate('paypalprocesspaymentspartial.php');
	}
	
	public function payPalPaymentCancel(){
		$this->disableCache();
		Booki_ThemeHelper::includeTemplate('paypalcancelpaymentpartial.php');
	}
	
	public function bookingItemDetails(){
		$this->disableCache();
		Booki_ThemeHelper::includeTemplate('bookingviewpartial.php');
	}
	
	public function historyPage(){
		Booki_ThemeHelper::includeTemplate('../user/historypartial.php');
	}
	
	public function statsPage(){
		Booki_ThemeHelper::includeTemplate('../user/singlecolstatspartial.php');
	}
	
	public function getProjectId(){
		return $this->projectId;
	}
	
	public function getListArgs(){
		return $this->listArgs;
	}
	
	protected function disableCache(){
		//using nonce, so ensure WP Super Cache 
		//and W3 Total Cache do not cache
		if (!defined('DONOTCACHEPAGE')){
			define('DONOTCACHEPAGE', true);
		}
	}
}

?>