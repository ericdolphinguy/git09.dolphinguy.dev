<?php
require_once dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
require_once dirname(__FILE__) . '/../base/EntityBase.php';

class Booki_PaypalSetting extends Booki_EntityBase
{
	public $id = -1;
	public $appId;
	public $username;
	public $password;
	public $signature;
	public $currency;
	public $brandName;
	public $customPageStyle;
	public $logo;
	public $headerImage;
	public $headerBorderColor;
	public $headerBackColor;
	public $payFlowColor;
	public $cartBorderColor;
	public $allowBuyerNote;
	public $useSandBox;
	public $itemCategory;
	public function __construct()
	{
		$numArgs = func_num_args();
		if($numArgs > 0)
		{
			$this->appId = func_get_arg(0);
			$this->username = func_get_arg(1);
			$this->password = func_get_arg(2);
			$this->signature = func_get_arg(3);
			$this->useSandBox = func_get_arg(4);
			$this->currency = func_get_arg(5);
			$this->brandName = func_get_arg(6);
			$this->customPageStyle = func_get_arg(7);
			$this->logo = func_get_arg(8);
			$this->headerImage = func_get_arg(9);
			$this->headerBorderColor = func_get_arg(10);
			$this->headerBackColor = func_get_arg(11);
			$this->payFlowColor = func_get_arg(12);
			$this->cartBorderColor = func_get_arg(13);
			$this->allowBuyerNote = func_get_arg(14);
			$this->itemCategory = func_get_arg(15);
			if($numArgs === 17)
			{
				$this->id = func_get_arg(16);
			}
			if(!$this->itemCategory)
			{
				$this->itemCategory = 'Digital';
			}
		}
	}
}
?>