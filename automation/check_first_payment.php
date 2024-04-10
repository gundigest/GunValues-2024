<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//IF this is run after 9 am PT daily, we should not need to worry about timezone specifics, but for testing:
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/payment_functions.php");
include("../model/mail_functions.php");
//Cron to check Paytrace for missed recurring payments
//Runs daily
//Checks for New recurring customers from two days ago to make sur etheir payment was actually charged.
$customers = getNewRecurPayments();
if(!$customers){//None for today
	error_log("No recurring payments for 2 days ago.");
	exit;
}else{	
	$token = getPayTraceToken();	
	$today = date("Y-m-d");
	$start_range = 	date("Y-m-d",strtotime("-3 days"));
	foreach($customers AS $cust){
		$payment_date = "";
		$pt_payment = getLastRecurringPayment($token,$cust['user_id']);
		var_dump($pt_payment);
		if($pt_payment['success']){
			//check that date matches
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
			if(strtotime($payment_date) > strtotime($start_range)){//All good!							
				echo $cust['user_id'] . " was charged successfully";				
			}else{//A past payment was last, so today's failed				
				$recur_id = getPaymentID($cust['user_id']);
				echo "Will delete user " . $cust['user_id'] . " recurrence ID #:" . $recur_id;
				deleteRecurringProcess($token,$recur_id,$cust['user_id']);
				updateFailedPayment($cust['user_id'],$recur_id);
				updatePlanStatus($cust['user_id'],"cancelled");
				log_activity($cust['user_id'],"Automatic Plan Cancellation","Initial recurring Payment Failed on " . $today);
				sendFirstPaymentFailedEmail($cust['user_id']);
			}			
		}else{//No payments registered, so find payment/recur id and cancel recurring plan
			$recur_id = getPaymentID($cust['user_id']);
			echo "Will delete user " . $cust['user_id'] . " recurrence ID #:" . $recur_id;			
			if($recur_id){
				deleteRecurringProcess($token,$recur_id,$cust['user_id']);
				updateFailedPayment($cust['user_id'],$recur_id);
			}
			updatePlanStatus($cust['user_id'],"cancelled");
			log_activity($cust['user_id'],"Automatic Plan Cancellation","Initial recurring Payment Failed on " . $today);
			sendFirstPaymentFailedEmail($cust['user_id']);
		}
	}	
}
?>