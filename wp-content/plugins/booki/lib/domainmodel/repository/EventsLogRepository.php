<?php
require_once  dirname(__FILE__) . '/../entities/EventLog.php';
require_once  dirname(__FILE__) . '/../entities/EventsLog.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_EventsLogRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $events_log_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->events_log_table_name = $wpdb->prefix . 'booki_event_log';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->events_log_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id, entryDate, data
				FROM $this->events_log_table_name WHERE id = %d";
		$result = $this->wpdb->get_results($this->wpdb->prepare($sql, $id) );
		if($result){
			$r = $result[0];
			$data = unserialize($r->data);
			$entryDate = new Booki_DateTime((string)$r->entryDate);
			$id = (int)$r->id;
			return new Booki_EventLog($data, $entryDate, $id);
		}
		return false;
	}
	
	public function readAll($pageIndex = -1, $limit = 5, $orderBy = 'id', $order = 'asc'){
		if($pageIndex === null){
			$pageIndex = -1;
		}else{
			$pageIndex = intval($pageIndex);
		}
		if($limit === null){
			$limit = 5;
		}
		else{
			$limit = intval($limit);
		}

		if($orderBy === null || (strtolower($orderBy) != 'entrydate' && strtolower($orderBy) != 'id')){
			$orderBy = 'id';
		}

		if($order === null || (strtolower($order) != 'asc' && strtolower($order) != 'desc')){
			$order = 'asc';
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
					SELECT id, entryDate, data
				FROM $this->events_log_table_name";

		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}
		
		$result = $this->wpdb->get_results($query);
		$total = 0;
		if( is_array($result) ){
			$eventsLog = new Booki_EventsLog();
			foreach($result as $r){
				$eventsLog->total = (int)$r->total;
				$data = unserialize($r->data);
				$entryDate = new Booki_DateTime((string)$r->entryDate);
				$id = (int)$r->id;
			
				$eventsLog->add(new Booki_EventLog($data, $entryDate, $id));
			}
			return $eventsLog;
		}
		return false;
	}
	
	public function insert($eventLog){
		 $result = $this->wpdb->insert($this->events_log_table_name,  array(
			'data'=>serialize($eventLog->data)
			, 'entryDate'=>$eventLog->entryDate->format(BOOKI_DATEFORMAT)
		  ), array('%s', '%s'));

		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($eventLog){
		$result = $this->wpdb->update($this->events_log_table_name,  array(
			'data'=>serialize($eventLog->data)
		), array('id'=>$eventLog->id), array('%s'));
		
		return $result;
	}
	
	public function delete($id){
		$sql = "DELETE FROM $this->events_log_table_name WHERE id = %d";
		$rows_affected = $this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		return $rows_affected;
	}
	
	public function deleteAll(){
		$sql = "DELETE FROM $this->events_log_table_name";
		$rows_affected = $this->wpdb->query( $sql );
		return $rows_affected;
	}
	
	public function deleteExpired($fromDate){
		$sql = "DELETE FROM $this->events_log_table_name
					WHERE (entryDate < CONVERT( '$fromDate', DATETIME))";
		$rows_affected = $this->wpdb->query( $sql );
		return $rows_affected;
	}
}
?>