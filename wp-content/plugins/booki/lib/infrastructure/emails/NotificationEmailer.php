<?php
require_once  dirname(__FILE__) . '/base/Emailer.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/InvoiceSettingRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/BookedDaysRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/BookedOptionalsRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/BookedCascadingItemsRepository.php';
require_once  dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
require_once  dirname(__FILE__) . '/../ui/BillSettlement.php';
require_once  dirname(__FILE__) . '/../utils/PageNames.php';
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../utils/TimeHelper.php';
if(!class_exists('TCPDF')){
	require_once  BOOKI_TCPDF . 'tcpdf.php';
}

class Booki_NotificationEmailer extends Booki_Emailer{
	protected $invoiceSettings;
	protected $orderId;
	protected $bookedDay = null;
	protected $bookedOptional = null;
	protected $bookedCascadingItem = null;
	protected $refundAmount;
	protected $currency;
	protected $currencySymbol;
	protected $dateFormat;
	protected $userInfo;
	public function __construct($emailType, $orderId, $bookedDayId = null, $bookedOptionalId = null, $bookedCascadingItemId = null, 
												$refundAmount = 0, $userInfo = null){
		$this->orderId = $orderId;
		$this->refundAmount = $refundAmount;
		$localeInfo = Booki_Helper::getLocaleInfo();
		$this->currency = $localeInfo['currency'];
		$this->currencySymbol =	$localeInfo['currencySymbol'];
		$this->dateFormat = get_option('date_format');
		$this->userInfo = $userInfo;
		if($bookedDayId !== null){
			$bookedDaysRepo = new Booki_BookedDaysRepository();
			$this->bookedDay = $bookedDaysRepo->read($bookedDayId);
		}
		if($bookedOptionalId !== null){
			$bookedOptionalRepo = new Booki_BookedOptionalsRepository();
			$this->bookedOptional = $bookedOptionalRepo->read($bookedOptionalId);
		}
		
		if($bookedCascadingItemId !== null){
			$bookedCascadingItemRepo = new Booki_BookedCascadingItemsRepository();
			$this->bookedCascadingItem = $bookedCascadingItemRepo->read($bookedCascadingItemId);
		}
		
		$invoiceSettingRepository = new Booki_InvoiceSettingRepository();
		$this->invoiceSettings = $invoiceSettingRepository->read();
		
		parent::__construct($emailType);
	}
	
	protected function getUserInfo($data){

		if($this->userInfo){
			return $this->userInfo;
		}
		
		if($data->order && $data->order->userIsRegistered){
			return Booki_Helper::getUserInfo($data->order->userId);
		}
		
		return Booki_BookingProvider::getNonRegContactInfo($this->orderId);
	}
	
	public function send($projectId = null, $to = null){
		$emailSettings = $this->emailSettings;
		if($this->orderId && ($emailSettings && $emailSettings->enable)){
			$data = new Booki_BillSettlement($this->orderId, null, $projectId);
			$userInfo = $this->getUserInfo($data);
			$customerName = '';
			if($userInfo){
				$customerName = $userInfo['name'];
			}
			if($to === null){
				$to = $userInfo['email'];
			}
			$content = $this->getEmailBody($customerName, $data);
			
			$subject = $emailSettings->subject;
			if(!$subject){
				$subject = __($this->emailType, 'booki');
			}
			add_filter( 'wp_mail_content_type', array($this, 'setHtmlContentType'));
			add_filter('wp_mail_from', array($this, 'getSenderAddress'));
			add_filter('wp_mail_from_name',array($this, 'getSenderName'));
			
			//workaround for thirdparty email plugins that love to use nl2br
			$content = str_replace(array("\r\n", "\n\r", "\n", "\r"), "", $content);
			$result = wp_mail( $to, $subject, $content);

			remove_filter('wp_mail_from', array($this, 'getSenderAddress'));
			remove_filter('wp_mail_from_name',array($this, 'getSenderName'));
			remove_filter( 'wp_mail_content_type', array($this, 'setHtmlContentType'));
			$this->logErrorIfAny($result);
			return $result;
		}
		return false;
	}
	
	public function getSenderAddress(){
		return $this->emailSettings->senderEmail;
	}
	
	public function getSenderName(){
		return  $this->emailSettings->senderName;
	}
	public function generateInvoice(){
		if(!$this->orderId)
		{
			return;
		}
		$data = new Booki_BillSettlement($this->orderId);
		$userInfo = Booki_Helper::getUserInfo($data->order->userId);
		$content = $this->invoice($data, $userInfo);
		
		return $this->generatePDF($content);
	}
	
