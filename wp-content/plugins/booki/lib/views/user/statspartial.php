<?php

$_Booki_StatsTmpl = new Booki_StatsTmpl();

?>
<div class="booki col-lg-12">
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Stats', 'booki') ?></h4>
			<p><?php echo $_Booki_StatsTmpl->isAdmin ? __('A brief overview of your bookings.', 'booki') : __('A brief overview of bookings approved by you.', 'booki')?> </p>
		</div>
	</div>
</div>
<div class="booki col-lg-12">
	<div class="col-lg-6">
		<div class="booki-content-box">
			<div class="col-lg-3 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[1] ?></h1>
				<h2><?php echo __('payments made', 'booki') ?></h2>
			</div>
			<div class="col-lg-5 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[0] ?></h1>
				<h2><?php echo __('payments pending', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('Purchasers are made, not born. --Henry Ford', 'booki') ?>
					</span>
				</div>
			</div>
			<div class="col-lg-4 booki-stats-block">
				<h1 class="badge"><?php echo (int)$_Booki_StatsTmpl->donut[2]  ?></h1>
				<h2><?php echo __('Refunds', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('Don\'t dwell on negativity.', 'booki') ?>
					</span>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="booki-content-box">
			<div class="col-lg-3 booki-stats-block booki-stats-separator">
				<h1 class="badge"><?php echo $_Booki_StatsTmpl->summary->count ?></h1>
				<h2><?php echo __('total bookings', 'booki') ?></h2>
			</div>
			<div class="col-lg-5 booki-stats-block booki-stats-separator">
				<h1><?php echo Booki_Helper::toMoney((int)$_Booki_StatsTmpl->totalAmountEarned) . ' ' . $_Booki_StatsTmpl->localInfo['currency'] ?></h1>
				<h2><?php echo __('total amount earned', 'booki') ?></h2>
				<div>
					<span>
						<?php echo __('I do not love the money. What I love is the making of it. --Philip Armour', 'booki') ?> 
					</span>
				</div>
			</div>
			<div class="col-lg-4 booki-stats-block">
				<h1><?php echo Booki_Helper::toMoney((int)$_Booki_StatsTmpl->summary->discount) . ' ' . $_Booki_StatsTmpl->localInfo['currency']  ?></h1>
				<h2><?php echo __('total discounts given', 'booki') ?></h2>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="booki col-lg-12">
	<?php if($_Booki_StatsTmpl->orderId !== null): ?>
		<?php require dirname(__FILE__) . '/../partials/bookingdetails.php' ?>
		<?php if($_Booki_StatsTmpl->singleOrderDetails->bookedFormElements->count()):?>
			<?php require dirname(__FILE__) .'/../partials/bookedformelements.php' ?>
		<?php endif; ?>
	<?php endif;?>
	</div>
	<?php if($_Booki_StatsTmpl->orderList): ?>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="booki-callout booki-callout-default">
				<h4><?php echo __('Bookings', 'booki') ?></h4>
				<p><?php echo __('Listing of all bookings approved by you', 'booki') ?></p>
			</div>
			<div class="table-responsive">
				<?php $_Booki_StatsTmpl->orderList->display();?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
<div class="booki col-lg-12">
	<form class="form-horizontal" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/stats.php" ?>" method="post">
		<input type="hidden" name="controller" value="booki_stats" />
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-callout booki-callout-default">
					<h4><?php echo __('Bookings', 'booki')?></h4>
					<p><?php echo $_Booki_StatsTmpl->isAdmin ? __('Bookings made in the last 3 months', 'booki') : __('Bookings you approved in the last 3 months', 'booki')?></p>
				</div>
				<div class="table-responsive">
					<?php $_Booki_StatsTmpl->ordersMadeAggregateList->display();?>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-callout booki-callout-info">
					<h4><?php echo __('Sales', 'booki')?></h4>
					<p><?php echo $_Booki_StatsTmpl->isAdmin ? __('Sales in the last 3 months', 'booki') : __('Sales of bookings you approved in the last 3 months', 'booki')?></p>
				</div>
				<div>
					<?php $_Booki_StatsTmpl->ordersTotalAmountAggregateList->display();?>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="col-lg-6">
			<div class="booki-content-box">
				<div class="booki-callout booki-callout-danger">
					<h4><?php echo __('Refunds', 'booki')?></h4>
					<p><?php echo $_Booki_StatsTmpl->isAdmin ? __('Refunds in the last 3 months', 'booki') : __('Refunded bookings you approved in the last 3 months', 'booki')?></p>
				</div>
				<div class="table-responsive">
					<?php $_Booki_StatsTmpl->ordersRefundAmountAggregateList->display(); ?>
				</div>
			</div>
		</div>
	</form>
</div>