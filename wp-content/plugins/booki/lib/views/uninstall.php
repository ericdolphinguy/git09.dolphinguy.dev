<?php
require_once  dirname(__FILE__) . '/../controller/UninstallController.php';
class Booki_Uninstall{
	public $disabled = '';
	public $clearMessage;
	public function __construct(){
		if(!current_user_can('delete_plugins')){
			$this->disabled = 'disabled';
		}
		new Booki_UninstallController(array($this, 'clear'), array($this, 'delete'));
		if(isset($_GET['clear'])){
			$this->clearMessage = __('All data has been cleared. Your spick and span, sparky!', 'booki');
		}
	}
	
	public function clear(){
		$loc = admin_url() . 'admin.php?page=booki/uninstall.php&clear=true';
		wp_redirect($loc);
	}
	
	public function delete(){
		//we're already deactivated, can't do nothing now.
	}
}
$_Booki_Uninstall = new Booki_Uninstall();
?>
<div class="booki">
<div class="booki col-lg-12">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="col-lg-12">
		<div class="booki-callout booki-callout-danger">
			<h4><?php echo __('Uninstall', 'booki') ?></h4>
			<p><?php echo __('You are about to wipe out all data associated with booki. Attention: You cannot undo this operation.', 'booki') ?> </p>
		</div>
	</div>
	<form class="form-horizontal" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/uninstall.php&amp;noheader=true" ?>" method="post">
		<input type="hidden" name="controller" value="booki_uninstall" />
		<div class="col-lg-6">
			<div class="booki-content-box">
				<?php if($_Booki_Uninstall->clearMessage): ?>
					<div class="alert alert-success">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
						<?php echo $_Booki_Uninstall->clearMessage ?>
					</div>
				<?php endif; ?>
				<p class="alert alert-warning">
					<?php echo __('Clear all data but leave the booki tables intact. 
					You still lose everything, careful. Brings the plugin back to a fresh install.', 'booki') ?>
				</p>
				<div>
					<button type="button" <?php echo $_Booki_Uninstall->disabled ?>
						data-toggle="modal" data-target="#clearDbModal"
						class="btn btn-warning input-lg">
						<i class="glyphicon glyphicon-fire"></i>
						<?php echo __('Clear', 'booki') ?>
					</button>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
				<div class="booki-content-box">
					<p class="alert alert-danger">
						<?php echo __('Just delete everything, tables included. You lose everything. Plugin will be deactivated.', 'booki') ?>
					</p>
					<div>
						<button type="button" <?php echo $_Booki_Uninstall->disabled ?>
							data-toggle="modal" data-target="#deleteDbModal"
							class="btn btn-danger input-lg">
							<i class="glyphicon glyphicon-trash"></i>
							<?php echo __('Delete', 'booki') ?>
						</button>
					</div>
				</div>
			</div>
			<div class="modal fade" id="clearDbModal" tabindex="-1" role="dialog" aria-labelledby="clearDbModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="clearDbModalLabel"><?php echo __('Clear all data in Booki', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('Are you sure you want to clear all data in the Booki database tables ? You won\'t be able to undo this operation.', 'booki') ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button
								name="clear"
								 <?php echo $_Booki_Uninstall->disabled ?>
								class="btn btn-warning">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Clear', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="deleteDbModal" tabindex="-1" role="dialog" aria-labelledby="deleteDbModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="deleteDbModalLabel"><?php echo __('Delete Booki', 'booki') ?></h4>
						</div>
						<div class="modal-body">
							<?php echo __('Are you sure you want to delete Booki ? You won\'t be able to undo this operation.', 'booki') ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close', 'booki') ?></button>
							<button name="delete" 
								class="delete btn btn-danger">
								<i class="glyphicon glyphicon-trash"></i>
								<?php echo __('Delete', 'booki') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>