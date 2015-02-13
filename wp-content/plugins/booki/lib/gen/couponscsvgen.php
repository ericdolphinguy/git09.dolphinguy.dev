<?php
	require_once  dirname(__FILE__) . '/../infrastructure/handlers/couponscsvhandler.php';
	
	$name = md5(uniqid() . microtime(true) . mt_rand()). '.csv';
	
	ob_start();
	
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=coupons'. $name);
    header('Pragma: no-cache');
    header("Expires: 0");

	new Booki_CouponsCSVHandler();
	
	ob_end_flush();
?>