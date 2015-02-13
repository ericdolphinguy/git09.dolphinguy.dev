<?php
require_once  dirname(__FILE__) . '/../entities/BookedFormElement.php';
require_once  dirname(__FILE__) . '/../entities/BookedFormElements.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_BookedFormElementsRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $order_table_name;
	private $order_form_elements_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
	}
	
	public function read($id){
		$sql = "SELECT id, orderId, projectId, label, elementType, rowIndex, colIndex, value, capability
				FROM $this->order_form_elements_table_name
				WHERE id = %d";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $id ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedFormElement(
				(int)$r->projectId
				, $this->decode((string)$r->label)
				, (int)$r->elementType
				, (int)$r->rowIndex
				, (int)$r->colIndex
				, $this->decode((string)$r->value)
				, (int)$r->capability
				, (int)$r->orderId
				, (int)$r->id
			);
		}
		return false;
	}
	
	public function readAll($projectId){
		$sql = "SELECT id, orderId, projectId, label, elementType, rowIndex, colIndex, value, capability
				FROM $this->order_form_elements_table_name
				WHERE projectId = %d";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $projectId ) );
		if( $result ){
			$r = $result[0];
			return new Booki_BookedFormElement(
				(int)$r->projectId
				, $this->decode((string)$r->label)
				, (int)$r->elementType
				, (int)$r->rowIndex
				, (int)$r->colIndex
				, $this->decode((string)$r->value)
				, (int)$r->capability
				, (int)$r->orderId
				, (int)$r->id
			);
		}
		return false;
	}
	
	public function readByOrder($orderId){
		$sql = "SELECT ofe.id, ofe.orderId, ofe.projectId, ofe.label, ofe.elementType, ofe.rowIndex, ofe.colIndex, ofe.value, ofe.capability
				FROM $this->order_form_elements_table_name AS ofe
				LEFT OUTER JOIN $this->order_table_name AS o
				ON o.id = ofe.orderId 
				WHERE ofe.orderId = %d";
		
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $orderId ) );
		if ( is_array( $result) ){
			$formElements = new Booki_BookedFormElements();
			foreach($result as $r){
				$formElements->add(new Booki_BookedFormElement(
					(int)$r->projectId
					, $this->decode((string)$r->label)
					, (int)$r->elementType
					, (int)$r->rowIndex
					, (int)$r->colIndex
					, (string)$r->value
					, $this->decode((int)$r->capability)
					, (int)$r->orderId
					, (int)$r->id
				));
			}
			return $formElements;
		}
		return false;
	}
	
	public function readOrderByCapability($orderId, $elementType, $capability){
		$sql = "SELECT id, orderId, projectId, label, elementType, rowIndex, colIndex, value, capability
				FROM $this->order_form_elements_table_name
				WHERE orderId = %d AND elementType = %d AND capability IN (" . $capability . ") GROUP BY capability";

		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql,  $orderId, $elementType) );
		if ( is_array( $result) ){
			$formElements = new Booki_BookedFormElements();
			foreach($result as $r){
				$formElements->add(new Booki_BookedFormElement(
					(int)$r->projectId
					, $this->decode((string)$r->label)
					, (int)$r->elementType
					, (int)$r->rowIndex
					, (int)$r->colIndex
					, $this->decode((string)$r->value)
					, (int)$r->capability
					, (int)$r->orderId
					, (int)$r->id
				));
			}
			return $formElements;
		}
		return false;
	}
	
	public function insert($orderId, $bookedFormElement){
		 $result = $this->wpdb->insert($this->order_form_elements_table_name,  array(
			'projectId'=>$bookedFormElement->projectId
			, 'label'=>$this->encode($bookedFormElement->label)
			, 'elementType'=>$bookedFormElement->elementType
			, 'rowIndex'=>$bookedFormElement->rowIndex
			, 'colIndex'=>$bookedFormElement->colIndex
			, 'value'=>$this->encode($bookedFormElement->value)
			, 'capability'=>$bookedFormElement->capability
			, 'orderId'=>$orderId
		  ), array('%d', '%s', '%d', '%d', '%d', '%s','%d', '%d'));
		 if($result !== false){
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function delete($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_form_elements_table_name WHERE orderId = %d", $orderId) );
	}
	
	public function deleteByOrderId($orderId){
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_form_elements_table_name WHERE orderId = %d", $orderId) );
	}
	
	public function deleteByUserId($userId){
		return 	$this->wpdb->query($this->wpdb->prepare("DELETE fe.* FROM $this->order_form_elements_table_name as fe
				LEFT JOIN $this->order_table_name as o
				ON o.id = fe.orderId WHERE o.userId = %d", $userId));
	}
}
?>