<?php
require_once  dirname(__FILE__) . '/../entities/Coupons.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_CouponRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $coupons_table_name;
	private $project_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->coupons_table_name = $wpdb->prefix . 'booki_coupons';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->coupons_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc'){
		if($pageIndex === null){
			$pageIndex = -1;
		}
		if($limit === null){
			$limit = 5;
		}
		if($orderBy === null){
			$orderBy = 'id';
		}
		if($order === null){
			$order = 'asc';
		}
		
		$where = array();
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
				SELECT c.id, c.projectId, c.code, c.discount, c.orderMinimum, c.expirationDate, c.emailedTo, c.couponType, p.name as projectName
				FROM $this->coupons_table_name as c
				LEFT JOIN $this->project_table_name as p
				ON p.id = c.projectId
				ORDER BY $orderBy $order) result, (SELECT FOUND_ROWS() AS total) total";
		
		if($pageIndex > -1){
			$query .= ' LIMIT %d, %d;';
		}
		
		$result = $this->wpdb->get_results( sprintf($query, $pageIndex, $limit ) );
		$total = 0;
		if ( is_array($result) ){
			$coupons = new Booki_Coupons();
			foreach($result as $r){
				$coupons->total = (int)$r->total;
				$coupons->add(new Booki_Coupon((array)$r));
			}
			return $coupons;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id, projectId, code, discount, orderMinimum, expirationDate, emailedTo, couponType FROM $this->coupons_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_Coupon((array)$r);
		}
		return false;
	}
	
	public function find($code){
		$sql = "SELECT id, projectId, code, discount, orderMinimum, expirationDate, emailedTo, couponType FROM $this->coupons_table_name WHERE code = %s";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $code) );
		if( $result ){
			$r = $result[0];
			return new Booki_Coupon((array)$r);
		}
		return false;
	}
	
	public function insert($coupon){
		 $result = $this->wpdb->insert($this->coupons_table_name,  array(
			'code'=>$coupon->code ? $coupon->code : sha1(uniqid(mt_rand(), true))
			, 'discount'=>$coupon->discount
			, 'orderMinimum'=>$coupon->orderMinimum
			, 'expirationDate'=>$coupon->expirationDate->format(BOOKI_DATEFORMAT)
			, 'emailedTo'=>$coupon->emailedTo
			, 'projectId'=>$coupon->projectId
			, 'couponType'=>$coupon->couponType
		  ), array('%s', '%f', '%f', '%s', '%s', '%d', '%d'));
		  
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function insertMany($coupon, $count){
		if($count < 0){
			return false;
		}
		$values = array();
		$sql = "INSERT INTO $this->coupons_table_name (code, projectId, discount, orderMinimum, expirationDate, couponType) VALUES ";
		for($i = 0; $i < $count; $i++){
			$value = "(%s, %d, %f, %f, %s, %d)";
			$code = sha1(uniqid(mt_rand(), true));
			array_push($values, $this->wpdb->prepare($value
				, $code
				, $coupon->projectId
				, $coupon->discount
				, $coupon->orderMinimum
				, $coupon->expirationDate->format(BOOKI_DATEFORMAT)
				, Booki_CouponType::REGULAR
			));
		}
		
		$sql .= (implode(', ', $values) . ';');
		return $this->wpdb->query($sql);
	}
	
	public function insertList($list, $errors = array()){
		if(!$list || $list->count() === 0){
			return false;
		}
		$values = array();
		$sql = "INSERT INTO $this->coupons_table_name (code, projectId, discount, orderMinimum, expirationDate, emailedTo, couponType) VALUES ";
		
		$errorList = array();
		
		if(count($errors) > 0){
			foreach($errors as $error){
				array_push($errorList, $error['email']['email']);
			}
		}
		
		foreach($list as $coupon){
			if(in_array($coupon->emailedTo, $errorList)){
				continue;
			}
			$value = "(%s, %d, %f, %f, %s, %s, %d)";
			array_push($values, $this->wpdb->prepare($value
				, $coupon->code
				, $coupon->projectId
				, $coupon->discount
				, $coupon->orderMinimum
				, $coupon->expirationDate->format(BOOKI_DATEFORMAT)
				, $coupon->emailedTo
				, $coupon->couponType
			));
		}
		
		$sql .= (implode(', ', $values) . ';');
		return $this->wpdb->query($sql);
	}
	
	public function update($coupon){
		$result = $this->wpdb->update($this->coupons_table_name,  array(
			'discount'=>$coupon->discount
			, 'orderMinimum'=>$coupon->orderMinimum
			, 'expirationDate'=>$coupon->expirationDate->format(BOOKI_DATEFORMAT)
			, 'emailedTo'=>$coupon->emailedTo
			, 'projectId'=>$coupon->projectId
		), array('id'=>$coupon->id), array('%f', '%f', '%s', '%s', '%d'));
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->coupons_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
}
?>