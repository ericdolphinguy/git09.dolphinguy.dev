<?php
	/**
	* Template Name: Booki Cart Details
	*/
	$_Booki_CheckoutGridTmpl = new Booki_CheckoutGridTmpl();
?>
<form class="booki form-horizontal" data-parsley-validate action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
	<input type="hidden" name="booki_nonce" value="<?php echo Booki_NonceHelper::create('booki-checkout-grid');?>"/>
	<div class="booki col-lg-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<?php if($_Booki_CheckoutGridTmpl->confirmCheckout): ?>
					<?php if($_Booki_CheckoutGridTmpl->checkoutFailure): ?>
						<div class="alert alert-danger">
							 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT_LOC, $_Booki_CheckoutGridTmpl->checkoutFailure) ?>
						</div>
					<?php elseif($_Booki_CheckoutGridTmpl->paymentSuccess): ?>
						<div class="alert alert-success">
							 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<p>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT_LOC ?>
								<?php if(is_user_logged_in()){ 
										echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY_LOC;
								} ?>
							</p>
							<p>
								<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->ORDER_ID_REF_LOC, $_Booki_CheckoutGridTmpl->data->orderId)?>
								<?php if(is_user_logged_in()){
										echo sprintf($_Booki_CheckoutGridTmpl->resx->VIEW_ORDER_HISTORY_LOC
											, sprintf('<a href="%s">%s</a>'
												, $_Booki_CheckoutGridTmpl->orderHistoryUrl
												, $_Booki_CheckoutGridTmpl->resx->HISTORY_LOC));
									}
								?>
							</p>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<?php if($_Booki_CheckoutGridTmpl->checkoutSuccessMessage): ?>
				<div class="alert alert-success">
					 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<p>
						<?php echo $_Booki_CheckoutGridTmpl->checkoutSuccessMessage ?>
					</p>
					<?php if(isset($_Booki_CheckoutGridTmpl->data->orderId)):?>
					<p>
						<?php echo sprintf($_Booki_CheckoutGridTmpl->resx->ORDER_ID_REF_LOC, $_Booki_CheckoutGridTmpl->data->orderId)?>
						<?php if(is_user_logged_in()){
								echo sprintf($_Booki_CheckoutGridTmpl->resx->VIEW_ORDER_HISTORY_LOC
									, sprintf('<a href="%s">%s</a>'
										, $_Booki_CheckoutGridTmpl->orderHistoryUrl
										, $_Booki_CheckoutGridTmpl->resx->HISTORY_LOC)); 
							}
						?>
					</p>
					<?php endif;?>
				</div>
				<?php endif; ?>
				<?php if(isset($_Booki_CheckoutGridTmpl->data->hasBookedElements) && $_Booki_CheckoutGridTmpl->data->hasBookedElements):?>
				<div class="alert alert-warning">
					 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE_LOC ?>
				</div>
				<?php endif; ?>
				<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
				<div class="booki col-lg-12 booki-remove-horizontal-padding">
					<table class="table table-condensed table-striped">
						<thead>
							<tr>
								<th><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_CHECKOUT_CART_HEADING_LOC ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach( $_Booki_CheckoutGridTmpl->data->bookings as $booking ) : ?>
								<?php if(isset($booking->bookingExhausted) && $booking->bookingExhausted): ?>
									<div class="alert alert-danger">
										 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKINGS_EXHAUSTED_LOC ?>
									</div>
								<?php endif;?>
								<tr>
									<td>
										<?php if($_Booki_CheckoutGridTmpl->data->enableCartItemHeader):?>
										<p>
											<small class="text-muted">
												<?php echo $booking->projectName ?>
											</small>
										</p>
										<?php endif; ?>
										<ul class="list-group">
										<?php if(count($booking->dates) > 1):?>
											<?php foreach( $booking->dates as $item ) : ?>
												<li class="booki-list-group-item-borderless">
													<div class="col-sm-9">
														<div> 
															<i class="glyphicon glyphicon-calendar"
																data-container="body" 
																data-toggle="popover" 
																data-placement="top" 
																data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_DATE_LOC ?>"></i>
																<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
																	<?php echo $item['formattedDate'] ?>
																</span>
														</div>
														<hr class="visible-xs" />
														<div class="visible-xs">
															<?php if($_Booki_CheckoutGridTmpl->editable && (isset($item['isRequired']) && !$item['isRequired'])): ?>
															<button type="submit"
																	name="booki_remove_date" 
																	class="booki-styleless-btn pull-right" 
																	value="<?php echo $item['bookingId'] . ':' . $item['rawDate'] ?>">
																<i class="glyphicon glyphicon-trash remove-date"></i>
															</button>
															<?php endif; ?>
															<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"
																data-container="body" 
																data-toggle="popover" 
																data-placement="top" 
																data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																<?php echo $item['formattedCost'] ?>
															</span>
														</div>
													</div>
													<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
														<span class="visible-sm visible-md visible-lg">
															<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['isBooked']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
															<button name="booki_remove_date" 
																	class="btn btn-default btn-sm" 
																	type="submit" value="<?php echo $item['bookingId'] . ':' . $item['rawDate'] ?>"> 
																	<span class="booki-cart-cost">
																		<span><?php echo $item['formattedCost'] ?></span>
																		<span><?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																	</span>
																	<i class="glyphicon glyphicon-trash remove-date"></i>
															</button>
															<?php else:?>
															<span class="booki-cart-cost <?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
																<span><?php echo $item['formattedCost'] ?></span>
																<span><?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
															</span>
															<?php endif; ?>
														</span>
													</div>
													<div class="clearfix"></div>
												</li>
											<?php endforeach; ?>
										<?php elseif (count($booking->dates) > 0): ?>
											<li class="booki-list-group-item-borderless">
												<div class="col-sm-9">
													<div class="<?php echo  $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
														<i class="glyphicon glyphicon-calendar"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_DATE_LOC ?>"></i>
														<?php if(isset($booking->dates[0]['formattedDate'])){
																		echo $booking->dates[0]['formattedDate'];
																}
														?>
													</div>
													<?php if($booking->dates[0]['formattedTime']):?>
													<div class="<?php echo $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
														<i class="glyphicon glyphicon-time"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOKING_TIME_LOC ?>"></i>
															<?php echo $booking->dates[0]['formattedTime'] ?>
													</div>
													<?php if ($_Booki_CheckoutGridTmpl->displayTimezone):?>
													<div>
														<i class="glyphicon glyphicon-globe"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo sprintf('%s : %s'
																					, $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_TIMEZONE_OFFSET_LOC
																					, $_Booki_CheckoutGridTmpl->data->timezoneInfo['abbr']) ?>"></i>
														<span>
															<small>
																<?php echo $_Booki_CheckoutGridTmpl->data->timezoneInfo['timezone'] ?> 
															</small>
														</span>
													</div>
													<?php endif; ?>
													<?php endif; ?>
													<hr class="visible-xs" />
													<div class="visible-xs">
														<?php if(($_Booki_CheckoutGridTmpl->editable && !$booking->dates[0]['isBooked']) &&
																	(isset($booking->dates[0]['isRequired']) && !$booking->dates[0]['isRequired'])): ?>
														<button type="submit" name="booki_remove_order" 
																class="booki-styleless-btn pull-right" 
																value="<?php echo $booking->dates[0]['bookingId']?>">
															<i class="glyphicon glyphicon-trash"></i>
														</button>
														<?php endif; ?>
														<span class="<?php echo $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>"
															data-container="body" 
															data-toggle="popover" 
															data-placement="top" 
															data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
															<?php echo $booking->dates[0]['formattedCost'] ?>
														</span>
													</div>
												</div>
												<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
													<span class="visible-sm visible-md visible-lg <?php echo $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
														<?php if(($_Booki_CheckoutGridTmpl->editable && !$booking->dates[0]['isBooked']) &&
																			(isset($booking->dates[0]['isRequired']) && !$booking->dates[0]['isRequired'])): ?>
														<button type="submit" 
																name="booki_remove_order" 
																class="btn btn-default btn-sm" 
																value="<?php echo $booking->dates[0]['bookingId']?>">
																	<span><?php if(isset($booking->dates[0]['formattedCost'])){
																					echo $booking->dates[0]['formattedCost'];
																				}
																			?></span>
																	<span><?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																	<i class="glyphicon glyphicon-trash"></i>
														</button>
														<?php else: ?>
															<?php if(isset($booking->dates[0]['formattedCost'])):?>
															<span><?php echo $booking->dates[0]['formattedCost'] ?></span>
															<?php endif; ?>
															<span><?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
														<?php endif; ?>
													</span>
												</div>
												<div class="clearfix"></div>
											</li>
										<?php endif; ?>
										</ul>
										<?php if(count($booking->optionals) > 0):?>
											<ul class="list-group">
												<?php foreach( $booking->optionals as $item ) : ?>
													<li class="list-group-item booki-list-group-item">
														<div class="col-sm-9">
															<div class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
																<i class="glyphicon glyphicon-plus-sign"></i>
																	<?php echo $item['calculatedName'] ?>
															</div>
															<hr class="visible-xs" />
															<div class="visible-xs">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['isBooked']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button type="submit" 
																		name="booki_remove_optional" 
																		class="booki-styleless-btn pull-right" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>">
																	<i class="glyphicon glyphicon-trash"></i>
																</button>
																<?php endif; ?>
																<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"
																	data-container="body" 
																	data-toggle="popover" 
																	data-placement="top" 
																	data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																	<?php echo $item['formattedCalculatedCost'] ?>
																</span>
															</div>
														</div>
														<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
															<span class="visible-sm visible-md visible-lg">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['isBooked']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button 
																		name="booki_remove_optional" 
																		class="btn btn-default btn-xs" 
																		type="submit" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
																		<span><?php echo $item['formattedCalculatedCost'] ?></span>
																		<span> <?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																	<i class="glyphicon glyphicon-trash remove-option"></i>
																</button>
																<?php else: ?>
																	<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"><?php echo $item['formattedCalculatedCost'] ?>
																		<?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																<?php endif; ?>
															</span>
														</div>
														<div class="clearfix"></div>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
										<?php if(count($booking->cascadingItems) > 0):?>
											<ul class="list-group">
												<?php foreach( $booking->cascadingItems as $item ) : ?>
													<li class="list-group-item booki-list-group-item">
														<div class="col-sm-9">
															<div class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
																<i class="glyphicon glyphicon-plus-sign"></i>
																	<?php echo $item['calculatedName'] ?>
															</div>
															<hr class="visible-xs" />
															<div class="visible-xs">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['isBooked']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button type="submit" 
																		name="booki_remove_cascadingitem" 
																		class="booki-styleless-btn pull-right" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>">
																	<i class="glyphicon glyphicon-trash"></i>
																</button>
																<?php endif; ?>
																<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"
																	data-container="body" 
																	data-toggle="popover" 
																	data-placement="top" 
																	data-content="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COST_LOC ?>">
																	<?php echo $item['formattedCalculatedCost'] ?>
																</span>
															</div>
														</div>
														<div class="col-sm-3 visible-sm visible-md visible-lg booki-cart-price-align">
															<span class="visible-sm visible-md visible-lg">
																<?php if(($_Booki_CheckoutGridTmpl->editable && !$item['isBooked']) &&
																			(isset($item['isRequired']) && !$item['isRequired'])): ?>
																<button 
																		name="booki_remove_cascadingitem" 
																		class="btn btn-default btn-xs" 
																		type="submit" 
																		value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
																		<span><?php echo $item['formattedCalculatedCost'] ?></span>
																		<span> <?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																	<i class="glyphicon glyphicon-trash remove-option"></i>
																</button>
																<?php else: ?>
																	<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"><?php echo $item['formattedCalculatedCost'] ?>
																		<?php echo $_Booki_CheckoutGridTmpl->data->currency ?></span>
																<?php endif; ?>
															</span>
														</div>
														<div class="clearfix"></div>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
										<?php if($booking->deposit > 0):?>
										<div class="col-sm-9  booki-cart-price-align">
											<strong><?php echo $_Booki_CheckoutGridTmpl->resx->SUBTOTAL_LOC ?></strong>
										</div>
										<div class="col-sm-3 booki-cart-price-align">
											<?php echo $_Booki_CheckoutGridTmpl->data->currencySymbol . 
														Booki_Helper::toMoney($booking->subTotal) . 
														$_Booki_CheckoutGridTmpl->data->currency
											?>
										</div>
										<div class="col-sm-9 booki-cart-price-align">
											<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW_LOC ?></strong>
										</div>
										<div class="col-sm-3 booki-cart-price-align">
											<?php echo $_Booki_CheckoutGridTmpl->data->currencySymbol . 
														Booki_Helper::toMoney($booking->deposit) . 
														$_Booki_CheckoutGridTmpl->data->currency
											?>
										</div>
										<div class="col-sm-9 booki-cart-price-align">
											<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL_LOC ?></strong>
										</div>
										<div class="col-sm-3 booki-cart-price-align">
											<?php echo $_Booki_CheckoutGridTmpl->data->currencySymbol . 
														Booki_Helper::toMoney($booking->total) . 
														$_Booki_CheckoutGridTmpl->data->currency
											?>
										</div>
										<?php endif;?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
					<div class="alert alert-warning">
						 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT_LOC ?></p>
					</div>
				<?php endif; ?>
				<div class="clearfix"></div>
				<?php if($_Booki_CheckoutGridTmpl->data->hasDiscount && (!$_Booki_CheckoutGridTmpl->confirmCheckout && !$_Booki_CheckoutGridTmpl->checkoutSuccessMessage)): ?>
				<div class="alert alert-success alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<strong><?php echo $_Booki_CheckoutGridTmpl->resx->CONGRATS_LOC ?></strong> <?php echo sprintf($_Booki_CheckoutGridTmpl->resx->GOT_DISCOUNT_LOC, Booki_Helper::toMoney($_Booki_CheckoutGridTmpl->data->discount) ) ?>
				</div>
				<?php endif;?>
				<div class="booki col-md-6 booki-remove-horizontal-padding">
					<?php if($_Booki_CheckoutGridTmpl->enableCoupons && (!$_Booki_CheckoutGridTmpl->data->hasDiscount && !$_Booki_CheckoutGridTmpl->data->hasDeposit)):?>
					<div class="input-group">
					  <input type="text"
							id="booki_couponcode"
							name="booki_couponcode" 
							class="form-control" 
							value="<?php echo $_Booki_CheckoutGridTmpl->coupon ? $_Booki_CheckoutGridTmpl->coupon->code : '' ?>"
							placeholder="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_CODE_LOC ?>" />
						<div class="input-group-btn">
							<button class="btn btn-default booki-redeem-button" 
							type="submit" name="booki_redeem_coupon" title="<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REDEEM_LOC ?>">
								<i class="glyphicon glyphicon-ok-circle"></i>
							</button>
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
								<span class="caret"></span>
								<span class="sr-only"><?php echo __('Toggle Dropdown', 'booki') ?></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>
									<button class="btn btn-default booki-styleless-btn dropdown-button"
										name="booki_redeem_coupon"
										type="submit">
										<i class="glyphicon glyphicon-ok-circle"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REDEEM_LOC ?>
									</button>
								</li>
								<li>
									<button class="btn btn-default booki-styleless-btn dropdown-button" 
										type="submit" name="booki_cancel_coupon">
										<i class="glyphicon glyphicon-remove-circle"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_REMOVE_COUPON_LOC ?>
									</button>
								</li>
								<li>
									<a class="booki-coupon-help accordion-toggle btn btn-default booki-styleless-btn dropdown-button" data-toggle="collapse" href=".booki-coupon-info">
										<i class="glyphicon glyphicon-question-sign help"></i>
										<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_COUPON_HELP_LOC ?>
									</a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
					</div>
					<?php if($_Booki_CheckoutGridTmpl->couponErrorMessage): ?>
					<ul class="data-parsley-error-list">
						<li><?php echo $_Booki_CheckoutGridTmpl->couponErrorMessage ?></li>
					</ul>
					<?php endif; ?>
					<div id="booki_couponcode_error"></div>
					<div class="clearfix"></div>
					<div class="accordion-body">
						<div class="panel panel-info booki-coupon-info collapse">
							<div class="panel-heading">
								<a class="close accordion-toggle" data-toggle="collapse" href=".booki-coupon-info">&times;</a>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_HOW_COUPONS_WORK_LOC ?>
							</div>
						  <div class="panel-body">
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP_LOC ?></p>
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP_LOC ?></p>
							<p><?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP_LOC ?></p>
						  </div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<div class="booki col-md-6 booki-cart-totals-row booki-remove-horizontal-padding">
					<div>
						<div class="col-lg-6">
							<strong><?php echo $_Booki_CheckoutGridTmpl->resx->SUBTOTAL_LOC ?></strong>
						</div>
						<div class="col-lg-6">
								<?php echo $_Booki_CheckoutGridTmpl->data->currencySymbol . $_Booki_CheckoutGridTmpl->data->formattedTotalAmount ?>
									<?php echo $_Booki_CheckoutGridTmpl->data->currency ?>
						</div>
					</div>
					<?php if($_Booki_CheckoutGridTmpl->data->tax && $_Booki_CheckoutGridTmpl->data->hasBookings):?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-tax-label"><?php echo $_Booki_CheckoutGridTmpl->resx->TAX_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
								<?php echo $_Booki_CheckoutGridTmpl->data->tax ?>%
						</div>
					</div>
					<?php endif;?>
					<?php if($_Booki_CheckoutGridTmpl->data->hasDiscount): ?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-discount-label"><?php echo $_Booki_CheckoutGridTmpl->resx->DISCOUNT_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							-<?php echo $_Booki_CheckoutGridTmpl->data->discount ?>%
						</div>
					</div>
					<?php elseif($_Booki_CheckoutGridTmpl->coupon && $_Booki_CheckoutGridTmpl->coupon->isValid()): ?>
					<div>
						<div class="col-lg-6">
							<strong><span class="booki-discount-label"><?php echo $_Booki_CheckoutGridTmpl->resx->DISCOUNT_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							-<?php echo $_Booki_CheckoutGridTmpl->coupon->discount ?>%
						</div>
					</div>
					<?php endif; ?>
					<div>
						<div class="col-lg-6">
						<strong><span class="booki-total-label"><?php echo $_Booki_CheckoutGridTmpl->resx->TOTAL_LOC ?></span></strong>
						</div>
						<div class="col-lg-6">
							<?php echo $_Booki_CheckoutGridTmpl->data->currencySymbol . $_Booki_CheckoutGridTmpl->data->formattedTotalAmountIncludingTax . $_Booki_CheckoutGridTmpl->data->currency ?>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<?php if($_Booki_CheckoutGridTmpl->showFooter): ?>
			<div class="panel-footer">
				<?php if($_Booki_CheckoutGridTmpl->confirmCheckout && !$_Booki_CheckoutGridTmpl->globalSettings->autoConfirmOrderAfterPayment): ?>
					<div class="alert alert-info">
						 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PAYMENT_AUTHORIZED_LOC ?>
					</div>
				<?php endif; ?>
				<div class="pull-right">
					<?php if(!$_Booki_CheckoutGridTmpl->confirmCheckout): ?>
						<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
							<?php if($_Booki_CheckoutGridTmpl->editable): ?>
							<button type="submit" 
									name="booki_empty_cart" 
									class="btn btn-danger booki-empty-cart">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_EMPTY_CART_LOC ?>
							</button>
							<?php endif; ?>
						<div class="visible-xs booki-vertical-gap-xs"></div>
						<?php endif;?>
						<?php if($_Booki_CheckoutGridTmpl->editable): ?>
						<button type="submit" 
								name="booki_continue_booking" 
								value="<?php echo $_Booki_CheckoutGridTmpl->globalSettings->continueBookingUrl ?>" 
								class="btn btn-primary booki-book-more">
							<i class="glyphicon glyphicon-plus-sign"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_BOOK_MORE_LOC ?>
						</button>
						<?php endif; ?>
						<div class="visible-xs booki-vertical-gap-xs"></div>
						<?php if($_Booki_CheckoutGridTmpl->data->hasBookings): ?>
							<?php if ( is_user_logged_in()  || !$_Booki_CheckoutGridTmpl->data->globalSettings->membershipRequired): ?> 
								<?php if($_Booki_CheckoutGridTmpl->data->enableBookingWithAndWithoutPayment || !$_Booki_CheckoutGridTmpl->globalSettings->enablePayments):?>
									<button type="submit" 
											name="booki_checkout"
											value="0"
											class="btn btn-primary booki-make-booking">
											<?php echo $_Booki_CheckoutGridTmpl->globalSettings->enablePayments ? $_Booki_CheckoutGridTmpl->resx->BOOK_NOW_PAY_LATER_LOC : $_Booki_CheckoutGridTmpl->resx->BOOK_NOW_LOC ?>
									</button>
								<?php endif; ?>
								<?php if ($_Booki_CheckoutGridTmpl->data->globalSettings->enablePayments && $_Booki_CheckoutGridTmpl->data->totalAmount > 0): ?> 
									<button type="submit" 
											name="booki_checkout" 
											value="1"
											class="booki-cart-checkout">
											<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left">
									</button>
								<?php endif; ?>
							<?php else: ?>
							<a class="btn btn-success booki-proceed" href="<?php echo Booki_Helper::appendReferrer($_Booki_CheckoutGridTmpl->globalSettings->loginPageUrl, 'redirect_to') ?>">
								<i class="glyphicon glyphicon-circle-arrow-right"></i>
								<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_PROCEED_LOC ?>
							</a>
							<?php endif; ?>
						<?php endif; ?>
					<?php elseif(!$_Booki_CheckoutGridTmpl->globalSettings->autoConfirmOrderAfterPayment): ?>
						<button class="btn btn-primary pull-right" name="booki_paypal_process_payment">
							<i class="glyphicon glyphicon-circle-arrow-right"></i>
							<?php echo $_Booki_CheckoutGridTmpl->resx->CHECKOUT_GRID_CONFIRM_AND_PAY_LOC ?>
						</button>
					<?php endif; ?>
				</div>
				<div class="clearfix"></div>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="clearfix"></div>
</form>