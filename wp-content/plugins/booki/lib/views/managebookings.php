<?php
require_once  dirname(__FILE__) . '/../domainmodel/repository/ProjectRepository.php';
require_once  dirname(__FILE__) . '/../infrastructure/ui/lists/ManageOrderList.php';
require_once  dirname(__FILE__) . '/../controller/ManageBookingsController.php';
require_once  dirname(__FILE__) . '/../controller/RefundController.php';
require_once  dirname(__FILE__) . '/../controller/BookedDayController.php';
require_once  dirname(__FILE__) . '/../controller/BookedOptionalController.php';
require_once  dirname(__FILE__) . '/../controller/BookedCascadingItemController.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/ElementType.php';
require_once  dirname(__FILE__) . '/../domainmodel/entities/EmailType.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../infrastructure/ui/OrderDetails.php';

class Booki_ManageBookings{
	public $orderList;
	public $orderId = null;
	public $singleOrderDetails;
	public $refundResult;
	public $emailStatus;
	public $fromDate;
	public $toDate;
	public $status;
	public $pageIndex;
	public $totalPages;
	public $csvUrl;
	public $projects;
	public $hasFullControl;
	public $canEdit;
	public $onDelete;
	public $onAddUser;
	public $newUserCreated = null;
	function __construct( ){
		
		new Booki_RefundController(
			array($this, 'refunded')
		);
		
		new Booki_BookedDayController();
		new Booki_BookedOptionalController();
		new Booki_BookedCascadingItemController();
		
		$this->hasFullControl = Booki_Helper::hasAdministratorPermission();
		$this->canEdit = Booki_Helper::hasEditorPermission();
		
		$userId = isset($_GET['userid']) ? (int)$_GET['userid'] : null;
		$this->fromDate = isset($_GET['from']) ? new Booki_DateTime($_GET['from']) : new Booki_DateTime();
		$this->toDate = isset($_GET['to']) ? new Booki_DateTime($_GET['to']) : new Booki_DateTime();
		$this->status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
		$this->orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		
		$this->orderList = new Booki_ManageOrderList();
		
		new Booki_ManageBookingsController(
			array($this, 'refund')
			, array($this, 'delete')
			, array($this, 'addedUser')
			, array($this, 'registeredUser')
			, array($this, 'invoiceNotification')
			, array($this, 'refundNotification')
			, array($this, 'approveAll')
			, array($this, 'markPaid')
			, array($this, 'export')
			, $this->orderList->perPage
		);
		
		$this->orderList->bind();
		
		$this->pageIndex = $this->orderList->currentPage;
		$this->totalPages = $this->orderList->totalPages;
		$csvUrlHandlers = Booki_Helper::handlerUrls();
		$this->csvUrl = $csvUrlHandlers->bookingsCsvHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($this->csvUrl);
		
		$this->csvUrl .= $delimiter . 'perpage=' . $this->orderList->perPage;
		$this->csvUrl .= '&orderby=' . $this->orderList->orderBy;
		$this->csvUrl .= '&order=' . $this->orderList->order;
		$this->csvUrl .= '&pageindex=';
		
		$this->singleOrderDetails = new Booki_OrderDetails($this->orderId);
		
		add_filter( 'booki_refund_result', array($this, 'getRefundResult'));
		
		if($this->singleOrderDetails->order){
			add_filter( 'booki_single_order_details', array($this, 'getSingleOrderDetails'));
			add_filter( 'booki_booked_form_elements', array($this, 'getBookedFormElements'));
		}
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
		
		$projectRepo = new Booki_ProjectRepository();
		$this->projects = $projectRepo->readAll();
		
		$this->onDelete = (isset($_GET['command']) && $_GET['command'] === 'delete');
		$this->onAddUser = (isset($_GET['command']) && $_GET['command'] === 'adduser');
	}
	
	public function isBackEnd(){
		return true;
	}

	function getSingleOrderDetails(){
		return $this->singleOrderDetails;
	}
	
	function getBookedFormElements(){
		return $this->singleOrderDetails->bookedFormElements;
	}
	
	function delete($orderId){}
	function addedUser($isNewUser){
		$this->newUserCreated = $isNewUser;
	}
	function registeredUser($isNewUser){
		$this->newUserCreated = true;
	}
	function approveAll(){}
	function markPaid(){}
	function invoiceNotification($orderId, $result){
		//$this->orderId = $orderId;
		$this->emailStatus = $result ? 'success' : 'warning';
	}
	
