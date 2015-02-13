<?php
require_once dirname(__FILE__) . '/../domainmodel/entities/User.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/ProjectRepository.php';
require_once dirname(__FILE__) . '/../domainmodel/repository/UserRepository.php';
require_once dirname(__FILE__) . '/../infrastructure/ui/lists/ManageUserList.php';
require_once dirname(__FILE__) . '/../infrastructure/ui/lists/UserOrderList.php';
require_once dirname(__FILE__) . '/../controller/MailChimpController.php';
require_once dirname(__FILE__) . '/../controller/ManageUsersController.php';
require_once dirname(__FILE__) . '/../controller/UserOrderHistoryController.php';
require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
require_once dirname(__FILE__) . '/../infrastructure/ui/BillSettlement.php';
require_once dirname(__FILE__) . '/../domainmodel/entities/ElementType.php';


class Booki_ManageUsers{
	public $userList;
	public $orderList;
	public $orderId = null;
	public $fromDate;
	public $toDate;
	public $user;
	public $singleOrderDetails = null;
	public $pageIndex;
	public $totalPages;
	public $csvUrl;
	public $mailingList;
	public $selectedMailingList;
	public $couponExpiryDate;
	public $mailChimpExportResult;
	public $projects;
	function __construct( ){
		$this->couponExpiryDate = new Booki_DateTime();
		$this->couponExpiryDate->modify('+30 day');
			
		$this->orderId = isset($_GET['orderid']) ? (int)$_GET['orderid'] : null;
		$userId = isset($_GET['userid']) ? (int)$_GET['userid'] : null;
		$this->fromDate = isset($_GET['from']) ? new Booki_DateTime($_GET['from']) : new Booki_DateTime();
		$this->toDate = isset($_GET['to']) ? new Booki_DateTime($_GET['to']) : new Booki_DateTime();

		if($userId !== null){
			$this->orderList = new Booki_UserOrderList($userId);
			$this->orderList->bind();
		}
		
		$this->userList = new Booki_ManageUserList();
		
		new Booki_ManageUsersController(array($this, 'delete'), array($this, 'export'), $this->userList->perPage);
		new Booki_UserOrderHistoryController(array($this, 'cancelAll'));
		
		if($this->orderId){
			$this->singleOrderDetails = new Booki_OrderDetails($this->orderId);
		}
		
		add_filter( 'booki_single_order_details', array($this, 'getSingleOrderDetails'));
		add_filter( 'booki_booked_form_elements', array($this, 'getBookedFormElements'));
		
		$this->userList->bind();
		
		$this->pageIndex = $this->userList->currentPage;
		$this->totalPages = $this->userList->totalPages;
		
		$csvUrlHandlers = Booki_Helper::handlerUrls();
		$this->csvUrl = $csvUrlHandlers->usersCsvHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($this->csvUrl);
		
		$this->csvUrl .= $delimiter . 'perpage=' . $this->userList->perPage;
		$this->csvUrl .= '&orderby=' . $this->userList->orderBy;
		$this->csvUrl .= '&order=' . $this->userList->order;
		$this->csvUrl .= '&pageindex=';
		
		new Booki_MailChimpController(
			array($this, 'readMailChimp')
			, array($this, 'refreshMailChimp')
			, array($this, 'exportMailChimp')
			, $this->userList->perPage
			, $this->userList->orderBy
			, $this->userList->order
		);
		
		$projectRepo = new Booki_ProjectRepository();
		$this->projects = $projectRepo->readAll();
	}
	
	function cancelAll($result){
	
	}
	
	function getSingleOrderDetails(){
		return $this->singleOrderDetails;
	}
	
	function getBookedFormElements(){
		return $this->singleOrderDetails->bookedFormElements;
	}
	public function readMailChimp($result){
		$this->mailingList = $result;
	}
	public function refreshMailChimp($result){
		$this->mailingList = $result;
	}
	public function exportMailChimp($result){
		$this->mailChimpExportResult = $result;
	}
	
