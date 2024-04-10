<?php
error_reporting(E_ALL);
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
	$token = getPayTraceToken();
	echo "hello";
	echo $token;
	$today = date("Y-m-d");
	$start_range = 	date("Y-m-d",strtotime("-5 days"));
	foreach($customers AS $cust){
		$payment_date = "";
		$pt_payment = getLastRecurringPayment($token,$cust['user_id']);
		echo "<br/><br/>" . $cust['user_id'] . "<br/>";
		var_dump($pt_payment);
		if($pt_payment['success']){
			//check that date matches
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
			if(strtotime($payment_date) > strtotime($start_range)){//All good!			
				//If not a Refund
				if($pt_payment['transaction_type'] != "REFUND"){
					//If it exists, add to DB
					$payment_date = date("Y-m-d H:i:s",strtotime($pt_payment['created']['at']));
					$payment = array(
						"user_id" 	=> $cust['user_id'],
						"payment_id" 	=> $cust['payment_id'],
						"pt_id"			=> $pt_payment['approval_code'],
						"payment_date"	=> $payment_date,
						"amount"		=> $pt_payment['amount']	
					);
					echo $cust['user_id'];
					$recur = addRecurPayment($payment);					
					if(!$recur) error_log("Recurring cron attempted duplicate recurring payment recording for " . $cust['user_id'] . ", PT Approval ID: " . $pt_payment['approval_code']);
				}	
			}else{//A past payment was last, so today's failed
				if($cust['status'] == "ongoing"){//Only cancel if this was an ongoing account
					//WRONG $recur_id = $pt_payment['recurrence']['id'];
					$recur_id = getPaymentID($cust['user_id']);
					echo "Would delete user " . $cust['user_id'] . " recurrence ID #:" . $recur_id;
					deleteRecurringProcess($token,$recur_id,$cust['user_id']);
					updatePlanStatus($cust['user_id'],"cancelled");
					log_activity($cust['user_id'],"Automatic Plan Cancellation recurrence " . $recur_id,"Recurring Payment Failed on " . $today);
				}else{//This is not an ongoing account. It may already be cancelled or expiring
					echo "Account in the status of " . $cust['status'] . " will have no effect taken";
					log_activity($cust['user_id'],"Account in " . $cust['status'] . " status. No action taken on " . $recur_id,"Recurring Payment Failed on " . $today);
				}					
			}			
		}else{//No payments registered at all, so CANCEL ANYWAY
			//Cancel even though nothing registered, because Paytrace is trash and will keep trying
			$recur_id = getPaymentID($cust['user_id']);
			echo "Would delete user " . $cust['user_id'] . " recurrence ID #:" . $recur_id;
			deleteRecurringProcess($token,$recur_id,$cust['user_id']);
			updatePlanStatus($cust['user_id'],"cancelled");
			log_activity($cust['user_id'],"Automatic Plan Cancellation: no recurring payment for " . $recur_id,"Recurring Payment Failed on " . $today);
		}
	}	
}
?>