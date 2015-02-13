<?php
	require_once  dirname(__FILE__) . '/../infrastructure/handlers/bookingscsvhandler.php';
	
	$name = md5(uniqid() . microtime(true) . mt_rand()). '.csv';
	
	ob_start();
	
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=bookings'. $name);
    header('Pragma: no-cache');
    header("Expires: 0");

	new Booki_BookingsCSVHandler();
	
	ob_end_flush();
?>