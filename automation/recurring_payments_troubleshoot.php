<?php
  ini_set('error_reporting',1);
//IF this is run after 9 am PT daily, we should not need to worry about timezone specifics, but for testing:
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/payment_functions.php");
//Cron to check Paytrace for missed recurring payments
//Runs daily
//Checks for Ongoing Monthly and yearly plans that should recur today (query on day of the month)
$customers = getRecurTodayPayments();
if(!$customers){//None for today
	error_log("No recurring payments for today");
	exit;
}else{
	echo "hello<br/>";
	$token = getPayTraceToken();	
	$today = date("Y-m-d");
	$start_range = 	date("Y-m-d",strtotime("-5 days"));
	foreach($customers AS $cust){
		$payment_date = "";
		$pt_payment = getLastRecurringPayment($token,$cust['user_id']);
		echo "<br/><br/>" . $cust['user_id'] . "<br/>";
		var_dump($pt_payment);
		if($pt_payment['success']){
			echo "Success!" . "<br/>";
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
			if(strtotime($payment_date) > strtotime($start_range)){//All good!			
				echo "Date is Good!" . "<br/>";
			}	
		}
	}
}	
?>