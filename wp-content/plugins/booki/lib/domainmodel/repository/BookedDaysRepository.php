<?php
require_once dirname(__FILE__) . '/../entities/BookedDay.php';
require_once dirname(__FILE__) . '/../entities/BookedDays.php';
require_once dirname(__FILE__) . '/../entities/BookingStatus.php';
require_once dirname(__FILE__) . '/../base/RepositoryBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/DateHelper.php';
class Booki_BookedDaysRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_days_table_name;
	private $project_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->project_table_name = $wpdb->prefix . 'booki_project';
	}
	
	public function readAgentToNotifyByOrderId($orderId){
		$sql = "SELECT DISTINCT p.id, p.notifyUserEmailList
				FROM $this->order_days_table_name as od
				INNER JOIN $this->project_table_name as p
				ON od.projectId = p.id
				WHERE orderId = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $orderId ) );
		if ( is_array( $result) ){
			$projectList = array();
			foreach($result as $r){
				array_push($projectList, array('id'=>(int)$r->id, 'notifyUserEmailList'=>(string)$r->notifyUserEmailList));
			}
			return $projectList;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT od.id, od.orderId, od.projectId, od.bookingDate, 
				od.hourStart, od.minuteStart, od.hourEnd, od.minuteEnd, od.cost, od.status, od.handlerUserId, od.deposit, od.enableSingleHourMinuteFormat,
				p.notifyUserEmailList, p.name as projectName
				FROM $this->order_days_table_name as od
				INNER JOIN $this->project_table_name as p
				ON od.projectId = p.id
				WHERE od.id = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedDay((array)$r);
		}
		return false;
	}
	
	public function readByOrder($orderId){
		$sql = "SELECT od.id, od.orderId, od.projectId, od.bookingDate, 
				od.hourStart, od.minuteStart, od.hourEnd, od.minuteEnd, od.cost, od.status, od.handlerUserId, od.deposit, od.enableSingleHourMinuteFormat,
				p.notifyUserEmailList, p.name as projectName
				FROM $this->order_days_table_name as od 
				INNER JOIN $this->project_table_name as p
				ON od.projectId = p.id
				WHERE od.orderId = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $orderId ) );
		if ( is_array( $result) ){
			$bookedDays = new Booki_BookedDays();
			foreach($result as $r){
				$bookedDays->add(new Booki_BookedDay((array)$r));
			}
			return $bookedDays;
		}
		return false;
	}
	
	public function readByDays($days, $projectId){
		$dates = array();
		foreach($days as $dateString){
			$day = Booki_DateHelper::parseFormattedDateString($dateString);
			array_push($dates, sprintf('CONVERT( \'%s\', DATETIME)', $day->format(BOOKI_DATEFORMAT)));
		}
		
		if(count($dates) === 0){
			return new Booki_BookedDays();
		}
		
		$sql = "SELECT od.id, od.orderId, od.projectId, od.bookingDate, 
				od.hourStart, od.minuteStart, od.hourEnd, od.minuteEnd, od.cost, od.status, od.handlerUserId, od.deposit, od.enableSingleHourMinuteFormat,
				p.notifyUserEmailList, p.name as projectName
				FROM $this->order_days_table_name as od
				INNER JOIN $this->project_table_name as p
				ON od.projectId = p.id
				WHERE bookingDate IN (%s)
				AND od.projectId = %d
				ORDER BY p.id";
				
		$result = $this->wpdb->get_results( sprintf($sql,  implode(',', $dates), $projectId ) );
		if ( is_array( $result) ){
			$bookedDays = new Booki_BookedDays();
			foreach($result as $r){
				$bookedDays->add(new Booki_BookedDay((array)$r));
			}
			return $bookedDays;
		}
		return false;
	}
	
	public function readByProject($projectId){
		$sql = "SELECT od.id, od.orderId, od.projectId, od.bookingDate, 
				od.hourStart, od.minuteStart, od.hourEnd, od.minuteEnd, od.cost, od.status, od.deposit, od.enableSingleHourMinuteFormat,
				od.handlerUserId, p.notifyUserEmailList, p.name as projectName
				FROM $this->order_table_name as o
				LEFT JOIN $this->order_days_table_name as od
				ON o.id = od.orderId
				INNER JOIN $this->project_table_name as p
				ON od.projectId = p.id
				WHERE od.projectId = %d
				ORDER BY p.id";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ) );
		if ( is_array( $result) ){
			$bookedDays = new Booki_BookedDays();
			foreach($result as $r){
				$bookedDays->add(new Booki_BookedDay((array)$r));
			}
			return $bookedDays;
		}
		return false;
	}
	
	public function readCostByRange($idList){
		$sql = "SELECT sum(cost) as totalCost, (SELECT deposit FROM $this->order_days_table_name WHERE id IN ($idList) LIMIT 1) as deposit
				FROM $this->order_days_table_name
				WHERE id IN ($idList)";
		
		$result = $this->wpdb->get_results($sql);
		if( $result ){
			$r = $result[0];
			return (double)$r->totalCost;
		}
		return false;
	}
	
	public function insert($orderId, $bookedDay){
		$fields = array(
			'projectId'=>$bookedDay->projectId
			, 'bookingDate'=>$bookedDay->bookingDate->format(BOOKI_DATEFORMAT)
			, 'cost'=>$bookedDay->cost
			, 'status'=>$bookedDay->status
			, 'orderId'=>$orderId
			, 'handlerUserId'=>$bookedDay->handlerUserId
			, 'enableSingleHourMinuteFormat'=>$bookedDay->enableSingleHourMinuteFormat
		);
		$formatStrings = array('%d', '%s', '%f', '%d', '%d', '%d', '%d');
		
		if($bookedDay->hourStart !== null){
			$fields['hourStart'] = $bookedDay->hourStart;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->hourEnd !== null){
			$fields['hourEnd'] = $bookedDay->hourEnd;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->minuteStart !== null){
			$fields['minuteStart'] = $bookedDay->minuteStart;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->minuteEnd !== null){
			$fields['minuteEnd'] = $bookedDay->minuteEnd;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->deposit !== null){
			$fields['deposit'] = $bookedDay->deposit;
			array_push($formatStrings, '%f');
		}
		$result = $this->wpdb->insert($this->order_days_table_name, $fields , $formatStrings);
		if($result !== false){
			return $this->wpdb->insert_id;
		}
		 return $result;
	}
	
	public function update($bookedDay){
		$fields = array(
			'bookingDate'=>$bookedDay->bookingDate->format(BOOKI_DATEFORMAT)
			, 'cost'=>$bookedDay->cost
			, 'status'=>$bookedDay->status
			, 'handlerUserId'=>$bookedDay->handlerUserId
			, 'enableSingleHourMinuteFormat'=>$bookedDay->enableSingleHourMinuteFormat
		);
		$formatStrings = array('%s', '%f', '%d', '%d', '%d');
		
		if($bookedDay->hourStart !== null){
			$fields['hourStart'] = $bookedDay->hourStart;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->hourEnd !== null){
			$fields['hourEnd'] = $bookedDay->hourEnd;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->minuteStart !== null){
			$fields['minuteStart'] = $bookedDay->minuteStart;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->minuteEnd !== null){
			$fields['minuteEnd'] = $bookedDay->minuteEnd;
			array_push($formatStrings, '%d');
		}
		if($bookedDay->deposit !== null){
			$fields['deposit'] = $bookedDay->deposit;
			array_push($formatStrings, '%f');
		}
		 $result = $this->wpdb->update($this->order_days_table_name, $fields, array('id'=>$bookedDay->id), $formatStrings);
		 return $result;
	}
	
	public function updateStatus($id, $status){
		 $result = $this->wpdb->update($this->order_days_table_name,  array(
			'status'=>$status
		  ), array('id'=>$id), array('%d'));
		 return $result;
	}
	
	public function updateStatusByOrderId($orderId, $status){
		 $result = $this->wpdb->update($this->order_days_table_name,  array(
			'status'=>$status
		  ), array('orderId'=>$orderId), array('%d'));
		 return $result;
	}
	
	public function setOwner($id, $userId){
		$result = $this->wpdb->update($this->order_days_table_name,  array(
			'handlerUserId'=>$userId
		), array('id'=>$id), array('%d'));
		return $result;
	}
	
	public function updateStatusByRange($idList, $status){
		return $this->wpdb->query($this->wpdb->prepare("UPDATE $this->order_days_table_name SET status = %d WHERE id IN(' . $idList . ')", $status));
	}
	
	public function updateStatusAndHandlerByRange($idList, $status, $handlerUserId){
		return $this->wpdb->query($this->wpdb->prepare("UPDATE $this->order_days_table_name SET status = %d, handlerUserId = %d WHERE id IN (" . $idList . ")", $status, $handlerUserId));
	}
	
	public function deleteByOrderId($orderId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->order_days_table_name WHERE orderId = %d", $orderId));
	}
	public function deleteByRange($idList){
		return $this->wpdb->query("DELETE FROM $this->order_days_table_name WHERE id IN (" . $idList . ")");
	}
	public function delete($id){
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->order_days_table_name WHERE id = %d", $id));
	}
	public function deleteByUserId($userId){
		return $this->wpdb->query($this->wpdb->prepare("DELETE od.* FROM $this->order_days_table_name as od 
			LEFT JOIN $this->order_table_name as o
			ON o.id = od.orderId WHERE o.userId = %d", $userId));
	}
}
?>