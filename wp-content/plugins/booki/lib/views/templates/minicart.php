<?php

	$_Booki_MiniCartTmpl = new Booki_MiniCartTmpl();
?>
<div class="booki">
	<form class="booki form-horizontal" data-parsley-validate action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
		<div class="btn-group">
			<a href="<?php echo $_Booki_MiniCartTmpl->url ?>" class="btn btn-primary" <?php echo $_Booki_MiniCartTmpl->data->hasBookings ? '' : 'disabled' ?>>
				<span>
					<span class="badge"><?php echo $_Booki_MiniCartTmpl->data->currencySymbol . $_Booki_MiniCartTmpl->data->formattedTotalAmountIncludingTax ?></span>
				</span>
				<i class="glyphicon glyphicon-shopping-cart"></i> 
				<span>
					(<?php echo $_Booki_MiniCartTmpl->data->bookingsCount?>)
				</span>
			</a>
			<?php if($_Booki_MiniCartTmpl->data->hasBookings): ?>
			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="booki-minicart dropdown-menu" role="menu">
			<?php if($_Booki_MiniCartTmpl->data->hasBookedElements): ?>
			<li>
				<div class="booki col-lg-12">
					<div class="alert alert-warning alert-dismissable">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<strong><?php echo __('Oops!', 'booki') ?></strong> <?php echo __('Some items got booked by someone else before you could checkout.', 'booki')?>
					</div>
				</div>
			</li>
			<?php endif;?>
				<?php foreach( $_Booki_MiniCartTmpl->data->bookings as $booking ) : ?>
				<li>
					<div class="booki col-lg-12">
						<?php if($_Booki_MiniCartTmpl->data->enableCartItemHeader):?>
						<h4 class="list-group-item-heading">
							<?php echo $booking->projectName ?>
						</h4>
						<?php endif; ?>
					</div>
					<div class="booki col-lg-12">
						<ul class="list-group">
						<?php if(count($booking->dates) > 1):?>
							<?php foreach( $booking->dates as $item ) : ?>
								<li class="booki-list-group-item-borderless">
									<div class="col-sm-6">
										<div> 
											<i class="glyphicon glyphicon-calendar"></i>
												<span class="booki-formatted-date <?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
													<?php echo $item['formattedDate'] ?>
												</span>
										</div>
										<hr class="visible-xs" />
										<div class="visible-xs">
											<?php if($_Booki_MiniCartTmpl->editable): ?>
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
												data-content="<?php echo __('Cost', 'booki') ?>">
												<?php echo $item['formattedCost'] ?>
											</span>
										</div>
									</div>
									<div class="col-sm-6 visible-sm visible-md visible-lg booki-cart-price-align">
										<span class="visible-sm visible-md visible-lg">
											<?php if($_Booki_MiniCartTmpl->editable && !$item['isBooked']): ?>
											<button name="booki_remove_date" 
													class="btn btn-default btn-sm" 
													type="submit" value="<?php echo $item['bookingId'] . ':' . $item['rawDate'] ?>"> 
													<span class="booki-cart-cost">
														<span><?php echo $item['formattedCost'] ?></span>
														<span><?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
													</span>
													<i class="glyphicon glyphicon-trash remove-date"></i>
											</button>
											<?php else:?>
											<span class="booki-cart-cost <?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
												<span><?php echo $item['formattedCost'] ?></span>
												<span><?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
											</span>
											<?php endif; ?>
										</span>
									</div>
									<div class="clearfix"></div>
								</li>
							<?php endforeach; ?>
						<?php elseif (count($booking->dates) > 0): ?>
							<li class="booki-list-group-item-borderless">
								<p class="list-group-item-text">
									<div class="col-sm-6">
										<div class="booki-formatted-date <?php echo  $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
											<i class="glyphicon glyphicon-calendar"></i>
											<?php if(isset($booking->dates[0]['formattedDate'])){
															echo $booking->dates[0]['formattedDate'];
													}
											?>
										</div>
										<?php if($booking->dates[0]['formattedTime']):?>
										<div class="booki-formatted-time <?php echo $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
											<i class="glyphicon glyphicon-time"></i>
												<?php echo $booking->dates[0]['formattedTime'] ?>
										</div>
										<?php if($_Booki_MiniCartTmpl->displayTimezone):?>
										<div>
											<i class="glyphicon glyphicon-globe"></i>
											<span>
												<small>
													<?php echo $_Booki_MiniCartTmpl->data->timezoneInfo['timezone'] ?> 
												</small>
											</span>
										</div>
										<?php endif; ?>
										<?php endif; ?>
										<hr class="visible-xs" />
										<div class="visible-xs">
											<?php if($_Booki_MiniCartTmpl->editable && !$booking->dates[0]['isBooked']): ?>
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
												data-content="<?php echo __('Cost', 'booki') ?>">
												<?php echo $booking->dates[0]['formattedCost'] ?>
											</span>
										</div>
									</div>
									<div class="col-sm-6 visible-sm visible-md visible-lg booki-cart-price-align">
										<span class="visible-sm visible-md visible-lg <?php echo $booking->dates[0]['isBooked'] ? 'booki-strike' : '' ?>">
											<?php if($_Booki_MiniCartTmpl->editable && !$booking->dates[0]['isBooked']): ?>
											<button type="submit" 
													name="booki_remove_order" 
													class="btn btn-default btn-sm" 
													value="<?php echo $booking->dates[0]['bookingId']?>">
														<span><?php if(isset($booking->dates[0]['formattedCost'])){
																		echo $booking->dates[0]['formattedCost'];
																	}
																?></span>
														<span><?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
														<i class="glyphicon glyphicon-trash"></i>
											</button>
											<?php else: ?>
												<?php if(isset($booking->dates[0]['formattedCost'])):?>
												<span><?php echo $booking->dates[0]['formattedCost'] ?></span>
												<?php endif; ?>
												<span><?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
											<?php endif; ?>
										</span>
									</div>
								</p>
								<div class="clearfix"></div>
							</li>
						<?php endif; ?>
						</ul>
						<?php if(count($booking->optionals) > 0):?>
							<hr class="booki-half-rule"/>
							<ul class="list-group">
								<?php foreach( $booking->optionals as $item ) : ?>
									<li class="list-group-item booki-list-group-item">
										<div class="col-sm-6">
											<div class="booki-align-center <?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
												<i class="glyphicon glyphicon-plus-sign"></i>
													<?php echo $item['calculatedName'] ?>
											</div>
											<hr class="visible-xs" />
											<div class="visible-xs">
												<?php if($_Booki_MiniCartTmpl->editable && !$item['isBooked']): ?>
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
													data-content="<?php echo __('Cost', 'booki') ?>">
													<?php echo $item['formattedCalculatedCost'] ?>
												</span>
											</div>
										</div>
										<div class="col-sm-6 visible-sm visible-md visible-lg booki-cart-price-align">
											<span class="visible-sm visible-md visible-lg">
												<?php if($_Booki_MiniCartTmpl->editable && !$item['isBooked']): ?>
												<button 
														name="booki_remove_optional" 
														class="btn btn-default btn-xs" 
														type="submit" 
														value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
														<span><?php echo $item['formattedCalculatedCost'] ?></span>
														<span> <?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
													<i class="glyphicon glyphicon-trash remove-option"></i>
												</button>
												<?php else: ?>
													<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"><?php echo $item['formattedCalculatedCost'] ?>
														<?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
												<?php endif; ?>
											</span>
										</div>
										<div class="clearfix"></div>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if(count($booking->cascadingItems) > 0):?>
							<hr class="booki-half-rule"/>
							<ul class="list-group">
								<?php foreach( $booking->cascadingItems as $item ) : ?>
									<li class="list-group-item booki-list-group-item">
										<div class="col-sm-6">
											<div class="booki-align-center <?php echo $item['isBooked'] ? 'booki-strike' : '' ?>">
												<i class="glyphicon glyphicon-plus-sign"></i>
													<?php echo $item['calculatedName'] ?>
											</div>
											<hr class="visible-xs" />
											<div class="visible-xs">
												<?php if($_Booki_MiniCartTmpl->editable && !$item['isBooked']): ?>
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
													data-content="<?php echo __('Cost', 'booki') ?>">
													<?php echo $item['formattedCalculatedCost'] ?>
												</span>
											</div>
										</div>
										<div class="col-sm-6 visible-sm visible-md visible-lg booki-cart-price-align">
											<span class="visible-sm visible-md visible-lg">
												<?php if($_Booki_MiniCartTmpl->editable && !$item['isBooked']): ?>
												<button 
														name="booki_remove_cascadingitem" 
														class="btn btn-default btn-xs" 
														type="submit" 
														value="<?php echo $item['bookingId'] . ':' . $item['id'] ?>"> 
														<span><?php echo $item['formattedCalculatedCost'] ?></span>
														<span> <?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
													<i class="glyphicon glyphicon-trash remove-option"></i>
												</button>
												<?php else: ?>
													<span class="<?php echo $item['isBooked'] ? 'booki-strike' : '' ?>"><?php echo $item['formattedCalculatedCost'] ?>
														<?php echo $_Booki_MiniCartTmpl->data->currency ?></span>
												<?php endif; ?>
											</span>
										</div>
										<div class="clearfix"></div>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif;?>
		</div>
	</form>
</div>