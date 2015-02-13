<?php
require_once dirname(__FILE__) . '/../controller/SettingsGlobalController.php';
require_once dirname(__FILE__) . '/../domainmodel/repository/SettingsGlobalRepository.php';
require_once dirname(__FILE__) . '/../domainmodel/entities/SettingGlobal.php';
require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_GeneralSettings{
	public $setting;
	public $themes;
	public $calendarThemes;
	public $adminUsersList;
	public $timezoneString;
	public $weekDays;
	public $shortDateFormats;
	private $settingsGlobalRepository;
	public function __construct(){
		$this->settingsGlobalRepository = new Booki_SettingsGlobalRepository();
		new Booki_SettingsGlobalController(
			array($this, 'create')
			, array($this, 'update')
			, array($this, 'delete')
		);
		
		$this->adminUsersList = get_users(array('fields'=>array('id','user_login'), 'role'=>'administrator'));

		$this->weekDays = $this->getWeekDays();
		$this->shortDateFormats = $this->getShortDateFormats();
		$this->render();
	}
	
	public function create(){
	}
	public function update(){
	}
	public function delete(){
	}
	
	protected function render(){
		$this->setting = $this->settingsGlobalRepository->read();
		if(!$this->setting){
			$this->setting = new Booki_SettingGlobal();
		}
		$themeRoot = get_stylesheet_directory() . '/booki/';
		if($this->setting->theme === '-1'){
			$calendarThemeRoot = dirname(__FILE__) . '/../../assets/css/jquery-ui/';
		}else{
			$calendarThemeRoot = get_stylesheet_directory() . '/booki/assets/css/jquery-ui/';
		}
		
		$this->themes = $this->getThemes(array('default'=>'-1'), $themeRoot);
		
		$calendarThemesLocalPath = dirname(__FILE__) . '/../../assets/css/jquery-ui/';
		$calendarThemesExternalPath = get_stylesheet_directory() . '/booki/assets/css/jquery-ui/';
		$calendarThemesLocal = $this->getThemes(array('None'=>''), $calendarThemesLocalPath);
		$calendarThemesExternal = $this->getThemes(array('None'=>''), $calendarThemesExternalPath);
		
		$this->calendarThemes = array_merge((array)$calendarThemesLocal, (array)$calendarThemesExternal);
	}
	
	protected function getThemes($themes, $themeRoot){
		//if using a child theme then the theme has to be defined there 
		//and not in the parent theme
		if(file_exists($themeRoot)){
			$children = glob($themeRoot . '*' , GLOB_ONLYDIR);
			foreach($children as $child){
				$name = basename($child);
				$themes[$name] = $name;
			}
		}
		return $themes;
	}
	
	protected function getWeekDays(){
		return array(
			__('Sunday', 'booki')
			, __('Monday', 'booki')
			, __('Tuesday', 'booki')
			, __('Wednesday', 'booki')
			, __('Thursday', 'booki')
			, __('Friday', 'booki')
			, __('Saturday', 'booki')
			, __('Default', 'booki')
		);
	}
	
	protected function getShortDateFormats(){
		$result = array();
		$separators = array('/', '-', '.');
		$formats = array('YYYY%1$sMM%1$sDD', 'MM%1$sDD%1$sYYYY', 'DD%1$sMM%1$sYYYY');
		foreach($formats as $format){
			foreach($separators as $separator){
				array_push($result, sprintf($format, $separator));
			}
		}
		return $result;
	}
}

