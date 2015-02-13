<?php
	$_Booki_BookedFormElements = apply_filters( 'booki_booked_form_elements', null);
?>
<div class="booki booki-content-box">
	<div class="booki-callout booki-callout-warning">
		<h4><?php echo __('Additional Information', 'booki') ?></h4>
		<p><?php echo __('This is additional information collected during the booking process.', 'booki') ?></p>
	</div>
	<table class="booki-grid table table-striped">
		<thead>
			<th><?php echo __('Field name', 'booki'); ?></th>
			<th><?php echo __('Value', 'booki'); ?></th>
		</thead>
		<tbody>
		<?php foreach( $_Booki_BookedFormElements as $item ) : ?>
		  <tr>
			<td><span><?php echo $item->label ?></span></td>
			<?php switch($item->elementType):
					case Booki_ElementType::CHECKBOX:
					case Booki_ElementType::RADIOBUTTON:
			?>
			<td>
				<span><?php echo __('Checked', 'booki') ?></span>
			</td>
				<?php 	
						break;
						default:
				?>
			<td>
				<span><?php echo esc_html($item->value) ?></span>
			</td>
			<?php endswitch;?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
