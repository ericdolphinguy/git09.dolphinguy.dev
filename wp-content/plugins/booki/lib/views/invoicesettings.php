<?php
require_once  dirname(__FILE__) . '/../controller/InvoiceSettingController.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../infrastructure/ui/OrderDetails.php';
class Booki_InvoiceSettings{
	public $setting;
	private $invoiceSettingRepository;
	public function __construct(){
		$this->invoiceSettingRepository = new Booki_InvoiceSettingRepository();

		new Booki_InvoiceSettingController(
			array($this, 'create')
			, array($this, 'update')
			, array($this, 'delete')
		);
		$this->render();
	}
	
	public function create($result){
	}
	public function update($result){
	}
	public function delete($result){
	}
	
	public function render(){
		$this->setting = $this->invoiceSettingRepository->read();
		if(!$this->setting){
			$this->setting = new Booki_InvoiceSetting();
		}
	}
}
$_Booki_InvoiceSettings = new Booki_InvoiceSettings();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Invoice settings', 'booki') ?></h4>
			<p><?php echo __('The system sends out invoices based on specific events. The invoice will also include the information below if provided.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<?php if($_Booki_InvoiceSettings->setting->id === -1) :?>
			<div class="booki-callout booki-callout-warning">
				<?php echo __('These settings go in the PDF document which is attached to the invoice email. To change the message in the email, use the email settings page by selecting "invoice" in the template list.', 'booki') ?>
			</div>
			<?php endif; ?>
			<form class="form-horizontal" id="invoicesettings" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/invoicesettings.php" ?>" method="post">
				<div class="form-group companyname">
					<label class="col-lg-4 control-label" for="companyName">
						<?php echo __('Company name', 'booki')?>
					</label>
					<div class="col-lg-8">
						<input type="text" 
							class="form-control booki_parsley_validated"  
							data-parsley-maxlength="256"
							data-parsley-trigger="change" 
							id="companyName"
							placeholder="<?php echo __('Your company name or your full name', 'booki')?>"
							name="companyName" value="<?php echo $_Booki_InvoiceSettings->setting->companyName ?>"/> 
					</div>
				</div>
				<div class="form-group companynumber">
					<label class="col-lg-4 control-label" for="companyNumber">
						<?php echo __('Company number', 'booki')?>
					</label>
					<div class="col-lg-8">
						<input type="text" 
							class="form-control booki_parsley_validated"  
							placeholder="<?php echo __('Your company number eg: VAT number', 'booki')?>"
							data-parsley-maxlength="256"
							data-parsley-trigger="change" 
							id="companyNumber"
							name="companyNumber" value="<?php echo $_Booki_InvoiceSettings->setting->companyNumber ?>"/> 
					</div>
				</div>
				<div class="form-group address">
					<label class="col-lg-4 control-label" for="address">
						<?php echo __('Address', 'booki')?>
					</label>
					<div class="col-lg-8">
						<input type="text" 
							class="form-control booki_parsley_validated"  
							data-parsley-maxlength="256"
							data-parsley-trigger="change" 
							id="address"
							name="address" value="<?php echo $_Booki_InvoiceSettings->setting->address ?>"/> 
					</div>
				</div>
				<div class="form-group telephone">
					<label class="col-lg-4 control-label" for="telephone">
						<?php echo __('Telephone', 'booki')?>
					</label>
					<div class="col-lg-8">
						<input type="text" 
							class="form-control booki_parsley_validated"  
							data-parsley-maxlength="256"
							data-parsley-trigger="change" 
							id="telephone"
							name="telephone" value="<?php echo $_Booki_InvoiceSettings->setting->telephone ?>"/> 
					</div>
				</div>
				<div class="form-group email">
					<label class="col-lg-4 control-label" for="email">
						<?php echo __('Email', 'booki')?>
					</label>
					<div class="col-lg-8">
						<input type="text" 
							class="form-control booki_parsley_validated"  
							data-parsley-maxlength="256"
							data-parsley-type="email"
							data-parsley-trigger="change" 
							id="email"
							name="email" 
							value="<?php echo $_Booki_InvoiceSettings->setting->email ?>"/> 
					</div>
				</div>
				<div class="form-group content">
					<label class="col-lg-4 control-label" for="additionalNote">
						<?php echo __('Additional note', 'booki')?>
					</label>
					<div class="col-lg-8">
						<textarea name="additionalNote"
							placeholder="<?php echo __('Optional blob of text to appear under the invoice statement.', 'booki')?>"
							class="form-control" rows="6"><?php echo $_Booki_InvoiceSettings->setting->additionalNote ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<?php if($_Booki_InvoiceSettings->setting->id === -1) :?>
						<button class="create btn btn-primary" name="booki_create"><i class="glyphicon glyphicon-ok"></i> Save</button>
						<?php else:?>
						<button class="save btn btn-primary" name="booki_update" value="<?php echo $_Booki_InvoiceSettings->setting->id?>"><i class="glyphicon glyphicon-ok"></i> Save</button>
						<button class="delete btn btn-danger" name="booki_delete" value="<?php echo $_Booki_InvoiceSettings->setting->id?>"><i class="glyphicon glyphicon-trash"></i> Reset</button>
						<?php endif;?>
					</div>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$('[data-toggle=tooltip]').tooltip();
					});
				</script>
			</form>
		</div>
	</div>
</div>