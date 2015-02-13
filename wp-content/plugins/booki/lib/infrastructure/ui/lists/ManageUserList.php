<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/UserRepository.php';

class Booki_ManageUserList extends Booki_List {
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
  function __construct( ){
		$this->perPage = 10;
		$this->uniqueNamespace = 'booki_manageuser_';
        parent::__construct( array(
            'singular'  => 'user', 
            'plural'    => 'users', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$userId = isset($_GET['userid']) ? (int)$_GET['userid'] : null;
		if($item['id'] == $userId){
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
		return sprintf('<p><span>%s</span></p>', $item['id']);
	}
	
	
	function column_username($item){
		$name = $item['firstname'] ? $item['firstname'] . ' ' . $item['lastname'] : __('Name not provided','booki');
		return sprintf('<p><span title="%s">%s</span></p>', $name,  $item['username']);
	}
	
	function column_email($item){
		return sprintf('<p><span>%s</span></p>', $item['email']);
	}
    
	function column_bookingsCount($item){
		$buttonGroups = array();
		
		array_push($buttonGroups, sprintf(
			'<a href="%s" class="btn btn-default">
				<i class="glyphicon glyphicon-ok-circle"></i>
				%s [%s]
			</a>'
			, add_query_arg('userid', $item['id'], remove_query_arg('orderid'))
			, __('Select', 'booki')
			, $item['bookingsCount']
		));
		
		array_push($buttonGroups, 
			'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" role="menu">'
		);
		
		array_push($buttonGroups, sprintf(
			'<li>
				<a href="%s" class="booki-btnlink btn btn-default" name="booki_delete">
					<i class="glyphicon glyphicon-trash"></i>
					%s
				</a>
			</li>'
			,  add_query_arg(array('selecteduserid'=>$item['id'], 'command'=>'delete'), remove_query_arg('orderid'))
			, __('Delete', 'booki')
		));
		
		array_push($buttonGroups, '</ul>');


        return sprintf(
			'<form action="%s" method="post">
				<input type="hidden" name="controller" value="booki_manageusers" />
				<div class="form-group">
					<div class="grid-btn-group">
						<div class="btn-group">
							%s
						</div>
					</div>
				</div>
			</form>'
			, $_SERVER['REQUEST_URI']
			, join("\n", $buttonGroups)
        );
	}
	
    function get_columns(){
        $columns = array(
            'id'=>__('ID', 'booki')
			, 'username'=>__('Username', 'booki')
			, 'email'=>__('Email', 'booki')
			, 'bookingsCount'=>__('Bookings', 'booki')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', true)
            , 'username'=> array('username', false)
			, 'email'=>array('email', false)
			, 'bookingsCount'=>array('bookingsCount', false)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->currentPage = $this->get_pagenum() - 1;
		if( $this->currentPage ){
			$this->currentPage = $this->currentPage * $this->perPage;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		
		$userRepository = new Booki_UserRepository();
		$fromDate = null;
		$toDate = null;
		$userId = null;
		
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_manageusers'){
			
			if (array_key_exists('from', $_GET) && array_key_exists('to', $_GET)){
				$fromDate = new Booki_DateTime($_GET['from']);
				$toDate = new Booki_DateTime($_GET['to']);
			}
			
			if (array_key_exists('userid', $_GET)){
				$userId = (int)$_GET['userid'];
			}
		}

        $result = $userRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order, $fromDate, $toDate, $userId);
        $this->totalPages = ceil($result->total / $this->perPage);
        $total_items = $result->total;
        
        $this->items = $result->toArray();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $this->perPage,
            'total_pages' => $this->totalPages
        ) );
    }
	
	function get_table_classes() {
		return array( 'booki', 'booki-grid', 'table', 'table-striped');
	}
}
?>