	/**
		%customerName% : Name of customer, if it exists in their profile.
		%bookedDateTime%: The date or datetime of the day booked and confirmed/cancelled or refunded.
		%bookedDateTimeCost%: The cost of the booked date.
		%optionalItemName%: The optional item name.
		%optionalItemCost%: The optional item cost.
		%orderId% : The order ID.
		%orderDate% : The date the order was made.
		%orderDetails% : Order details.
		%orderAddtionalInfo% : Order additional info.
		%invoicePaymentLink% : A link is generated that redirects the user to paypal for payment of the invoice.
		%adminName%: The admin name, normally found in emails send out to the admin user when a new booking is made.
		%refundAmount%: The amount refunded.
		%orderUrl%: The url to the order for admin use.
	*/
	protected function getEmailBody($customerName, $data){
		$emailSettings = $this->emailSettings;
		$content = $emailSettings->content;
		$attachment = null;
		if(!$content){
			$content = Booki_Helper::readEmailTemplate($this->emailType);
		}
		if(!$content){
			return;
		}

		$orderDetails = $this->orderDetails($data);
		$orderAddtionalInfo = $this->orderAddtionalInfo($data);
		$orderDate = $data && $data->order ? Booki_Helper::formatDate( $data->order->orderDate) : '';
		$bookedDateTime = '';
		$bookedDateTimeCost = '';
		$optionalItemName = '';
		$optionalItemCost = '';
		$invoicePaymentLink = '';
		
		if($this->bookedDay){
			$bookedDateTime = $this->bookedDay->bookingDate->format($this->dateFormat) . ' ' . Booki_TimeHelper::formatTime($this->bookedDay, $data->timezoneInfo['timezone'], $this->bookedDay->enableSingleHourMinuteFormat);
			$bookedDateTimeCost = $this->currencySymbol . Booki_Helper::toMoney($this->bookedDay->cost) . ' ' . $this->currency;
		}
		
		if($this->bookedOptional){
			$optionalItemName = $this->bookedOptional->getName();
			$optionalItemCost = $this->currencySymbol . $this->bookedOptional->getCalculatedCost() . ' ' . $this->currency;
		}
		else if($this->bookedCascadingItem){
			$optionalItemName = $this->bookedCascadingItem->getName();
			$optionalItemCost = $this->currencySymbol . $this->bookedCascadingItem->getCalculatedCost() . ' ' . $this->currency;
		}

		$invoicePaymentLink = Booki_Helper::getUrl(Booki_PageNames::PAYPAL_HANDLER);
		$delimiter = Booki_Helper::getUrlDelimiter($invoicePaymentLink);
		$invoicePaymentLink = sprintf('<a href="%1$s">%1$s</a>', $invoicePaymentLink . $delimiter . "orderid=$this->orderId");
		
		
		$adminName = '';
		$adminUserId = $this->globalSettings->adminUserId;
		if($adminUserId){
			$adminUserInfo = Booki_Helper::getUserInfo($adminUserId);
			$adminName = $adminUserInfo['name'];
		}
		$content = str_ireplace("%orderId%", $this->orderId, $content);
		if($this->refundAmount){
			$content = str_ireplace("%refundAmount%", $this->refundAmount, $content);
		}
		
		$invoiceDownloadLink = Booki_Helper::handlerUrls()->invoiceHandlerUrl;
		$delimiter = Booki_Helper::getUrlDelimiter($invoiceDownloadLink);
		$invoiceDownloadLink = sprintf('<a href="%1$s">%1$s</a>', $invoiceDownloadLink . $delimiter . "orderid=$this->orderId");
		$orderUrl = sprintf('<a href="%1$s">%1$s</a>', admin_url() . "admin.php?page=booki/managebookings.php&amp;orderid=$this->orderId");
		
		$content = str_ireplace("%orderDate%", $orderDate, $content);
		$content = str_ireplace("%bookedDateTime%", $bookedDateTime, $content);
		$content = str_ireplace("%bookedDateTimeCost%", $bookedDateTimeCost, $content);
		$content = str_ireplace("%optionalItemName%", $optionalItemName, $content);
		$content = str_ireplace("%optionalItemCost%", $optionalItemCost, $content);
		$content = str_ireplace("%customerName%", $customerName, $content);
		$content = str_ireplace("%orderDetails%", $orderDetails, $content);
		$content = str_ireplace("%orderAddtionalInfo%", $orderAddtionalInfo, $content);
		$content = str_ireplace("%invoicePaymentLink%", $invoicePaymentLink, $content);
		$content = str_ireplace("%invoiceDownloadLink%", $invoiceDownloadLink, $content);
		$content = str_ireplace("%adminName%", $adminName, $content);
		$content = str_ireplace("%orderUrl%", $orderUrl, $content);
		
		return $content;
	}

