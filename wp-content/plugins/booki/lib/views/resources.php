<?php
require_once dirname(__FILE__) . '/../controller/ResxController.php';
require_once dirname(__FILE__) . '/../domainmodel/repository/ResxRepository.php';
require_once dirname(__FILE__) . '/../domainmodel/entities/Resx.php';
require_once dirname(__FILE__) . '/../infrastructure/utils/Helper.php';

class Booki_StringResources{
	public $resx;
	public function __construct(){
		new Booki_ResxController(
			array($this, 'create')
			, array($this, 'update')
			, array($this, 'delete')
		);
		$repository = new Booki_ResxRepository();
		$this->resx = $repository->read();
	}
	
	public function create(){
	}
	public function update(){
	}
	public function delete(){
	}
}

$_Booki_StringResources = new Booki_StringResources();

?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Edit string resources in the booking form', 'booki') ?></h4>
			<p><?php echo __('These string resources are used throughout Booki in the front-end. Customize each field as desired here or through WPML.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<form class="form-horizontal" id="generalsettings" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/resources.php" ?>" method="post">
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="ORDER_ID_REF"
							name="ORDER_ID_REF" value="<?php echo $_Booki_StringResources->resx->ORDER_ID_REF ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="HISTORY"
							name="HISTORY" value="<?php echo $_Booki_StringResources->resx->HISTORY ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="VIEW_ORDER_HISTORY"
							name="VIEW_ORDER_HISTORY" value="<?php echo $_Booki_StringResources->resx->VIEW_ORDER_HISTORY ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="SUBTOTAL"
							name="SUBTOTAL" value="<?php echo $_Booki_StringResources->resx->SUBTOTAL ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="DEPOSIT"
							name="DEPOSIT" value="<?php echo $_Booki_StringResources->resx->DEPOSIT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="DISCOUNT"
							name="DISCOUNT" value="<?php echo $_Booki_StringResources->resx->DISCOUNT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="TAX"
							name="TAX" value="<?php echo $_Booki_StringResources->resx->TAX ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="TOTAL"
							name="TOTAL" value="<?php echo $_Booki_StringResources->resx->TOTAL ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CONGRATS"
							name="CONGRATS" value="<?php echo $_Booki_StringResources->resx->CONGRATS ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="GOT_DISCOUNT"
							name="GOT_DISCOUNT" value="<?php echo $_Booki_StringResources->resx->GOT_DISCOUNT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOK_NOW"
							name="BOOK_NOW" value="<?php echo $_Booki_StringResources->resx->BOOK_NOW ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOK_NOW_PAY_LATER"
							name="BOOK_NOW_PAY_LATER" value="<?php echo $_Booki_StringResources->resx->BOOK_NOW_PAY_LATER ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_BOOKING_SNATCHED"
							name="BOOKING_WIZARD_BOOKING_SNATCHED" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_BOOKING_SNATCHED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_REQUIRED_FIELD"
							name="BOOKING_WIZARD_REQUIRED_FIELD" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_REQUIRED_FIELD ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_LAST_BOOKING_ADDED"
							name="BOOKING_WIZARD_LAST_BOOKING_ADDED" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_LAST_BOOKING_ADDED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_PAY_ONLY_DEPOSIT"
							name="BOOKING_WIZARD_PAY_ONLY_DEPOSIT" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_PAY_ONLY_DEPOSIT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_CREATE_BOOKING"
							name="BOOKING_WIZARD_CREATE_BOOKING" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_CREATE_BOOKING ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_WIZARD_CHECK_OUT"
							name="BOOKING_WIZARD_CHECK_OUT" value="<?php echo $_Booki_StringResources->resx->BOOKING_WIZARD_CHECK_OUT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT"
							name="CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_PROBLEM_PROCESSING_PAYMENT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT"
							name="CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_SUCCESS_PROCESSING_PAYMENT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY"
							name="CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOKING_CONFIRM_SHORTLY ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOKING_NOT_AVAILABLE"
							name="CHECKOUT_GRID_BOOKING_NOT_AVAILABLE" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOKING_NOT_AVAILABLE ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_CHECKOUT_CART_HEADING"
							name="CHECKOUT_GRID_CHECKOUT_CART_HEADING" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_CHECKOUT_CART_HEADING ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOKINGS_EXHAUSTED"
							name="CHECKOUT_GRID_BOOKINGS_EXHAUSTED" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOKINGS_EXHAUSTED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_COST"
							name="CHECKOUT_GRID_COST" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_COST ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOKING_DATE"
							name="CHECKOUT_GRID_BOOKING_DATE" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOKING_DATE ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOKING_TIME"
							name="CHECKOUT_GRID_BOOKING_TIME" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOKING_TIME ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_TIMEZONE_OFFSET"
							name="CHECKOUT_GRID_TIMEZONE_OFFSET" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_TIMEZONE_OFFSET ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW"
							name="CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_DEPOSIT_REQUIRED_NOW ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL"
							name="CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_AMOUNT_DUE_ON_ARRIVAL ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT"
							name="CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_NO_ITEMS_TO_CHECKOUT ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_ENTER_COUPON_CODE"
							name="CHECKOUT_GRID_ENTER_COUPON_CODE" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_ENTER_COUPON_CODE ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_REDEEM"
							name="CHECKOUT_GRID_REDEEM" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_REDEEM ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_REMOVE_COUPON"
							name="CHECKOUT_GRID_REMOVE_COUPON" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_REMOVE_COUPON ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_COUPON_HELP"
							name="CHECKOUT_GRID_COUPON_HELP" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_COUPON_HELP ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_HOW_COUPONS_WORK"
							name="CHECKOUT_GRID_HOW_COUPONS_WORK" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_HOW_COUPONS_WORK ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_ENTER_COUPON_CODE_HELP"
							name="CHECKOUT_GRID_ENTER_COUPON_CODE_HELP" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_ENTER_COUPON_CODE_HELP ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP"
							name="CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_ENTER_COUPON_REFUND_HELP ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_ENTER_COUPON_VALID_HELP"
							name="CHECKOUT_GRID_ENTER_COUPON_VALID_HELP" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_ENTER_COUPON_VALID_HELP ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_PAYMENT_AUTHORIZED"
							name="CHECKOUT_GRID_PAYMENT_AUTHORIZED" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_PAYMENT_AUTHORIZED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_EMPTY_CART"
							name="CHECKOUT_GRID_EMPTY_CART" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_EMPTY_CART ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_BOOK_MORE"
							name="CHECKOUT_GRID_BOOK_MORE" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_BOOK_MORE ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_PROCEED"
							name="CHECKOUT_GRID_PROCEED" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_PROCEED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="CHECKOUT_GRID_CONFIRM_AND_PAY"
							name="CHECKOUT_GRID_CONFIRM_AND_PAY" value="<?php echo $_Booki_StringResources->resx->CHECKOUT_GRID_CONFIRM_AND_PAY ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="BOOKING_FOR"
							name="BOOKING_FOR" value="<?php echo $_Booki_StringResources->resx->BOOKING_FOR ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="DAYS_BOOKED"
							name="DAYS_BOOKED" value="<?php echo $_Booki_StringResources->resx->DAYS_BOOKED ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="EXTRAS"
							name="EXTRAS" value="<?php echo $_Booki_StringResources->resx->EXTRAS ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="DISCOUNT_BY_PERCENTAGE"
							name="DISCOUNT_BY_PERCENTAGE" value="<?php echo $_Booki_StringResources->resx->DISCOUNT_BY_PERCENTAGE ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="COUPON"
							name="COUPON" value="<?php echo $_Booki_StringResources->resx->COUPON ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="PROMOTIONS"
							name="PROMOTIONS" value="<?php echo $_Booki_StringResources->resx->PROMOTIONS ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="FROM"
							name="FROM" value="<?php echo $_Booki_StringResources->resx->FROM ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12">
						<input type="text" 
							class="booki_parsley_validated form-control"  
							data-parsley-trigger="change" 
							id="TO"
							name="TO" value="<?php echo $_Booki_StringResources->resx->TO ?>"/> 
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4">
						<?php if($_Booki_StringResources->resx->id === -1) :?>
						<button class="create btn btn-primary" name="booki_create"><i class="glyphicon glyphicon-ok"></i> <?php echo __('Save', 'booki') ?></button>
						<?php else:?>
						<button class="save btn btn-primary" name="booki_update" value="<?php echo $_Booki_StringResources->resx->id?>"><i class="glyphicon glyphicon-ok"></i> <?php echo __('Save', 'booki') ?></button>
						<button class="delete btn btn-danger" name="booki_delete" value="<?php echo $_Booki_StringResources->resx->id?>"><i class="glyphicon glyphicon-trash"></i> <?php echo __('Reset', 'booki') ?></button>
						<?php endif;?>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
