<?php
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_StatsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_days_table_name;
	private $order_optionals_table_name;
	private $dateFormat;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->dateFormat = get_option('date_format');
	}

	public function readOrdersMadeAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
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
				SELECT COUNT(*) as count, o.orderDate
				FROM $this->order_table_name as o
				LEFT OUTER JOIN $this->order_days_table_name as od
				ON o.id = od.orderId
				LEFT OUTER JOIN $this->order_optionals_table_name as op
				on o.id = op.orderId
				WHERE o.orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)";
		
		if($userId){
			$query .= " AND od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		return $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) ); 
	}
	
	public function readOrdersTotalAmountAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
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
					SELECT SUM(COALESCE(od.cost, 0) + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count )) - SUM(o.refundAmount) as totalAmount, SUM(o.discount/100*od.cost + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count )) as discount, o.orderDate
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId
					WHERE orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)";
					
		if($userId){
			$query .= " AND od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}
		
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		return $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) );
	}
	
	public function readOrdersRefundAmountAggregate($userId, $pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'desc', $period = 3){
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
					SELECT COUNT(*) as count, SUM(o.refundAmount) as refundTotal, o.orderDate
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId
					WHERE o.orderDate > DATE_SUB(NOW(), INTERVAL %d MONTH)
					AND o.refundAmount > 0";
		if($userId){
			$query .= " AND od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}
		$query .= ' GROUP BY o.orderDate ORDER BY o.' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		return $this->wpdb->get_results( $this->wpdb->prepare($query, $period ) );
	}
	
	public function readOrdersByStatus($userId){
		$query = "SELECT COUNT(*) as count, o.status
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId";
		
		if($userId){
			$query .= " WHERE od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}
		
		$query .= " GROUP BY o.status";
		return $this->wpdb->get_results( $query );
	}
	
	public function summary($userId){
		$query = "SELECT COUNT(*) as count,
					SUM(o.discount/100*(COALESCE(od.cost, 0) + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count ))) as discount
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId";
		
		if($userId){
			$query .= " WHERE od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}

		$result = $this->wpdb->get_results( $query );
		if( $result ){
			return $result[0];
		}
		return $result;
	}
	
	public function readTotalAmountEarned($userId){
		$query = "SELECT SUM(COALESCE(od.cost, 0) + Greatest(COALESCE(op.cost, 0), COALESCE(op.cost, 0) * op.count )) - SUM(o.refundAmount) as totalAmount
					FROM $this->order_table_name as o
					LEFT OUTER JOIN $this->order_days_table_name as od
					ON o.id = od.orderId
					LEFT OUTER JOIN $this->order_optionals_table_name as op
					on o.id = op.orderId
					WHERE o.status = 1";
		
		if($userId){
			$query .= " AND od.handlerUserId = $userId OR op.handlerUserId = $userId";
		}
		
		$result = $this->wpdb->get_results( $query );
		if( $result ){
			return (int)$result[0]->totalAmount;
		}
		return $result;
	}
}
?>