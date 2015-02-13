<?php
	require_once dirname(__FILE__) . '/base/BaseController.php';
	require_once dirname(__FILE__) . '/utils/BookingFormParser.php';
	require_once dirname(__FILE__) . '/../infrastructure/session/OrderLogger.php';
	require_once dirname(__FILE__) . '/../infrastructure/session/Bookings.php';
	require_once dirname(__FILE__) . '/../infrastructure/emails/NotificationEmailer.php';
	require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
	
class Booki_CreateBookingController extends Booki_BaseController{
	private $projectId;
	private $bookings;
	private $userEmail;
	public function __construct(){
		$callback = null;
		$numArgs = func_num_args();
		if(BOOKI_RESTRICTED_MODE){
			return;
		}
		if($numArgs > 0){
			$callback = func_get_arg(0);
		}
		$this->projectId = isset($_POST['projectid']) ? (int)$_POST['projectid'] : -1;
		if (array_key_exists('booki_add_new_booking', $_POST)){
			$this->addNewBooking($callback);
		}
	}
	
	public function addNewBooking($callback){
		if($this->projectId === -1 || $this->projectId === null){
			return;
		}
		$bookings = new Booki_Bookings();
		$errors = array();
		$userEmail = $_POST['useremail'];
		if(!filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
			array_push($errors, __('Email is invalid', 'booki'));
		}else{
			$result = Booki_BookingFormParser::populateBookingFromPostData($this->projectId, $bookings);
			$bookings = $result['bookings'];
			$errors = $result['errors'];
			if(count($errors) === 0){
				$this->log($bookings, $userEmail);
			}
		}
		$this->executeCallback($callback, array($this->projectId, $errors));
	}
	
	protected function log($bookings, $userEmail){
		$result = Booki_Helper::createUserIfNotExists($userEmail);
		$userId = $result['userId'];
		$orderLogger = new Booki_OrderLogger($bookings, $userId);
		$order = $orderLogger->log();
		if($order !== false){
			$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::INVOICE, $order->id);
			$result = $notificationEmailer->send();
			if($result){
				++$order->invoiceNotification;
				Booki_BookingProvider::orderRepository()->update($order);
			}
		}
	}
}
?>