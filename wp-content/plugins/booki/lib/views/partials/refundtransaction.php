<?php
	class Booki_RefundTransaction{
		public $bookedDay;
		public $bookedDaysList;
		public $bookedOptional;
		public $bookedCascadingItem;
		public $result;
		public $amount;
		public $currency;
		public $refundType;
		public $orderId;
		public function __construct(){
			$this->orderId = apply_filters( 'booki_refund_order_id', null);
			$this->amount = apply_filters( 'booki_refund_amount', null);
			$this->currency = apply_filters( 'booki_refund_currency', null);
			$this->bookedDay = apply_filters( 'booki_refund_booked_day', null);
			$this->bookedDaysList = apply_filters( 'booki_refund_booked_days_list', null);
			$this->bookedOptional = apply_filters( 'booki_refund_booked_optional', null);
			$this->bookedCascadingItem = apply_filters( 'booki_refund_booked_cascading_item', null);
			$this->refundType = apply_filters( 'booki_refund_type', null);
			$this->result = apply_filters( 'booki_refund_result', null);

			if(!$this->refundType){
				$this->refundType = 'Full';
			}
		}
	}
	
	$_Booki_RefundTransaction = new Booki_RefundTransaction();
?>
<?php if($_Booki_RefundTransaction->result && $_Booki_RefundTransaction->result->Ack): ?>
	<?php if(strtolower($_Booki_RefundTransaction->result->Ack) === 'failure'): ?>
		<div class="booki-callout booki-callout-danger">
			<h4><?php echo __('Refund Status', 'booki') ?></h4>
			<p><?php echo __('Short Message:', 'booki') ?> <?php echo $_Booki_RefundTransaction->result->Errors[0]->ShortMessage ?></p>
			<p><?php echo __('Long Message:', 'booki') ?> <?php echo $_Booki_RefundTransaction->result->Errors[0]->LongMessage ?></p>
		</div>
	<?php else: ?>
		<div class="booki-callout booki-callout-success">
			<h4><?php echo __('Refund Status', 'booki') ?></h4>
			<p><?php echo __('Net refund amount:', 'booki') ?> <?php 
				echo $_Booki_RefundTransaction->result->NetRefundAmount->value ?></p>
			<p><?php echo __('Fee refund amount:', 'booki') ?> <?php 
				echo $_Booki_RefundTransaction->result->FeeRefundAmount->value ?></p>
			<p><?php echo __('Gross refund amount:', 'booki') ?> <?php 
				echo $_Booki_RefundTransaction->result->GrossRefundAmount->value ?></p>
			<p><?php echo __('Total refunded amount:', 'booki') ?> <?php 
				echo $_Booki_RefundTransaction->result->TotalRefundedAmount->value . ' '
						.  $_Booki_RefundTransaction->result->TotalRefundedAmount->currencyID ?></p>
			<p><?php echo __('Refund status:', 'booki') ?> <?php echo $_Booki_RefundTransaction->result->RefundInfo->RefundStatus ?></p>
			<?php if(strtolower($_Booki_RefundTransaction->result->RefundInfo->PendingReason) === 'pending'): ?>
			<p><span class="label label-important"><?php echo __('Pending reason:', 'booki') ?> <?php echo $_Booki_RefundTransaction->result->RefundInfo->PendingReason ?></span></p>
			<?php require_once  dirname(__FILE__) . '/partials/pendingstatus.php'; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