	function refundNotification($orderId, $result){
		//$this->orderId = $orderId;
		$this->emailStatus = $result ? 'success' : 'warning';
	}
	
	function export($pageIndex){
		$this->pageIndex = $pageIndex;
	}
	
	function refunded($refundResult){
		$this->refundResult = $refundResult;
	}

	function getRefundResult(){
		return $this->refundResult;
	}
	
	function refund(){}
}
$_Booki_ManageBookings = new Booki_ManageBookings();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Manage Bookings', 'booki')?></h4>
			<p><?php echo __('Confirm bookings, manually create new bookings, send out invoices and issue refunds', 'booki') ?> </p>
		</div>
	</div>
	<?php if($_Booki_ManageBookings->emailStatus): ?>
	<div class="booki col-lg-12">
		<div class="alert alert-<?php echo $_Booki_ManageBookings->emailStatus ?>">
			 <button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong><?php echo ucfirst($_Booki_ManageBookings->emailStatus) ?>:</strong>
				<?php if($_Booki_ManageBookings->emailStatus == 'success'): ?>
				<?php echo __('No errors were encountered while sending the email.', 'booki') ?>
				<?php else: ?>
				<?php echo __('An error was encountered while sending the email. 
								Ensure that sending emails works with your wordpress 
								instance and then try again.', 'booki') ?>
				<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if($_Booki_ManageBookings->onDelete):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-danger">
				<h4><?php echo __('Delete booking', 'booki') ?></h4>
				<p><?php echo __('You are about to delete a booking. Are you sure ?', 'booki') ?></p>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/managebookings.php"?>" method="post">
			<input type="hidden" name="controller" value="booki_managebookings" />
			<div class="form-group">
				<div class="col-lg-8 col-lg-offset-4">
					<button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo __('Cancel', 'booki')?></button>
					<button class="btn btn-danger" value="<?php echo $_GET['orderid']?>" name="delete">
						<span class="badge">#<?php echo $_GET['orderid']?></span>
						<?php echo __('Delete', 'booki')?>
					</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
	<?php if($_Booki_ManageBookings->newUserCreated !== null):?>
	<div class="booki col-lg-12">
		<div class="alert alert-warning">
			 <button type="button" class="close" data-dismiss="alert">&times;</button>
			 <p>
				<?php echo $_Booki_ManageBookings->newUserCreated ? 
					__('A new user was created and the login credentials were emailed to the user.', 'booki') :
					__('User was found in system.', 'booki')
					. __('Booking is now owned by the user and is also available in the users history page.', 'booki');
				?>
			</p>
		</div>
	</div>
	<?php endif; ?>
	<?php if($_Booki_ManageBookings->onAddUser):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-warning">
				<h4><?php echo __('Assign ownership of booking to user', 'booki')?></h4>
				<p>
					<?php echo __('Provide an email address. If user is not already registered, 
						then a new user is created and login credentials are emailed to the provided email address. The booking will be owned by this user where they can view the booking in their history page.', 'booki')?>
				</p>
			</div>
			<form class="adduserinfo" action="<?php echo admin_url() . "admin.php?page=booki/managebookings.php" ?>" method="post" data-parsley-validate>
				<input type="hidden" name="controller" value="booki_managebookings" />
				<div class="panel-body">
					<div class="form-group">
						 <label class="col-lg-4 control-label" for="adduseremail">
							<?php echo __('User email', 'booki') ?>
						</label>
						<div class="col-lg-8">
							<input type="text" 
									id="adduseremail"
									class="form-control"
									name="adduseremail" 
									data-parsley-required="true" 
									data-parsley-type="email" 
									data-parsley-trigger="change" />
						</div>
						 <div class="clearfix"></div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo __('Cancel', 'booki')?></button>
						<button class="btn btn-primary" name="adduser"  value="<?php echo $_GET['orderid']?>">
							<span class="badge">#<?php echo $_GET['orderid']?></span>
							<?php echo __('Add user', 'booki') ?>
						</button>
					</div>
					<div class="clearfix"></div>
				</div>
			</form>
		</div>
	</div>
	<?php endif;?>
	<div class="booki col-lg-12">
		<?php if($_Booki_ManageBookings->hasFullControl): ?>
		 <?php require dirname(__FILE__) . '/partials/refundtransaction.php' ?>
		 <?php endif; ?>
		<?php if($_Booki_ManageBookings->orderId !== null): ?>
			<?php require dirname(__FILE__) . '/partials/bookingdetails.php' ?>
			<?php if($_Booki_ManageBookings->singleOrderDetails->order && $_Booki_ManageBookings->singleOrderDetails->bookedFormElements->count()):?>
				<?php require dirname(__FILE__) .'/partials/bookedformelements.php' ?>
			<?php endif; ?>
		<?php endif;?>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-info">
				<h4><?php echo __('New bookings', 'booki') ?></h4>
				<p><?php echo __('Create new bookings manually and email them to a new or existing user', 'booki') ?></p>
			</div>
			<div>
				<select name="projectid" class="form-control">
					<option value="-1"><?php echo __('Select a project', 'booki') ?></option>
					<?php foreach($_Booki_ManageBookings->projects as $project): ?>
						<option value="<?php echo $project->id ?>"><?php echo $project->name ?></option>
					<?php endforeach;?>
				</select>
			</div>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Bookings', 'booki') ?></h4>
				<p><?php echo __('Listing of all bookings made', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_ManageBookings->orderList->display();?>
			</div>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="accordion-heading">
		  <a class="booki-search-heading accordion-toggle" data-toggle="collapse" href=".collapseSearch">
			<strong><?php echo __('Advanced options', 'booki') ?></strong>
		  </a>
		</div>
		<div class="accordion-body">
			<div class="collapseSearch collapse">
				<div class="booki-content-box">
					<div class="form-horizontal panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo __('Filter by Booking Date', 'booki')?></h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<label class="col-lg-4 control-label" for="fromdate"><?php echo __('From', 'booki') ?></label>
								<div class="col-lg-8">
									<div class="input-group">
										<input type="text" id="fromdate" name="fromdate" class="booki-datepicker form-control" readonly="true">
										<label class="input-group-addon" 
												for="fromdate">
												<i class="glyphicon glyphicon-calendar"></i>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-4 control-label" for="todate"><?php echo __('To', 'booki') ?></label>
								<div class="col-lg-8">
									<div class="input-group">
										<input type="text" id="todate" name="todate" class="booki-datepicker form-control" readonly="true">
										<label class="input-group-addon" 
												for="todate">
												<i class="glyphicon glyphicon-calendar"></i>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<a class="btn btn-primary filter-by-bookingdate" href="#">
								<i class="glyphicon glyphicon-filter"></i>
								<?php echo __('Filter', 'booki') ?>
							</a>
						</div>
					</div>
				</div>
				<div class="booki-content-box">
					<div class="form-horizontal panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo __('Filter by status', 'booki')?></h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
											<input type="radio" name="status" value="-1" 
												<?php echo $_Booki_ManageBookings->status === -1 ? 'checked="checked"' : '' ?>> <?php echo __('No filter', 'booki')?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
											<input type="radio" name="status" value="0"
											 <?php echo $_Booki_ManageBookings->status === 0 ? 'checked="checked"' : '' ?>> <?php echo __('Pending (Unpaid)', 'booki')?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
											<input type="radio" name="status" value="1"
											 <?php echo $_Booki_ManageBookings->status === 1 ? 'checked="checked"' : '' ?>> <?php echo __('Paid', 'booki')?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
											<input type="radio" name="status" value="2"
											 <?php echo $_Booki_ManageBookings->status === 2 ? 'checked="checked"' : '' ?>> <?php echo __('Refunded', 'booki')?>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="booki-content-box">
					<div class="form-horizontal panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo __('Find bookings by user', 'booki')?></h3>
						</div>
						<form id="userinfo" action="<?php echo admin_url() . "admin.php?page=booki/managebookings.php" ?>" method="post" data-parsley-validate>
							<div class="panel-body">
								<input type="hidden" name="userid" />
								<div class="form-group">
									 <?php require dirname(__FILE__) . '/partials/userinfo.php'?>
									 <div class="clearfix"></div>
								</div>
							</div>
							<div class="panel-footer">
								<a class="btn btn-primary find-user" href="#">
									<i class="glyphicon glyphicon-search"></i>
									<?php echo __('Find', 'booki') ?>
								</a>
							</div>
						</form>
					</div>
				</div>
				<?php if($_Booki_ManageBookings->hasFullControl): ?>
				<div class="booki-content-box">
					<div class="form-horizontal panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo __('Export', 'booki')?></h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
										  <input type="radio" name="pageindex" value="-1" checked>
										  <?php echo __('All bookings', 'booki') ?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
										  <input type="radio" name="pageindex" value="<?php echo $_Booki_ManageBookings->pageIndex ?>">
										   <?php echo __('The current records only', 'booki') ?>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<button type="button" class="btn btn-primary export-csv">
								<i class="glyphicon glyphicon-file" 
								data-toggle="tooltip" 
								data-placement="right" 
								data-original-title="<?php echo __('Export to CSV file', 'booki')?>"></i>
								<?php echo __('CSV', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="clearfix booki-vertical-gap"></div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $fromDate = $('#fromdate')
			, $toDate = $('#todate')
			, $findUserButton = $('.find-user')
			, $userIdField = $('[name="userid"]')
			, $statusOptions = $('[name="status"]')
			, $pageIndexSelect = $('select[name="pageindex"]')
			, dateFormatString = 'MM/DD/YYYY'
			, datepickerFormat = 'mm/dd/yy'
			, from = moment('<?php echo $_Booki_ManageBookings->fromDate->format('Y-m-d') ?>').format(dateFormatString)
			, to = moment('<?php echo $_Booki_ManageBookings->toDate->format('Y-m-d') ?>').format(dateFormatString)
			, url = "<?php echo admin_url() . "admin.php?page=booki/managebookings.php&controller=booki_managebookings" ?>";
		
		$fromDate.val(from);
		$toDate.val(to);
		
		$fromDate.datepicker({
				'defaultDate': from._d
				, 'dateFormat': datepickerFormat
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$toDate.datepicker({
				'defaultDate': to._d
				, 'dateFormat': datepickerFormat
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$('.filter-by-bookingdate').click(function(){
			var redirectUrl = url + '&from=' + encodeURIComponent($fromDate.val()) + '&to=' + encodeURIComponent($toDate.val());
			window.location.href = redirectUrl;
			return false;
		});
		
		$('#userinfo').BookiUserInfo({
			'ajaxUrl': '<?php echo admin_url('admin-ajax.php') ?>'
			, 'triggerButton': $findUserButton
			, 'userIdField': $userIdField
			, "userNotFoundMessage": "<?php echo __('User not found. Try again.', 'booki') ?>"
			, "userFoundMessage": "<?php echo __('User found. Username is', 'booki') ?>"
			, 'success': function(){
				var redirectUrl = url + '&userid=' + $userIdField.val();
				window.location.href = redirectUrl;
				return false;
			}
		});

		$statusOptions.change(function(){
			var value = parseInt($(this).val(), 10)
				, redirectUrl = url + '&status=' + value;
			if(value === -1){
				redirectUrl = url;
			}
			window.location.href = redirectUrl;
		});
		
		$('.export-csv').click(function(){
			var pageIndex = $('[name="pageindex"]:checked').val()
				, redirectUrl = "<?php echo $_Booki_ManageBookings->csvUrl ?>" + pageIndex;
			window.location.href = redirectUrl;
			return false;
		});
		
		<?php if($_Booki_ManageBookings->orderId):?>
		$('select[name="timezone"]').change(function(){
			var redirectUrl = "<?php echo admin_url() . "admin.php?page=booki/managebookings.php" ?>"
				, orderId = "&orderid=<?php echo  $_Booki_ManageBookings->orderId ?>";
			redirectUrl += (orderId + "&timezone=" + encodeURIComponent($(this).find(":selected").val()) + "#timezone");
			window.location.href = redirectUrl;
		});
		<?php endif; ?>
		
		$('select[name="projectid"]').change(function(){
			var $this = $(this)
				, selectedValue = $this.find(":selected").val()
				, redirectUrl = "<?php echo admin_url() . "admin.php?page=booki/createbookings.php" ?>";
			redirectUrl += ("&projectid=" + selectedValue);
			if(selectedValue !== '-1'){
				window.location.href = redirectUrl;
			}
		});
	});
</script>