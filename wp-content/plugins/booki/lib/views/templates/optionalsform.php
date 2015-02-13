<?php

	$_Booki_OptionalsFormTmpl = new Booki_OptionalsFormTmpl();
?>
<?php if(count($_Booki_OptionalsFormTmpl->data->optionals) > 0):?>
	<div><strong><?php echo $_Booki_OptionalsFormTmpl->data->optionalItemsLabel ?></strong></div>
	<ul class="booki booki-optionals-list list-group">
	<?php foreach($_Booki_OptionalsFormTmpl->data->optionals as $key=>$optional): ?>
		<li class="booki_<?php echo $optional->id ?> list-group-item">
			<span class="badge">
				<span class="booki_optionals_cost">
					<?php echo $optional->formattedCost ?>
				</span>
			</span>
			
			<input type="<?php echo $_Booki_OptionalsFormTmpl->data->optionalsListingMode === Booki_OptionalsListingMode::CHECKBOXLIST ? 'checkbox' : 'radio' ?>"
					id="booki_optional_<?php echo $optional->id ?>"
					name="<?php echo $_Booki_OptionalsFormTmpl->data->groupName ?>[]" 
					value="<?php echo $optional->id ?>" 
					class="booki-optional booki_parsley_validated" 
					data-cost="<?php echo $optional->cost ?>"
					<?php echo $optional->checkedStatus ?>
					<?php if($key === 0):?>
					<?php echo $_Booki_OptionalsFormTmpl->data->optionalsMinimumSelection ? 
							sprintf('data-parsley-mincheck="%s"', $_Booki_OptionalsFormTmpl->data->optionalsMinimumSelection) : '' ?>
					<?php endif;?>
			/>
			<span>
				<?php echo $optional->name ?>
				<?php if($_Booki_OptionalsFormTmpl->data->optionalsBookingMode === Booki_OptionalsBookingMode::EACH_DAY): ?>
				<strong><sup class="booki_optionals_count"></sup></strong>
				<?php endif; ?>
			</span>
			<div class="clearfix"></div>
		</li>
	<?php endforeach;?>
	</ul>
<?php endif;?>