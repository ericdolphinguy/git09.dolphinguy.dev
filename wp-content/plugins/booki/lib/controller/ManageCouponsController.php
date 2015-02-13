<?php
require_once  dirname(__FILE__) . '/base/BaseController.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/CouponRepository.php';
require_once  dirname(__FILE__) . '/../infrastructure/emails/CouponEmailer.php';
class Booki_ManageCouponsController extends Booki_BaseController{
	private $orderId;
	private $couponRepository;
	public function __construct($createCallback, $updateCallback, $deleteCallback, $emailCallback){
		if (!(array_key_exists('controller', $_POST) 
			&& $_POST['controller'] == 'booki_managecoupons')){
			return;
		}
		$this->couponRepository = new Booki_CouponRepository();
		
		parent::__construct($createCallback, $updateCallback, $deleteCallback);
		
		if (array_key_exists('booki_email', $_POST)){
			$this->sendEmail($emailCallback);
		}
	}
	
	public function sendEmail($callback){
		$couponId = (int)$this->getPostValue('booki_email');
		$userId = (int)$this->getPostValue('userid');
		$coupon = $this->couponRepository->read($couponId);
		$result = false;
		if($coupon){
			$couponEmailer = new Booki_CouponEmailer($userId, $coupon->code);
			$status = $couponEmailer->send();
			if($status && $coupon->couponType === Booki_CouponType::REGULAR){
				$result = $status['result'];
				if($result){
					$coupon->emailedTo = $status['emailedTo'];
					$this->couponRepository->update($coupon);
				}
			}
		}
		$this->executeCallback($callback, array($result));
	}
	public function create($callback){
		$discount = (float)$this->getPostValue('discount');
		$orderMinimum = (float)$this->getPostValue('orderminimum');
		$expirationDate = new Booki_DateTime($this->getPostValue('expirationdate'));
		$couponsCount = (int)$this->getPostValue('couponscount');
		$projectId = (int)$this->getPostValue('projectId');
		$code = (string)$this->getPostValue('code');
		$couponType = (int)$this->getPostValue('coupontype');
		if($couponsCount > 1){
			$result = $this->couponRepository->insertMany(new Booki_Coupon(array(
				'discount'=>$discount
				, 'orderMinimum'=>$orderMinimum
				, 'expirationDate'=>$expirationDate->format(BOOKI_DATEFORMAT)
				, 'projectId'=>$projectId
			))
			, $couponsCount);
		}else{
			$coupon = $this->couponRepository->find($code);
			if($coupon){
				$this->executeCallback($callback, array(false));
				return;
			}
			$result = $this->couponRepository->insert(new Booki_Coupon(array(
				'discount'=>$discount
				, 'orderMinimum'=>$orderMinimum
				, 'expirationDate'=>$expirationDate->format(BOOKI_DATEFORMAT)
				, 'projectId'=>$projectId
				, 'code'=>$code
				, 'couponType'=>$couponType
			)));
		}
		$this->executeCallback($callback, array($result));
	}
	
	public function update($callback){
		$couponId = (int)$this->getPostValue('booki_update');
		$discount = (float)$this->getPostValue('discount');
		$orderMinimum = (float)$this->getPostValue('orderminimum');
		$expirationDate = new Booki_DateTime($this->getPostValue('expirationdate'));
		$projectId = (int)$this->getPostValue('projectId');
		$result = $this->couponRepository->update(new Booki_Coupon(array(
			'discount'=>$discount
			, 'orderMinimum'=>$orderMinimum
			, 'expirationDate'=>$expirationDate->format(BOOKI_DATEFORMAT)
			, 'projectId'=>$projectId
			, 'id'=>$couponId
		)));
		$this->executeCallback($callback, array($result));
	}
	
	public function delete($callback){
		$couponId = (int)$this->getPostValue('booki_delete');
		$result = $this->couponRepository->delete($couponId);
		$this->executeCallback($callback, array($result));
	}
}
?>