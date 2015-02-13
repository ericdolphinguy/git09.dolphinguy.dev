<?php
require_once  dirname(__FILE__) . '/../entities/BookedOptional.php';
require_once  dirname(__FILE__) . '/../entities/BookedOptionals.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_BookedOptionalsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_optionals_table_name;
	private $project_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}
	
	public function read($id){
		$sql = "SELECT oop.id, oop.orderId, oop.projectId, oop.name, oop.cost, oop.deposit,
				oop.status, oop.handlerUserId, p.notifyUserEmailList, p.name as projectName, oop.count
				FROM $this->order_optionals_table_name oop
				INNER JOIN $this->project_table_name as p
				ON oop.projectId = p.id
				WHERE oop.id = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedOptional(
				(int)$r->projectId
				, $this->decode((string)$r->name)
				, (double)$r->cost
				, (double)$r->deposit
				, (int)$r->status
				, (int)$r->orderId
				, (int)$r->handlerUserId
				, trim((string)$r->notifyUserEmailList)
				, (string)$r->projectName
				, (int)$r->count
				, (int)$r->id
			);
		}
		return false;
	}
	
	public function readByOrder($orderId){
		$sql = "SELECT oop.id, oop.orderId, oop.projectId, oop.name, oop.cost, oop.deposit,
				oop.status, oop.handlerUserId, p.notifyUserEmailList, p.name as projectName, oop.count
				FROM $this->order_optionals_table_name AS oop
				INNER JOIN $this->project_table_name as p
				ON oop.projectId = p.id
				WHERE oop.orderId = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql,  $orderId ));
		if ( is_array( $result) ){
			$optionals = new Booki_BookedOptionals();
			foreach($result as $r){
				$optionals->add(new Booki_BookedOptional(
					(int)$r->projectId
					, $this->decode((string)$r->name)
					, (double)$r->cost
					, (double)$r->deposit
					, (int)$r->status
					, (int)$r->orderId
					, (int)$r->handlerUserId
					, trim((string)$r->notifyUserEmailList)
					, (string)$r->projectName
					, (int)$r->count
					, (int)$r->id
				));
			}
			return $optionals;
		}
		return false;
	}
	
	public function insert($orderId, $bookedOptional){
		 $result = $this->wpdb->insert($this->order_optionals_table_name,  array(
			'projectId'=>$bookedOptional->projectId
			, 'name'=>$this->encode($bookedOptional->name)
			, 'cost'=>$bookedOptional->cost
			, 'status'=>$bookedOptional->status
			, 'orderId'=>$orderId
			, 'handlerUserId'=>$bookedOptional->handlerUserId
			, 'count'=>$bookedOptional->count
			, 'deposit'=>$bookedOptional->deposit
		  ), array('%d', '%s','%f', '%d', '%d', '%d', '%d', '%f'));
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($bookedOptional){
		 $result = $this->wpdb->update($this->order_optionals_table_name,  array(
			'name'=>$this->encode($bookedOptional->name)
			, 'cost'=>$bookedOptional->cost
			, 'status'=>$bookedOptional->status
			, 'handlerUserId'=>$bookedOptional->handlerUserId
			, 'count'=>$bookedOptional->count
			, 'deposit'=>$bookedOptional->deposit
		  ), array('id'=>$bookedOptional->id), array('%s','%f', '%d', '%d', '%d', '%f'));

		 return $result;
	}
	
	public function updateStatus($id, $status){
		 $result = $this->wpdb->update($this->order_optionals_table_name,  array(
			'status'=>$status
		  ), array('id'=>$id), array('%d'));

		 return $result;
	}
	
	public function updateCount($id, $count){
		 $result = $this->wpdb->update($this->order_optionals_table_name,  array(
			'count'=>$count
		  ), array('id'=>$id), array('%d'));

		 return $result;
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_optionals_table_name,  array(
			'status'=>$status
		  ), array('orderId'=>$orderId), array('%d'));
		 return $result;
	}
	
	public function setOwner($id, $userId){
		$result = $this->wpdb->update($this->order_optionals_table_name,  array(
			'handlerUserId'=>$userId
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function deleteByOrderId($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_optionals_table_name WHERE orderId = %d", $orderId) );
	}
	
	public function delete($id){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_optionals_table_name WHERE id = %d", $id) );
	}
	
	public function deleteByUserId($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE op.* FROM $this->order_optionals_table_name as op 
				LEFT JOIN $this->order_table_name as o
				ON o.id = op.orderId WHERE o.userId = %d", $userId));
	}
}
?>