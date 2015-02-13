<?php
$_Booki_BookingViewTmpl = new Booki_BookingViewTmpl();
?>

<div>
	<?php Booki_ThemeHelper::includeTemplate('master.php') ?>
</div>
<div>
<?php 
	$render = new Booki_Render();
	echo $render->bookingList($_Booki_BookingViewTmpl->projectListArgs);
?>
</div>