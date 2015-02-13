<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/StatsRepository.php';

class Booki_OrdersMadeAggregateList extends Booki_List {
	public $perPage;
	public $orderBy;
	public $order;
	protected $dateFormat;
  function __construct($userId = null){
		$this->uniqueNamespace = 'booki_orders_made_aggregate_';
		$this->dateFormat = get_option('date_format');
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); 
        }
    }
    
	function column_count($item){
		$result = sprintf('<p><span>%s</span></p>'
			, $item['count'] 
		);
		return $result;
	}
	
	function column_orderDate($item){
		$orderDate = new Booki_DateTime($item['orderDate']);
		$result = sprintf('<p><span>%s</span></p>'
			, $orderDate->format($this->dateFormat)
		);
		return $result;
	}
	
    function get_columns(){
        $columns = array(
			'count'=>__('Bookings', 'booki')
			, 'orderDate'=>__('Order Date', 'booki')
			
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'count'=>array('count', false)
            , 'orderDate'=> array('orderDate', true)
        );
        return $sortable_columns;
    }
    
    /**
		@description binds to data
	*/
    function bind() {
		$this->items = array();
        $per_page = 10;
        
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
		$period =  (!empty($_REQUEST[$this->uniqueNamespace . 'period'])) ? intval($_REQUEST[$this->uniqueNamespace . 'period']) : 3; 
		$statsRepository = new Booki_StatsRepository();
		$handlerUserId = null;
		if(!Booki_Helper::hasAdministratorPermission()){
			$handlerUserId = get_current_user_id();
		}
        $result = $statsRepository->readOrdersMadeAggregate($handlerUserId, $current_page, $per_page, $this->orderBy, $this->order, $period);

		if(!$result){
			return;
		}
		
        $total_pages = ceil((int)$result[0]->total / $per_page);
        $total_items = (int)$result[0]->total;
		
        foreach($result as $r){
			array_push($this->items, array(
				'count'=>(int)$r->count
				, 'orderDate'=>$r->orderDate
			));
		}
		
        
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