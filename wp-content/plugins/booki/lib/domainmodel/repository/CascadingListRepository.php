<?php
require_once  dirname(__FILE__) . '/../entities/CascadingItem.php';
require_once  dirname(__FILE__) . '/../entities/CascadingItems.php';
require_once  dirname(__FILE__) . '/../entities/CascadingList.php';
require_once  dirname(__FILE__) . '/../entities/CascadingLists.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
class Booki_CascadingListRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $cascading_list_table_name;
	private $cascading_item_table_name;
	private $order_cascading_item_table_name;
	
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->cascading_list_table_name = $wpdb->prefix . 'booki_cascading_list';
		$this->cascading_item_table_name = $wpdb->prefix . 'booki_cascading_item';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
	}
	
	public function readAll($projectId){
		$sql = "SELECT id, projectId, label, isRequired
				FROM $this->cascading_list_table_name WHERE projectId = %d ORDER BY id DESC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );
		if ( is_array($result) ){
			$cascadingLists = new Booki_CascadingLists();
			foreach($result as $r){
				$cascadingLists->add( new Booki_CascadingList((int)$r->projectId, $this->decode((string)$r->label), (bool)$r->isRequired, (int)$r->id));
			}
			return $cascadingLists;
		}
		return false;
	}
	
	public function readAllTopLevel($projectId){
		$sql = "SELECT cl.id, cl.projectId, cl.label, cl.isRequired
				FROM $this->cascading_list_table_name as cl 
				WHERE cl.id NOT IN(SELECT id FROM $this->cascading_list_table_name WHERE id IN (SELECT parentId FROM $this->cascading_item_table_name))
				AND cl.projectId = %d ORDER BY cl.id DESC";
				
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $projectId) );

		if ( is_array($result) ){
			$cascadingLists = new Booki_CascadingLists();
			foreach($result as $r){
				$cascadingLists->add( new Booki_CascadingList((int)$r->projectId, $this->decode((string)$r->label), (bool)$r->isRequired, (int)$r->id));
			}
			return $cascadingLists;
		}
		return false;
	}
	
	public function readList($id){
		$sql = "SELECT id, projectId, label, isRequired
				FROM $this->cascading_list_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_CascadingList((int)$r->projectId, $this->decode((string)$r->label), (bool)$r->isRequired, (int)$r->id);
		}
		return false;
	}
	
	public function readItem($id){
		$sql = "SELECT ci.id, ci.listId, ci.parentId, ci.value, ci.cost, ci.lat, ci.lng, cl.isRequired
				FROM $this->cascading_item_table_name as ci
				INNER JOIN $this->cascading_list_table_name as cl
				ON ci.listId = cl.id
				WHERE ci.id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result ){
			$r = $result[0];
			return new Booki_CascadingItem((array)$r);
		}
		return false;
	}
	
	public function readItemsByList($cascadingList){
		if(is_null($cascadingList->id)){
			return $cascadingList;
		}
		
		$sql = "SELECT ci.id, ci.listId, ci.parentId, ci.value, ci.cost, ci.lat, ci.lng, cl.isRequired
				FROM $this->cascading_item_table_name as ci
				INNER JOIN $this->cascading_list_table_name as cl
				ON ci.listId = cl.id
				WHERE ci.listId = %d ORDER BY ci.id ASC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $cascadingList->id) );
		if ( is_array($result) ){
			foreach($result as $r){
				$cascadingList->cascadingItems->add(new Booki_CascadingItem((array)$r));
			}
		}
		return $cascadingList;
	}
	
	public function readItemsByListId($id){
		$cascadingItems = new Booki_CascadingItems();
		$sql = "SELECT ci.id, ci.listId, ci.parentId, ci.value, ci.cost, ci.lat, ci.lng, cl.isRequired
				FROM $this->cascading_item_table_name as ci
				INNER JOIN $this->cascading_list_table_name as cl
				ON ci.listId = cl.id
				WHERE ci.listId = %d ORDER BY ci.id ASC";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if ( is_array($result) ){
			foreach($result as $r){
				$cascadingItems->add(new Booki_CascadingItem((array)$r));
			}
		}
		return $cascadingItems;
	}
	public function readItemsByLists($cascadingLists){
		foreach($cascadingLists as $cascadingList){
			$sql = "SELECT ci.id, ci.listId, ci.parentId, ci.value, ci.cost, ci.lat, ci.lng, cl.isRequired
					FROM $this->cascading_item_table_name as ci
					INNER JOIN $this->cascading_list_table_name as cl
					ON ci.listId = cl.id
					WHERE ci.listId = %d ORDER BY ci.id ASC";
			$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $cascadingList->id) );
			if ( is_array($result) ){
				foreach($result as $r){
					$cascadingList->cascadingItems->add(new Booki_CascadingItem((array)$r));
				}
			}
		}
		return $cascadingLists;
	}
	
	
	public function insertList($cascadingList){
		 $result = $this->wpdb->insert($this->cascading_list_table_name,  array(
			'projectId'=>$cascadingList->projectId 
			, 'label'=>$this->encode($cascadingList->label)
			, 'isRequired'=>$cascadingList->isRequired
		  ), array('%d', '%s', '%d'));
		  
		 if($result !== false){
			$cascadingList->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function insertItem($cascadingItem){
		$keys = array(
			'listId'=>$cascadingItem->listId 
			, 'value'=>$this->encode($cascadingItem->value)
			, 'cost'=>$cascadingItem->cost
			, 'lat'=>$cascadingItem->lat
			, 'lng'=>$cascadingItem->lng
		);
		$values = array('%d', '%s', '%f', '%f', '%f');
		if(isset($cascadingItem->parentId)){
			$keys['parentId'] = $cascadingItem->parentId;
			array_push($values, '%d');
		}
		
		$result = $this->wpdb->insert($this->cascading_item_table_name,  $keys, $values);
		  
		if($result !== false){
			$cascadingItem->updateResources();
			return $this->wpdb->insert_id;
		}
		return $result;
	}
	
	public function updateList($cascadingList){
		$result = $this->wpdb->update($this->cascading_list_table_name,  array(
			'label'=>$this->encode($cascadingList->label)
			, 'isRequired'=>$cascadingList->isRequired
		), array('id'=>$cascadingList->id), array('%s', '%d'));
		$this->deleteListResources($cascadingList->id);
		$cascadingList->updateResources();
		return $result;
	}
	
	public function updateItem($cascadingItem){
		$keys = array(
			'value'=>$this->encode($cascadingItem->value)
			, 'cost'=>$cascadingItem->cost
			, 'lat'=>$cascadingItem->lat
			, 'lng'=>$cascadingItem->lng
		);
		$values = array('%s', '%f', '%f', '%f');
		if(isset($cascadingItem->parentId)){
			$keys['parentId'] = $cascadingItem->parentId;
			array_push($values, '%d');
		}
		
		$result = $this->wpdb->update($this->cascading_item_table_name,  $keys, array('id'=>$cascadingItem->id), $values);
		$this->deleteItemResources($cascadingItem->id);
		$cascadingItem->updateResources();
		return $result;
	}
	
	public function deleteByItem($id){
		$this->deleteItemResources($id);
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_item_table_name WHERE id = %d", $id));
	}
	
	public function deleteByList($listId){
		$this->deleteItemByListResources($listId);
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_list_table_name WHERE id = %d", $listId));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_item_table_name WHERE listId = %d", $listId));
	}
	
	public function deleteByProject($projectId){
		$cascadingLists = $this->readListByProject($projectid);
		foreach($cascadingLists as $cascadingList){
			$this->deleteItemByListResources($cascadingList->id);
			$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_list_table_name WHERE id = %d", $cascadingList->id));
			$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_item_table_name WHERE listId = %d", $cascadingList->id));
		}
	}
	
	public function deleteItemResources($id){
		$cascadingItem = $this->readItem($id);
		if($cascadingItem){
			$cascadingItem->deleteResources();
		}
	}
	
	public function deleteListResources($id){
		$cascadingList = $this->readList($id);
		if($cascadingList){
			$cascadingList->deleteResources();
		}
	}
	
	public function deleteItemByListResources($id){
		$this->deleteListResources($id);
		$cascadingItems = $this->readItemsByListId($id);
		foreach($cascadingItems as $cascadingItem){
			if($cascadingItem){
				$cascadingItem->deleteResources();
			}
		}
	}
	
}
?>