<label class="col-lg-4 control-label" for="useremail">
	<?php echo __('User email', 'booki') ?>
</label>
<div class="col-lg-8">
	<input type="text" 
			id="useremail" 
			class="form-control"
			name="useremail" 
			data-parsley-required="true" 
			data-parsley-type="email" 
			data-parsley-trigger="change" />
	<div class="clearfix"></div>
	<div class="progress progress-striped active booki-useremail hide">
		<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
			aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
		<div class="clearfix"></div>
	</div>
	<div class="alert alert-info hide useremail-info"></div>
</div>

