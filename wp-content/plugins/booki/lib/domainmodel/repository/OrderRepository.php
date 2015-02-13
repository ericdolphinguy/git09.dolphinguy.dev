<?php
require_once  dirname(__FILE__) . '/../entities/Order.php';
require_once  dirname(__FILE__) . '/../entities/Orders.php';
require_once  dirname(__FILE__) . '/../entities/User.php';
require_once  dirname(__FILE__) . '/../entities/PaymentStatus.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';

class Booki_OrderRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $user_table_name;
	private $usermeta_table_name;
	private $order_table_name;
	private $project_table_name;
	private $order_days_table_name;
	private $order_form_elements_table_name;
	private $order_optionals_table_name;
	private $order_cascading_item_table_name;
	private $dateFormat;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->user_table_name =  $wpdb->users;
		$this->usermeta_table_name =  $wpdb->usermeta;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
		$this->dateFormat = get_option('date_format');
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->order_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	
	public function readAllCSV($pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', 
							$fromDate = null, $toDate = null, $userId = null, $status = null){
		
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'desc';
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
					SELECT u.user_login as username, u.user_email as email
							, (SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'first_name' AND user_id = o.userId limit 1) as firstname
							, (SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'last_name' AND user_id = o.userId limit 1) as lastname
							, (SELECT COUNT(*) FROM $this->order_table_name WHERE userId = u.ID) as bookingsCount
							, o.id, o.userId, o.orderDate, o.status, o.token, o.transactionId, o.note, o.totalAmount, o.currency
							, o.invoiceNotification, o.refundNotification, o.refundAmount, o.paymentDate, o.timezone
							, o.discount, o.tax, COALESCE(o.isRegistered, 1) as isRegistered,
					(SELECT value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND capability = 3 limit 1) as notRegUserFirstname,
					(SELECT value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND capability = 4 limit 1) as notRegUserLastname,
					(SELECT value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND (capability = 1 OR capability = 2) limit 1) as notRegUserEmail,
					(SELECT GROUP_CONCAT(DISTINCT p.name SEPARATOR ',') FROM $this->order_days_table_name as od INNER JOIN  $this->project_table_name as p ON od.projectId = p.id WHERE od.orderId = 185 GROUP BY od.orderId) as projectNames,
					(SELECT CONCAT(GROUP_CONCAT(date(bookingDate) SEPARATOR ',') , ' ', COALESCE(CONCAT('hhmm: [', GROUP_CONCAT(CONCAT(LPAD(od.hourStart, 2, '0'), ':', LPAD(od.minuteStart, 2, '0'), ' - ', LPAD(od.hourEnd, 2, '0'), ':', LPAD(od.minuteEnd, 2, '0'))  SEPARATOR ','), ']'), '')) FROM $this->order_days_table_name as od WHERE od.orderId = o.id GROUP BY od.orderId) as bookingDates,
					(SELECT GROUP_CONCAT(op.name SEPARATOR ',') FROM $this->order_optionals_table_name as op WHERE op.orderId = o.id GROUP BY op.orderId) as optionals,
					(SELECT GROUP_CONCAT(oc.value SEPARATOR ',') FROM $this->order_cascading_item_table_name as oc WHERE oc.orderId = o.id GROUP BY oc.orderId) as cascadingItems
					FROM $this->order_table_name as o 
					LEFT JOIN $this->user_table_name as u
					ON o.userId = u.ID";
					
		$where = array();

		if($fromDate !== null && $toDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			$toDate = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			array_push($where, 'o.userId = %3$d');
		}
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		
		$result = $this->wpdb->get_results( sprintf($query, $fromDate, $toDate, $userId, $status ) );
		$total = 0;
		if( is_array($result) ){
			$orders = new Booki_Orders();
			foreach($result as $r){
				$orders->total = (int)$r->total;
				$order = new Booki_Order((array)$r);
				
				$order->user = new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->userId
				);
				$orders->add($order);
			}
			return $orders;
		}
		return false;
	}
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'asc', 
							$fromDate = null, $toDate = null, $userId = null, $status = null){

		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'asc';
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
					SELECT
					u.user_login as username, u.user_email as email,
					o.id, o.userId, o.orderDate, o.status, o.token, o.transactionId, o.note, o.totalAmount, o.currency, 
					o.invoiceNotification, o.refundNotification, o.refundAmount, o.paymentDate, o.timezone, 
					o.discount, o.tax, COALESCE(o.isRegistered, 1) as isRegistered,
					(SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'first_name' AND user_id = o.userId limit 1) as firstname,
					(SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'last_name' AND user_id = o.userId limit 1) as lastname,
					(SELECT COUNT(*) FROM $this->order_table_name WHERE userId = u.ID) as bookingsCount,
					(SELECT COUNT(id) FROM $this->order_days_table_name WHERE orderId = o.id AND status = 0) as hasDaysPendingApproval,
					(SELECT COUNT(id) FROM $this->order_optionals_table_name WHERE orderId = o.id AND status = 0) as hasOptionalsPendingApproval,
					(SELECT COUNT(id) FROM $this->order_cascading_item_table_name WHERE orderId = o.id AND status = 0) as hasCascadingItemsPendingApproval,
					(SELECT COUNT(id) FROM $this->order_days_table_name WHERE orderId = o.id AND status = 4) as hasDaysPendingCancellation,
					(SELECT COUNT(id) FROM $this->order_optionals_table_name WHERE orderId = o.id AND status = 4) as hasOptionalsPendingCancellation,
					(SELECT COUNT(id) FROM $this->order_cascading_item_table_name WHERE orderId = o.id AND status = 4) as hasCascadingItemsPendingCancellation,
					(SELECT ofe.value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND capability = 3 limit 1) as notRegUserFirstname,
					(SELECT ofe.value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND capability = 4 limit 1) as notRegUserLastname,
					(SELECT ofe.value FROM $this->order_form_elements_table_name as ofe WHERE ofe.orderId = o.id AND (capability = 1 OR capability = 2) limit 1) as notRegUserEmail
					FROM $this->order_table_name as o
					LEFT JOIN $this->user_table_name as u
					ON o.userId = u.ID";
					
		$where = array();

		if($fromDate !== null && $toDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			$toDate = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($userId !== null){
			array_push($where, 'o.userId = %3$d');
		}
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		$result = $this->wpdb->get_results( sprintf($query, $fromDate, $toDate, $userId, $status ) );
		$total = 0;
		if( is_array($result) ){
			$orders = new Booki_Orders();
			foreach($result as $r){
				$orders->total = (int)$r->total;
				$order = new Booki_Order((array)$r);
				
				$order->user = new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->userId
				);
				$orders->add($order);
			}
			return $orders;
		}
		return false;
	}
	
	public function readAllApprovedByHandlerUser($handlerUserId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'asc', 
							$fromDate = null, $toDate = null, $status = null){
		
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'orderDate';
		}
		if($order === null){
			$order = 'asc';
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
					SELECT o.id, o.userId, o.orderDate, o.status, o.token, o.transactionId, o.note, o.totalAmount, o.currency, 
							o.invoiceNotification, o.refundNotification, o.refundAmount, o.paymentDate, o.timezone, o.discount, o.tax, COALESCE(o.isRegistered, 1) as isRegistered
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId";
					
		$where = array();

		if($fromDate !== null && $toDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			$toDate = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		else if($fromDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate = CONVERT( \'%1$s\', DATETIME)');
		}
		
		if($handlerUserId !== null){
			array_push($where, 'od.handlerUserId = %3$d OR op.handlerUserId = %3$d');
		}
		
		if($status !== null){
			array_push($where, 'o.status = %4$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		$query .= ' ORDER BY o.' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		
		$result = $this->wpdb->get_results( sprintf($query, $fromDate, $toDate, $handlerUserId, $status ) );
		$total = 0;
		if( is_array($result) ){
			$orders = new Booki_Orders();
			foreach($result as $r){
				$orders->total = (int)$r->total;
				$orders->add(new Booki_Order((array)$r));
			}
			return $orders;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id, userId, orderDate, status, token, transactionId, note, totalAmount, currency, 
							invoiceNotification, refundNotification, refundAmount, paymentDate, timezone, discount, tax, COALESCE(isRegistered, 1) as isRegistered
					FROM $this->order_table_name
					WHERE id = %d";
													
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_Order((array)$r);
		}
		return false;
	}
	
	public function insert($order){
		$params = array(
			'orderDate'=>$order->orderDate->format(BOOKI_DATEFORMAT)
			, 'status'=>$order->status
			, 'userId'=>$order->userId
			, 'token'=>$order->token
			, 'transactionId'=>$order->transactionId
			, 'note'=>$order->note
			, 'totalAmount'=>$order->totalAmount
			, 'currency'=>$order->currency
			, 'discount'=>$order->discount
			, 'tax'=>$order->tax
			, 'invoiceNotification'=>$order->invoiceNotification
			, 'refundNotification'=>$order->refundNotification
			, 'refundAmount'=>$order->refundAmount
			, 'timezone'=>$order->timezone
			, 'isRegistered'=>$order->userIsRegistered
		);
		
		$placeHolders = array(
			'%s'/*orderDate*/
			, '%d'/*status*/
			, '%d'/*userId*/
			, '%s'/*token*/
			, '%s'/*transactionId*/
			, '%s'/*note*/
			, '%f'/*totalAmount*/
			, '%s'/*currency*/
			, '%f'/*discount*/
			, '%f'/*tax*/
			, '%d'/*invoiceNotification*/
			, '%d'/*refundNotification*/
			, '%f'/*refundAmount*/
			, '%s'/*timezone*/
			, '%d' /*isRegistered*/
		);
		
		if($order->paymentDate){
			$params['paymentDate'] = $order->paymentDate->format(BOOKI_DATEFORMAT);
			array_push($placeHolders, '%s');
		}
		
		$result = $this->wpdb->insert($this->order_table_name,  $params, $placeHolders);
		
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return false;
	}

	public function update($order){
		$params = array(
			'status'=>$order->status
			, 'userId'=>$order->userId
			, 'token'=>$order->token
			, 'transactionId'=>$order->transactionId
			, 'note'=>$order->note
			, 'totalAmount'=>$order->totalAmount
			, 'currency'=>$order->currency
			, 'discount'=>$order->discount
			, 'tax'=>$order->tax
			, 'invoiceNotification'=>$order->invoiceNotification
			, 'refundNotification'=>$order->refundNotification
			, 'refundAmount'=>$order->refundAmount
			, 'timezone'=>$order->timezone
			, 'isRegistered'=>$order->userIsRegistered
		);
		
		$placeHolders = array(
			'%d'/*status*/
			, '%d'/*userId*/
			, '%s'/*token*/
			, '%s'/*transactionId*/
			, '%s'/*note*/
			, '%f'/*totalAmount*/
			, '%s'/*currency*/
			, '%f'/*discount*/
			, '%f'/*tax*/
			, '%d'/*invoiceNotification*/
			, '%d'/*refundNotification*/
			, '%f'/*refundAmount*/
			, '%s'/*timezone*/
			, '%d'/*isRegistered*/
		);
		
		if($order->paymentDate){
			$params['paymentDate'] = $order->paymentDate->format(BOOKI_DATEFORMAT);
			array_push($placeHolders, '%s');
		}
		return $this->wpdb->update($this->order_table_name,  $params , array('id'=>$order->id) , $placeHolders);
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_table_name,  array(
			'status'=>$status
		  ), array('id'=>$orderId), array('%d'));
		 return $result;
	}
	/**
		@description fromDate has to be a formatted date string BOOKI_DATEFORMAT.
	*/
	public function deleteExpired($fromDate, $status = Booki_PaymentStatus::UNPAID){
		$sql = "DELETE FROM $this->order_table_name
					WHERE (orderDate < CONVERT( '$fromDate', DATETIME))
					AND status = $status;";
		
		$this->wpdb->query($sql);
		
		$this->wpdb->query("DELETE od.* FROM $this->order_days_table_name as od 
				LEFT JOIN $this->order_table_name as o
				ON o.id = od.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE fe.* FROM $this->order_form_elements_table_name as fe 
				LEFT JOIN $this->order_table_name as o
				ON o.id = fe.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE op.* FROM $this->order_optionals_table_name as op 
				LEFT JOIN $this->order_table_name as o
				ON o.id = op.orderId WHERE o.id IS NULL");
		$this->wpdb->query("DELETE oci.* FROM $this->order_cascading_item_table_name as oci 
				LEFT JOIN $this->order_table_name as o
				ON o.id = oci.orderId WHERE o.id IS NULL");
	}
	
	public function delete($id){
		//myisam has no delete cascades. manual labor of love.
		$sql = "DELETE FROM $this->order_days_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_form_elements_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_optionals_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_cascading_item_table_name WHERE orderId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		$sql = "DELETE FROM $this->order_table_name WHERE id = %d";
		return	$this->wpdb->query($this->wpdb->prepare($sql, $id));
	}
	
}
?>