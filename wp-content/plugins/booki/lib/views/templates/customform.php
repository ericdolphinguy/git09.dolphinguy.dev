<?php 
	if(!isset($_Booki_CustomFormTmpl)){
		$_Booki_CustomFormTmpl = new Booki_CustomFormTmpl();
	}
?>
<?php foreach($_Booki_CustomFormTmpl->data->rows as $row): ?>
	<?php foreach($row as $col): ?>
		<div class="col-lg-<?php echo floor(12 / count($row)) ?>">
			<div class="<?php 
						if (!in_array($col->elementType, array(4,5,13))):
							echo "form-group ";
						endif;?>booki_form_element_<?php echo $col->id ?>">
				<?php if(in_array($col->elementType, array(0,1,2,3))): ?>
					<label class="col-lg-4 control-label" for="booki_form_element_<?php echo $col->id ?>"><?php echo $col->label_loc?></label>
				<?php endif;?>
				<div class="<?php if (in_array($col->elementType, array(4,5,13))):
										echo "col-lg-8 col-lg-offset-4";
									elseif($col->elementType < 4):
										echo "col-lg-8";
									else:
										echo "col-lg-12";
									endif;?>">
					<?php if($col->elementType === Booki_ElementType::TEXTBOX): ?>
						<input type="text"
								id="booki_form_element_<?php echo $col->id ?>" 
								name="booki_form_element_<?php echo $col->id ?>" 
								value="<?php echo $col->value ?>"
								class="<?php echo $col->className ?> form-control booki_parsley_validated"
								<?php echo $_Booki_CustomFormTmpl->data->fieldStatus($col) ?>
								<?php echo $_Booki_CustomFormTmpl->data->getAttributes($col)?>/>
					<?php elseif($col->elementType === Booki_ElementType::TEXTAREA): ?>
						<textarea 
								id="booki_form_element_<?php echo $col->id ?>"
								name="booki_form_element_<?php echo $col->id ?>" 
								value="<?php echo $col->value ?>" 
								class="<?php echo $col->className ?> form-control booki_parsley_validated"
								<?php echo $_Booki_CustomFormTmpl->data->fieldStatus($col) ?>
								<?php echo $_Booki_CustomFormTmpl->data->getAttributes($col)?>></textarea>
					<?php elseif($col->elementType === Booki_ElementType::DROPDOWNLIST || $col->elementType === Booki_ElementType::LISTBOX): ?>
						<select 
								id="booki_form_element_<?php echo $col->id ?>"
								name="booki_form_element_<?php echo $col->id ?>" 
								class="<?php echo $col->className ?> form-control booki_parsley_validated"
								<?php echo $_Booki_CustomFormTmpl->data->fieldStatus($col) ?>
								<?php echo $col->elementType === Booki_ElementType::LISTBOX ? 'multiple="multiple"' : '' ?>>
								<?php foreach($col->bindingData as $key=>$value):?>
									<option value="<?php echo $value?>" <?php echo $col->value === $value ? 'selected="selected"' : '' ?>><?php echo $value?></option>
								<?php endforeach;?>
						</select>
					<?php elseif($col->elementType === Booki_ElementType::TC): ?>
						<div class="checkbox">
							<label class="checkbox" for="booki_form_element_<?php echo $col->id ?>">
								<input type="checkbox"
									id="booki_form_element_<?php echo $col->id ?>"
									class="<?php echo $col->className ?> booki_parsley_validated"
									data-parsley-required="true"
									data-parsley-trigger="change"/>
									<a href="<?php echo $col->value ?>">
										<?php echo $col->label_loc ?>
									</a>
							</label>
						</div>
					<?php elseif($col->elementType === Booki_ElementType::CHECKBOX): ?>
						<div class="checkbox">
							<label class="checkbox">
								<input type="checkbox"
									id="booki_form_element_<?php echo $col->id ?>"
									name="booki_form_element_<?php echo $col->id ?>"
									value="checkbox" 
									class="<?php echo $col->className ?> booki_parsley_validated"
									<?php echo (count($col->bindingData) > 0 && $col->bindingData[0]) ? 'checked="checked"' : ''?>
									<?php echo $_Booki_CustomFormTmpl->data->fieldStatus($col) ?>
									<?php echo $_Booki_CustomFormTmpl->data->getAttributes($col)?>/>
									<span>
										<?php echo $col->label_loc ?>
									</span>
							</label>
						</div>
					<?php elseif($col->elementType === Booki_ElementType::RADIOBUTTON): ?>
						<div class="radio">
							<label class="radio">
								<input type="radio" 
									id="booki_form_element_<?php echo strlen($col->value) > 0 ? $col->value : $col->id ?>" 
									name="booki_form_element_<?php echo strlen($col->value) > 0 ? $col->value : $col->id ?>" 
									value="<?php echo $col->label_loc ?>" 
									class="<?php echo $col->className ?> booki_parsley_validated"
									<?php echo (count($col->bindingData) > 0 && $col->bindingData[0]) ? 'checked="checked"' : ''?>
									<?php echo $_Booki_CustomFormTmpl->data->fieldStatus($col) ?>
									<?php echo $_Booki_CustomFormTmpl->data->getAttributes($col)?>/>
									<span>
										<?php echo $col->label_loc ?>
									</span>
							</label>
						</div>
					<?php elseif(in_array($col->elementType, array(6,7,8,9,10,11,12))): ?>
						<?php if($col->elementType === Booki_ElementType::H1): ?>
						<h1 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h1>
						<?php elseif($col->elementType === Booki_ElementType::H2): ?>
						<h2 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h2>
						<?php elseif($col->elementType === Booki_ElementType::H3): ?>
						<h3 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h3>
						<?php elseif($col->elementType === Booki_ElementType::H4): ?>
						<h4 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h4>
						<?php elseif($col->elementType === Booki_ElementType::H5): ?>
						<h5 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h5>
						<?php elseif($col->elementType === Booki_ElementType::H6): ?>
						<h6 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</h6>
						<?php elseif($col->elementType === Booki_ElementType::PLAINTEXT): ?>
						<p 
							<?php if($col->className):?>
							class="<?php echo $col->className ?>"
							<?php endif;?>>
							<?php echo $col->value ?>
						</p>
						<?php endif; ?>
					<?php endif;?>
				</div>
			</div>
			<?php if($col->lineSeparator):?>
			<hr class="booki-hr"/>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<?php endforeach; ?>