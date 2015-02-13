<?php

	require_once  dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';

	class Booki_BookingDetails{
		public $dataset;
		public $hasFullControl;
		public $canEdit;
		public $canCancel;
		public $refundableDays;
		public $refundableOptionals;
		public function __construct(){
			$globalSettings = Booki_Helper::globalSettings();
			$this->hasFullControl = Booki_Helper::hasAdministratorPermission();
			$this->canEdit = Booki_Helper::hasEditorPermission();
			$this->canCancel = $this->canEdit || $globalSettings->enableUserCancelBooking;
			$this->dataset = apply_filters( 'booki_single_order_details', null);
			if(!$this->dataset || !$this->dataset->order){
				return;
			}
			$this->refundableOptionals = ($this->dataset->order->status === Booki_PaymentStatus::PAID || 
									$this->dataset->order->status === Booki_PaymentStatus::PARTIALLY_REFUNDED) && $this->hasFullControl;
			$this->refundableDays = $this->refundableOptionals && $this->dataset->bookedDays->count() > 1;
		}
		public function timezoneControlHeaderCollapsed(){
			return false;
		}
		public function timezoneControlCollapsed(){
			return true;
		}
	}
	$_Booki_BookingDetails = new Booki_BookingDetails();
	if(!$_Booki_BookingDetails->dataset || !$_Booki_BookingDetails->dataset->order){
		return;
	}
