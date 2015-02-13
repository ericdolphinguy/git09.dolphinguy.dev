<?php
require_once dirname(__FILE__) . '/../../domainmodel/entities/EmailType.php';
require_once  dirname(__FILE__) . '/base/Emailer.php';
class Booki_CouponEmailer extends Booki_Emailer{
	private $userId;
	private $couponCode;
	public function __construct($userId, $couponCode){
		$this->userId = $userId;
		$this->couponCode = $couponCode;
		parent::__construct(Booki_EmailType::COUPON);
	}
	
	public function send(){
		if(!$this->userId || !$this->couponCode){
			return false;
		}
		$emailSettings = $this->emailSettings;
		if($emailSettings && $emailSettings->enable){
			$from = $emailSettings->senderEmail;
			$senderName = $emailSettings->senderName;
			$userInfo = Booki_Helper::getUserInfo($this->userId);
			$content = $this->getEmailBody($userInfo['name']);
			$to = $userInfo['email'];
			$subject = $emailSettings->subject;
			if(!$subject){
				$subject = __($this->emailType, 'booki');
			}
			
			add_filter( 'wp_mail_content_type', array($this, 'setHtmlContentType'));
			add_filter('wp_mail_from', array($this, 'getSenderAddress'));
			add_filter('wp_mail_from_name',array($this, 'getSenderName'));
			
			//workaround for thirdparty email plugins that love to use nl2br
			$content = str_replace(array("\r\n", "\n\r", "\n", "\r"), "", $content);
			$result = wp_mail( $to, $subject, $content);
			
			remove_filter('wp_mail_from', array($this, 'getSenderAddress'));
			remove_filter('wp_mail_from_name',array($this, 'getSenderName'));
			remove_filter( 'wp_mail_content_type', array($this, 'setHtmlContentType'));
				
			parent::logErrorIfAny($result);
			return array('result'=>$result, 'emailedTo'=>$to);
		}
		return false;
	}
	
	public function getSenderAddress(){
		return $this->emailSettings->senderEmail;
	}
	
	public function getSenderName(){
		return  $this->emailSettings->senderName;
	}
	/**
		%customerName% : Replaced with name of customer, if it exists in their profile
		%siteName% : Replaced with the "Site Title" set in Settings > General.
		%code% : Replaced with the coupon code. This token is only valid on a "coupon" template.
	*/
	protected function getEmailBody($customerName){
		$emailSettings = $this->emailSettings;
		$content = $emailSettings->content;
		if(!$content){
			$content = Booki_Helper::readEmailTemplate($this->emailType);
		}
		if(!$content){
			return;
		}
		$content = str_replace("%customerName%", $customerName, $content);
		$content = str_replace("%siteName%", get_bloginfo(), $content);
		$content = str_replace("%code%", $this->couponCode, $content);
		
		return $content;
	}
	
	public function setHtmlContentType() {
		return 'text/html';
	}
}
?>