<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/entities/EventLog.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/EventsLogRepository.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';

class Booki_EventsLogList extends Booki_List {
	public $perPage;
	public $orderBy;
  function __construct(){
		$this->uniqueNamespace = 'booki_eventslog_';
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
    
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$id = isset($_GET['eventlogid']) ? (int)$_GET['eventlogid'] : null;
		if($item['id'] == $id){
			$row_class .= ' info';
		}
		$row_class = $row_class ? ' class="' . $row_class . '"' : '';
		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}
	
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_id($item){
		$result = sprintf('<p><span>%s</span></p>'
			, $item['id'] 
		);
		return $result;
	}
	
	function column_entryDate($item){
		return $item['entryDate']->format(get_option('date_format'));
	}
	
	function column_data($item){
		ob_start();
		var_dump($item['data']);
		$result = ob_get_clean();
		return sprintf('<pre class="eventslog-data">%s</pre>', $result);
	}
    
	
	function column_tasks($item){
		$buttonGroups = array();
		array_push($buttonGroups, sprintf(
			'<button class="btn btn-default" name="delete" value="%s">
				<i class="glyphicon glyphicon-trash"></i> 
				%s
			</a>'
			, $item['id']
			, __('Delete', 'booki')
		));
		
		return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_eventslog" />
				<div class="form-group">
					<div class="grid-btn-group">
						<div class="btn-group">
							%s
						</div>
					</div>
				</div>
			</form>'
			, admin_url() . 'admin.php?page=booki/eventslog.php'
			, join("\n", $buttonGroups)
        );
	}
	
	
    function get_columns(){
        $columns = array(
			'id'=>__('#id', 'booki')
			, 'entryDate'=>__('Entry Date', 'booki')
			, 'data'=>__('Error', 'booki')
			, 'tasks'=>__('Tasks', 'booki')
        );
		
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
            , 'entryDate'=> array('entryDate', false)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        $per_page = 5;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $current_page = $this->get_pagenum() - 1;
		if($current_page){
			$current_page = $current_page * $per_page;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 

		$eventsLogRepository = new Booki_EventsLogRepository();
        $result = $eventsLogRepository->readAll($current_page, $per_page, $this->orderBy, $this->order);
        $total_pages = ceil($result->total / $per_page);

        $total_items = $result->total;
        
        $this->items = $result->toArray();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-striped');
	}
}
?>