?>
<div class="booki booki-content-box">
	<div class="booki-callout booki-callout-default">
		<h4><?php echo __('Booking details', 'booki') ?></h4>
	</div>
	<div class="booki form-horizontal">
		<div>
			<div class="col-lg-12 booki-remove-horizontal-padding">
				<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
					<input type="hidden" name="controller" value="booki_managebookedday" />
					<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
					<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->dataset->currency?>" />
					<table class="table table-condensed table-striped">
						<tbody>
							<?php $projectName = null; ?>
							<?php foreach( $_Booki_BookingDetails->dataset->bookedDays as $item ) : ?>
								<?php if($projectName != $item->projectName):?>
									<tr class="booki-details-heading">
										<td>
											<strong><?php echo sprintf(__('Booked dates and time slots for %s', 'booki'), $item->projectName)?></strong>
										</td>
									</tr>
									<?php $projectName = $item->projectName; ?>
								<?php endif; ?>
								<tr>
									<td>
										<div class="col-sm-9">
											<div>
												<i class="glyphicon glyphicon-calendar"
																data-container="body" 
																data-toggle="popover" 
																data-placement="top" 
																data-content="<?php echo __('Booking date', 'booki') ?>"></i>
															<?php echo Booki_Helper::formatDate( $item->bookingDate) ?>
											</div>
											<?php if($item->hasTime()):?>
											<div>
												<i class="glyphicon glyphicon-globe"
													data-container="body" 
													data-toggle="popover" 
													data-placement="top" 
													data-content="<?php echo __('Time based on the admin timezone', 'booki') ?>"></i>
													<small>
														<strong><?php echo __('Admin Timezone', 'booki')?>:</strong>
														<?php echo $_Booki_BookingDetails->dataset->timezone ?> 
														<span class="label label-primary"><?php echo $_Booki_BookingDetails->dataset->formatTime($item) ?></span>
													</small>
													<br/>
													
											</div>
											<div>
												<i class="glyphicon glyphicon-user"
													data-container="body" 
													data-toggle="popover" 
													data-placement="top" 
													data-content="<?php echo __('Time based on customer timezone', 'booki') ?>"></i>
												<small>
													<strong><?php echo __('Customer Timezone', 'booki')?>:</strong>
													<?php echo $_Booki_BookingDetails->dataset->userDefinedTimezone ?>
													<span class="label label-default"><?php echo $_Booki_BookingDetails->dataset->formatTime($item, $_Booki_BookingDetails->dataset->userDefinedTimezone) ?></span>
												</small>
											</div>
											<?php endif; ?>
											<div class="booki-vertical-gap-xs"></div>
											<?php $item->fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableDays);?>
											
											<?php if($item->currentStatus):?>
												<div class="badge"><?php echo $item->currentStatus ?></div>
											<?php elseif (count($item->contextButtons) > 0): ?>
											<div class="btn-group">
												<button type="button" class="btn btn-<?php echo $item->getStatusLabel()?> btn-sm dropdown-toggle" data-toggle="dropdown">
													<?php echo __($item->getStatusText(), 'booki') ?>
													<span class="caret"></span>
												</button>
												<ul class="dropdown-menu" role="menu">
													<?php foreach($item->contextButtons as $key=>$value): ?>
													<li>
														<button 
															<?php if (strtolower($key) !== 'cancel'):?>
															name="<?php echo strtolower($key) ?>"
															type="submit"
															value="<?php echo $item->id ?>"
															<?php else: ?>
															data-booki-id="<?php echo $item->id ?>"
															type="button"
															data-toggle="modal" 
															data-target="#cancelDayModal"
															<?php endif;?>
															 class="booki-btnlink btn btn-default">
															<i class="glyphicon <?php echo $value['icon']?>"></i> 
															<?php echo __($value['label'], 'booki')?>
														</button>
													</li>
													<?php endforeach; ?>
												</ul>
											</div>
											<?php endif; ?>
											<hr class="visible-xs" />
											<div class="visible-xs">
												<span data-container="body" 
													data-toggle="popover" 
													data-placement="top" 
													data-content="<?php echo __('Cost', 'booki') ?>">
													<?php echo $_Booki_BookingDetails->dataset->formatCost($item->cost) ?>
												</span>
											</div>
										</div>
										<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
											<span class="visible-sm visible-md visible-lg">
												<span data-container="body" 
													data-toggle="popover" 
													data-placement="top" 
													data-content="<?php echo __('Cost', 'booki') ?>">
													<?php echo $_Booki_BookingDetails->dataset->formatCost($item->cost) . ' ' . $_Booki_BookingDetails->dataset->currency ?>
												</span>
											</span>
										</div>
										<div class="clearfix"></div>
									</td>
								</tr>
							<?php endforeach; ?>
							<?php if($_Booki_BookingDetails->dataset->bookedDays->count() === 0):?>
							<tr>
								<td>
								<div class="alert alert-info"><?php echo __('No days booked.', 'booki')?></div>
								</td>
							</tr>
							<?php endif;?>
						</tbody>
					</table>
					<div class="modal fade" id="cancelDayModal" tabindex="-1" role="dialog" aria-labelledby="cancelDayModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="cancelDayModalLabel"><?php echo __('Cancel booked date confirmation', 'booki') ?></h4>
								</div>
								<div class="modal-body">
									<?php echo __('When you cancel a booked date, the cost is deducted from the order total and the booked date is removed from the system. If there is only one date then the order is deleted.', 'booki') ?>
									<strong><?php echo __('Booki does not keep records of cancelled bookings. Proceed ?', 'booki') ?></strong>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
									<button
										name="cancel"
										class="btn btn-danger booki-confirm">
										<i class="glyphicon glyphicon-trash"></i>
										<?php echo __('Cancel', 'booki') ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</form>
				<?php if($_Booki_BookingDetails->dataset->bookedOptionals->count()): ?>
					<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
						<input type="hidden" name="controller" value="booki_managebookedoptionals" />
						<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
						<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->dataset->currency?>" />
						<ul class="list-group">
							<?php $projectName = null; ?>
							<?php foreach( $_Booki_BookingDetails->dataset->bookedOptionals as $item ) : ?>
							<?php if($projectName != $item->projectName):?>
							<li class="list-group-item booki-optionals-heading active">
								<strong><?php echo  sprintf(__('Optional/s for %s', 'booki'), $item->projectName) ?></strong>
							</li>
							<?php $projectName = $item->projectName; ?>
							<?php endif; ?>
							<li class="list-group-item">
								<div class="col-sm-9">
									<?php $item->fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableOptionals);?>
									<?php if($item->currentStatus):?>
										<div class="badge"><?php echo $item->currentStatus ?></div>
									<?php elseif (count($item->contextButtons) > 0): ?>
									<div class="btn-group">
										<button type="button" class="btn btn-<?php echo $item->getStatusLabel()?> btn-sm dropdown-toggle" data-toggle="dropdown">
											<?php echo __($item->getStatusText(), 'booki') ?>
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">
											<?php foreach($item->contextButtons as $key=>$value): ?>
											<li>
												<button 
													<?php if (strtolower($key) !== 'cancel'):?>
													name="<?php echo strtolower($key) ?>"
													type="submit"
													value="<?php echo $item->id ?>"
													<?php else: ?>
													data-booki-id="<?php echo $item->id ?>"
													type="button"
													data-toggle="modal" 
													data-target="#cancelOptionalModal"
													<?php endif;?>
													 class="booki-btnlink btn btn-default">
													<i class="glyphicon <?php echo $value['icon']?>"></i> 
													<?php echo __($value['label'], 'booki')?>
												</button>
											</li>
											<?php endforeach; ?>
										</ul>
									</div>
									<?php endif; ?>
									<div class="visible-xs booki-gap-top"></div>
									<i class="glyphicon glyphicon-plus-sign"></i>
									<?php echo $item->getName() ?>
									<hr class="visible-xs" />
									<div class="visible-xs">
										<span data-container="body" 
											data-toggle="popover" 
											data-placement="top" 
											data-content="<?php echo __('Cost', 'booki') ?>">
											<?php echo $_Booki_BookingDetails->dataset->formatCost($item->getCalculatedCost())  ?>
										</span>
									</div>
								</div>
								<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
									<span class="visible-sm visible-md visible-lg">
										<span data-container="body" 
											data-toggle="popover" 
											data-placement="top" 
											data-content="<?php echo __('Cost', 'booki') ?>">
											<?php echo $_Booki_BookingDetails->dataset->formatCost($item->getCalculatedCost())  . ' ' . $_Booki_BookingDetails->dataset->currency ?>
										</span>
									</span>
								</div>
								<div class="clearfix"></div>
							</li>
							<?php endforeach; ?>
							<?php if($_Booki_BookingDetails->dataset->bookedDays->count() === 0):?>
								<li class="list-group-item">
									<div class="alert alert-info">
										<?php echo __('No optionals in booking.', 'booki')?>
									</div>
								</li>
							<?php endif;?>
						</ul>
						<div class="modal fade" id="cancelOptionalModal" tabindex="-1" role="dialog" aria-labelledby="cancelOptionalModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="cancelOptionalModalLabel"><?php echo __('Cancel optional item confirmation', 'booki') ?></h4>
									</div>
									<div class="modal-body">
										<?php echo __('When you cancel an optional item, the cost is deducted from the order total and the optional item is removed from the system.', 'booki') ?>
										<strong><?php echo __('Booki does not keep records of cancelled optional item selections. Proceed ?', 'booki') ?></strong>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
										<button
											name="cancel"
											class="btn btn-danger booki-confirm">
											<i class="glyphicon glyphicon-trash"></i>
											<?php echo __('Cancel', 'booki') ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				<?php endif; ?>
				<?php if($_Booki_BookingDetails->dataset->bookedCascadingItems->count()): ?>
					<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
						<input type="hidden" name="controller" value="booki_managebookedcascadingitems" />
						<input type="hidden" name="orderid" value="<?php echo $_GET['orderid']?>" />
						<input type="hidden" name="currency" value="<?php echo $_Booki_BookingDetails->dataset->currency?>" />
						<ul class="list-group">
							<?php $projectName = null; ?>
							<?php foreach( $_Booki_BookingDetails->dataset->bookedCascadingItems as $item ) : ?>
							<?php if($projectName != $item->projectName):?>
							<li class="list-group-item booki-optionals-heading active">
								<strong><?php echo  sprintf(__('Optional/s for %s', 'booki'), $item->projectName) ?></strong>
							</li>
							<?php $projectName = $item->projectName; ?>
							<?php endif; ?>
							<li class="list-group-item">
								<div class="col-sm-9">
									<?php $item->fillContextMenu($_Booki_BookingDetails->canEdit, $_Booki_BookingDetails->canCancel, $_Booki_BookingDetails->refundableOptionals);?>
									
									<?php if($item->currentStatus):?>
										<div class="badge"><?php echo $item->currentStatus ?></div>
									<?php elseif (count($item->contextButtons) > 0): ?>
									<div class="btn-group">
										<button type="button" class="btn btn-<?php echo $item->getStatusLabel()?> btn-sm dropdown-toggle" data-toggle="dropdown">
											<?php echo __($item->getStatusText(), 'booki') ?>
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu" role="menu">
											<?php foreach($item->contextButtons as $key=>$value): ?>
											<li>
												<button 
													<?php if (strtolower($key) !== 'cancel'):?>
													name="<?php echo strtolower($key) ?>"
													type="submit"
													value="<?php echo $item->id ?>"
													<?php else: ?>
													data-booki-id="<?php echo $item->id ?>"
													type="button"
													data-toggle="modal" 
													data-target="#cancelCascadingModal"
													<?php endif;?>
													 class="booki-btnlink btn btn-default">
													<i class="glyphicon <?php echo $value['icon']?>"></i> 
													<?php echo __($value['label'], 'booki')?>
												</button>
											</li>
											<?php endforeach; ?>
										</ul>
									</div>
									<?php endif; ?>
									<div class="visible-xs booki-gap-top"></div>
									<i class="glyphicon glyphicon-plus-sign"></i>
									<?php echo $item->getName() ?>
									<hr class="visible-xs" />
									<div class="visible-xs">
										<span data-container="body" 
											data-toggle="popover" 
											data-placement="top" 
											data-content="<?php echo __('Cost', 'booki') ?>">
											<?php echo $_Booki_BookingDetails->dataset->formatCost($item->getCalculatedCost())  ?>
										</span>
									</div>
								</div>
								<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
									<span class="visible-sm visible-md visible-lg">
										<span data-container="body" 
											data-toggle="popover" 
											data-placement="top" 
											data-content="<?php echo __('Cost', 'booki') ?>">
											<?php echo $_Booki_BookingDetails->dataset->formatCost($item->getCalculatedCost())  . ' ' . $_Booki_BookingDetails->dataset->currency ?>
										</span>
									</span>
								</div>
								<div class="clearfix"></div>
							</li>
							<?php endforeach; ?>
							<?php if($_Booki_BookingDetails->dataset->bookedDays->count() === 0):?>
								<li class="list-group-item">
									<div class="alert alert-info">
										<?php echo __('No optionals in booking.', 'booki')?>
									</div>
								</li>
							<?php endif;?>
						</ul>
						<div class="modal fade" id="cancelCascadingModal" tabindex="-1" role="dialog" aria-labelledby="cancelCascadingModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="cancelCascadingModalLabel"><?php echo __('Cancel cascading item confirmation', 'booki') ?></h4>
									</div>
									<div class="modal-body">
										<?php echo __('When you cancel an cascading item, the cost is deducted from the order total and the cascading item is removed from the system.', 'booki') ?>
										<strong><?php echo __('Booki does not keep records of cancelled cascading item selections. Proceed ?', 'booki') ?></strong>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
										<button
											name="cancel"
											class="btn btn-danger booki-confirm">
											<i class="glyphicon glyphicon-trash"></i>
											<?php echo __('Cancel', 'booki') ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<div>
			<div class="clearfix"></div>
			<div class="pull-right">
				<?php if($_Booki_BookingDetails->dataset->totalCost > 0):?>
				<div class="booki-subtotal">
					<strong><?php echo __('Subtotal', 'booki') ?></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->formatCost($_Booki_BookingDetails->dataset->totalCost) ?>
						<span> 
							<?php echo $_Booki_BookingDetails->dataset->currency ?>
						</span>
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->totalCost > 0 && $_Booki_BookingDetails->dataset->deposit > 0):?>
				<div>
						<strong><span class="booki-total-label"><?php echo __('Due on arrival', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->currencySymbol . $_Booki_BookingDetails->dataset->formattedTotalAmountIncludingTax ?>
					</span>
					<span> 
						<?php echo $_Booki_BookingDetails->dataset->currency ?>
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->depositSubTotalFormatted):?>
				<div>
					<strong><span class="booki-total-label"><?php echo __('Deposit Subtotal', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->currencySymbol . $_Booki_BookingDetails->dataset->depositSubTotalFormatted ?>
					</span>
					<span> 
						<?php echo $_Booki_BookingDetails->dataset->currency ?>
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->tax > 0):?>
				<div>
					<strong><span class="booki-tax-label"><?php echo __('Tax', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->tax ?>%
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->discount): ?>
				<div>
					<strong>
						<span class="booki-coupon-label"><?php echo __('Discount', 'booki') ?></span>
					</strong>
					<span>
						-<?php echo $_Booki_BookingDetails->dataset->discount ?>%
					</span>
				</div>
				<?php endif; ?>
				<?php if($_Booki_BookingDetails->dataset->refundTotal > 0):?>
				<div>
					<strong><span class="booki-refund-label"><?php echo __('Refunded', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->currencySymbol . $_Booki_BookingDetails->dataset->refundTotal ?>
					</span>
					<span> 
						<?php echo $_Booki_BookingDetails->dataset->currency ?>
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->deposit > 0):?>
				<div>
					<strong><span class="booki-deposit-label"><?php echo __('Deposit', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->currencySymbol . $_Booki_BookingDetails->dataset->deposit ?>
					</span>
					<span> 
						<?php echo $_Booki_BookingDetails->dataset->currency ?>
					</span>
				</div>
				<?php endif;?>
				<?php if($_Booki_BookingDetails->dataset->totalCost > 0 && !($_Booki_BookingDetails->dataset->deposit > 0)):?>
				<div>
					<strong><span class="booki-total-label"><?php echo __('Total', 'booki') ?></span></strong>
					<span>
						<?php echo $_Booki_BookingDetails->dataset->currencySymbol . $_Booki_BookingDetails->dataset->formattedTotalAmountIncludingTax ?>
					</span>
					<span> 
						<?php echo $_Booki_BookingDetails->dataset->currency ?>
					</span>
				</div>
				<?php endif;?>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$("#cancelDayModal").BookiModalPopup();
		$("#cancelOptionalModal").BookiModalPopup();
		$("#cancelCascadingModal").BookiModalPopup();
	});
</script>