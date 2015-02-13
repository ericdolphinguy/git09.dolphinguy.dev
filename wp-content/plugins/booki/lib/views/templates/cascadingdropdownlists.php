<?php

	$_Booki_CascadingDropdownListTmpl = new Booki_CascadingDropdownListTmpl();
	if($_Booki_CascadingDropdownListTmpl->data->cascadingLists->count() === 0){
		return;
	}
?>

<?php foreach($_Booki_CascadingDropdownListTmpl->data->cascadingLists as $cascadingList): ?>
	<div class="form-group">
		<label class="col-lg-4 control-label" for="booki_cascadingdropdown_<?php echo $cascadingList->id?>">
			<?php echo $cascadingList->label_loc ?>
		</label>
		<div class="col-lg-8">
			<select name="booki_cascadingdropdown_<?php echo $cascadingList->id?>" 
				<?php if($cascadingList->isRequired):?>
				data-parsley-trigger="change"
				data-parsley-required="true"
				<?php endif; ?>
				data-booki-placeholder="#booki_cascading_list_placeholder_<?php echo $cascadingList->id?>"
				id="booki_cascadingdropdown_<?php echo $cascadingList->id?>"
				class="booki_parsley_validated form-control booki-cascading-list">
				<option value=""><?php echo __('Select an item', 'booki') ?></option>
				<?php foreach($cascadingList->cascadingItems as $cascadingItem):?>
				<option value="<?php echo $cascadingItem->id ?>" 
					data-booki-parent="<?php echo $cascadingItem->parentId?>" 
					data-booki-cost="<?php echo $cascadingItem->cost ?>"
					data-booki-original-value="<?php echo $cascadingItem->value_loc ?>">
					<?php echo $cascadingItem->getValuePlusFormattedCost($_Booki_CascadingDropdownListTmpl->data->currency, $_Booki_CascadingDropdownListTmpl->data->currencySymbol) ?>
				</option>
				<?php endforeach;?>
			</select>
		</div>
	</div>
	<div id="booki_cascading_list_placeholder_<?php echo $cascadingList->id?>"></div>
<?php endforeach;?>
<div class="form-group">
	<div class="col-lg-8 col-lg-offset-4">
		<div class="progress booki-progress-cascades progress-striped hide">
			<div class="progress-bar"  role="progressbar" aria-valuenow="100" 
				aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