	public function delete($result){ }
	function export($pageIndex){
		$this->pageIndex = $pageIndex;
	}
}
$_Booki_Users = new Booki_ManageUsers();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Users', 'booki') ?></h4>
			<p><?php echo __('List and manage users that made bookings.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<?php if($_Booki_Users->mailChimpExportResult): ?>
		<div class="alert alert-warning">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="mailchimp-status-body">
				<p><strong><?php echo __('MailChimp export status', 'booki')?></strong></p>
				<p><?php echo __('Emails added', 'booki') ?>: <strong><?php echo $_Booki_Users->mailChimpExportResult['add_count'] ?></strong></p>
				<ol class="horizontal-list">
				<?php foreach($_Booki_Users->mailChimpExportResult['adds'] as $item): ?>
					<li>
						<?php echo $item['email'] ?>
					</li>
				<?php endforeach; ?>
				</ol>
				<?php if($_Booki_Users->mailChimpExportResult['error_count'] > 0): ?>
				<p><?php echo __('Error count', 'booki') ?>: <strong><?php echo $_Booki_Users->mailChimpExportResult['error_count'] ?></strong></p>
				<ol class="horizontal-list">
				<?php foreach($_Booki_Users->mailChimpExportResult['errors'] as $item): ?>
					<li>
						<div><?php echo __('Email', 'booki') . ': ' . $item['email']['email']?></div>
						<div><?php echo __('Error msg', 'booki') . ': ' . $item['error']?></div>
					</li>
				<?php endforeach; ?>
				</ol>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php if(isset($_GET['command']) && $_GET['command'] === 'delete'):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-danger">
				<h4><?php echo __('Delete booking made by user', 'booki') ?></h4>
				<p><?php echo __('You are about to delete all bookings made by this user. This is permanent. Are you sure ?', 'booki') ?></p>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/users.php"?>" method="post">
			<input type="hidden" name="controller" value="booki_manageusers" />
			<div class="form-group">
				<div class="col-lg-8 col-lg-offset-4">
					<button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo __('Cancel', 'booki')?></button>
					<button class="btn btn-danger" value="<?php echo $_GET['selecteduserid']?>" name="booki_delete">
						<span class="badge">#<?php echo $_GET['selecteduserid']?></span>
						<?php echo __('Delete', 'booki')?>
					</button>
				</div>
			</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
	<div class="booki col-lg-12">
		<?php if($_Booki_Users->orderId): ?>
			<?php require dirname(__FILE__) . '/partials/bookingdetails.php' ?>
			<?php if($_Booki_Users->singleOrderDetails->bookedFormElements):?>
				<?php require dirname(__FILE__) .'/partials/bookedformelements.php' ?>
			<?php endif;?>	
		<?php endif;?>
	</div>
	<div class="booki col-lg-12">
		<?php if($_Booki_Users->orderList): ?>
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-info">
				<h4><?php echo __('User bookings', 'booki') ?></h4>
				<p><?php echo __('List of bookings made by selected user.', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_Users->orderList->display() ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Users', 'booki') ?></h4>
				<p><?php echo __('List of all users that made bookings.', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_Users->userList->display();?>
			</div>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Export', 'booki') ?></h4>
				<p><?php echo __('Export users/coupons to CSV or MailChimp', 'booki') ?></p>
			</div>
			<form class="form-horizontal" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" data-parsley-validate>
				<input type="hidden" name="controller" value="booki_mailchimp" />
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<div class="radio">
							<label>
								<input type="radio" name="pageindex" value="-1" checked>
								<?php echo __('All users', 'booki') ?>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<div class="radio">
							<label>
							  <input type="radio" name="pageindex" value="<?php echo $_Booki_Users->pageIndex ?>">
							   <?php echo __('The current records only', 'booki') ?>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="mailchimplist">
						<?php echo __('Mailing list', 'booki') ?>
						<i class="glyphicon glyphicon-question-sign help"
							data-toggle="tooltip" 
							data-placement="top" 
							data-original-title="<?php echo __('Exports users displayed in the users grid to MailChimp.', 'booki') ?>"></i>
					</label>
					<div class="col-lg-8">
						<select name="mailchimplist" class="form-control" <?php echo !$_Booki_Users->mailingList ? "disabled=disabled" : "" ?>>
							<?php if($_Booki_Users->mailingList): ?>
							<?php foreach($_Booki_Users->mailingList['data'] as $m): ?>
								<option value="<?php echo $m['id'] ?>" <?php echo $m['id'] === $_Booki_Users->selectedMailingList ? 'selected=selected' : '' ?>>
									<?php echo $m['name'] ?>
								</option>
							<?php endforeach;?>
							<?php else: ?>
								<option value="-1"><?php echo __('Select mailing list', 'booki') ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<?php if(!$_Booki_Users->mailingList): ?>
					<div class="alert alert-warning">
						 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p><strong><?php echo __('Attention', 'booki') ?></strong></p>
						<p><?php echo __('To export to MailChimp you\'ll have to first load the mailing list and select a list. 
						If you still see this alert, then double check your MailChimp API Key is set in "General settings" and verify if it is correct.', 'booki') ?> </p>
					</div>
				<?php else: ?>
				<div class="accordion-body">
					<div class="collapseCoupons collapse">
						<fieldset <?php echo !$_Booki_Users->mailingList ? "disabled" : "" ?>>
							<div class="form-group">
								<label class="col-lg-4 control-label" for="projectId">
									<?php echo __('Project', 'booki') ?>
								</label>
								<div class="col-lg-8">
									<select name="projectId" 
										id="projectId"
										class="form-control">
										<option value="-1"><?php echo __('Coupon applies to any project', 'booki')?></option>
										<?php foreach($_Booki_Users->projects as $project):?>
										<option value="<?php echo $project->id?>"><?php echo $project->name?></option>
										<?php endforeach;?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-4 control-label" for="expirationdate">
									<?php echo __('Expiration Date', 'booki')?>
									<small>(<?php echo __('Coupon', 'booki') ?>)</small>
								</label>
								<div class="col-lg-8">
									<div class="input-group">
										<input type="text" 
											id="expirationdate" 
											name="expirationdate" 
											class="booki-datepicker form-control" 
											readonly="true">
										<label class="input-group-addon" 
												for="expirationdate">
												<i class="glyphicon glyphicon-calendar"></i>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-4 control-label" for="discount">
									<?php echo __('Discount', 'booki')?>
									<small>(<?php echo __('Coupon', 'booki') ?>)</small>
									<i class="glyphicon glyphicon-question-sign help"
									data-toggle="tooltip" 
									data-placement="top" 
									data-original-title="<?php echo __('A discount value greater than 0 must be provided, otherwise no coupons are generated.', 'booki') ?>"></i>
								</label>
								<div class="col-lg-8">
									<div class="input-group">
									  <input type="text" 
											id="discount" 
											name="discount" 
											class="form-control" 
											data-parsley-type="number"
											data-parsley-trigger="change" 
											data-parsley-errors-container="#discounterror"><span class="input-group-addon">%</span>
									</div>
									<div id="discounterror"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-4 control-label" for="orderminimum">
									<?php echo __('Order Minimum', 'booki')?>
									<small>(<?php echo __('Coupon', 'booki') ?>)</small>
								</label>
								<div class="col-lg-8">
								  <input type="text" 
										id="orderminimum" 
										name="orderminimum" 
										class="form-control" 
										data-parsley-type="number"
										data-parsley-min="0"
										data-parsley-trigger="change"
										value="0">
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<button class="btn btn-primary export-csv">
							<i class="glyphicon glyphicon-file"
								data-toggle="tooltip" 
								data-placement="right" 
								data-original-title="<?php echo __('Export to CSV file', 'booki')?>"></i>
							<?php echo __('To CSV', 'booki') ?>
						</button>
						<button class="btn btn-primary export-mailchimp" name="export" <?php echo !$_Booki_Users->mailingList ? "disabled=disabled" : "" ?>>
							<i class="glyphicon glyphicon-file"
								data-toggle="tooltip" 
								data-placement="right" 
								data-original-title="<?php echo __('Export to MailChimp', 'booki')?>"></i>
							<?php echo __('To MailChimp', 'booki') ?>
						</button>
						<button class="btn btn-primary" name="refresh">
							<i class="glyphicon glyphicon-refresh"
								data-toggle="tooltip" 
								data-placement="top" 
								data-original-title="<?php echo __('Loads/reloads MailChimp mailing list in the dropdownlist above', 'booki') ?>"></i>
							<?php echo __('Load mailing lists', 'booki') ?>
						</button>
						<?php if($_Booki_Users->mailingList): ?>
						<a class="btn btn-default" data-toggle="collapse" href=".collapseCoupons">
							<i class="glyphicon glyphicon-tags"
								data-toggle="tooltip" 
								data-placement="top" 
								data-original-title="<?php echo __('Coupon settings', 'booki') ?>"></i>
							<?php echo __('Advanced', 'booki') ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
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
							<h3 class="panel-title"><?php echo __('Filter users by bookings made between', 'booki')?></h3>
						</div>
						<div class="panel-body">
							<div class="form-group">
								<label class="col-lg-4 control-label" for="fromdate"><?php echo __('From', 'booki') ?></label>
								<div class="col-lg-8">
									<div class="input-group">
										<input type="text" class="form-control" id="fromdate" name="fromdate" class="booki-datepicker" readonly="true">
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
										<input type="text" class="form-control" id="todate" name="todate" class="booki-datepicker" readonly="true">
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
							<h3 class="panel-title"><?php echo __('Find User by email', 'booki')?></h3>
						</div>
						<div class="panel-body">
							<form id="userinfo" action="<?php echo admin_url() . "admin.php?page=booki/users.php" ?>" method="post" data-parsley-validate>
								<input type="hidden" name="controller" value="booki_manageusers" />
								<input type="hidden" name="userid" />
									<div class="form-group">
										 <?php require dirname(__FILE__) . '/partials/userinfo.php'?>
									</div>
									<div class="clearfix"></div>
							</form>
						</div>
						<div class="panel-footer">
							<a class="btn btn-primary find-user" href="#">
								<i class="glyphicon glyphicon-search"></i>
								<?php echo __('Find', 'booki') ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix booki-vertical-gap"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var $datePicker = $('#expirationdate')
			, $fromDate = $('#fromdate')
			, $toDate = $('#todate')
			, $findUserButton = $('.find-user')
			, $userIdField = $('[name="userid"]')
			, $pageIndexSelect = $('select[name="pageindex"]')
			, $expirationDate = $('#expirationdate')
			, datepickerFormat = 'mm/dd/yy'
			, dateFormatString = 'MM/DD/YYYY'
			, selectedDate = moment().add('months', 1)
			, from = moment('<?php echo $_Booki_Users->fromDate->format('Y-m-d') ?>').format(dateFormatString)
			, to = moment('<?php echo $_Booki_Users->toDate->format('Y-m-d') ?>').format(dateFormatString)
			, url = "<?php echo admin_url() . "admin.php?page=booki/users.php" . "&controller=booki_manageusers" ?>";
		
		$datePicker.datepicker(
			{
				'dateFormat': datepickerFormat
				, 'defaultDate': selectedDate._d
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$datePicker.datepicker('setDate', selectedDate.format(dateFormatString));
		
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
		
		$('select[name="timezone"]').change(function(){
			var redirectUrl = "<?php echo remove_query_arg('timezone') ?>";
			redirectUrl += ('&timezone=' + encodeURIComponent($(this).find(':selected').val()) + '#timezone');
			window.location.href = redirectUrl;
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
		
		$('.filter-by-bookingdate').click(function(){
			var redirectUrl = url + '&from=' + encodeURIComponent($fromDate.val()) + '&to=' + encodeURIComponent($toDate.val());
			window.location.href = redirectUrl;
			return false;
		});
		
		$('.export-csv').click(function(){
			var pageIndex = $('[name="pageindex"]:checked').val()
				, redirectUrl = "<?php echo $_Booki_Users->csvUrl ?>" + pageIndex;
			window.location.href = redirectUrl;
			return false;
		});
		
		$('manage-order-item').BookiTimezoneControlState();
	});
</script>