<?php
	if(!isset($_Booki_CustomFormTmpl)){
		$_Booki_CustomFormTmpl = new Booki_CustomFormTmpl();
	}
	
	$_Booki_BookingWizardTmpl = new Booki_BookingWizardTmpl($_Booki_CustomFormTmpl->data->hasCustomFormFields);
	
	if(!$_Booki_BookingWizardTmpl->globalSettings->useCartSystem){
		$projectId = apply_filters( 'booki_shortcode_id', null);
		if($projectId === null || $projectId === -1){
			$projectId = apply_filters( 'booki_project_id', null);
		}
	}
	
	if(!isset($_Booki_BookingFormTmpl)){
		$_Booki_BookingFormTmpl = new Booki_BookingFormTmpl();
	}
?>

<?php if($_Booki_BookingWizardTmpl->errors && count($_Booki_BookingWizardTmpl->errors) > 0):?>
	<div class="col-lg-12 alert alert-danger">
		<?php foreach($_Booki_BookingWizardTmpl->errors as $key=>$value):?>
			<div><strong><?php echo $key?></strong>: <?php echo $value ?></div>
		<?php endforeach;?>
	</div>
	<div class="clearfix"></div>
<?php endif; ?>
<?php if($_Booki_BookingWizardTmpl->checkoutSuccessMessage): ?>
<div class="alert alert-success">
	 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<p>
		<?php echo $_Booki_BookingWizardTmpl->checkoutSuccessMessage ?>
	</p>
	<p>
		<?php echo sprintf($_Booki_BookingWizardTmpl->resx->ORDER_ID_REF_LOC, $_Booki_BookingWizardTmpl->orderId)?>
		<?php if(is_user_logged_in()){
				echo sprintf($_Booki_BookingWizardTmpl->resx->VIEW_ORDER_HISTORY_LOC
					, sprintf('<a href="%s">%s</a>'
						, $_Booki_BookingWizardTmpl->orderHistoryUrl
						, $_Booki_BookingWizardTmpl->resx->HISTORY_LOC));
			}
		?>
	</p>
</div>
<?php endif; ?>
<?php if(isset($_Booki_BookingWizardTmpl->data->hasBookedElements) && $_Booki_BookingWizardTmpl->data->hasBookedElements):?>
<div class="alert alert-danger">
	 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_BOOKING_SNATCHED_LOC ?>
</div>
<?php endif; ?>
<div id="bookiwizard_validation_<?php echo $_Booki_BookingWizardTmpl->projectId ?>" class="alert alert-danger hide">
	 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_REQUIRED_FIELD_LOC?>
