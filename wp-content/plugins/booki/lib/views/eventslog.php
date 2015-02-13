<?php
	require_once  dirname(__FILE__) . '/../infrastructure/utils/Helper.php';
	require_once  dirname(__FILE__) . '/../infrastructure/ui/lists/EventsLogList.php';
	require_once  dirname(__FILE__) . '/../controller/EventsLogController.php';
	class Booki_EventsLogView{
		public $eventsLogList;
		public function __construct(){
			
			new Booki_EventsLogController(array($this, 'delete'), array($this, 'deleteAll'));
			
			$this->eventsLogList = new Booki_EventsLogList();
			$this->eventsLogList->bind();
		}
		
		public function delete($result){
		
		}
		public function deleteAll($result){

		}
	}
	
	$_Booki_EventsLogView = new Booki_EventsLogView();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<div class="booki-callout booki-callout-info">
			<h4><?php echo __('Events log', 'booki') ?></h4>
			<p><?php echo __('An overview of all the errors thrown by services like Paypal and MailChimp and failed Emails. 
			If there are failures, they will show here. Messages are geeky var_dumps, feel free to ignore when in doubt.', 'booki') ?> </p>
		</div>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<div class="alert alert-warning">
				<p><?php echo __('From time to time, make sure you clear events out. The data itself is of no use after reading through the log.', 'booki')?></p>
			</div>
			<form class="form-horizontal" action="<?php echo admin_url() . 'admin.php?page=booki/eventslog.php' ?>" method="post">
				<input type="hidden" name="controller" value="booki_eventslog" />
				<p><button class="btn btn-primary" name="deleteall"><?php echo __('Clear Event Log', 'booki') ?></button></p>
			</form>
			<div class="table-responsive">
				<?php $_Booki_EventsLogView->eventsLogList->display() ?>
			</div>
		</div>
	</div>
</div>