	protected function invoice($data, $userInfo){
		ob_start();
		if(!($data && $data->order)){
			return '';
		}
		$orderDetails = $this->orderDetails($data);
		
	?>
		<table width="100%" cellpadding="2">
			<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<?php if(isset($this->invoiceSettings->companyName)):?>
					<?php echo $this->invoiceSettings->companyName ?>
					<?php endif;?>
				</th>
				<th align="right">
					<strong><?php echo __('INVOICE', 'booki') ?></strong>
				</th>
			</tr>
			<tr>
				<td>
					<?php if(isset($this->invoiceSettings->companyNumber)):?>
					<?php echo $this->invoiceSettings->companyNumber ?>
					<br>
					<?php endif;?>
					<?php if(isset($this->invoiceSettings->address)): ?>
					<strong><?php echo __('Address', 'booki') ?></strong>: <?php echo $this->invoiceSettings->address ?>
					<br>
					<?php endif; ?>
					<?php if(isset($this->invoiceSettings->telephone)): ?>
					<strong><?php echo __('Tel', 'booki') ?></strong>: <?php echo $this->invoiceSettings->telephone ?>
					<br>
					<?php endif; ?>
					<?php if(isset($this->invoiceSettings->email)): ?>
					<strong><?php echo __('Email', 'booki') ?></strong>: <?php echo $this->invoiceSettings->email ?>
					<br>
					<?php endif; ?>
					<br>
					<br>
					<br>
					<?php if($data->order->status === Booki_PaymentStatus::PAID): ?>
					<strong><?php echo __('STATUS: PAID', 'booki')?></strong>
					<?php else:?>
					<strong><?php echo __('STATUS: UNPAID', 'booki')?></strong>
					<?php endif; ?>
					<br>
					<br>
					<br>
					<?php if(isset($userInfo['name'])): ?>
					<strong><?php echo __('SOLD TO:', 'booki')?></strong>
					<br>
					<?php echo $userInfo['name'] ?>
					<br>
					<?php endif; ?>
					<strong><?php echo __('EMAIL', 'booki')?>:</strong> <?php echo $userInfo['email'] ?>
					<?php if($data->discount > 0):?>
					<br>
					<?php echo __('Coupon discount:', 'booki')?> <strong><?php echo -$data->discount ?>%</strong>
					<?php endif; ?>
					<?php if($this->globalSettings->tax > 0): ?>
					<br>
					<?php echo __('SALES TAX RATE:', 'booki')?> <strong><?php echo $this->globalSettings->tax ?>%</strong>
					<?php endif; ?>
				</td>
				<td align="right" valign="top">
					<?php echo __('ORDER NUMBER') ?>: #<?php echo $data->orderId ?>
					<br>
					<?php echo __('CUSTOMER NUMBER') ?>: #<?php echo $data->order->userId ?>
					<br>
					<?php echo __('ORDER DATE') ?>: <?php echo Booki_Helper::formatDate( $data->order->orderDate) ?>
				</td>
			</tr>
		</table>
		<br>
		<br>
		<?php echo $orderDetails ?>
		<br>
		<?php if(isset($this->invoiceSettings->additionalNote)){
			 echo $this->invoiceSettings->additionalNote . '<br>';
		}?>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function orderDetails($data){
		ob_start();
		if(!$data){
			return '';
		}
	?>
	<table width="100%" border="1" cellpadding="2">
		<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<?php echo __('Dates', 'booki') ?>
				</th>
				<?php if($data->totalAmount > 0):?>
				<th align="left">
					<?php echo __('Cost', 'booki') ?>
				</th>
				<?php endif; ?>
			</tr>
		</thead>
			<?php foreach($data->bookings as $booking): ?>
				<?php $projectName = null; ?>
				<?php foreach( $booking->dates as $item ) : ?>
					<?php if($projectName != $item['projectName']):?>
					<tr>
						<td align="middle" colspan="2"><strong><?php echo $item['projectName']?></strong></td>
					</tr>
					<?php $projectName = $item['projectName']; ?>
					<?php endif; ?>
					<tr>
						<td>
							<?php echo $item['formattedDate'] ?>
							<?php if($item['formattedTime']):?>
								<br>
								<?php echo $item['formattedTime'] ?>
								<?php if($this->displayTimezone):?>
									(<small><strong><?php echo __('Timezone', 'booki')?>:</strong>
										<?php echo $data->timezoneInfo['timezone']  ?>
									</small>)
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if($data->totalAmount > 0):?>
								<?php echo $item['formattedCost']  . ' ' . $data->currency ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php $projectName = null; ?>
			<?php if(count($booking->optionals) > 0):?>
				<?php foreach( $booking->optionals as $item ) : ?>
					<?php if($projectName != $item['projectName']):?>
					<tr>
						<td align="middle" colspan="2"><strong><?php echo $item['projectName']?></strong></td>
					</tr>
					<?php $projectName = $item['projectName']; ?>
					<?php endif; ?>
					<tr>
						<td><?php echo $item['calculatedName'] ?></td>
						<td>
							<?php if($data->totalAmount > 0):?>
								<?php echo $item['formattedCalculatedCost'] . ' ' . $data->currency ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php $projectName = null; ?>
			<?php if(count($booking->cascadingItems) > 0):?>
				<?php foreach( $booking->cascadingItems as $item ) : ?>
					<?php if($projectName != $item['projectName']):?>
					<tr>
						<td align="middle" colspan="2"><strong><?php echo $item['projectName']?></strong></td>
					</tr>
					<?php $projectName = $item['projectName']; ?>
					<?php endif; ?>
					<tr>
						<td><?php echo $item['calculatedName'] ?></td>
						<td>
							<?php if($data->totalAmount > 0):?>
								<?php echo $item['formattedCalculatedCost'] . ' ' . $data->currency ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if($data->deposit > 0):?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Payment due upon arrival', 'booki') ?></strong>
					<?php echo $data->currencySymbol . $data->formattedTotalAfterDeposit . ' ' . $data->currency ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Advance deposit required now', 'booki') ?></strong>
					<?php echo $data->currencySymbol . $data->deposit . ' ' . $data->currency ?>
				</td>
			</tr>
		<?php endif;?>
		<?php if($data->totalAmount > 0):?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Sub total', 'booki') ?></strong>
					<?php echo $data->formattedTotalAmount . ' ' . $data->currency ?>
				</td>
			</tr>
			<?php if($data->discount > 0 && $data->hasBookings):?>
			<tr>
				<td colspan="2" align="right">
						<strong><?php echo __('Discount', 'booki') ?></strong>
						-<?php echo $data->discount . '%' ?>
				</td>
			</tr>
			<?php endif;?>
			<?php if($data->tax > 0 && $data->hasBookings):?>
			<tr>
				<td colspan="2" align="right">
						<strong><?php echo __('Tax', 'booki') ?></strong>
						<?php echo $data->tax ?>%
				</td>
			</tr>
			<?php endif;?>
			<tr>
				<td colspan="2" align="right">
					<strong><?php echo __('Total', 'booki') ?></strong>
					<?php echo $data->currencySymbol . $data->formattedTotalAmountIncludingTax  . ' ' . $data->currency ?>
				</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function orderAddtionalInfo($data)
	{
		if((!($data && $data->order)) || $data->order->bookedFormElements->count() === 0){
			return '';
		}
		ob_start();
	?>
	<strong><?php echo __('Additional Information', 'booki');?></strong>
	<br>
	<table width="100%" border="1" cellpadding="2">
		<thead>
			<tr bgcolor="#f1f1f1">
				<th align="left">
					<?php echo __('Field name', 'booki'); ?>
				</th>
				<th align="left">
					<?php echo __('Value', 'booki'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach( $data->order->bookedFormElements as $item ) : ?>
		  <tr>
			<td align="left">
				<?php echo $item->label ?>
			</td>
			<?php switch($item->elementType):
					case Booki_ElementType::CHECKBOX:
					case Booki_ElementType::RADIOBUTTON:
			?>
			<td align="left">
				<?php echo __('Selected', 'booki') ?>
			</td>
			<?php 	
					break;
					default:
			?>
			<td align="left">
				<?php echo esc_html($item->value) ?>
			</td>
			<?php endswitch;?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<br />
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	protected function generatePDF($buffer, $orientation = 'P', $unit = 'mm', $format = 'A4'){
		$fileName = $this->orderId . '-invoice.pdf';
		$pdf = new TCPDF($orientation, $unit, $format); 
		$pdf->AddPage(); 
		@$pdf->WriteHTML($buffer); 
		$pdf->Output($fileName, 'D');
	}
	
	
	public function setHtmlContentType() {
		return 'text/html';
	}
}
?>