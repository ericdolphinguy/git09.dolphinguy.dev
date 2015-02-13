<?php
require_once  dirname(__FILE__) . '/../entities/Optional.php';
require_once  dirname(__FILE__) . '/../entities/Optionals.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_OptionalRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $optional_table_name;
	private $order_optional_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->optional_table_name = $wpdb->prefix . 'booki_optional';
		$this->order_optional_table_name = $wpdb->prefix . 'booki_order_optional';
	}
	
	public function readAll($projectId){
		$sql = "SELECT id, projectId, name, cost FROM $this->optional_table_name WHERE projectId = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if ( is_array($result) ){
			$optionals = new Booki_Optionals();
			foreach($result as $r){
				$optionals->add( new Booki_Optional((int)$r->projectId, $this->decode((string)$r->name), (double)$r->cost, (int)$r->id));
			}
			return $optionals;
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id, projectId, name, cost FROM $this->optional_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_Optional((int)$r->projectId, $this->decode((string)$r->name), (double)$r->cost, (int)$r->id);
		}
		return false;
	}
	
	public function insert($optional){
		 $result = $this->wpdb->insert($this->optional_table_name,  array(
			'projectId'=>$optional->projectId 
			, 'name'=>$this->encode($optional->name)
			, 'cost'=>$optional->cost
		  ), array('%d', '%s', '%s'));
		  
		 if($result !== false){
			$optional->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($optional){
		$result = $this->wpdb->update($this->optional_table_name,  array(
			'name'=>$this->encode($optional->name)
			, 'cost'=>$optional->cost
		), array('id'=>$optional->id), array('%s', '%s'));
		$this->deleteResources($optional->id);
		$optional->updateResources();
		return $result;
	}
	
	public function delete($id){
		$this->deleteResources($id);
		return $this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE id = %d", $id));
	}
	
	public function deleteResources($id){
		$optional = $this->read($id);
		if($optional){
			$optional->deleteResources();
		}
	}
}
?>