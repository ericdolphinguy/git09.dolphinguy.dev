<?php
require_once  dirname(__FILE__) . '/../controller/PaypalController.php';
require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
require_once  dirname(__FILE__) . '/../domainmodel/repository/PaypalSettingRepository.php';
class Booki_Paypal
{
	private $repo;
	public $settings;
	public $currencies;
	public function __construct()
	{
		$this->currencies = Booki_Helper::getPaypalCurrencies();
		$this->repo = new Booki_PaypalSettingRepository();
		$this->settings = $this->repo->read();
		new Booki_PaypalController(
				array($this, 'create')
				, array($this, 'update')
				, array($this, 'delete')
		);
	}
	
	public function create($settings, $errors)
	{
		$this->settings = $settings;
	}
	
	public function update($settings, $errors)
	{
		$this->settings = $settings;
	}
	
	public function delete($success)
	{
		if($success)
		{
			$this->settings = $this->repo->read();
		}
	}
}

$_Booki_Paypal = new Booki_Paypal();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('PayPal', 'booki') ?></h4>
			<p><?php echo __('Accept Payments Online with PayPal.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<ul class="nav nav-tabs">
			  <li><a href="#ppsettings" data-toggle="tab">Settings</a></li>
			  <li><a href="#instructions" data-toggle="tab">Instructions</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="ppsettings">
					<div class="booki-vertical-gap"></div>
					<form class="form-horizontal" action="<?php echo admin_url() . "admin.php?page=booki/paypal.php" ?>" method="post">
						<fieldset>
							<input type="hidden" name="booki_paypal" />
							<div class="form-group username">
								<label class="col-lg-4 control-label" for="username"><?php echo __('Username', 'booki') ?></label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="username" value="<?php echo $_Booki_Paypal->settings->username ?>"/>
								</div>
							</div>
							<div class="form-group password">
								<label class="col-lg-4 control-label" for="password"><?php echo __('Password', 'booki') ?></label>
								<div class="col-lg-8">
									<input type="password" class="form-control " name="password" value="<?php echo $_Booki_Paypal->settings->password ?>"/>
								</div>
							</div>
							<div class="form-group signature">
								<label class="col-lg-4 control-label" for="signature"><?php echo __('Signature', 'booki') ?></label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="signature" value="<?php echo $_Booki_Paypal->settings->signature ?>"/>
								</div>
							</div>
							<?php /*
							<div class="form-group appid">
								<label class="col-lg-4 control-label" for="appid">
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('An AppID is required if you enable adapative payments. Express checkout does not require this at all.', 'booki')?>"></i>
									<?php echo __('App ID', 'booki') ?>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="appid" value="<?php echo $_Booki_Paypal->settings->appId ?>"/>
								</div>
							</div>
							*/?>
							<div class="form-group currency">
								<label class="col-lg-4 control-label" for="currency">
									<?php echo __('Currency', 'booki') ?>
								</label>
								<div class="col-lg-8">
									<select name="currency" class="form-control ">
									<?php foreach( $_Booki_Paypal->currencies as $key => $value ) : ?>
										<option value="<?php echo $key ?>" <?php selected($_Booki_Paypal->settings->currency, $key) ?>>
											<?php echo esc_html( $value ); ?>
										</option>
									<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="col-lg-8 col-md-offset-4">
								<div class="booki-callout booki-callout-warning">
									<h4><?php echo __('Item category', 'booki') ?></h4>
									<p><?php echo __('"Digital" requires that you are signed up with PayPal and have a business account in 
									order to take advantage of what it offers. 
									It differs from "Physical" where you will have a better fee structure per transaction 
									if your selling items at low cost and they are strictly digital goods. We really aren\'t experts with all things PayPal, 
									so you will have to look this one up. "Physical" on the other hand will just work right off the bat, all you need is a PayPal account.', 'booki') ?> </p>
								</div>
							</div>
							<div class="form-group itemcategory">
								<label class="col-lg-4 control-label" for="itemcategory">
									<?php echo __('Item category') ?>
								</label>
								<div class="col-lg-8">
									<select name="itemcategory" class="form-control " value="<?php echo $_Booki_Paypal->settings->itemCategory ?>">
										<option value="Digital" <?php echo $_Booki_Paypal->settings->itemCategory === 'Digital' ? 'selected' : '' ?>>Digital</option>
										<option value="Physical" <?php echo $_Booki_Paypal->settings->itemCategory === 'Physical' ? 'selected' : '' ?>>Physical</option>
									</select>
								</div>
							</div>
							<?php /*<div class="form-group">
								<div class="col-lg-8 col-md-offset-4">
								   <label class="checkbox">
										<input id="allowbuyernote" name="allowbuyernote" type="checkbox" 
											<?php echo $_Booki_Paypal->settings->allowBuyerNote ? 'checked' : ''; ?> /> Allow buyer to leave a note 
									</label>
								</div>
							</div>
							*/?>
							<div class="col-lg-8 col-md-offset-4">
								<div class="booki-callout booki-callout-warning">
									<h4><?php echo __('Sandbox', 'booki') ?></h4>
									<p><?php echo __('The PayPal Sandbox is a self-contained, virtual testing environment that mimics the 
									live PayPal production environment. In order to use the sandbox, check the box below and in addition you\'ll have to manually FTP
									into the config file that contains the live environment public URL endpoints.', 'booki') ?> </p>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-8 col-md-offset-4">
								   <label class="checkbox">
										<input id="usesandbox" name="usesandbox" type="checkbox" 
											<?php echo $_Booki_Paypal->settings->useSandBox ? 'checked' : ''; ?> />
											<i class="glyphicon glyphicon-question-sign help"
												data-toggle="tooltip" 
												data-placement="top" 
												data-original-title="<?php echo __('When checked, all Paypal checkout requests will be 
												made towards the Paypal Sandbox test environment: https://www.sandbox.paypal.com', 'booki')?>"></i>
												Use paypal sandbox
									</label>
								</div>
							</div>
						</fieldset>
						<hr />
						<fieldset>
							<div class="form-group brandname">
								<label class="col-lg-4 control-label" for="brandname">
									<?php echo __('Business name') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Display in PayPal hosted checkout pages', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="brandname" value="<?php echo $_Booki_Paypal->settings->brandName ?>"/>
								</div>
							</div>
							<div class="form-group pagestyle">
								<label class="col-lg-4 control-label" for="pagestyle">
									<?php echo __('Custom page style', 'booki')?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('As configured in Merchant\'s account profile', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="pagestyle" id="pagestyle" 
									value="<?php echo $_Booki_Paypal->settings->customPageStyle ?>" />
								</div>
							</div>
							<div class="form-group cppheaderimage">
								<label class="col-lg-4 control-label" for="cppheaderimage">
									<?php echo __('Header image URL', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('The image will appear at the top left of the payment page', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control " name="cppheaderimage" id="cppheaderimage"
									value="<?php echo $_Booki_Paypal->settings->headerImage ?>" />
								</div>
							</div>
							<div class="form-group cppheaderbordercolor">
								<label class="col-lg-4 control-label" for="cppheaderbordercolor">
									<?php echo __('Header border', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Color is applied around header', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control  minicolors" name="cppheaderbordercolor" id="cppheaderbordercolor"
														value="<?php echo $_Booki_Paypal->settings->headerBorderColor ?>" />
								</div>
							</div>
							<div class="form-group cppheaderbackcolor">
								<label class="col-lg-4 control-label" for="cppheaderbackcolor">
									<?php echo __('Header background', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Color is applied in the header', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control minicolors" name="cppheaderbackcolor" id="cppheaderbackcolor" 
														value="<?php echo $_Booki_Paypal->settings->headerBackColor ?>" />
								</div>
							</div>
							<div class="form-group cpppayflowcolor">
								<label class="col-lg-4 control-label" for="cpppayflowcolor">
									<?php echo __('Page background', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Color is applied on payment page', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control  minicolors" name="cpppayflowcolor" id="cpppayflowcolor"
														value="<?php echo $_Booki_Paypal->settings->payFlowColor ?>" />
								</div>
							</div>
							<div class="form-group cppcartbordercolor">
								<label class="col-lg-4 control-label" for="cppcartbordercolor">
									<?php echo __('Minicart border', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Color applied in the Mini Cart on 1X flow', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control  minicolors" name="cppcartbordercolor" id="cppcartbordercolor"
														value="<?php echo $_Booki_Paypal->settings->cartBorderColor ?>" />
								</div>
							</div>
							<div class="form-group cpplogoimage">
								<label class="col-lg-4 control-label" for="cpplogoimage">
									<?php echo __('Logo image URL', 'booki') ?>
									<i class="glyphicon glyphicon-question-sign help"
										data-toggle="tooltip" 
										data-placement="top" 
										data-original-title="<?php echo __('Image to appear above the mini-cart', 'booki')?>"></i>
								</label>
								<div class="col-lg-8">
									<input type="text" class="form-control" name="cpplogoimage" id="cpplogoimage" value="<?php echo $_Booki_Paypal->settings->logo ?>" />
								</div>
							</div>
						</fieldset>
						<div class="form-group">
							<div class="col-lg-8 col-md-offset-4">
								 <input type="hidden" name="id" value="<?php echo $_Booki_Paypal->settings->id ?>"/>
								 <button class="save btn btn-primary" name="booki_<?php echo $_Booki_Paypal->settings->id === -1 ? 'create': 'update'?>"><i class="glyphicon glyphicon-ok"></i> <?php echo __('Save', 'booki') ?></button>
								 <?php if($_Booki_Paypal->settings->id !== -1): ?>
								 <button class="delete btn btn-danger" name="booki_delete"><i class="glyphicon glyphicon-refresh"></i> <?php echo __('Reset to sandbox', 'booki') ?></button>
								 <?php endif; ?>
							</div>
						</div>
					</form>
				</div>
				<div class="tab-pane active" id="instructions">
					<div class="booki-vertical-gap"></div>
					<p><strong>How to retrieve your paypal express checkout username, password and API Signature information from Paypal.</strong></p>
					<ol class="paypal-instructions">
						<li>Click the "My Account" tab.</li>
						<li>Click "Profile" at the top of the page.</li>
						<li>Click the "API Access" link in the Account Information column.</li>
						<li>Click the "Request API Credentials" link.</li>
						<li>Accept payments from your online shops - Click Allow</li>
						<li>Select "Request API signature."</li>
						<li>Click "Agree," and then click "Submit." Click "Done".</li>
						<li>Copy your API username, API password and signature information.</li>
					</ol>
					<p class="alert alert-info">
							<strong>Note!</strong>
							When setting Header image URL and Logo image URL, we recommend that you enter an image URL only if the image is stored on a 
							secure (https) server. Otherwise, your customer's web browser will display a message that 
							the payment page contains nonsecure items.
							If you don't have ssl, try signing up for a free service such as 
							<a href="http://www.sslpic.com/">sslpic</a>
					</p>
					<dl>
						<dt>Page Style Name (required)</dt>
								<dd>Enter a name up to 30 characters in length. The name can contain letters, numbers, 
								and the underscore mark - but no other symbols or spaces. The Page Style Name will be 
								used to refer to the page style within your PayPal account and in the HTML code for 
								your PayPal Website Payment buttons.</dd>
						<dt> Header Image URL (optional)</dt>
						<dd>
							<p>Enter the URL for an image that is a maximum size of 750 pixels wide by 90 
							pixels high; larger images will be cut to this size. The image must be in a 
							valid graphics format such as .gif, .jpg, .png, and .swf. The image will 
							appear at the top left of the payment page.</p>
						</dd>
						<dt>Header Background Color (optional)</dt>
						<dd>
							<p>Enter the background color for the header using HTML hex code. 
							The color code must be six digits long and should not contain the # symbol. 
							If the Header Image URL is present, then the header will be a 750 pixel wide by 90 
							pixel high space at the top of the payment page. If the Header Image URL is not present, 
							the header height will be reduced to 45 pixels.</p>
						</dd>
						<dt>Header Border Color (optional)</dt>
						<dd>
							<p>Enter the border color for the header using HTML hex code. The color code must be six digits 
							long and should not contain the # symbol. The header border is a 2 pixel perimeter around the header space.</p>
						</dd>
						<dt>Background Color (optional)</dt>
						<dd>
							<p>Enter the background color for the payment page using HTML hex code. 
							The color code must be six digits long and should not contain the # symbol.</p>
						</dd>
						<dt>Logo image URL (optional)</dt>
						<dd>
							<p>Enter the image to appear above the mini-cart.</p>
						</dd>
					</dl>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var $tabs = $('.nav.nav-tabs');
		$('.minicolors').minicolors();
		$('[data-toggle=tooltip]').tooltip();
		$tabs.find('a:first').tab('show');
		$tabs.find('a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		});
	});
</script>