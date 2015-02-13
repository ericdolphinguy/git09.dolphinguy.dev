<?php
/**
 * Template Name: Booki List Bookings
 *
 * Selectable from a dropdown menu on the edit page screen.
 */
 
 $_Booki_ListTmpl = new Booki_ListTmpl();
	
?>
<div class="booki booki-no-padding">
	<div class="clearfix"></div>
	<?php if($_Booki_ListTmpl->enableSearch): ?>
	<form class="horizontal-form booki-search-filter" role="form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="get" data-parsley-validate >
		<?php if(get_query_var('page_id')):?>
		<input type="hidden" name="page_id" value="<?php echo get_query_var('page_id')?>" />
		<?php endif; ?>
		<input type="hidden" name="controller" value="booki_searchcontrol" />
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?php echo $_Booki_ListTmpl->heading ?>
				</h3>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<div class="input-group">
						<input type="text" 
							id="<?php echo 'fromdate_' . $_Booki_ListTmpl->uniqueKey ?>"
							name="fromDate" 
							class="form-control booki-datepicker" 
							value="<?php echo $_Booki_ListTmpl->fromDate?>"
							data-parsley-trigger="change"
							data-parsley-required="true"
							data-parsley-errors-container=".<?php echo $_Booki_ListTmpl->uniqueKey ?>_fromdate_error"
							placeholder="<?php echo $_Booki_ListTmpl->fromLabel ?>" 
							readonly="true"/> 
						<label class="input-group-addon" 
							for="<?php echo 'fromdate_' . $_Booki_ListTmpl->uniqueKey ?>">
							<i class="glyphicon glyphicon-calendar"></i>
						</label>
					</div>
					<div class="clearfix"></div>
					<ul class="<?php echo $_Booki_ListTmpl->uniqueKey ?>_fromdate_error"></ul>
				</div>
				<div class="form-group">
					<div class="input-group">
						<input type="text" 
							id="<?php echo 'todate_' . $_Booki_ListTmpl->uniqueKey ?>"
							name="toDate" 
							class="form-control booki-datepicker" 
							value="<?php echo $_Booki_ListTmpl->toDate?>"
							data-parsley-trigger="change"
							data-parsley-required="true"
							data-parsley-errors-container=".<?php echo $_Booki_ListTmpl->uniqueKey ?>_todate_error"
							placeholder="<?php echo $_Booki_ListTmpl->toLabel ?>" 
							readonly="true"/> 
						<label class="input-group-addon" 
							for="<?php echo 'todate_' . $_Booki_ListTmpl->uniqueKey ?>">
							<i class="glyphicon glyphicon-calendar"></i>
						</label>
					</div>
					<div class="clearfix"></div>
					<ul class="<?php echo $_Booki_ListTmpl->uniqueKey ?>_todate_error"></ul>
				</div>
			</div>
			<div class="panel-footer">
				<button class="btn btn-primary pull-right">
					<i class="glyphicon glyphicon-filter"></i>
					<?php echo __('Filter', 'booki') ?>
				</button>
				<div class="clearfix"></div>
			</div>
		</div>
	</form>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.booki-search-filter').BookiSearchFilter({
				'fromDefaultDate': '<?php echo $_Booki_ListTmpl->fromDate ?>'
				, 'toDefaultDate': '<?php echo $_Booki_ListTmpl->toDate ?>'
				, 'fromDateElem': '[name="fromDate"]'
				, 'toDateElem': '[name="toDate"]'
				, 'altFormat': '<?php echo $_Booki_ListTmpl->altFormat ?>'
				, 'dateFormat': '<?php echo $_Booki_ListTmpl->dateFormat ?>'
				, 'calendarCssClasses': '<?php echo $_Booki_ListTmpl->calendarCssClasses ?>'
				, 'calendarFirstDay': <?php echo $_Booki_ListTmpl->calendarFirstDay ?>
				, 'showCalendarButtonPanel': <?php echo $_Booki_ListTmpl->showCalendarButtonPanel ?>
			});
		});
	</script>
	<?php endif; ?>
	<?php if($_Booki_ListTmpl->projectList): ?>
		<?php $_Booki_ListTmpl->projectList->display() ?>
	<?php endif; ?>
	<div class="clearfix"></div>
</div>
