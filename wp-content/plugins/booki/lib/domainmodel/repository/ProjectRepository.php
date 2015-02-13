<?php
require_once  dirname(__FILE__) . '/../entities/Project.php';
require_once  dirname(__FILE__) . '/../entities/Projects.php';
require_once  dirname(__FILE__) . '/../base/RepositoryBase.php';
require_once  'CascadingListRepository.php';
class Booki_ProjectRepository extends Booki_RepositoryBase{
	private $wpdb;
	private $project_table_name;
	private $calendar_table_name;
	private $calendarDay_table_name;
	private $order_table_name;
	private $order_days_table_name;
	private $form_element_table_name;
	private $optional_table_name;
	private $order_form_elements_table_name;
	private $order_optionals_table_name;
	private $cascading_list_table_name;
	private $cascading_item_table_name;
	private $order_cascading_item_table_name;
	public function __construct(){
		global $wpdb;
		$this->wpdb = &$wpdb;
		$this->project_table_name = $wpdb->prefix . 'booki_project';
		$this->calendar_table_name = $wpdb->prefix . 'booki_calendar';
		$this->calendarDay_table_name = $wpdb->prefix . 'booki_calendar_day';
		$this->order_table_name = $wpdb->prefix . 'booki_order';
		$this->order_days_table_name = $wpdb->prefix . 'booki_order_days';
		$this->form_element_table_name = $wpdb->prefix . 'booki_form_element';
		$this->optional_table_name = $wpdb->prefix . 'booki_optional';
		$this->order_form_elements_table_name = $wpdb->prefix . 'booki_order_form_elements';
		$this->order_optionals_table_name = $wpdb->prefix . 'booki_order_optionals';
		$this->cascading_list_table_name = $wpdb->prefix . 'booki_cascading_list';
		$this->cascading_item_table_name = $wpdb->prefix . 'booki_cascading_item';
		$this->order_cascading_item_table_name = $wpdb->prefix . 'booki_order_cascading_item';
	}
	
	public function count(){
		$sql = "SELECT count(id) as count FROM $this->project_table_name";
		$result = $this->wpdb->get_results( $sql);
		if( $result){
			$r = $result[0];
			return (int)$r->count;
		}
		return false;
	}
	
	public function readAll(){
		$sql = "SELECT id,  CAST(status AS unsigned integer) as status, name, bookingDaysMinimum, bookingDaysLimit, calendarMode, bookingMode, 
			description, previewUrl, tag , defaultStep, bookingTabLabel, customFormTabLabel, availableDaysLabel,
			selectedDaysLabel, nextLabel, prevLabel, addToCartLabel, optionalItemsLabel, bookingTimeLabel, fromLabel, 
			toLabel, proceedToLoginLabel, makeBookingLabel, bookingLimitLabel,
			notifyUserEmailList, optionalsBookingMode, optionalsListingMode, optionalsMinimumSelection, contentTop, 
			contentBottom, bookingWizardMode, hideSelectedDays
			FROM $this->project_table_name ORDER BY tag";
		$result = $this->wpdb->get_results( $sql);
		if( is_array( $result )){
			$projects = new Booki_Projects();
			foreach($result as $r){
				$projects->add(new Booki_Project((array)$r));
			}
			return $projects;
		}
		return false;
	}
	
	public function readAllTags(){
		//tags aren't normalized, perhaps it's better this way. less joins.
		$query = "SELECT DISTINCT tag FROM $this->project_table_name WHERE tag IS NOT NULL AND tag <> ''";
		$result = $this->wpdb->get_results($query);
		$tags = array();
		if( is_array( $result )){
			foreach($result as $r){
				array_push($tags, array('name'=>$r->tag));
			}
		}
		return $tags;
	}
	
