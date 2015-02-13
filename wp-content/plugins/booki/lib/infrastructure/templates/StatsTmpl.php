<?php
require_once  dirname(__FILE__) . '/../../domainmodel/repository/StatsRepository.php';
require_once  dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../../infrastructure/ui/lists/OrdersMadeAggregateList.php';
require_once  dirname(__FILE__) . '/../../infrastructure/ui/lists/OrdersRefundAmountAggregateList.php';
require_once  dirname(__FILE__) . '/../../infrastructure/ui/lists/OrdersTotalAmountAggregateList.php';
require_once  dirname(__FILE__) . '/../../infrastructure/ui/lists/EditorApprovedOrderList.php';
require_once  dirname(__FILE__) . '/../../controller/BookedDayController.php';
require_once  dirname(__FILE__) . '/../../controller/BookedOptionalController.php';
require_once  dirname(__FILE__) . '/../../controller/ManageBookingsController.php';
require_once  dirname(__FILE__) . '/../../infrastructure/ui/OrderDetails.php';
class Booki_StatsTmpl{
	public $ordersMadeAggregateList;
	public $ordersRefundAmountAggregateList;
	public $ordersTotalAmountAggregateList;
	public $singleOrderDetails;
	public $donut = array('0'=>0, '1'=>0, '2'=>0);
	public $summary;
	public $totalAmountEarned;
	public $localInfo;
	public $orderId;
	public $orderList = null;
	public $isAdmin;
	public function __construct(){
		if(!isset($GLOBALS['hook_suffix'])){
			$GLOBALS['hook_suffix'] = '';
		}
		$this->localInfo = Booki_Helper::getLocaleInfo();
		$this->ordersMadeAggregateList = new Booki_OrdersMadeAggregateList();
		$this->ordersRefundAmountAggregateList = new Booki_OrdersRefundAmountAggregateList();
		$this->ordersTotalAmountAggregateList = new Booki_OrdersTotalAmountAggregateList();
		
		$this->orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		
		$this->ordersMadeAggregateList->bind();
		$this->ordersRefundAmountAggregateList->bind();
		$this->ordersTotalAmountAggregateList->bind();
		
		$handlerUserId = null;
		$this->isAdmin = Booki_Helper::hasAdministratorPermission();
		if(!$this->isAdmin){
			$handlerUserId = get_current_user_id();
			$this->orderList = new Booki_EditorApprovedOrderList($handlerUserId);
			
			new Booki_BookedDayController();
			new Booki_BookedOptionalController();
		
			new Booki_ManageBookingsController(
				null
				, null
				, null
				, null
				, array($this, 'invoiceNotification')
				, array($this, 'refundNotification')
				, null
				, null
				, null
				, $this->orderList->perPage
			);
			$this->orderList->bind();
			
			$this->singleOrderDetails = new Booki_OrderDetails($this->orderId);
			
			add_filter( 'booki_single_order_details', array($this, 'getSingleOrderDetails'));
			add_filter( 'booki_booked_form_elements', array($this, 'getBookedFormElements'));
		}
		$repo = new Booki_StatsRepository();
		$donut = $repo->readOrdersByStatus($handlerUserId);
		
		foreach($donut as $d){
			$this->donut["$d->status"] = $d->count;
		}

		$this->summary = $repo->summary($handlerUserId);
		$this->totalAmountEarned = $repo->readTotalAmountEarned($handlerUserId);
	}
	public function invoiceNotification(){}
	public function refundNotification(){}
	function getSingleOrderDetails(){
		return $this->singleOrderDetails;
	}
	
	function getBookedFormElements(){
		return $this->singleOrderDetails->bookedFormElements;
	}
}
?>