<?php 
	require_once  dirname(__FILE__) . '/base/CSVBaseHandler.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/entities/PaymentStatus.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
	class Booki_BookingsCSVHandler extends Booki_CSVBaseHandler
	{
		public function __construct(){
			$pageIndex = isset($_GET['pageindex']) ? $_GET['pageindex'] : -1;
			$perPage = isset($_GET['perpage']) ? $_GET['perpage'] : null;
			$orderBy = isset($_GET['orderby']) ? $_GET['orderby'] : 'id';
			$order = isset($_GET['order']) ? $_GET['order'] : null;
			$result = Booki_BookingProvider::orderRepository()->readAllCSV($pageIndex, $perPage, $orderBy, $order);

			$columnNames = array(
				'id'
				, 'orderDate'
				, 'status'
				, 'totalAmount'
				, 'discount'
				, 'paymentDate'
				, 'timezone'
				, 'invoiceNotification'
				, 'refundNotification'
				, 'refundAmount'
				, 'userId'
				, 'username'
				, 'firstname'
				, 'lastname'
				, 'email'
				, 'bookingsCount'
				, 'projectNames'
				, 'bookingDates'
				, 'optionals'
				, 'cascadingItems'
			);
			
			$records = array();
			foreach($result as $order){
				if($order->status === Booki_PaymentStatus::UNPAID){
					$status = __('Un paid', 'booki');
				}else if($order->status === Booki_PaymentStatus::PAID){
					$status = __('Un paid', 'booki');
				}else{
					$status = __('Refunded', 'booki');
				}
				
				$firstname = $order->user->firstname;
				$lastname = $order->user->lastname;
				$email = $order->user->email;
				
				if($order->notRegUserFirstname){
					$firstname = $order->notRegUserFirstname;
				}
				if($order->notRegUserLastname){
					$lastname = $order->notRegUserLastname;
				}
				if($order->notRegUserEmail){
					$email = $order->notRegUserEmail;
				}
				array_push($records, implode(",", array(
					$this->encode($order->id)
					, $this->encode($order->orderDate->format('Y-m-d'))
					, $this->encode($status)
					, $this->encode($order->totalAmount)
					, $this->encode($order->discount) . '%'
					, $this->encode($order->paymentDate ? $order->paymentDate->format('Y-m-d') : null)
					, $this->encode($order->timezone)
					, $this->encode($order->invoiceNotification)
					, $this->encode($order->refundNotification)
					, $this->encode($order->refundAmount)
					, $this->encode($order->user->id)
					, $this->encode($order->user->username)
					, $this->encode($firstname)
					, $this->encode($lastname)
					, $this->encode($email)
					, $this->encode($order->user->bookingsCount)
					, $this->encode($order->csvFields['projectNames'])
					, '"' . $order->csvFields['bookingDates'] . '"'
					, $this->encode($order->csvFields['optionals'])
					, $this->encode($order->csvFields['cascadingItems'])
				)));
			}
			
			echo implode(",", $columnNames);
			echo "\n";
			echo implode("\n", $records);
		}
	}
?>