	public function readByTag($tags, $startDate = null, $endDate = null, $projectId = -1, $pageIndex = -1, $limit = 5, $orderBy = 'name', $order = 'asc'){
		if($projectId === null){
			$projectId = -1;
		}
		if($pageIndex === null){
			$pageIndex = -1;
		}

		if($limit === null || $limit <= 0){
			$limit = 5;
		}
		
		if($orderBy === null){
			$orderBy = 'name';
		}
		
		if($order === null){
			$order = 'asc';
		}
		
		if($startDate){
			$startDate = $startDate->format(BOOKI_DATEFORMAT);
		}
		if($endDate){
			$endDate = $endDate->format(BOOKI_DATEFORMAT);
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM (
					SELECT p.id,  CAST(p.status AS unsigned integer) as status, p.name,  p.calendarMode
						, p.description, p.previewUrl, p.tag
						, c.startDate, c.endDate FROM $this->project_table_name as p
						INNER JOIN $this->calendar_table_name as c 
						ON p.id = c.projectId";
		
		$where = array();

		if($tags){
			array_push($where, "p.tag IN ('" . implode("','", explode(',', $tags)) . "')");
		}
		if($startDate && $endDate){
			array_push($where, 'c.startDate <= CONVERT( \'%1$s\', DATETIME) AND c.endDate >= CONVERT( \'%2$s\', DATETIME)');
		}

		if($projectId !== -1){
			array_push($where, 'p.id <> %3$d');
		}
		
		if(count($where) > 0){
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		$query .= ' ORDER BY ' . $orderBy . ' ' . $order;
		$query .= ') result, (SELECT FOUND_ROWS() AS \'total\') total';
		if($pageIndex > -1){
			$query .= ' LIMIT ' . $pageIndex . ', ' . $limit . ';';
		}

		$result = $this->wpdb->get_results($this->wpdb->prepare($query, $startDate, $endDate, $projectId));

		if( is_array( $result )){
			$projects = array();
			$total = 0;
			foreach($result as $r){
				$total = $r->total;
				array_push($projects, array(
					'id'=>(int)$r->id
					, 'status'=>(int)$r->status
					, 'calendarMode'=>(int)$r->calendarMode
					, 'name'=>$this->decode((string)$r->name)
					, 'description'=>$this->decode((string)$r->description)
					, 'previewUrl'=>(string)$r->previewUrl
					, 'tag'=>$this->decode((string)$r->tag)
					, 'startDate'=>(string)$r->startDate
					, 'endDate'=>(string)$r->endDate
				));
			}
			return array('total'=>$total, 'projects'=>$projects);
		}
		return false;
	}
	
	public function read($id){
		$sql = "SELECT id,  CAST(status AS unsigned integer) as status, name, bookingDaysMinimum, bookingDaysLimit, calendarMode, bookingMode, 
				description, previewUrl, tag , defaultStep, bookingTabLabel, customFormTabLabel, availableDaysLabel,
				selectedDaysLabel, nextLabel, prevLabel, addToCartLabel, optionalItemsLabel, bookingTimeLabel, fromLabel, 
				toLabel, proceedToLoginLabel, makeBookingLabel, bookingLimitLabel,
				notifyUserEmailList, optionalsBookingMode, optionalsListingMode, optionalsMinimumSelection, 
				contentTop, contentBottom, bookingWizardMode, hideSelectedDays
				FROM $this->project_table_name WHERE id = %d";
		$result = $this->wpdb->get_results( $this->wpdb->prepare($sql, $id) );
		if( $result){
			$r = $result[0];
			return new Booki_Project((array)$r);
		}
		return false;
	}
	
	public function insert($project){
		$result = $this->wpdb->insert($this->project_table_name, array( 
			'status'=>$project->status
			, 'name'=>$this->encode($project->name)
			, 'bookingDaysMinimum'=>$project->bookingDaysMinimum
			, 'bookingDaysLimit'=>$project->bookingDaysLimit
			, 'calendarMode'=>$project->calendarMode
			, 'bookingMode'=>$project->bookingMode
			, 'description'=>$this->encode($project->description)
			, 'previewUrl'=>$project->previewUrl
			, 'tag'=>$this->encode($project->tag)
			, 'defaultStep'=>$this->encode($project->defaultStep)
			, 'bookingTabLabel'=>$this->encode($project->bookingTabLabel)
			, 'customFormTabLabel'=>$this->encode($project->customFormTabLabel)
			, 'availableDaysLabel'=>$this->encode($project->availableDaysLabel)
			, 'selectedDaysLabel'=>$this->encode($project->selectedDaysLabel)
			, 'bookingTimeLabel'=>$this->encode($project->bookingTimeLabel)
			, 'optionalItemsLabel'=>$this->encode($project->optionalItemsLabel)
			, 'nextLabel'=>$this->encode($project->nextLabel)
			, 'prevLabel'=>$this->encode($project->prevLabel)
			, 'addToCartLabel'=>$this->encode($project->addToCartLabel)
			, 'fromLabel'=>$this->encode($project->fromLabel)
			, 'toLabel'=>$this->encode($project->toLabel)
			, 'proceedToLoginLabel'=>$this->encode($project->proceedToLoginLabel)
			, 'makeBookingLabel'=>$this->encode($project->makeBookingLabel)
			, 'bookingLimitLabel'=>$this->encode($project->bookingLimitLabel)
			, 'notifyUserEmailList'=>trim($this->encode($project->notifyUserEmailList))
			, 'optionalsBookingMode'=>$project->optionalsBookingMode
			, 'optionalsListingMode'=>$project->optionalsListingMode
			, 'optionalsMinimumSelection'=>$project->optionalsMinimumSelection
			, 'contentTop'=>$project->contentTop
			, 'contentBottom'=>$project->contentBottom
			, 'bookingWizardMode'=>$project->bookingWizardMode
			, 'hideSelectedDays'=>$project->hideSelectedDays
		), array('%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d'));
		 if($result !== false){
			$project->updateResources();
			return $this->wpdb->insert_id;
		 }
		 return $result;
	}
	
	public function update($project){
		$result = $this->wpdb->update($this->project_table_name, array( 
			'status'=>$project->status
			, 'name'=>$this->encode($project->name)
			, 'bookingDaysMinimum'=>$project->bookingDaysMinimum
			, 'bookingDaysLimit'=>$project->bookingDaysLimit
			, 'calendarMode'=>$project->calendarMode
			, 'bookingMode'=>$project->bookingMode
			, 'description'=>$this->encode($project->description)
			, 'previewUrl'=>$project->previewUrl
			, 'tag'=>$this->encode($project->tag)
			, 'defaultStep'=>$this->encode($project->defaultStep)
			, 'bookingTabLabel'=>$this->encode($project->bookingTabLabel)
			, 'customFormTabLabel'=>$this->encode($project->customFormTabLabel)
			, 'availableDaysLabel'=>$this->encode($project->availableDaysLabel)
			, 'selectedDaysLabel'=>$this->encode($project->selectedDaysLabel)
			, 'bookingTimeLabel'=>$this->encode($project->bookingTimeLabel)
			, 'optionalItemsLabel'=>$this->encode($project->optionalItemsLabel)
			, 'nextLabel'=>$this->encode($project->nextLabel)
			, 'prevLabel'=>$this->encode($project->prevLabel)
			, 'addToCartLabel'=>$this->encode($project->addToCartLabel)
			, 'fromLabel'=>$this->encode($project->fromLabel)
			, 'toLabel'=>$this->encode($project->toLabel)
			, 'proceedToLoginLabel'=>$this->encode($project->proceedToLoginLabel)
			, 'makeBookingLabel'=>$this->encode($project->makeBookingLabel)
			, 'bookingLimitLabel'=>$this->encode($project->bookingLimitLabel)
			, 'notifyUserEmailList'=>trim($this->encode($project->notifyUserEmailList))
			, 'optionalsBookingMode'=>$project->optionalsBookingMode
			, 'optionalsListingMode'=>$project->optionalsListingMode
			, 'optionalsMinimumSelection'=>$project->optionalsMinimumSelection
			, 'contentTop'=>$project->contentTop
			, 'contentBottom'=>$project->contentBottom
			, 'bookingWizardMode'=>$project->bookingWizardMode
			, 'hideSelectedDays'=>$project->hideSelectedDays
		), array('id'=>$project->id), array('%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d'));
		
		$project->updateResources();
		return $result;
	}
	
	public function delete($id){
		//the orders table has no references to a project. 
		//so don't leave orphaned when deleting a project.
		//MyISAM does not support on delete cascades.
		
		$sql = "DELETE o.* FROM $this->order_table_name as o
				INNER JOIN $this->order_days_table_name as ods
				ON o.id = ods.orderId
				WHERE ods.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id) );
		
		//myisam has no delete cascades. manual labor of love.
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_days_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_form_elements_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_optionals_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->form_element_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->optional_table_name WHERE projectId = %d", $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->order_cascading_item_table_name WHERE projectId = %d", $id) );
		$sql = "DELETE cd.* FROM $this->calendarDay_table_name as cd
				INNER JOIN $this->calendar_table_name as c
				ON c.id = cd.calendarId
				WHERE c.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id));
		$this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->calendar_table_name WHERE projectId = %d", $id));

		$sql = "DELETE ci.* FROM $this->cascading_item_table_name as ci
			INNER JOIN $this->cascading_list_table_name as cl
			ON cl.id = ci.listId
			WHERE cl.projectId = %d";
		$this->wpdb->query( $this->wpdb->prepare($sql, $id));
		$this->wpdb->query($this->wpdb->prepare("DELETE FROM $this->cascading_list_table_name WHERE projectId = %d", $id));
		
		$project = new Booki_Project(array('id'=>$id));
		$project->deleteResources();
		
		return $this->wpdb->query( $this->wpdb->prepare("DELETE FROM $this->project_table_name WHERE id = %d", $id));
	}
}
?>