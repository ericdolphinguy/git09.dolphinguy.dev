<?php
	$_Booki_PaypalCancelPaymentTmpl = new Booki_PaypalCancelPaymentTmpl();
?>
<div class="booki col-lg-12">
	<div class="alert alert-success">
		 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?php if($_Booki_PaypalCancelPaymentTmpl->success): ?>
				<?php echo __('Payment cancelled successfully! The order has been deleted as well. Thanks for trying to book with us. Hope to see you back.', 'booki');?>
		<?php else: ?>
				<?php echo __('Payment cancelled. The order is still available in your order history. If you change your mind, you can make payment directly through your order history page. Thankyou for booking with us.', 'booki'); ?>
		<?php endif; ?>
	</div>
	<div class="alert alert-warning">
		 <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?php echo __('Go to your order', 'booki') . ' ' ?> 
		<a href="<?php echo Booki_ThemeHelper::getHistoryPage() ?>"><?php echo __('history page', 'booki') ?>
		</a>
	</div>
</div>