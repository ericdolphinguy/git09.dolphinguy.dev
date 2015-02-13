<?php
require_once dirname(__FILE__) . '/../controller/CreateBookingController.php';
require_once dirname(__FILE__) . '/../domainmodel/repository/CalendarRepository.php';
require_once dirname(__FILE__) . '/../domainmodel/service/BookingProvider.php';
require_once dirname(__FILE__) . '/../infrastructure/utils/DateHelper.php';
class Booki_CreateBookings{
	public $projectId;
	public $errors = array();
	public $projects;
	public $bookingPeriodValid = false;
	public $hasAvailableBookings = false;
	public $projectCreatedSuccess;
	function __construct( ){
		$this->projectId = isset($_GET['projectid']) ? (int)$_GET['projectid'] : -1;
		
		new Booki_CreateBookingController(array($this, 'onCreated'));
		add_filter( 'booki_is_backend', array($this, 'isBackEnd'));
		add_filter( 'booki_shortcode_id', array($this, 'shortCodeId'));
		
		if($this->projectId !== -1){
			$calendarRepository =  new Booki_CalendarRepository();
			$calendar = $calendarRepository->readByProject($this->projectId);
			if(!$calendar){
				return;
			}
			$this->bookingPeriodValid = Booki_DateHelper::todayLessThanOrEqualTo($calendar->endDate);
			if($this->bookingPeriodValid){
				$this->hasAvailableBookings = Booki_BookingProvider::hasAvailability($this->projectId);
			}
		}
	}
	public function isBackEnd(){
		return true;
	}
	public function shortCodeId(){
		return $this->projectId;
	}
	
	public function onCreated($projectId, $errors){
		$this->projectId = $projectId;
		$this->errors = $errors;
		if(count($errors) === 0){
			$this->projectCreatedSuccess = true;
		}
	}
}
$_Booki_CreateBookings = new Booki_CreateBookings();
?>
<div class="booki">
	<div class="booki-vertical-gap-xs"></div>
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Create bookings', 'booki')?></h4>
			<p><?php echo __('Create bookings manually and email them to user.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
	<?php if($_Booki_CreateBookings->projectId > -1) : ?>
		<?php if($_Booki_CreateBookings->projectCreatedSuccess):?>
			<div class="alert alert-success">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<?php echo __('The booking was created successfully and an email was sent.', 'booki') ?>
			</div>
		<?php endif; ?>
		<?php if($_Booki_CreateBookings->errors && count($_Booki_CreateBookings->errors) > 0):?>
			<div class="alert alert-danger">
				<?php foreach($_Booki_CreateBookings->errors as $key=>$value):?>
					<div><strong><?php echo $key?></strong>: <?php echo $value ?></div>
				<?php endforeach;?>
			</div>
		<?php endif; ?>
		</div>
		<div class="clearfix"></div>
		<form class="booki form-horizontal booki-form-elements" id="booki_<?php echo $_Booki_CreateBookings->projectId ?>_form"
					name="booki_<?php echo $_Booki_CreateBookings->projectId ?>_form"
					action="<?php echo admin_url() . "admin.php?page=booki/createbookings.php" ?>" data-parsley-validate method="post">
			<input type="hidden" name="projectid" value="<?php echo $_Booki_CreateBookings->projectId ?>" />
			<div class="booki col-lg-12">
				<div class="booki-content-box">
				<?php if($_Booki_CreateBookings->projectId > -1 && $_Booki_CreateBookings->hasAvailableBookings): ?>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<div class="booki-callout booki-callout-info">
							<p>
							<?php echo __('The booking will be emailed to the user email provided below. If payments are enabled then an invoice is emailed along with payment instructions.
							If user is not already registered, then a new user is also created and login credentials along with invoice are emailed.', 'booki')?>
							</p>
						</div>
					</div>
					<?php require dirname(__FILE__) . '/partials/userinfo.php'?>
					<div class="clearfix"></div>
				</div>
				<?php endif; ?>
				</div>
				<?php if($_Booki_CreateBookings->projectId > -1): ?>
				<div class="booki-content-box">
					<div class="form-group">
						<div class="col-lg-12">
						<?php if($_Booki_CreateBookings->hasAvailableBookings): ?>
								<input type="hidden" name="booki_add_new_booking" />
								<?php include_once('templates/bookingwizard.php') ?>
						<?php else: ?>
							<div class="alert alert-warning">
								<?php echo __('Whoops! No more bookings available for selected project.', 'booki') ?>
							</div>
						<?php endif; ?>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php endif; ?>
			</div>
		</form>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#userinfo').BookiUserInfo({
					"ajaxUrl": "<?php echo admin_url('admin-ajax.php') ?>"
					, "userNotFoundMessage": "<?php echo __('Email not found. A new user will be created and the booking emailed along with the user account credentials.', 'booki') ?>"
					, "userFoundMessage": "<?php echo __('User email found. Username is', 'booki') ?>"
				});
				$('[data-toggle=tooltip]').tooltip();
			});
		</script>
	<?php endif; ?>
</div>