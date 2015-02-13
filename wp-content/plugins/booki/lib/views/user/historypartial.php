<div class="booki">
<?php if(!is_user_logged_in()):?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('History', 'booki') ?></h4>
			<p><?php __('You need to login to view your history.', 'booki') ?> </p>
		</div>
	</div>
<?php else: ?>
	<?php 
		$_Booki_HistoryTmpl = new Booki_HistoryTmpl();
	?>
	<div class="col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('User history', 'booki') ?></h4>
			<p><?php echo sprintf(__('Past bookings made by current user. YOU (%s)', 'booki'), $_Booki_HistoryTmpl->userName) ?> </p>
		</div>
	</div>
	<div class="col-lg-12">
		<?php if($_Booki_HistoryTmpl->hasFullControl): ?>
			<?php require dirname(__FILE__) . '/../partials/refundtransaction.php' ?>
		<?php endif; ?>
		<?php if($_Booki_HistoryTmpl->orderId): ?>
			<?php require dirname(__FILE__) . '/../partials/bookingdetails.php' ?>
			<?php if($_Booki_HistoryTmpl->singleOrderDetails->order && $_Booki_HistoryTmpl->singleOrderDetails->bookedFormElements->count()):?>
				<?php require dirname(__FILE__) .'/../partials/bookedformelements.php' ?>
			<?php endif; ?>
		<?php endif;?>
		<div class="booki-content-box">
			<div class="table-responsive">
				<?php $_Booki_HistoryTmpl->orderList->display() ?>
			</div>
		</div>
	</div>
<?php endif; ?>
</div>