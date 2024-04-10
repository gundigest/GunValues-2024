<?php
//IF this is run after 9 am PT daily, we should not need to worry about timezone specifics, but for testing:
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/payment_functions.php");
//Cron to check Paytrace for old recurring payments
//Get all monthly users
global $db;	
	try
       {	
        $stmt = $db->prepare("SELECT p.payment_id,u.user_id FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE u.`status` LIKE 'ongoing' AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'monthly')");		
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);			
		}else return false;	
	  }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
	$token = getPayTraceToken();
	$count = 0;
	foreach($customers AS $cust){		
		$payment_date = "";
		
		 //Get ALL Recurring Payments
			echo "<br/>" . $cust['user_id'];
	 $data = array("customer_id"=>"PTGV_" . $cust['user_id']);  
	 //initialize session
        $ch=curl_init("https://api.paytrace.com/v1/recurrence/export_approved");

        //set options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type:application/json",
			"Authorization: Bearer " . $token)
			);
        //execute session
        $last_payment_json = curl_exec($ch);
        $pt_payment = json_decode($last_payment_json,true);		
		var_dump($pt_payment);
		//close session
        curl_close($ch);
		
		/*if($pt_payment['success']){
			//check that date matches
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
					
				//If it exists, add to DB
				$payment_date = date("Y-m-d H:i:s",strtotime($pt_payment['created']['at']));
				$payment = array(
					"user_id" 	=> $cust['user_id'],
					"payment_id" 	=> $cust['payment_id'],
					"pt_id"			=> $pt_payment['approval_code'],
					"payment_date"	=> $payment_date,
					"amount"		=> $pt_payment['amount']	
				);
				addRecurPayment($payment);
			}else{//A past payment was last, so today's failed					
				echo "No payment";				
			}*/
			
		$count++;
	}	
	echo "Total: " . $count;

?>