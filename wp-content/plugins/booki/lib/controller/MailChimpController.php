<?php
require_once  dirname(__FILE__) . '/../infrastructure/emails/MailChimp.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/UserRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/CouponRepository.php';
class Booki_MailChimpController extends Booki_BaseController
{
	private $repo;
	private $mailChimp;
	private	$perPage;
	private $orderBy;
	private $order;
	private $globalSettings;
	
	public function __construct($readCallback, $refreshCallback, $exportCallback, 
									$perPage, $orderBy, $order){
		$this->repo = new Booki_UserRepository();
		$this->mailChimp = new Booki_MailChimp();
		$this->globalSettings = Booki_Helper::globalSettings();
		$this->perPage = $perPage;
		$this->orderBy = $orderBy;
		$this->order = $order;
		
		$this->read($readCallback);
		
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_mailchimp')){
			return;
		}

		if(array_key_exists('refresh', $_POST)){
			$this->refresh($refreshCallback);
		}else if(array_key_exists('export', $_POST)){
			$this->export($exportCallback);
		}
	}
	
	public function read($callback){
		$mailingList = $this->mailChimp->getCachedList();
		$this->executeCallback($callback, array($mailingList));
	}
	
	public function refresh($callback){
		$mailingList = $this->mailChimp->refreshList();
		$this->executeCallback($callback, array($mailingList));
	}
	
	public function export($callback){
		
		$userRepository = new Booki_UserRepository();
		
		$pageIndex = (int)$this->getPostValue('pageindex');
		$userId = isset($_GET['userid']) ? (int)$_GET['userid'] : null;
		$fromDate = isset($_GET['from']) ? new Booki_DateTime($_GET['from']) : null;
		$toDate = isset($_GET['to']) ? new Booki_DateTime($_GET['to']) : null;
		
		$mailChimpListId = $this->getPostValue('mailchimplist');
		$discount = (float)$this->getPostValue('discount');
		$orderMinimum = (float)$this->getPostValue('orderminimum');
		$expirationDate = new Booki_DateTime($this->getPostValue('expirationdate'));
		$projectId = (int)$this->getPostValue('projectId');
		
		$coupon = null;
		$result = null;
		if($mailChimpListId !== '-1'){
			if($discount > 0){
				$coupon = new Booki_Coupon(array(
					'discount'=>$discount
					, 'orderMinimum'=>$orderMinimum
					, 'expirationDate'=>$expirationDate->format(BOOKI_DATEFORMAT)
					, 'projectId'=>$projectId
				));
			}
			
			$users = $userRepository->readAll($pageIndex, $this->perPage, $this->orderBy, $this->order, $fromDate, $toDate, $userId);
			
			$result = $this->mergeVars($mailChimpListId);		
			$result = $this->batchSubscribe($mailChimpListId, $users, $coupon);
		}
		$this->executeCallback($callback, array($result));
	}
	
	protected function batchSubscribe($listId, $users, $couponParams){
		$couponRepository = new Booki_CouponRepository();
		$coupons = new Booki_Coupons();
		$batch = array();
		foreach($users as $user){
			$coupon = null;
			if($couponParams){
				$coupon = new Booki_Coupon(array(
					'discount'=>$couponParams->discount
					, 'orderMinimum'=>$couponParams->orderMinimum
					, 'expirationDate'=>$couponParams->expirationDate->format(BOOKI_DATEFORMAT)
					, 'projectId'=>$couponParams->projectId
					, 'code'=>sha1(uniqid(mt_rand(), true))
					, 'emailedTo'=>$user->email
				));
				$coupons->add($coupon);
			}
			//dateformat, can we get this from global settings instead ?
			array_push($batch, array( 'email'=>array('email'=>$user->email), 'email_type'=>'html', 'merge_vars'=>array(
				'ID'=>$user->id
				, 'UNAME'=>$user->username
				, 'FNAME'=>$user->firstname
				, 'LNAME'=>$user->lastname
				, 'CDISCOUNT'=>$coupon ? $coupon->discount : ''
				, 'CORDERMIN'=>$coupon ? $coupon->orderMinimum : ''
				, 'CEXPIRY'=>$coupon ? $coupon->expirationDate->format($this->globalSettings->getServerFormatShorthandDate()) : null
				, 'CCODE'=>$coupon ? $coupon->code : ''
			)));
		}
		$result = $this->mailChimp->batchSubscribe($listId, $batch);
		$couponRepository->insertList($coupons, $result['errors']);
		return $result;
	}
	
	protected function mergeVars($listId){
		$vars = array(
			array(
				'tag'=>'ID'
				, 'name'=>'User id'
				, 'options'=>array('field_type'=>'number', 'visible'=>false)
			)
			, array(
				'tag'=>'UNAME'
				, 'name'=>'User name'
				, 'options'=>array('visible'=>false)
			)
			, array(
				'tag'=>'FNAME'
				, 'name'=>'First name'
			)
			, array(
				'tag'=>'LNAME'
				, 'name'=>'Last name'
			)
			, array(
				'tag'=>'EMAIL'
				, 'name'=>'Email'
				, 'options'=>array('field_type'=>'email')
			)
			, array(
				'tag'=>'CDISCOUNT'
				, 'name'=>'Coupon discount'
				, 'options'=>array('field_type'=>'number', 'visible'=>false)
			)
			, array(
				'tag'=>'CORDERMIN'
				, 'name'=>'Coupon Order Minimum'
				, 'options'=>array('field_type'=>'number', 'visible'=>false)
			)
			, array(
				'tag'=>'CEXPIRY'
				, 'name'=>'Coupon Expiration date'
				, 'options'=>array('field_type'=>'date', 'dateformat'=>$this->globalSettings->shorthandDateFormat, 'visible'=>false)
			)
			, array(
				'tag'=>'CCODE'
				, 'name'=>'Coupon Code'
				, 'options'=>array('visible'=>false)
			)
		);
		
		return $this->mailChimp->mergeVarsAdd($listId, $vars);
	}
}
?>