<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/PaypalSettingRepository.php';

class Booki_PaypalController extends Booki_BaseController
{
	public $repo;
	public function __construct($createCallback, $updateCallback, $deleteCallback)
	{
		$this->repo = new Booki_PaypalSettingRepository();
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
	}
	
	public function create($callback)
	{
		$result = $this->execute($callback);
		$settings = $result['settings'];
		$errors = $result['errors'];
		if(!in_array('error', $errors, true)){
			$settings->id = $this->repo->insert($settings);
		}
		$this->executeCallback($callback, array($settings, $errors));
	}
	
	public function update($callback)
	{
		$result = $this->execute($callback);
		$settings = $result['settings'];
		$errors = $result['errors'];
		if(!in_array('error', $errors, true)){
			$this->repo->update($settings);
		}
		$this->executeCallback($callback, array($settings, $errors));
	}
	
	public function delete($callback)
	{
		$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
		$success = false;
		if($id !== -1)
		{
			$this->repo->delete($id);
			$success = true;
		}
		$this->executeCallback($callback, array($success));
	}
	
	public function execute($callback)
	{
		$errors = array('username'=>'','password'=>'', 'signature'=>'');
		$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
		$appId = isset($_POST['appid']) ? $_POST['appid'] : '';
		$username = isset($_POST['username']) ? $_POST['username'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';
		$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
		$useSandBox = isset($_POST['usesandbox']) ? ($_POST['usesandbox'] === 'on' ? '1' : 0) : 0;
		$currency = isset($_POST['currency']) ? $_POST['currency'] : '';
		$brandName = isset($_POST['brandname']) ? $_POST['brandname'] : '';
		$pageStyle = isset($_POST['pagestyle']) ? $_POST['pagestyle'] : '';
		$headerImage = isset($_POST['cppheaderimage']) ? $_POST['cppheaderimage'] : '';
		$headerBorderColor = isset($_POST['cppheaderbordercolor']) ? $_POST['cppheaderbordercolor'] : '';
		$headerBackColor = isset($_POST['cppheaderbackcolor']) ? $_POST['cppheaderbackcolor'] : '';
		$payFlowColor = isset($_POST['cpppayflowcolor']) ? $_POST['cpppayflowcolor'] : '';
		$customPageStyle = isset($_POST['pagestyle']) ? $_POST['pagestyle'] : '';
		$cartBorderColor = isset($_POST['cppcartbordercolor']) ? $_POST['cppcartbordercolor'] : '';
		$allowBuyerNote = isset($_POST['allowbuyernote']) ? ($_POST['allowbuyernote'] === 'on' ? '1' : 0) : 0;
		$logo = isset($_POST['cpplogoimage']) ? $_POST['cpplogoimage'] : '';
		$itemCategory = isset($_POST['itemcategory']) ? $_POST['itemcategory'] : 'Physical';
		
		$settings = new Booki_PaypalSetting(
			$appId
			, $username
			, $password
			, $signature
			, $useSandBox
			, $currency
			, $brandName
			, $customPageStyle
			, $logo
			, $headerImage
			, $headerBorderColor
			, $headerBackColor
			, $payFlowColor
			, $cartBorderColor
			, $allowBuyerNote
			, $itemCategory
			, $id
		);
		
		if(!trim($username))
		{
			$errors['username'] = 'error';
		}
		
		if(!trim($password))
		{
			$errors['password'] = 'error';
		}
		
		if(!trim($signature))
		{
			$errors['signature'] = 'error';
		}
		
		return array('settings'=>$settings, 'errors'=>$errors);
	}
}
?>