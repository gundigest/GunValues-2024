<?php
    include("../model/config.php");    
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

	$user_id = "PGTV_" . 5639;	

//-------------------------------------------------------------------------------------------------------------Authenticate with Paytrace
	$token = getPayTraceToken();		
	$pt_payment = getLastRecurringPayment($token,$user_id);
	var_dump($pt_payment);
?>