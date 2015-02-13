<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/entities/PaymentStatus.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/OrderRepository.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';
class Booki_EditorApprovedOrderList extends Booki_List {
	private $currencySymbol;
	private $currency;
	public $totalPages;
	public $currentPage;
	public $perPage;
	public $orderBy;
	public $order;
	public $canEdit;
	public $hasFullControl;
	public $shorthandDateFormat;
	public $globalSettings;
	function __construct($handlerUserId ){
		$this->perPage = 10;
		$this->handlerUserId = $handlerUserId;
		
		$this->hasFullControl = Booki_Helper::hasAdministratorPermission();
		$this->canEdit = Booki_Helper::hasEditorPermission();
		$this->globalSettings = Booki_Helper::globalSettings();
		$this->shorthandDateFormat = $this->globalSettings->getServerFormatShorthandDate();
		
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol = $localeInfo['currencySymbol'];
		$this->uniqueNamespace = 'booki_editor_approved_';
		
        parent::__construct( array(
            'singular'  => 'order', 
            'plural'    => 'orders', 
            'ajax'      => false    
        ) );
    }
	
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );
		$orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		if($item['id'] == $orderId){
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
	function column_orderDate($item){
		return $item['orderDate']->format($this->shorthandDateFormat);
	}
	function column_paymentDate($item){
		return $item['paymentDate'] ? $item['paymentDate']->format($this->shorthandDateFormat) : '--';
	}
	function column_discount($item){
		return $item['discount'] . '%';
	}
	
	function column_totalAmount($item){
		$result = sprintf('<span title="%s">%s</span>'
			, __('Total amount paid.', 'booki')
			, $this->currencySymbol . Booki_Helper::toMoney($item['totalAmount'])
		);
		if($item['refundAmount']){
			$result .= sprintf('<span title="%s"> - %s</span>'
				, __('Refunded amount.', 'booki')
				, $this->currencySymbol . Booki_Helper::toMoney($item['refundAmount'])
			);
		}
		return $result;
	}
	
    function column_status($item){
		$span = '<span class="label %1$s" title="%2$s">%3$s</span>';
		$result = '';
		if($item['status'] == Booki_PaymentStatus::PAID){
			$result = sprintf(
				$span
				, 'label-success'
				, __('Order has been paid. Send a confirmation.', 'booki')
				, __('Paid','booki')
			);
		}else if ($item['status'] == Booki_PaymentStatus::REFUNDED){
			$result = sprintf(
				$span
				, 'label-warning'
				, __('Payment has been refunded.', 'booki')
				, __('Refunded','booki')
			);
		}else if ($item['status'] == Booki_PaymentStatus::PARTIALLY_REFUNDED){
			$result = sprintf(
				$span
				, 'label-warning'
				, __('Payment has been partially refunded. You can refund the remainder by clicking refund.', 'booki')
				, __('Partially Refunded','booki')
			);
		}else{
			//unpaid
			$result = sprintf(
				$span
				, 'label-info'
				, __('Not yet paid. Try sending an invoice.', 'booki')
				, __('Pending','booki')
			);
		}
		
		if($item['invoiceNotification']){
			if($result){
				$result .= '<br/>';
			}
			$result .= sprintf(
				$span
				, 'label-success'
				, __('Number of invoices emailed.', 'booki')
				, __('Invoice', 'booki') . ' [' . $item['invoiceNotification'] . ']'
			);
		}
		
		if($item['refundNotification']){
			if($result){
				$result .= '<br/>';
			}
			$result .= sprintf(
				$span
				, 'label-success'
				, __('Number of refund notifications emailed.', 'booki')
				, __('Refund', 'booki') . ' [' . $item['refundNotification'] . ']'
			);
		}
        return $result;
    }
    
	function column_token($item){
		$fields = array();
		$buttonGroups = array();
		$selectUrl = add_query_arg(array('orderid'=>$item['id'], 'timezone'=>false));
		array_push($buttonGroups, sprintf(
			'<a class="manage-order-item btn btn-default" href="%s">
				<i class="glyphicon glyphicon-ok-circle"></i> 
				%s
			</a>'
			, $selectUrl
			, __('Select', 'booki')
		));

		
		if ($item['status'] == Booki_PaymentStatus::UNPAID && $this->canEdit){
			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="invoiceNotification" value="%s" title="%s">
						<i class="glyphicon glyphicon-envelope"></i> 
						%s
					</button>
				</li>'
				, $item['id']
				, __('Sends an invoice to the client with payment instructions.', 'booki')
				, __('Invoice', 'booki')
			));
		}else if ($item['status'] == Booki_PaymentStatus::REFUNDED && $this->canEdit){
			array_push($fields, sprintf('<input type="hidden" name="refundAmount" value="%s"/>', $item['refundAmount']));

			array_push($buttonGroups, sprintf(
				'<li>
					<button class="booki-btnlink btn btn-default" name="refundNotification" value="%s" title="%s">
						<i class="glyphicon glyphicon-envelope"></i> 
						%s
					</button>
				</li>'
				, $item['id']
				, __('Sends a refund confirmation email to user.', 'booki')
				, __('Refunded', 'booki')
			));
		}
		$buttons = join("\n", $buttonGroups);

		if(count($buttonGroups) > 1){
			$buttons = '<div class="btn-group">';
			$buttons .= $buttonGroups[0];
			unset($buttonGroups[0]);
			$buttons .= sprintf('<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" role="menu">
				%s
			</div>
			</ul>', join("\n", $buttonGroups));
			$buttons .= '</div>';
		}

        return sprintf(
			'<form class="form-horizontal" action="%s" method="post">
				<input type="hidden" name="controller" value="booki_managebookings" />
				%s
				<div class="form-group">
					<div class="grid-btn-group">
						%s
					</div>
				</div>
			</form>'
			, $_SERVER['REQUEST_URI']
			, join( "\n", $fields)
			, $buttons
        );
	}
	
    function get_columns(){
        $columns = array(
            'id'=>__('#', 'booki')
			, 'orderDate'=>__('Order Date', 'booki')
			, 'paymentDate'=>__('Payment Date', 'booki')
			, 'totalAmount'=>__('Amount', 'booki')
			, 'discount'=>__('Discount', 'booki')
			, 'status'=>__('Status', 'booki')
			, 'token'=>__('Task', 'booki')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', false)
            , 'orderDate'=> array('orderDate', true)
			, 'totalAmount'=>array('totalAmount', false)
			, 'discount'=>array('discount', false)
			, 'status'=>array('status', false)
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
		$fromDate = null;
		$toDate = null;
		$userId = null;
		$status = null;
		if (array_key_exists('controller', $_GET) && $_GET['controller'] == 'booki_managebookings'){
			
			if (array_key_exists('from', $_GET) && array_key_exists('to', $_GET)){
				$fromDate = new Booki_DateTime($_GET['from']);
				$toDate = new Booki_DateTime($_GET['to']);
			}
			
			if (array_key_exists('status', $_GET)){
				$status = (int)$_GET['status'];
			}
		}

		//status missing, fix.
		$orderRepository = new Booki_OrderRepository();

        $result = $orderRepository->readAllApprovedByHandlerUser($this->handlerUserId, $this->currentPage, $this->perPage, $this->orderBy, $this->order, $fromDate, $toDate, $status);
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