<?php 
	require_once  dirname(__FILE__) . '/../emails/NotificationEmailer.php';
	require_once  dirname(__FILE__) . '/../utils/Helper.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/entities/EmailType.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/repository/OrderRepository.php';
	class Booki_InvoiceHandler
	{
		public function __construct(){
			$orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
			$globalSettings = Booki_Helper::globalSettings();
			
			$userId = get_current_user_id();
			 if(!is_user_logged_in() && $globalSettings->membershipRequired){
				auth_redirect();
			}
			if($orderId !== null){
				$repo = new Booki_OrderRepository();
				$order = $repo->read($orderId);
				
				if($order && !$globalSettings->membershipRequired || ($order && ($order->userId === 0 || ($userId === $order->userId || Booki_Helper::userHasRole('administrator'))))){
					$notificationEmailer = new Booki_NotificationEmailer(Booki_EmailType::INVOICE, $orderId);
					$notificationEmailer->generateInvoice();
				}
			}
		}
	}
?>