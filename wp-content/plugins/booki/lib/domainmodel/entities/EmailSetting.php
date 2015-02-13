<?php
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';
class Booki_EmailSetting extends Booki_EntityBase{
	public $id = -1;
	public $enable = true;
	public $senderName;
	public $senderEmail;
	public $subject;
	public $content;
	public $templateName;
	public function  __construct(){
		$numArgs = func_num_args();
		if($numArgs > 0)
		{
			$this->content = func_get_arg(0);
			$this->senderName = func_get_arg(1);
			$this->senderEmail = func_get_arg(2);
			$this->subject = func_get_arg(3);
			if($numArgs >= 5)
			{
				$this->enable = func_get_arg(4);
			}
			if($numArgs === 6)
			{
				$this->id = func_get_arg(5);
			}
		}
		if(!$this->senderName)
		{
			$globalSettings = Booki_Helper::globalSettings();
			$userId = $globalSettings->adminUserId > 0 ? $globalSettings->adminUserId : 1;
			$userMeta = get_user_meta($userId);
			$firstName = $userMeta['first_name'][0];
			$lastName = $userMeta['last_name'][0];
			if($firstName)
			{
				$this->senderName = $firstName;
				if($lastName)
				{
					$this->senderName .= ' ' . $lastName;
				}
			}
		}
		if(!$this->senderEmail)
		{
			$this->senderEmail = get_site_option( 'admin_email' );
		}
	}
	public function toArray(){
		return array(
			'content'=>$this->content
			, 'senderName'=>$this->senderName
			, 'senderEmail'=>$this->senderEmail
			, 'subject'=>$this->subject
			, 'enable'=>$this->enable
			, 'id'=>$this->id
		);
	}
	
	protected function init(){
		$this->label = Booki_WPMLHelper::t('email_template_' . $this->templateName, $this->content);
	}
	
	public function updateResources(){
		$this->registerWPML();
	}
	
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('email_template_' . $this->templateName, $this->content);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('email_template_' . $this->templateName);
	}
}
?>