</div>
<div id="bookiwizard<?php echo $_Booki_BookingWizardTmpl->projectId ?>" class="booki col-lg-12 booki-no-padding">
	<?php if($_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::TABS): ?>
	<ul class="booki<?php echo $_Booki_BookingWizardTmpl->projectId ?> nav nav-tabs">
		<?php foreach($_Booki_BookingWizardTmpl->steps as $step): ?>
			<li>
				<a href="#<?php echo $step['id'] ?>" 
					data-toggle="tab" 
					data-step="<?php echo array_search($step, $_Booki_BookingWizardTmpl->steps) ?>">
					<?php echo $step['name'] ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	<div class="booki <?php echo $_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::TABS ? 'tab-content' : '' ?> form-horizontal">
		<div
			<?php if($_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::TABS):?>
			class="tab-pane active" 
			<?php endif;?>
			id="bookingtab<?php echo $_Booki_BookingWizardTmpl->projectId ?>">
			<div class="col-lg-12">
				<?php if(!$_Booki_BookingFormTmpl->data->bookingsExhausted): ?>
					<?php echo $_Booki_BookingWizardTmpl->project->contentTop ?>
					<?php Booki_ThemeHelper::includeTemplate('bookingform.php') ?>
					<?php echo $_Booki_BookingWizardTmpl->project->contentBottom ?>
					<div class="booki-vertical-gap"></div>
					<?php Booki_ThemeHelper::includeTemplate('cascadingdropdownlists.php') ?>
					<div class="booki-vertical-gap"></div>
					<?php Booki_ThemeHelper::includeTemplate('optionalsform.php') ?>
				<?php else: ?>
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<div class="alert alert-warning">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_LAST_BOOKING_ADDED_LOC ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if($_Booki_CustomFormTmpl->data->hasCustomFormFields): ?>
		<div 
			<?php if($_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::TABS):?>
			class="tab-pane" 
			<?php endif;?>
			id="detailstab<?php echo $_Booki_BookingWizardTmpl->projectId ?>">
			<?php if(!$_Booki_BookingFormTmpl->data->bookingsExhausted): ?>
				<?php Booki_ThemeHelper::includeTemplate('customform.php') ?>
				<div class="clearfix"></div>
			<?php else: ?>
				<div class="col-lg-12">
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<div class="alert alert-warning">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_LAST_BOOKING_ADDED_LOC ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if($_Booki_BookingFormTmpl->data->hasDiscount &&  !$_Booki_BookingWizardTmpl->checkoutSuccessMessage): ?>
		<div class="col-lg-12">
			<div class="form-group">
				<div class="col-lg-8 col-lg-offset-4">
					<div class="alert alert-success alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<strong><?php echo $_Booki_BookingWizardTmpl->resx->CONGRATS_LOC ?></strong> <?php echo sprintf($_Booki_BookingWizardTmpl->resx->GOT_DISCOUNT_LOC, Booki_Helper::toMoney($_Booki_BookingWizardTmpl->globalSettings->discount) ) ?>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
		<?php if(!$_Booki_BookingFormTmpl->data->bookingsExhausted && $_Booki_BookingWizardTmpl->globalSettings->includeBookingPrice):?>
		<div class="col-lg-12 booki-deposit hide">
			<div class="alert alert-warning">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_PAY_ONLY_DEPOSIT_LOC ?>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="pull-right">
				<div class="booki-sub-total hide">
					<strong>
						<?php echo $_Booki_BookingWizardTmpl->resx->SUBTOTAL_LOC ?>
					</strong>
						<span class="booki-sub-total-label"></span>
				</div>
				<div class="booki-deposit hide">
					<div>
						<strong>
							<?php echo $_Booki_BookingWizardTmpl->resx->DEPOSIT_LOC ?>
						</strong>
						<span class="booki-deposit-label"></span>
					</div>
				</div>
				<div class="booki-discount hide">
					<strong>
						<?php echo $_Booki_BookingWizardTmpl->resx->DISCOUNT_LOC ?>
					</strong>
					<span class="booki-discount-label"></span>
				</div>
				<?php if($_Booki_BookingWizardTmpl->data->tax):?>
				<div>
					<strong><span class="booki-tax-label"><?php echo $_Booki_BookingWizardTmpl->resx->TAX_LOC ?></span></strong>
					<span>
						<?php echo $_Booki_BookingWizardTmpl->data->tax ?>%
					</span>
				</div>
				<?php endif;?>
				<div>
					<strong class="booki-total">
						<?php echo $_Booki_BookingWizardTmpl->resx->TOTAL_LOC ?>
					</strong>
					<span class="booki-totals-label"></span>
					<span class="booki-currency-label"><?php echo $_Booki_BookingWizardTmpl->data->currency ?></span>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<div class="clearfix"></div>
	</div>
	<?php if($_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::LINEAR):?>
	<hr/>
	<?php endif;?>
	<div class="booki booki-wizard-footer <?php echo $_Booki_BookingWizardTmpl->project->bookingWizardMode === Booki_BookingWizardMode::TABS ? 'well' : ''?>">
		<div class="pull-right">
			<button type="button" class="btn btn-primary booki-booking-back" data-step="0" 
				<?php echo $_Booki_BookingFormTmpl->data->bookingsExhausted ? 'disabled' : ''?>>
				<i class="glyphicon glyphicon-circle-arrow-left"></i>
				<?php echo $_Booki_BookingWizardTmpl->project->prevLabel_loc ?>
			</button>
			<button type="button" class="btn btn-primary booki-booking-next" data-step="1" 
				<?php echo $_Booki_BookingFormTmpl->data->bookingsExhausted ? 'disabled' : ''?>>
				<i class="glyphicon glyphicon-circle-arrow-right"></i>
				<?php echo $_Booki_BookingWizardTmpl->project->nextLabel_loc ?>
			</button>
			
			<span class="booki-booking-button">
				<?php if($_Booki_BookingWizardTmpl->isBackEnd): ?>
				<button type="submit" name="booki_add_new" class="btn btn-primary booki-create-booking">
					<i class="glyphicon glyphicon-plus-sign"></i>
					<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_CREATE_BOOKING_LOC ?>
				</button>
				<?php else: ?>
					<?php if($_Booki_BookingWizardTmpl->globalSettings->useCartSystem):?>
					<button type="submit" name="booki_add_cart" class="btn btn-primary booki-add-to-cart" 
						<?php echo $_Booki_BookingFormTmpl->data->bookingsExhausted ? 'disabled' : ''?>>
						<i class="glyphicon glyphicon-plus"></i>
						<?php echo $_Booki_BookingWizardTmpl->project->addToCartLabel_loc?>
					</button>
					<?php else:?>
						<?php if ( is_user_logged_in() || !$_Booki_BookingWizardTmpl->globalSettings->membershipRequired): ?> 
							<?php if( $_Booki_BookingWizardTmpl->globalSettings->enableBookingWithAndWithoutPayment || !$_Booki_BookingWizardTmpl->globalSettings->enablePayments):?>
								<button type="submit" 
										name="booki_checkout"
										value="0"
										class="btn btn-primary booki-make-booking">
										<?php echo $_Booki_BookingWizardTmpl->globalSettings->enablePayments ? $_Booki_BookingWizardTmpl->resx->BOOK_NOW_PAY_LATER_LOC : $_Booki_BookingWizardTmpl->resx->BOOK_NOW_LOC ?>
								</button>
							<?php endif; ?>
							<?php if ( $_Booki_BookingWizardTmpl->globalSettings->enablePayments): ?> 
								<button type="submit" 
										name="booki_checkout"
										value="1"										
										class="booki-cart-checkout">
										<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left">
								</button>
							<?php endif; ?>
						<?php else: ?>
							<a class="btn btn-success booki-proceed" href="<?php echo Booki_Helper::appendReferrer($_Booki_BookingWizardTmpl->globalSettings->loginPageUrl, 'redirect_to') ?>">
								<i class="glyphicon glyphicon-circle-arrow-right"></i>
								<?php echo $_Booki_BookingWizardTmpl->project->proceedToLoginLabel_loc ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</span>
			<?php if(!$_Booki_BookingWizardTmpl->isBackEnd && $_Booki_BookingWizardTmpl->globalSettings->useCartSystem): ?>
			<a href="<?php echo $_Booki_BookingWizardTmpl->goToCartUrl ?>" class="btn btn-primary booki-go-to-checkout" <?php echo $_Booki_BookingWizardTmpl->cartEmpty ? 'disabled' : '' ?>>
				<i class="glyphicon glyphicon-shopping-cart"></i>
				<?php echo $_Booki_BookingWizardTmpl->resx->BOOKING_WIZARD_CHECK_OUT_LOC?>
			</a>
			<?php endif; ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var projectId = <?php echo $_Booki_BookingWizardTmpl->projectId ?>;
		
		$("#booki_<?php echo $_Booki_BookingFormTmpl->projectId ?>_form").Booki(
			<?php echo $_Booki_BookingFormTmpl->data->toJson() ?>
		);

		$("#bookiwizard" + projectId).BookiBookingWizard({
			"projectId": projectId
			, "bookingButton": ".booki-booking-button"
			, "nextButton": ".booki-booking-next"
			, "backButton": ".booki-booking-back"
			, "step1": "a[data-step=\"0\"]"
			, "tabs": ".booki" + projectId + ".nav.nav-tabs a"
			, "errorContainer": "#bookiwizard_validation_<?php echo $_Booki_BookingWizardTmpl->projectId ?>"
		});
	});

</script>