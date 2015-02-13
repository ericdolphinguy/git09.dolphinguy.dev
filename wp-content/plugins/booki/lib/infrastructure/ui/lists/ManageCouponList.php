<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/CouponRepository.php';

class Booki_ManageCouponList extends Booki_List {
	
	private $currencySymbol;
	private $currency;
	public $perPage;
	public $orderBy;
	public $order;
	public $totalPages;
	function __construct( ){
		$this->perPage = 10;
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol = $localeInfo['currencySymbol'];
		$this->uniqueNamespace = 'booki_managecoupon_';
        parent::__construct( array(
            'singular'  => 'coupon', 
            'plural'    => 'coupons', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$couponId = isset($_GET['couponid']) ? (int)$_GET['couponid'] : null;
		if($item['id'] == $couponId){
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
		return sprintf('<p><span>%1$s</span></p>' , $item['id'] );
	}
	function column_tasks($item){
		$expirationDate = Booki_Helper::formatDate( $item['expirationDate']);
		$expired = strtotime('now') >= strtotime($expirationDate);
		
		$buttonGroups = array();
		
		array_push($buttonGroups, sprintf(
			'<a class="btn btn-default" href="%s">
				<i class="glyphicon glyphicon-ok-circle"></i> 
				%s
			</a>'
			, add_query_arg('couponid', $item['id'])
			, __('Select', 'booki')
		));
		
		if(!$item['emailedTo'] && !$expired){
			array_push($buttonGroups, 
				'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="caret"></span>
					<span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul class="dropdown-menu" role="menu">'
			);
			
			array_push($buttonGroups, sprintf(
				'<li>
					<a href="%s" class="booki-btnlink btn btn-default" title="%s">
						<i class="glyphicon glyphicon-envelope"></i>
						%s
					</a>
				</li>'
				, add_query_arg(array('couponid'=>$item['id'], 'command'=>'email'))
				, __('Send coupon to user via email', 'booki')
				, __('Email', 'booki')
			));
			array_push($buttonGroups, '</ul>');
		}
			
		return sprintf(
			'<form action="%s" method="post">
				<input type="hidden" name="controller" value="booki_managecoupons" />
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
	
	function column_projectId($item){
		return $item['projectId'] === -1 ? __('Any project', 'booki') : $item['projectName'];
	}
	function column_couponType($item){
		return $item['couponType'] === Booki_CouponType::REGULAR ? '<span class="label label-info">' . __('Regular', 'booki') . '</span>': '<span class="label label-danger">' . __('Super', 'booki') . '</span>';
	}
	function column_expirationDate($item){
		$expirationDate = Booki_Helper::formatDate( $item['expirationDate']);
		$expired = strtotime('now') >= strtotime($expirationDate);
		
		$result = sprintf(
			'<p>
				<span title="%s" %s>%s</span>
			</p>'
			, $expired ? __('Coupon has expired!', 'booki') : __('Expiration date', 'booki')
			, $expired ? 'class="label label-danger"' : 'class="label label-info"'
			, $expirationDate
		);
		
		return $result;
	}
	
	function column_discount($item){
		$result = sprintf('<p><span title="%s">%s</span></p>'
			, __('Discount provided by code', 'booki')
			, Booki_Helper::toMoney($item['discount']) . '%'
		);
		return $result;
	}
	
    function column_orderMinimum($item){
		return sprintf('<span title="%s">%s</span>'
			, __('The minimum booking required for coupon discount to apply.', 'booki')
			, $this->currencySymbol . Booki_Helper::toMoney($item['orderMinimum'])
		);
    }
    
	function column_code($item){
		return sprintf('<p title="%s"><input type="text" value="%s" class="form-control" readonly="true"></p>'
			, __('The code to use for discount to apply.', 'booki')
			, $item['code']
		);
	}
	
	function column_emailedTo($item){
		return sprintf('<p title="%s">%s</p>'
			, $item['emailedTo'] ? 
				__('The code is to be claimed by', 'booki') . ': ' . $item['emailedTo'] : 
				__('The code has not been used yet.', 'booki')
			, $item['emailedTo'] ? $item['emailedTo'] : __('Unused', 'booki')
		);
	}
	
    function get_columns(){
        $columns = array(
			'id'=>__('#id', 'booki')
			, 'projectId'=>__('Project', 'booki')
			, 'discount'=>__('Discount', 'booki')
			, 'orderMinimum'=>__('Order Min.', 'booki')
			, 'expirationDate'=>__('Expiration', 'booki')
			, 'emailedTo'=>__('Status', 'booki')
			, 'couponType'=>__('Coupon type', 'booki')
			, 'code'=>__('Code', 'booki')
			, 'tasks'=>__('Tasks', 'booki')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
			, 'projectId'=>array('projectId', false)
			, 'discount'=>array('discount', false)
			, 'orderMinimum'=>array('status', false)
            , 'expirationDate'=> array('expirationDate', false)
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
		if($this->currentPage){
			$this->currentPage = $this->currentPage * $this->perPage;
		}

        $this->orderBy = (!empty($_REQUEST[$this->orderByKey])) ? $_REQUEST[$this->orderByKey] : 'id';
        $this->order = (!empty($_REQUEST[$this->orderKey])) ? $_REQUEST[$this->orderKey] : 'desc'; 
		
		$couponRepository = new Booki_CouponRepository();
        $result = $couponRepository->readAll($this->currentPage, $this->perPage, $this->orderBy, $this->order);
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