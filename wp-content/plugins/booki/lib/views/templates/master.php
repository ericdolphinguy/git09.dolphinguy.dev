<?php
	$_Booki_MasterTmpl = new Booki_MasterTmpl();
?>
<?php if($_Booki_MasterTmpl->active): ?>
	<div class="booki form-horizontal">
		<?php if($_Booki_MasterTmpl->hasAvailableBookings): ?>
			<form id="booki_<?php echo $_Booki_MasterTmpl->projectId ?>_form" name="booki_<?php echo $_Booki_MasterTmpl->projectId ?>_form" class="booki booki-form-elements" 
						action="<?php echo $_SERVER['REQUEST_URI'] ?>" data-parsley-validate method="post">
				<input type="hidden" name="booki_nonce" value="<?php echo Booki_NonceHelper::create('booki-wizard');?>"/>
				<input type="hidden" name="projectid" value="<?php echo $_Booki_MasterTmpl->projectId ?>" />
				<input type="hidden" name="deposit_field"/>
				<div class="form-group booki-name-field">
					<label class="col-lg-4 control-label">
						<?php echo __('Humanics test, skip it', 'booki') ?>
					</label>
					<div class="col-lg-8">
						<input type="text" name="booki_<?php echo $_Booki_MasterTmpl->projectId ?>_humanics" class="form-control">
					</div>
				</div>
				<?php Booki_ThemeHelper::includeTemplate('bookingwizard.php') ?>
			</form>
		<?php else: ?>
			<div class="alert alert-warning alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<strong><?php echo __('Oops!', 'booki') ?></strong> <?php echo __('All bookings exhausted.', 'booki') ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>