<?php if($_Booki_RefundTransaction->orderId): ?>
	<div class="booki-content-box">
		<div class="booki-callout booki-callout-danger">
			<h4><?php echo __('Refund transaction', 'booki') ?></h4>
			<p><?php echo __('You can send refunds within 60 days of receiving a payment. Alternatively, you
			can issue a refund directly through paypal, by going to the 
			Transaction Details page for your transaction and clicking "Issue a refund".', 'booki') ?></p>
		</div>
		<form class="form-horizontal" role="form" method="POST" data-parsley-validate action="<?php echo $_SERVER['REQUEST_URI']?>">
			<input type="hidden" name="controller" value="booki_refund" />
			<input type="hidden" name="orderId" value="<?php echo $_Booki_RefundTransaction->orderId ?>" />
			<?php if(isset($_Booki_RefundTransaction->bookedDay)): ?>
			<input type="hidden" name="bookedDayId" value="<?php echo $_Booki_RefundTransaction->bookedDay->id ?>" />
			<?php endif; ?>
			<?php if(isset($_Booki_RefundTransaction->bookedOptional)): ?>
			<input type="hidden" name="bookedOptionalId" value="<?php echo $_Booki_RefundTransaction->bookedOptional->id ?>" />
			<?php endif; ?>
			<?php if(isset($_Booki_RefundTransaction->bookedCascadingItem)): ?>
			<input type="hidden" name="bookedCascadingItemId" value="<?php echo $_Booki_RefundTransaction->bookedCascadingItem->id ?>" />
			<?php endif; ?>
			<input type="hidden" name="refundType" value="<?php echo $_Booki_RefundTransaction->refundType ?>"/>
			<input type="hidden" name="amount" value="<?php echo $_Booki_RefundTransaction->amount ?>"/>
			
			<div class="form-group">
				<label class="col-lg-4 control-label" for="refundType">
					<i class="glyphicon glyphicon-question-sign help"
						data-toggle="tooltip" 
						data-placement="top" 
						data-original-title="<?php echo __('Type of refund being made.', 'booki')?>"></i>
					<?php echo __('Refund Type', 'booki') ?>
				</label>
				<div class="col-lg-8">
					 <p class="form-control-static"><pre><?php echo $_Booki_RefundTransaction->refundType ?></pre></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-4 control-label" for="amount">
					<i class="glyphicon glyphicon-question-sign help"
						data-toggle="tooltip" 
						data-placement="top" 
						data-original-title="<?php echo __('Amount to be refunded (If RefundType is full, do not set the amount). 
						For Decimal point use a dot [.] and Thousands sep use a comma [,]', 'booki')?>"></i>
					<?php echo __('Refund amount') ?>
				</label>
				<div class="col-lg-8">
					 <p class="form-control-static"><pre><?php echo Booki_Helper::toMoney($_Booki_RefundTransaction->amount) ?></pre></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-4 control-label" for="currency">
					<i class="glyphicon glyphicon-question-sign help"
						data-toggle="tooltip" 
						data-placement="top" 
						data-original-title="<?php echo __('Currency must be the same currency type used to make the 
							payment that is being refunded.', 'booki')?>"></i>
						<?php echo __('Currency', 'booki') ?>
				</label>
				<div class="col-lg-8">
					 <p class="form-control-static"><pre><?php echo $_Booki_RefundTransaction->currency ?></pre></p>
				</div>
			</div>
			<div class="form-group clearfix">
				<label class="col-lg-4 control-label" for="refundSource">
					<i class="glyphicon glyphicon-question-sign help"
						data-toggle="tooltip" 
						data-placement="top" 
						data-original-title="<?php echo __('By default this will use any available funding source', 'booki')?>"></i>
					<?php echo __('Refund source') ?>
				</label>
				<div class="col-lg-8">
					<select name="refundSource" class="form-control">
						<option value=""></option>
						<option value="any"><?php echo __('Use any available funding source', 'booki') ?></option>
						<option value="default"><?php echo __('Use the preferred funding source, as configured in the profile', 'booki') ?></option>
						<option value="instant"><?php echo __('Use the balance as the funding source', 'booki')?></option>
						<option value="echeck"><?php echo __('Use the eCheck funding source', 'booki')?></option>
					</select>
				</div>
			</div>
			<div class="form-group clearfix">
				<label class="col-lg-4 control-label" for="memo">
				<i class="glyphicon glyphicon-question-sign help"
						data-toggle="tooltip" 
						data-placement="top" 
						data-original-title="<?php echo __('Memo', 'booki')?>"></i>
				<?php echo __('Memo', 'booki')?>
				</label>
				<div class="col-lg-8">
					<textarea class="form-control booki_parsley_validated" rows="4"
								data-parsley-maxlength="256"
								data-parsley-trigger="change" 
								name="memo"></textarea>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-4 col-lg-8">
					<button class="btn btn-danger" name="refund"><i class="glyphicon glyphicon-ok"></i> <?php echo __('Issue refund', 'booki')?> <span class="badge">#<?php echo $_Booki_RefundTransaction->orderId ?></span></button>
					<button class="btn btn-default" name="cancel"><i class="glyphicon glyphicon-remove-circle"></i> <?php echo __('Cancel', 'booki')?></button>
				</div>
			</div>
			<div class="clearfix"></div>
		</form>
	</div>
<?php endif;?>

