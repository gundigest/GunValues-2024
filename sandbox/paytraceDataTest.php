<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//IF this is run after 9 am PT daily, we should not need to worry about timezone specifics, but for testing:
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/payment_functions.php");

//Cron to check Paytrace for missed recurring payments
//Runs daily
//Checks for New recurring customers from two days ago to make sur etheir payment was actually charged.
	$cust_id = 5639;
	$token = getPayTraceToken();	
	$pt_payment = getLastRecurringPayment($token,$cust_id);
	var_dump($pt_payment);
	$recur_id = getRecurId($cust_id);
	echo $recur_id;
	//updateFailedPayment($cust_id,$recur_id);
?>