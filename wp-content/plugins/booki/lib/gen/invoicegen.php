<?php
	require_once  dirname(__FILE__) . '/../infrastructure/handlers/invoicehandler.php';
	
	//ob_start();
	
	new Booki_InvoiceHandler();
	
	//ob_end_flush();
?>