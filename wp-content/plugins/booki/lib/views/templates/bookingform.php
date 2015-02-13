<?php
	if(!isset($_Booki_BookingFormTmpl)){
		$_Booki_BookingFormTmpl = new Booki_BookingFormTmpl();
	}
?>
	<?php if($_Booki_BookingFormTmpl->data->enableItemHeading):?>
	<div class="form-group">
		<div class="col-lg-8 col-lg-offset-4">
			<label class="control-label">
				<?php echo $_Booki_BookingFormTmpl->data->projectName ?>
			</label>
		</div>
	</div>
	<?php endif;?>
	<?php if($_Booki_BookingFormTmpl->data->displayCurrentBookingsCount): ?>
		<div class="form-group">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php echo sprintf($_Booki_BookingFormTmpl->data->bookingLimitLabel, $_Booki_BookingFormTmpl->data->currentBookingCount) ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::INLINE): ?>
	<div class="form-group">
		<label class="col-lg-12 control-label booki-text-align-left">
			<?php echo $_Booki_BookingFormTmpl->data->availableDaysLabel ?>
		</label>
		<div class="clearfix"></div>
		<div class="col-lg-12">
			<div class="booki-single-datepicker booki-inline-calendar"></div>
		</div>
	</div>
	<?php elseif ($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::POPUP || 
				(($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::RANGE ||  
	 $_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::NEXT_DAY_CHECKOUT) && 
					$_Booki_BookingFormTmpl->data->bookingDaysLimit <= 1)): ?>
	<input type="hidden" name="contractstartdate" />
	<div class="form-group">
		<label class="col-lg-4 control-label">
			<?php echo $_Booki_BookingFormTmpl->data->availableDaysLabel ?>
		</label>
		<div class="col-lg-8">
			<div class="input-group booki-single-datepicker-group">
				<input type="text" id="<?php echo 'datepicker_' . $_Booki_BookingFormTmpl->uniqueKey ?>" 
				class="booki-single-datepicker form-control" readonly="true">
				<label for="<?php echo 'datepicker_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon">
					<?php if($_Booki_BookingFormTmpl->data->globalSettings->includeBookingPrice):?>
					.00
					<?php else:?>
					<i class="glyphicon glyphicon-calendar"></i>
					<?php endif;?>
				</label>
			</div>
		</div>
	</div>
	<?php elseif ($_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::RANGE ||
					$_Booki_BookingFormTmpl->data->calendarMode === Booki_CalendarMode::NEXT_DAY_CHECKOUT): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->fromLabel ?>
			</label>
			<div class="col-lg-8">
				<div class="input-group">
					<input type="text" id="<?php echo 'datepicker_from_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="booki-datepicker-from form-control" readonly="true">
					<label for="<?php echo 'datepicker_from_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->toLabel ?>
			</label>
			<div class="col-lg-8">
				<div class="input-group">
					<input type="text" id="<?php echo 'datepicker_to_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="booki-datepicker-to form-control" readonly="true">
					<label for="<?php echo 'datepicker_to_' . $_Booki_BookingFormTmpl->uniqueKey ?>" class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></label>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="clearfix"></div>
	<?php if($_Booki_BookingFormTmpl->data->bookingDaysMinimum): ?>
		<div class="form-group booki-minimum-days-required hide">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY):?>
						<?php echo sprintf('A minimum of %d days required.', $_Booki_BookingFormTmpl->data->bookingDaysMinimum) ?>
					<?php else: ?>
						<?php echo sprintf('A minimum of %d time slot selections required.', $_Booki_BookingFormTmpl->data->bookingDaysMinimum) ?>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->bookingDaysLimit > 1):?>
		<div class="form-group booki-booking-limit hide">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY):?>
						<?php echo str_replace('{0}' , $_Booki_BookingFormTmpl->data->bookingDaysLimit, __('You can only book {0} days at a time. Excess days not applied.', 'booki')) ?>
					<?php else: ?>
						<?php echo str_replace('{0}' , $_Booki_BookingFormTmpl->data->bookingDaysLimit, __('You can only book {0} slots at a time. Excess time not applied.', 'booki')) ?>
					<?php endif;?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_DAY &&
			($_Booki_BookingFormTmpl->data->bookingDaysLimit > 1 && !$_Booki_BookingFormTmpl->data->hideSelectedDays)): ?>
	<div class="form-group">
		<label class="col-lg-4 control-label">
			<?php echo $_Booki_BookingFormTmpl->data->selectedDaysLabel ?>
		</label>
		<div class="col-lg-8">
			<ul class="booki-dates"></ul>
		</div>
	</div>
	<?php endif; ?>
	<input name="selected_date" class="booki-selected-date" type="hidden"  />
	<?php if($_Booki_BookingFormTmpl->data->calendarPeriod === Booki_CalendarPeriod::BY_TIME): ?>
		<div class="form-group">
			<label class="col-lg-4 control-label">
				<?php echo $_Booki_BookingFormTmpl->data->bookingTimeLabel ?>
			</label>
			<div class="col-lg-8">
				<?php if($_Booki_BookingFormTmpl->data->globalSettings->timeSelector === Booki_TimeSelector::DROPDOWNLIST): ?>
				<select name="time[]" class="booki-time form-control"></select>
				<?php elseif ($_Booki_BookingFormTmpl->data->globalSettings->timeSelector === Booki_TimeSelector::LISTBOX): ?>
				<select name="time[]" class="booki-time form-control" multiple="multiple"></select>
				<?php endif; ?>
			</div>
		</div>
		<div class="form-group hide booki-time-slots-exhausted">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="alert alert-warning alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<strong><?php echo __('Exhausted!', 'booki') ?></strong> <?php echo __('All available slots have been added to cart. Check out ?', 'booki')?>
				</div>
			</div>
		</div>
		<div class="form-group hide booki-time-slots-booked">
			<div class="col-lg-8 col-lg-offset-4">
				<div class="alert alert-warning alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<strong><?php echo __('Booked!', 'booki') ?></strong> <?php echo __('All available slots have been booked for this day.', 'booki')?>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-8 col-md-offset-4">
				<div class="progress progress-striped active booki-time-progress hide">
					<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
						aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<?php if($_Booki_BookingFormTmpl->data->globalSettings->enableTimezoneEdit):?>
		<div class="form-group">
			<div class="col-lg-12">
				<?php Booki_ThemeHelper::includeTemplate('timezonecontrol.php') ?>
			</div>
		</div>
		<?php endif; ?>
	<?php else: ?>
	<div class="form-group hide booki-days-exhausted">
		<div class="col-lg-8 col-lg-offset-4">
			<div class="alert alert-warning alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<strong><?php echo __('Exhausted!', 'booki') ?></strong> <?php echo __('All Available days have been added to cart. Checkout ?', 'booki') ?>
			</div>
		</div>
	</div>
	<?php endif;?>