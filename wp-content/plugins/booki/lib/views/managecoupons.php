<?php
require_once  dirname(__FILE__) . '/../domainmodel/entities/Coupon.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/CouponRepository.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/ProjectRepository.php';
require_once  dirname(__FILE__) . '/../infrastructure/ui/lists/ManageCouponList.php';
require_once  dirname(__FILE__) . '/../controller/ManageCouponsController.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
class Booki_ManageCoupons{
	public $couponList;
	public $couponId;
	public $coupon;
	public $operation;
	public $pageIndex;
	public $totalPages;
	public $csvUrl;
	public $projects;
	public $hasError;
	function __construct( ){
	
		$this->couponId = isset($_GET['couponid']) ? (int)$_GET['couponid'] : null;
		new Booki_ManageCouponsController(array($this, 'create'), array($this, 'update'), array($this, 'delete'), array($this, 'sendEmail'));
		
		if($this->couponId !== null){
			$couponRepository = new Booki_CouponRepository();
			$this->coupon = $couponRepository->read($this->couponId);
		}
		
		if(!$this->coupon){
			$oneMonthLater = new Booki_DateTime();
			$oneMonthLater->modify('+30 day');
			$this->coupon = new Booki_Coupon(array('expirationDate'=>$oneMonthLater->format(BOOKI_DATEFORMAT)));
		}
		
		$this->couponList = new Booki_ManageCouponList();
		$this->couponList->bind();
		
		$this->pageIndex = $this->couponList->currentPage;
		$this->totalPages = $this->couponList->totalPages;
		$csvUrlHandlers = Booki_Helper::handlerUrls();
		$this->csvUrl = $csvUrlHandlers->couponsCsvHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($this->csvUrl);
		
		$this->csvUrl .= $delimiter . 'perpage=' . $this->couponList->perPage;
		$this->csvUrl .= '&orderby=' . $this->couponList->orderBy;
		$this->csvUrl .= '&order=' . $this->couponList->order;
		$this->csvUrl .= '&pageindex=';

		$projectRepo = new Booki_ProjectRepository();
		$this->projects = $projectRepo->readAll();
	}
	function create($result){
		$this->operation = $result === false ? 'failed' :'created';
	}
	function update($result){ 
		$this->operation =  'updated';
	}
	function delete($result){
		$this->couponId = null;
		$this->operation =  'deleted';
	}
	function sendEmail($result){ 
		$this->operation = 'emailed';
	}
}
$_Booki_ManageCoupons = new Booki_ManageCoupons();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Manage coupons', 'booki') ?></h4>
			<p><?php echo __('Create coupons and email them to a user. 
				If you prefer to mass create and email coupons then get on the "users" page. 
				You\'ll be able to export users and coupons to MailChimp.', 'booki') ?> </p>
		</div>
	</div>
	<?php if(isset($_GET['command']) && $_GET['command'] === 'email'):?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-info">
				<h4><?php echo __('Email coupon', 'booki') ?></h4>
			</div>
			<form id="userinfo" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/coupons.php" ?>" method="post">
				<input type="hidden" name="controller" value="booki_managecoupons" />
				<input type="hidden" name="userid" />
				<div class="form-group">
					<?php require dirname(__FILE__) . '/partials/userinfo.php'?>
					<div class="clearfix"></div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<button class="btn btn-primary" name="booki_email" value="<?php echo $_GET['couponid']?>">
							<i class="glyphicon glyphicon-envelope"></i>
							<?php echo __('Send Email', 'booki') ?>
							<span class="badge">#<?php echo $_GET['couponid']?></span>
						</button>
					</div>
					<div class="clearfix"></div>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
	<div class="booki col-lg-12">
	
		<?php if ($_Booki_ManageCoupons->operation === 'failed'): ?>
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo __('There was a problem creating the coupon. Most likely the coupon code already exists, try a unique combination.', 'booki') ?>
		</div>
		<?php elseif ($_Booki_ManageCoupons->operation === 'created'): ?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo __('The coupon was created successfully.', 'booki') ?>
		</div>
		<?php elseif($_Booki_ManageCoupons->operation === 'updated'): ?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo __('The coupon was updated successfully.', 'booki') ?>
		</div>
		<?php elseif($_Booki_ManageCoupons->operation === 'deleted'): ?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo __('The coupon was deleted successfully.', 'booki') ?>
		</div>
		<?php elseif($_Booki_ManageCoupons->operation === 'emailed'): ?>
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo __('The coupon was emailed successfully.', 'booki') ?>
		</div>
		<?php endif; ?>
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Create new coupons', 'booki') ?></h4>
				<p><?php echo __('Create new coupons or edit an existing coupon. If a project is selected then the coupon will apply for that project only. Note: If user adds bookings from more than one project in cart, the coupon wont apply.', 'booki') ?></p>
			</div>
			<form class="form-horizontal" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/coupons.php" ?>" method="post">
				<input type="hidden" name="controller" value="booki_managecoupons" />
				<div class="form-group">
					<label class="col-lg-4 control-label" for="projectId">
						<?php echo __('Project', 'booki') ?>
					</label>
					<div class="col-lg-8">
						<select name="projectId" 
							id="projectId"
							class="form-control">
							<option value="-1" <?php echo $_Booki_ManageCoupons->coupon->projectId == -1 ? 'selected' : '' ?>><?php echo __('Coupon applies to any project', 'booki')?></option>
							<?php foreach($_Booki_ManageCoupons->projects as $project):?>
							<option value="<?php echo $project->id?>" <?php echo $_Booki_ManageCoupons->coupon->projectId == $project->id ? 'selected' : '' ?>><?php echo $project->name?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="expirationdate"><?php echo __('Expiration Date', 'booki')?></label>
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
				<?php if ($_Booki_ManageCoupons->couponId === null):?>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<div class="booki-callout booki-callout-info">
							<h4><?php echo __('Attention: Coupon code', 'booki') ?></h4>
							<p><?php echo __('By default a GUID is generated for the coupon code. This has the benefit of being unique and you will avoid clashing with other coupons this way. If you decide to change the coupon code, eg: 10%OFF or something similar this is also possible, but to reuse the same name you will need to delete the older one.', 'booki') ?></p>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="code"><?php echo __('Coupon code', 'booki')?></label>
					<div class="col-lg-8">
					  <input type="text" 
							id="code" 
							name="code" 
							class="form-control"
							data-parsley-required="true"
							data-parsley-trigger="change" 
							value="<?php echo $_Booki_ManageCoupons->coupon->code ? $_Booki_ManageCoupons->coupon->code : sha1(uniqid(mt_rand(), true))?>">
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<div class="booki-callout booki-callout-info">
							<h4><?php echo __('Attention: Coupon type', 'booki') ?></h4>
							<p><?php echo __('A regular coupon is limited to one use only. After that one use i.e. after the user checks out using this code, it is no longer valid. A super coupon on the other hand will allow multiple uses until the expiration date.', 'booki') ?></p>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="couponType"><?php echo __('Coupon Type', 'booki')?></label>
					<div class="col-lg-8">
					  <select 
							id="coupontype" 
							name="coupontype" 
							class="form-control">
							<option value="0" <?php echo $_Booki_ManageCoupons->coupon->couponType === 0 ? "selected" : "" ?>>Regular</option>
							<option value="1" <?php echo $_Booki_ManageCoupons->coupon->couponType === 1 ? "selected" : "" ?>>Super</option>
						</select>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="discount"><?php echo __('Discount', 'booki')?></label>
					<div class="col-lg-8">
						<div class="input-group">
						  <input type="text" 
								id="discount" 
								name="discount" 
								class="form-control" 
								data-parsley-required="true"
								data-parsley-type="number"
								data-parsley-min="0.1"
								data-parsley-max="99.9"
								data-parsley-trigger="change" 
								data-parsley-errors-container="#discounterror"
								 value="<?php echo Booki_Helper::toMoney($_Booki_ManageCoupons->coupon->discount) ?>"><span class="input-group-addon">%</span>
						</div>
						<div class="clearfix"></div>
						<ul id="discounterror"></ul>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="orderminimum"><?php echo __('Order Minimum', 'booki')?></label>
					<div class="col-lg-8">
					  <input type="text" 
							id="orderminimum" 
							name="orderminimum" 
							class="form-control" 
							data-parsley-type="number"
							data-parsley-min="0"
							data-parsley-trigger="change" 
							value="<?php echo $_Booki_ManageCoupons->coupon->orderMinimum ?>">
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<div class="booki-callout booki-callout-info">
							<h4><?php echo __('Attention: No. of coupons', 'booki') ?></h4>
							<p><?php echo __('If you set a value greater than the default value of 1, coupon type will default to regular and the coupon code will be autogenerated internally.', 'booki') ?></p>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label" for="couponscount">
						<?php echo __('No. of coupons', 'booki')?>
						<i class="glyphicon glyphicon-info-sign"
							data-toggle="tooltip" 
							data-placement="top"
							data-original-title="Number of coupons to generate."></i>
					</label>
					<div class="col-lg-8">
					  <input type="text" 
							id="couponscount" 
							name="couponscount" 
							class="form-control" 
							data-parsley-type="digits"
							data-parsley-min="1"
							data-parsley-trigger="change"
							value="1">
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<button class="btn btn-default" name="booki_create">
							<i class="glyphicon glyphicon-ok"></i>
							<?php echo __('Create', 'booki') ?>
						</button>
						<button class="btn btn-primary" 
								name="booki_update" 
								value="<?php echo $_Booki_ManageCoupons->couponId ?>"
								<?php echo $_Booki_ManageCoupons->couponId === null ? "disabled=disabled" : "" ?>>
							<i class="glyphicon glyphicon-ok-circle"></i>
							<?php echo __('Update', 'booki')?>
							<?php if($_Booki_ManageCoupons->couponId !== null):?>
								<span class="badge badge-info">#<?php echo $_Booki_ManageCoupons->couponId?></span>
							<?php endif; ?>
						</button>
						<button class="btn btn-danger" 
								name="booki_delete" 
								value="<?php echo $_Booki_ManageCoupons->couponId ?>"  
								<?php echo $_Booki_ManageCoupons->couponId === null ? "disabled=disabled" : "" ?>>
							<i class="glyphicon glyphicon-trash"></i>
							<?php echo __('Delete', 'booki')?>
							<span class="badge">#<?php echo $_Booki_ManageCoupons->couponId?></span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Coupons', 'booki') ?></h4>
				<p><?php echo __('List of all coupons', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_ManageCoupons->couponList->display();?>
			</div>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="accordion-heading">
		  <a class="booki-search-heading accordion-toggle" data-toggle="collapse" href=".collapseAdvancedOptions">
			<strong><?php echo __('Advanced options', 'booki') ?></strong>
		  </a>
		</div>
		<div class="accordion-body">
			<div class="collapseAdvancedOptions collapse">
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
										  <?php echo __('All coupons', 'booki') ?>
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<div class="radio">
										<label>
										  <input type="radio" name="pageindex" value="<?php echo $_Booki_ManageCoupons->pageIndex ?>">
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
			</div>
		</div>
		<div class="clearfix booki-vertical-gap"></div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $datePicker = $('#expirationdate')
			, datepickerFormat = 'mm/dd/yy'
			, dateFormatString = 'MM/DD/YYYY'
			, selectedDate = moment('<?php echo $_Booki_ManageCoupons->coupon->expirationDate->format("Y-m-d")?>')
			, $pageIndexSelect = $('select[name="pageindex"]');
			
		$datePicker.datepicker(
			{
				'dateFormat': datepickerFormat
				, 'defaultDate': selectedDate._d
				, 'changeMonth': true
				, 'changeYear': true
		});
		
		$datePicker.datepicker('setDate', selectedDate.format(dateFormatString));
		
		$('[data-toggle=tooltip]').tooltip();
		
		$('#userinfo').BookiUserInfo({
			'ajaxUrl': '<?php echo admin_url('admin-ajax.php') ?>'
			, 'triggerButton': '[name="booki_email"]'
			, 'userIdField': '[name="userid"]'
			, "userNotFoundMessage": "<?php echo __('User not found. Try again.', 'booki') ?>"
			, "userFoundMessage": "<?php echo __('User found. Username is', 'booki') ?>"
		});
		
		$('.export-csv').click(function(){
			var pageIndex = $('[name="pageindex"]:checked').val()
				, redirectUrl = "<?php echo $_Booki_ManageCoupons->csvUrl ?>" + pageIndex;
			window.location.href = redirectUrl;
			return false;
		});
	});
</script>