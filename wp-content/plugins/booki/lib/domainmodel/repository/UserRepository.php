<?php
require_once  dirname(__FILE__) . '/../entities/User.php';
require_once  dirname(__FILE__) . '/../entities/Users.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
require_once 'OrderRepository.php';
require_once 'BookedDaysRepository.php';
require_once 'BookedFormElementsRepository.php';
require_once 'BookedOptionalsRepository.php';
require_once 'BookedCascadingItemsRepository.php';
class Booki_UserRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $user_table_name;
	private $usermeta_table_name;
	private $order_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->user_table_name =  $wpdb->users;
		$this->usermeta_table_name =  $wpdb->usermeta;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
	}
	
	public function count(){
		$sql = "SELECT COUNT(DISTINCT o.userId) as count FROM $this->order_table_name as o INNER JOIN $this->user_table_name as u ON o.userId = u.ID";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'orderDate', $order = 'asc', $fromDate = null, $toDate = null, $userId = null){

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
					SELECT DISTINCT u.ID as id, u.user_login as username, u.user_email as email
					, (SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'first_name' AND user_id = o.userId limit 1) as firstname
					, (SELECT meta_value FROM $this->usermeta_table_name WHERE meta_key = 'last_name' AND user_id = o.userId limit 1) as lastname
					, (SELECT COUNT(*) FROM $this->order_table_name WHERE userId = u.ID) as bookingsCount
					FROM $this->order_table_name as o INNER JOIN $this->user_table_name as u 
					ON o.userId = u.ID";
		
		$where = array();
		
		if($fromDate !== null && $toDate !== null){
			$fromDate = $fromDate->format(BOOKI_DATEFORMAT);
			$toDate = $toDate->format(BOOKI_DATEFORMAT);
			array_push($where, 'o.orderDate BETWEEN CONVERT( \'%1$s\', DATETIME) AND CONVERT( \'%2$s\', DATETIME)');
		}
		
		if($userId !== null){
			array_push($where, 'o.userId = %3$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		$result = $this->wpdb->get_results( sprintf($query, $fromDate, $toDate, $userId ) );
		$total = 0;
		if( is_array($result) ){
			$users = new Booki_Users();
			foreach($result as $r){
				$users->total = (int)$r->total;
				$users->add(new Booki_User(
					(string)$r->username
					, (string)$r->email
					, (string)$r->firstname
					, (string)$r->lastname
					, (int)$r->bookingsCount
					, (int)$r->id
				));
			}
			return $users;
		}
		return false;
	}
	
	public function delete($id){
		$bookedDaysRepo = new Booki_BookedDaysRepository();
		$bookedFormElementsRepository = new Booki_BookedFormElementsRepository();
		$bookedOptionalsRepository = new Booki_BookedOptionalsRepository();
		$bookedCascadingItemsRepository = new Booki_BookedCascadingItemsRepository();
		$result = $this->wpdb->get_results( $this->wpdb->prepare("SELECT id FROM $this->order_table_name WHERE userId = %d", $id) );
		if( is_array($result) ){
			foreach($result as $r){
				$bookedDaysRepo->deleteByOrderId($r->id);
				$bookedFormElementsRepository->deleteByOrderId($r->id);
				$bookedOptionalsRepository->deleteByOrderId($r->id);
				$bookedCascadingItemsRepository->deleteByOrderId($r->id);
				$this->wpdb->query( "DELETE FROM $this->order_table_name WHERE id = $r->id;");
			}
		}
		return is_array($result) ? count($result) : false;
	}
}
?>