<?php
require_once  dirname(__FILE__) . '/base/List.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/entities/PaymentStatus.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/repository/OrderRepository.php';
require_once  dirname(__FILE__) . '/../../../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../../utils/Helper.php';

class Booki_UserOrderList extends Booki_List {
	private $userId;
	public $perPage;
	public $orderBy;
	public $order;
	private $currency;
	private $currencySymbol;
	private $globalSettings;
  function __construct($userId = null){
		if(Booki_Helper::userHasRole('administrator') && $userId != null){
			$this->userId = $userId;
		}else{
			$currentUser = wp_get_current_user();
			$this->userId = $currentUser->ID;
		}
		
		$this->globalSettings = Booki_Helper::globalSettings();
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol =	$localeInfo['currencySymbol'];
		
		$this->uniqueNamespace = 'booki_userorder_';
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
		$result = sprintf('<p><span>%s</span></p>'
			, $item['id'] 
		);
		return $result;
	}
	
	function column_tasks($item){
		$buttonGroups = array();
		$selectUrl = add_query_arg(array('orderid'=>$item['id'], 'timezone'=>false));
		$order = Booki_BookingProvider::read($item['id']);
		$paymentUrl = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_HANDLER);
		$delimiter = Booki_Helper::getUrlDelimiter($paymentUrl);
		$paymentUrl .= $delimiter . 'orderid=' . $item['id'];
		$enablePayments = $item['status'] === Booki_PaymentStatus::UNPAID && $this->globalSettings->enablePayments;
		$hasDropDown = false;
		$enableCancel = false;
		if($this->globalSettings->enableUserCancelBooking){
			foreach($order->bookedDays as $day){
				if($day->status === Booki_BookingStatus::PENDING_APPROVAL){
					$enableCancel = true;
					break;
				}
			}
			if(!$enableCancel){
				foreach($order->bookedOptionals as $optional){
					if($optional->status === Booki_BookingStatus::PENDING_APPROVAL){
						$enableCancel = true;
						break;
					}
				}
			}
			if(!$enableCancel){
				foreach($order->bookedCascadingItems as $cascadingItem){
					if($cascadingItem->status === Booki_BookingStatus::PENDING_APPROVAL){
						$enableCancel = true;
						break;
					}
				}
			}
		}
		
		
		array_push($buttonGroups, sprintf(
			'<a class="manage-order-item btn btn-default" href="%s">
				<i class="glyphicon glyphicon-ok-circle"></i> 
				%s
			</a>'
			, $selectUrl
			, __('Select', 'booki')
		));
		
		if($enablePayments || $enableCancel){
			array_push($buttonGroups, 
				'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="caret"></span>
					<span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul class="dropdown-menu" role="menu">'
			);
		}
		
		if($enablePayments){
			$totalAmount = Booki_Helper::calcDiscount($item['discount'], $item['totalAmount']);
			if($item['tax'] > 0){
				$totalAmount += Booki_Helper::percentage($item['tax'], $totalAmount);
			}
			array_push($buttonGroups, sprintf(
				'<li>
					<a href="%s" class="booki-btnlink btn btn-default">
						<i class="glyphicon glyphicon-ok"></i>
						%s %s <span class="label label-success">%s</span>
					</a>
				</li>'
				, $paymentUrl
				, __('Pay', 'booki')
				, $this->currencySymbol . Booki_Helper::toMoney($totalAmount) . ' ' . $this->currency
				, $item['discount'] > 0 ? ($item['discount'] . '%') : ''
			));
		}
		
		if($enableCancel){
			array_push($buttonGroups, sprintf(
				'<li>
					<form class="form-horizontal" action="%s" method="post">
						<input type="hidden" name="controller" value="booki_userorderhistory" />
						<button class="booki-btnlink btn btn-default" name="cancelAll" value="%s" title="%s">
							<i class="glyphicon glyphicon-thumbs-down"></i> 
							%s
						</button>
					</form>
				</li>'
				, $_SERVER['REQUEST_URI']
				, $item['id']
				, __('A cancel request is given for all items in this order.', 'booki')
				, __('Cancel', 'booki')
			));
		}
		
		if($enablePayments || $enableCancel){
			array_push($buttonGroups, '</ul>');
		}
		
        return sprintf(
			'<div class="form-group">
				<div class="grid-btn-group">
					<div class="btn-group">
						%s
					</div>
				</div>
			</div>'
			, join("\n", $buttonGroups)
        );
	}
	
	function column_orderDate($item){
		//can we take format from wordpress ?
		return $item['orderDate']->format(get_option('date_format'));
	}
	
    function column_status($item){
        $paymentStatus = __('Pending','booki');
		$label = 'label-info';
		if($item['status'] == Booki_PaymentStatus::PAID){
			$paymentStatus = __('Paid','booki');
			$label = 'label-success';
		}else if ($item['status'] == Booki_PaymentStatus::REFUNDED){
			$paymentStatus = __('Refunded','booki');
			$label = 'label-warning';
		}
        return sprintf('<span class="label %1$s">%2$s</span>',
			$label
			, $paymentStatus
        );
    }
    
    function get_columns(){
        $columns = array(
			'id'=>__('order id', 'booki')
			, 'orderDate'=>__('Date', 'booki')
			, 'status'=>__('Payment', 'booki')
			, 'tasks'=>__('Tasks', 'booki')
        );
		
        return $columns;
    }
    
    function get_sortable_columns() {
		//true means its already sorted
        $sortable_columns = array(
			'id'=>array('id', true)
            , 'orderDate'=> array('orderDate', false)
			, 'status'=>array('status', false)
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

		$orderRepository = new Booki_OrderRepository();

        $result = $orderRepository->readAll($current_page, $per_page, $this->orderBy, $this->order, null, null, $this->userId);
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