$_Booki_GeneralSettings = new Booki_GeneralSettings();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('General Settings', 'booki') ?></h4>
			<p><?php echo __('These general settings apply to the entire booking system. Careful with what you wish for!', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<form class="form-horizontal" id="generalsettings" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/generalsettings.php" ?>" method="post">
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseTimezoneSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Timezone, Date Format Settings', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseTimezoneSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Timezone', 'booki') ?></h4>
							<p><?php echo __('This is your timezone. User selected booking time will adapt to this whose timezone offset will be based off. Make sure this is set to your current locations timezone.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-12">
								<input type="hidden" name="booki_timezone_selection" value="<?php echo $_Booki_GeneralSettings->setting->timezone ?>" />
								<?php require_once  dirname(__FILE__) . '/partials/timezonecontrol.php' ?>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Auto detect users timezone', 'booki') ?></h4>
							<p><?php echo __('When checked, the user\'s timezone is auto detected, otherwise the admin\'s timezone is used.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" class="booki_parsley_validated"  
											name="autoTimezoneDetection" <?php echo $_Booki_GeneralSettings->setting->autoTimezoneDetection ? 'checked' : '' ?>/>
									<?php echo __('Automatic', 'booki') ?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Enable user to change timezone', 'booki') ?></h4>
							<p><?php echo __('When checked, bookings will allow user to change the time manually, via a set of unobtrusive dropdown lists.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" class="booki_parsley_validated"  
											name="enableTimezoneEdit" <?php echo $_Booki_GeneralSettings->setting->enableTimezoneEdit ? 'checked' : '' ?>/>
									<?php echo __('Enable user to change timezone', 'booki') ?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Short date format', 'booki') ?></h4>
							<p><?php echo __('All date/time formatting is taken from the options set in WordPress general settings. Some areas however require
							a short date setting, which is taken from the option set below.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="shorthandDateFormat">
								<?php echo __('Short date format', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select name="shorthandDateFormat" 
									id="shorthandDateFormat"
									class="form-control">
									<?php foreach($_Booki_GeneralSettings->shortDateFormats as $shortDateFormat):?>
									<option value="<?php echo $shortDateFormat?>" <?php echo $_Booki_GeneralSettings->setting->shorthandDateFormat == $shortDateFormat ? 'selected' : '' ?>><?php echo $shortDateFormat?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Calendar\'s first day of week', 'booki') ?></h4>
							<p><?php echo __('Sets the first day of week in the calendar used to pick bookings.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="calendarFirstDay">
								<?php echo __('Calendar\'s first day of week', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select
									class="form-control"  
									id="calendarFirstDay"
									name="calendarFirstDay"> 
										<?php foreach($_Booki_GeneralSettings->weekDays as $key=>$value):?>
											<option value="<?php echo $key ?>" <?php echo $_Booki_GeneralSettings->setting->calendarFirstDay == $key ? 'selected' : '' ?>><?php echo $value ?></option>
										<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Show Calendar buttons panel', 'booki') ?></h4>
							<p><?php echo __('When checked, calendar will include a button for todays date selection and a done button for closing the popup.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" class="booki_parsley_validated"  
											name="showCalendarButtonPanel" <?php echo $_Booki_GeneralSettings->setting->showCalendarButtonPanel ? 'checked' : '' ?>/>
									<?php echo __('Show Calendar buttons panel', 'booki') ?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Display booked time slots', 'booki') ?></h4>
							<p><?php echo __('When checked, time slots that are booked are displayed in their respective dropdown list or listbox as disabled. This can be quite distracting to  your customers. Unchecking this option allows you to exclude booked time slots from the list.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" class="booki_parsley_validated"  
											name="displayBookedTimeSlots" <?php echo $_Booki_GeneralSettings->setting->displayBookedTimeSlots ? 'checked' : '' ?>/>
									<?php echo __('Display booked time slots', 'booki') ?>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseCartSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Cart Settings', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseCartSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Use Cart System', 'booki') ?></h4>
							<p><?php echo __('When unchecked, bookings made are immediately booked or paid for without adding to cart or going to a bookings summary page.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="useCartSystem" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->useCartSystem ? "checked" : ""?> /> 
									<?php echo __('Use Cart System', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Cart items with heading', 'booki') ?></h4>
							<p><?php echo __('When checked, items in the cart are organized by project name used as the heading.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enableCartItemHeader" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enableCartItemHeader ? "checked" : ""?> /> 
									<?php echo __('Cart items with heading', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Add to cart', 'booki') ?></h4>
							<p><?php echo __('When unchecked, clicking add to cart button will add the item and take you to the cart page instead of reloading the booking page for more bookings. When checked, make sure to add a Cart widget to your pages. This will allow your users to view activity while adding items to cart.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="addToCart" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->addToCart ? "checked" : ""?> /> 
									<?php echo __('Add to cart', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Show price in booking', 'booki') ?></h4>
							<p><?php echo __('When unchecked, price is removed from booking. Affects only the booking page and not the cart.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="includeBookingPrice" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->includeBookingPrice ? "checked" : ""?> /> 
									<?php echo __('Show price in booking', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Enable payment during checkout', 'booki') ?></h4>
							<p><?php echo __('When checked, customers will be asked to make payments during checkout i.e. immediately redirected to paypal. 
							This setting only forces payments during the checkout process.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enablePayments" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enablePayments ? "checked" : ""?> /> 
									<?php echo __('Enable payment during checkout', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Enable booking with and without payment', 'booki') ?></h4>
							<p><?php echo __('When checked, a "Book now" button is enabled alongside the pay now with PayPal button. This setting takes effect only when the above "Enable payment during checkout" setting is checked as well.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enableBookingWithAndWithoutPayment" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enableBookingWithAndWithoutPayment ? "checked" : ""?> /> 
									<?php echo __('Enable booking with and without payment', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Login page url', 'booki') ?></h4>
							<p><?php echo __('In order for users to make bookings, login is a requirement. By default, the login page in WordPress is used. 
							By changing the Url below, you can redirect it to your custom login page instead.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="loginPageUrl">
								<?php echo __('Login page url', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" 
									class="booki_parsley_validated form-control"  
									data-parsley-trigger="change" 
									id="loginPageUrl"
									name="loginPageUrl" value="<?php echo $_Booki_GeneralSettings->setting->loginPageUrl ?>"/> 
							</div>
						</div>
						<?php /*
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Auto login after registration', 'booki') ?></h4>
							<p><?php echo __('User is auto logged in immediately after registration, 
										if the user has items in their cart during registration.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" class="booki_parsley_validated"  
											id="autoLoginAfterRegistration"
											name="autoLoginAfterRegistration" <?php echo $_Booki_GeneralSettings->setting->autoLoginAfterRegistration ? 'checked' : '' ?>/>
									<?php echo __('Auto login after registration', 'booki')?>
								</label>
							</div>
						</div>
						*/?>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Continue booking url', 'booki') ?></h4>
							<p><?php echo __('This is where the continue button on the booking cart page takes you when clicked. 
							By default it goes to the referring page that lead you to the booking cart if one exists otherwise reloads the current page.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="continueBookingUrl">
								<?php echo __('Continue booking url', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" 
									class="booki_parsley_validated form-control"  
									data-parsley-maxlength="256"
									data-parsley-trigger="change" 
									id="continueBookingUrl"
									name="continueBookingUrl" value="<?php echo $_Booki_GeneralSettings->setting->continueBookingUrl?>"/> 
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Membership required to make booking', 'booki') ?></h4>
							<p><?php echo __('When un-checked, users can make bookings and payment without registration.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="membershipRequired" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->membershipRequired ? "checked" : ""?> /> 
									<?php echo __('Membership required to make booking', 'booki')?>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseNotificationSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Notifications, Confirmations and Invoices', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseNotificationSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Auto confirm payment', 'booki') ?></h4>
							<p><?php echo __('Auto confirms payment after buyer is redirected from Paypal. If you leave this unchecked, buyer needs to re-confirm purchase after being redirected from Paypal i.e. just like on ebay, a two-step process.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="autoConfirmOrderAfterPayment" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->autoConfirmOrderAfterPayment ? "checked" : ""?> /> 
									<?php echo __('Auto confirm order after returning from Paypal', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Confirm booking automatically after checkout', 'booki') ?></h4>
							<p><?php echo __('When checked, bookings made are approved and confirmed automatically after checkout or if payment is enabled,
							after payment is received.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="autoApproveBooking" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->autoApproveBooking ? "checked" : ""?> /> 
									<?php echo __('Approve and send confirmation email automatically after checkout or payment', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Send invoice automatically during checkout', 'booki') ?></h4>
							<p><?php echo __('When checked, an invoice is sent automatically after checkout. 
									Specifically when "Enable payments during checkout" option is unchecked 
									which implicitly means payment is to be made through an invoice.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="autoInvoiceNotification" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->autoInvoiceNotification ? "checked" : ""?> /> 
									<?php echo __('Send invoice automatically during checkout', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Send refund notification email automatically after a refund', 'booki') ?></h4>
							<p><?php echo __('When checked, a notification email is sent automatically after a successful refund.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="autoRefundNotification" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->autoRefundNotification ? "checked" : ""?> /> 
									<?php echo __('Send refund notification email automatically after a refund', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Send booking is cancelled notification email automatically after a booking is cancelled', 'booki') ?></h4>
							<p><?php echo __('When checked, a notification email is sent automatically after booking has been cancelled by admin.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="notifyBookingCancellation" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->notifyBookingCancellation ? "checked" : ""?> /> 
									<?php echo __('Send booking is cancelled notification email automatically after a booking is cancelled', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Notify admin via email everytime a new booking is made', 'booki') ?></h4>
							<p><?php echo __('When checked, a notification email is sent to admin everytime a new booking is received.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="autoNotifyAdminNewBooking" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->autoNotifyAdminNewBooking ? "checked" : ""?> /> 
									<?php echo __('Notify admin via email everytime a new booking is made.', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Notify user that their booking was received', 'booki') ?></h4>
							<p><?php echo __('When checked, a notification email is sent to the user to confirm that their booking was successfully received.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="notifyBookingReceivedSuccessfully" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->notifyBookingReceivedSuccessfully ? "checked" : ""?> /> 
									<?php echo __('Notify user that their booking was received', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Default owner of booking', 'booki') ?></h4>
							<p><?php echo __('Every booking made needs an owner. When a booking is made anonymously, without user login, i.e. when membership is not required, the default owner of this booking will be the user you set here until you later associate a user to the booking.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="adminUserId">
								<?php echo __('Default owner of booking', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select id="adminUserId" name="adminUserId" class="form-control">
									<?php foreach($_Booki_GeneralSettings->adminUsersList as $adminUser):?>
										<option value="<?php echo $adminUser->id?>" <?php echo $_Booki_GeneralSettings->setting->adminUserId === (int)$adminUser->id ? 'selected' : '' ?>><?php echo $adminUser->user_login ?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Email notifications TO:', 'booki') ?></h4>
							<p><?php echo __('If you have email notifications turned on, one is sent to the following address when a new booking is made.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="loginHeaderCaption">
								<?php echo __('Email Notifications TO:', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" 
									class="booki_parsley_validated form-control"  
									data-parsley-type="email"
									data-parsley-trigger="change" 
									id="notificationEmailTo"
									name="notificationEmailTo" value="<?php echo $_Booki_GeneralSettings->setting->notificationEmailTo ?>"/> 
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseDiscountSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Discounts', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseDiscountSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Enable coupons in shopping cart', 'booki') ?></h4>
							<p><?php echo __('When checked, a textbox for collecting coupon codes is available in the shopping cart.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enableCoupons" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enableCoupons ? "checked" : ""?> /> 
									<?php echo __('Enable coupons in shopping cart', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Discount', 'booki') ?></h4>
							<p>
								<?php echo __('A value in percentage to deduct from the actual cost set above. The discount is triggered based on the booking minimum property and applies to the total cost. The more one books, the less one pays! Keep in mind that discounts are disabled if a deposit is applicable.', 'booki') ?>
							</p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="discount">
								<?php echo __('Discount', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<div class="input-group">
									 <input type="text" 
										id="discount" 
										name="discount" 
										class="form-control" 
										data-parsley-type="number"
										data-parsley-min="0.00"
										data-parsley-max="99.9"
										data-parsley-trigger="change" 
										data-parsley-errors-container="#discounterror"
										value="<?php echo Booki_Helper::toMoney($_Booki_GeneralSettings->setting->discount) ?>"><span class="input-group-addon">%</span>
								</div>
								<div class="clearfix"></div>
								<ul id="discounterror"></ul>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Booking minimum for discount', 'booki') ?></h4>
							<p>
								<?php echo __('The discount value will be applied based on the booking minimum. A value of 0 will apply the discount without minimum booking restrictions. ', 'booki') ?>
							</p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="bookingMinimumDiscount"><?php echo __('Booking minimum for discount', 'booki') ?></label>
							<div class="col-lg-8">
							  <input type="text" 
									id="bookingMinimumDiscount" 
									name="bookingMinimumDiscount" 
									class="form-control" 
									data-parsley-type="number"
									data-parsley-min="0"
									data-parsley-trigger="change" 
									value="<?php echo $_Booki_GeneralSettings->setting->bookingMinimumDiscount ?>">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseThemeSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Theme Options', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseThemeSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Theme', 'booki') ?></h4>
							<p><?php echo __('Custom themes you upload into your current themes/booki directory will show up here. 
									Make sure you read the docs on how to achieve this.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="theme">
								<?php echo __('Theme', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select id="theme" name="theme" class="form-control">
								<?php foreach($_Booki_GeneralSettings->themes as $key=>$value): ?>
									<option value="<?php echo $value ?>" <?php echo $_Booki_GeneralSettings->setting->theme === $value ? 'selected' : '' ?>><?php echo $key ?></option>
								<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Calendar theme', 'booki') ?></h4>
							<p><?php echo __('The theme used in the calendar controls during booking. For custom themes, refer to the documentation.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="calendarTheme">
								<?php echo __('Calendar theme', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select id="calendarTheme" name="calendarTheme" class="form-control">
								<?php foreach($_Booki_GeneralSettings->calendarThemes as $key=>$value): ?>
									<option value="<?php echo $value ?>" <?php echo $_Booki_GeneralSettings->setting->calendarTheme === $value ? 'selected' : '' ?>><?php echo $key ?></option>
								<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Calendar Style Settings', 'booki') ?></h4>
							<div><?php echo __('Flat - Removes default images set by jQuery-ui calendar and squares out the calendar removing border radius.', 'booki') ?></div>
							<div><?php echo __('Borderless - Removes default borders set by jQuery-ui calendar and adds a single 2px black border under the header.', 'booki') ?></div>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" name="calendarFlatStyle" <?php echo $_Booki_GeneralSettings->setting->calendarFlatStyle ? 'checked' : '' ?>/>
									<?php echo __('Flat', 'booki') ?>
								</label>
							</div>
							<div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" name="calendarBorderlessStyle" <?php echo $_Booki_GeneralSettings->setting->calendarBorderlessStyle ? 'checked' : '' ?>/>
									<?php echo __('Borderless', 'booki') ?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Highlight Selected Optional Item', 'booki') ?></h4>
							<div><?php echo __('Highlights the selected optional item.', 'booki') ?></div>
						</div>
						<div class="form-group">
							 <div class="col-lg-8 col-lg-offset-4">
								<label class="checkbox">
									<input type="checkbox" name="highlightSelectedOptionals" <?php echo $_Booki_GeneralSettings->setting->highlightSelectedOptionals ? 'checked' : '' ?>/>
									<?php echo __('Highlight Selected Optional Item', 'booki') ?>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseMaintenenceSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Maintenance Options', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseMaintenenceSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Clear Event log', 'booki') ?></h4>
							<p><?php echo __('The event log contains unexpected error messages due to Email, MailChimp or PayPal failures. There is no reason to keep these messages for longer than necessary. By default, it will be cleared every 7 days. A value of zero has no effect and event log messages will never be cleared automatically.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="eventsLogExpiry">
								<?php echo __('Clear Event log after', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" class="booki_parsley_validated form-control"  
								data-parsley-type="digits"
								data-parsley-trigger="change"
								id="eventsLogExpiry"
								name="eventsLogExpiry"
								value="<?php echo $_Booki_GeneralSettings->setting->eventsLogExpiry?>"/>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Reference bootstrap style sheet', 'booki') ?></h4>
							<p><?php echo __('When checked, bootstrap stylesheet is referenced. The booking system depend on this, so unless your theme already references bootstrap, do not uncheck. 
							Note that the bootstrap version used is namespaced and will not conflict with your theme in use.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="refBootstrapStyleSheet" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->refBootstrapStyleSheet ? "checked" : ""?> /> 
									<?php echo __('Reference bootstrap style sheet include', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Reference bootstrap JavaScript include', 'booki') ?></h4>
							<p><?php echo __('When checked, bootstrap JavaScript include is referenced. 
									The booking system depends on this, so unless your theme already references bootstrap, do not uncheck.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="refBootstrapJS" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->refBootstrapJS ? "checked" : ""?> /> 
									<?php echo __('Reference bootstrap JavaScript include', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Debug mode', 'booki') ?></h4>
							<p><?php echo __('When checked, Booki will reference debug versions of it\'s client side JavaScript/CSS files. Debug versions of the client side scripts are larger, so do not do this unless you have good reason for doing so.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="debugMode" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->debugMode ? "checked" : ""?> /> 
									<?php echo __('Debug Mode', 'booki')?>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8">
						<a data-toggle="collapse" href=".collapseMiscSettings" class="btn btn-default">
							<i class="glyphicon glyphicon-plus-sign"></i>
							<?php echo __('Misc Options', 'booki') ?>
						</a>
					</div>
				</div>
				<div class="accordion-body">
					<div class="collapseMiscSettings collapse">
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Tax', 'booki') ?></h4>
							<p><?php echo __('Tax is displayed in the shopping cart during checkout. 
							When setting tax below, it has to be a percentage value, which is the value deducted from the total amount.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="tax">
								<?php echo __('Tax', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<div class="input-group">
									<input type="text" class="booki_parsley_validated form-control"  
									data-parsley-type="number"
									data-parsley-min="0"
									data-parsley-trigger="change" 
									data-parsley-errors-container="#taxerror"
									id="tax"
									name="tax" value="<?php echo Booki_Helper::toMoney($_Booki_GeneralSettings->setting->tax) ?>"/>
								  <span class="input-group-addon">%</span>
								</div>
								<ul id="taxerror"></ul>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Time Selection', 'booki') ?></h4>
							<p><?php echo __('How do you want users to select time in your bookings. The default is a Dropdown List. While the Dropdown list 
							is compact and takes up only a single line, it does not allow multiple selections. Hence your users need to 
							select one slot at a time and add it to the cart. 
							If you want multiple selections in a single click, use the ListBox.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="timeSelector">
								<?php echo __('Time selection', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<select id="timeSelector" name="timeSelector" class="form-control">
									<option value="0" <?php echo $_Booki_GeneralSettings->setting->timeSelector === 0 ? 'selected' : '' ?>><?php echo __('Dropdown List', 'booki') ?></option>
									<option value="1" <?php echo $_Booki_GeneralSettings->setting->timeSelector === 1 ? 'selected' : '' ?>><?php echo __('ListBox', 'booki') ?></option>
								</select>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Enable editors', 'booki') ?></h4>
							<p><?php echo __('When checked, users in the "Editors" role are allowed to view and manage bookings. In particular, they are allowed to 
							confirm bookings by sending out confirmation emails, refund notifications for refunded bookings and send out an invoice for unpaid bookings.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enableEditors" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enableEditors ? "checked" : ""?> /> 
									<?php echo __('Enable editors', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Delete booking upon payment cancellation', 'booki') ?></h4>
							<p><?php echo __('When checked, if user cancels during a PayPal checkout (on PayPals page they hit cancel instead of proceeding with payment), the order will also be deleted for good. If unchecked, user can access booking from their history page and attempt payment again.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="deletePayPalCancelledBooking" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->deletePayPalCancelledBooking ? "checked" : ""?> /> 
									<?php echo __('Delete booking upon payment cancellation', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Allow user to cancel their booking', 'booki') ?></h4>
							<p><?php echo __('When checked, allows a user to cancel their booking, if booking has not been confirmed yet.
							This can be done directly by the user from their bookings history page.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="enableUserCancelBooking" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->enableUserCancelBooking ? "checked" : ""?> /> 
									<?php echo __('Allow user to cancel their booking', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('MailChimp API Key', 'booki') ?></h4>
							<p><?php echo __('Your mailchimp API Key. This will allow you to upload your users to 
									mailchimp along with freshly generated coupon codes per user.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="mailChimpKey">
								<?php echo __('MailChimp API Key', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" 
									class="booki_parsley_validated form-control"  
									data-parsley-maxlength="36"
									data-parsley-minlength="36"
									data-parsley-trigger="change" 
									id="mailChimpKey"
									name="mailChimpKey" value="<?php echo $_Booki_GeneralSettings->setting->mailChimpKey?>"/> 
							</div>
						</div>
							<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Order expiry', 'booki') ?></h4>
							<p><?php echo __('The number of days an unpaid order is still 
									valid and payable after booking is made. Unpaid orders will be automatically purged out of the system after expiry. 
									A value of zero (the default) has no effect and unpaid orders will not expire.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="unpaidOrderExpiry">
								<?php echo __('Order expiry', 'booki') ?>
							</label>
							<div class="col-lg-8">
								<input type="text" class="booki_parsley_validated form-control"  
								data-parsley-type="digits"
								data-parsley-trigger="change"
								id="unpaidOrderExpiry"
								name="unpaidOrderExpiry"
								value="<?php echo $_Booki_GeneralSettings->setting->unpaidOrderExpiry?>"/>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Use history page in dashboard', 'booki') ?></h4>
							<p><?php echo __('When checked, the system will link to a history page in the back-end ( via dashboard). Unchecking this option will force the system to link to the history page in the front end. Be sure to include it in your menu if un-checked.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="useDashboardHistoryPage" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->useDashboardHistoryPage ? "checked" : ""?> /> 
									<?php echo __('Use history page in dashboard', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Display once form on multiple projects', 'booki') ?></h4>
							<p><?php echo __('When checked, if all your form fields have the "Display once" option checked, the setting will propagate to all projects that have display once.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="oneForm" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->oneForm ? "checked" : ""?> /> 
									<?php echo __('Display once form', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Disable back button cache', 'booki') ?></h4>
							<p><?php echo __('When checked, the browser will be forced to revalidate with the server in case a previous response of the page due to a postback is available. Note: This setting when checked will affect all pages on your site, so do this with caution and if you truly want to use the no-cache header directive. It is unchecked by default.', 'booki') ?> </p>
						</div>
						<div class="form-group">
							<div class="col-lg-8 col-lg-offset-4">
							   <label class="checkbox">
									<input name="noCache" type="checkbox" <?php echo $_Booki_GeneralSettings->setting->noCache ? "checked" : ""?> /> 
									<?php echo __('Disable back button cache', 'booki')?>
								</label>
							</div>
						</div>
						<div class="booki-callout booki-callout-warning">
							<h4><?php echo __('Currency code and symbol', 'booki') ?></h4>
							<p>
								<?php echo __('If your not using PayPal and would rather provide the currency and currency symbol manually, set it below. Make sure "Enable Payments" is unchecked. When payments are enabled, currency and currency symbol are taken from the PayPal settings page.', 'booki') ?>
							</p>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="currencyCode">
								<?php echo __('Currency Code', 'booki') ?>
							</label>
							<div class="col-lg-8">
									 <input type="text" 
										id="currencyCode" 
										name="currencyCode" 
										class="form-control" 
										value="<?php echo $_Booki_GeneralSettings->setting->currencyCode ?>" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-4 control-label" for="currencySymbol">
								<?php echo __('Currency Symbol', 'booki') ?>
							</label>
							<div class="col-lg-8">
									 <input type="text" 
										id="currencySymbol" 
										name="currencySymbol" 
										class="form-control" 
										value="<?php echo $_Booki_GeneralSettings->setting->currencySymbol ?>" />
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<?php if($_Booki_GeneralSettings->setting->id === -1) :?>
						<button class="create btn btn-primary" name="booki_create"><i class="glyphicon glyphicon-ok"></i> <?php echo __('Save', 'booki') ?></button>
						<?php else:?>
						<button class="save btn btn-primary" name="booki_update" value="<?php echo $_Booki_GeneralSettings->setting->id?>"><i class="glyphicon glyphicon-ok"></i> Save</button>
						<button class="delete btn btn-danger" name="booki_delete" value="<?php echo $_Booki_GeneralSettings->setting->id?>"><i class="glyphicon glyphicon-trash"></i> Reset</button>
						<?php endif;?>
					</div>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$('[data-toggle=tooltip]').tooltip();
					});
				</script>
			</form>
		</div>
	</div>
</div>
