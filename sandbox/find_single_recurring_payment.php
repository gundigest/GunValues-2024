<?php
//IF this is run after 9 am PT daily, we should not need to worry about timezone specifics, but for testing:
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/payment_functions.php");

	$customer = 17859;

	echo "hello";
	$token = getPayTraceToken();	
	$today = date("Y-m-d");
	$start_range = 	date("Y-m-d",strtotime("-5 days"));
		$payment_date = "";
		$pt_payment = getLastRecurringPayment($token,$customer);
		var_dump($pt_payment);
		if($pt_payment['success']){
			echo "Success!";
		
			//check that date matches
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
			if(strtotime($payment_date) > strtotime($start_range)){//All good!			
				echo "date is good";
				//If not a Refund
				if($pt_payment['transaction_type'] != "REFUND"){
					echo "Not a refund!";
				}
			}
		}
			
		/*		//If it exists, add to DB
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
			}else{//A past payment was last, so today's failed				
				$recur_id = $pt_payment['recurrence']['id'];
				echo "Will delete user " .$cust['user_id']. " recurrence ID #:" . $recur_id;
				deleteRecurringProcess($token,$recur_id,$cust['user_id']);
				updatePlanStatus($cust['user_id'],"cancelled");
				log_activity($cust['user_id'],"Automatic Plan Cancellation","Recurring Payment Failed on " . $today);
			}			
		}else{//No payments registered at all, so nothing to cancel at PT
			echo "Will delete user " .$cust['user_id'];
			updatePlanStatus($cust['user_id'],"cancelled");
			log_activity($cust['user_id'],"Automatic Plan Cancellation","Recurring Payment Failed on " . $today);
		}*/
		

?>