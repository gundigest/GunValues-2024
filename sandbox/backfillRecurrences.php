<?php //Use to manually backfill payment recurrences in the database using PayTrace data
    include("../model/config.php");   
    include("../model/payment_functions.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;	
	//$customers = getRecurPaymentsByDate("2022-10-01");//done up to 4
	$curdate = date("Y-m-d",strtotime("2023-06-28"));//This is the start date for when the porocesss should have run
	$finaldate = date("Y-m-d",strtotime("2023-07-11"));//This is the end date for when the process should have run
	while($curdate <= $finaldate){
		echo "Current Date is: " . $curdate . "<br/>";
		$customers = getRecurPaymentsByDate($curdate);	
		if(!$customers){//None for today
			error_log("No recurring payments");
			exit;
		}else{
			echo "hello";
			$token = getPayTraceToken();	
			$today = date("Y-m-d");			
			foreach($customers AS $cust){
				$payment_date = "";
				$format_curdate = date("m/d/Y",strtotime($curdate));
				$format_finaldate = date("m/d/Y",strtotime($finaldate));
				$pt_payments = getAllRecurringPayments($token,$cust['user_id'],$format_curdate,$format_finaldate);
				echo "<br/>";		
				print("<pre>".print_r($pt_payments,true)."</pre>");
				foreach($pt_payments['transactions'] AS $pt_payment){
					if(strpos($pt_payment['status_message'],"Settled") > -1){
						//check that date matches
						$payment_date = date("Y-m-d",strtotime($pt_payment['settled']));				
							//If not a Refund
							if($pt_payment['transaction_type'] != "REFUND"){
								//If it exists, add to DB
								$payment_date = date("Y-m-d H:i:s",strtotime($pt_payment['settled']));
								$payment = array(
									"user_id" 	=> $cust['user_id'],
									"payment_id" 	=> $cust['payment_id'],
									"pt_id"			=> $pt_payment['transaction_id'],
									"payment_date"	=> $payment_date,
									"amount"		=> $pt_payment['amount']	
								);				
								$recur = addRecurPayment($payment);				
								if(!$recur) echo "Backfill attempted duplicate recurring payment recording for " . $cust['user_id'] . ", PT Approval ID: " . $pt_payment['transaction_id'];
							}else echo "This was a refund";	
						}		
					}	
				}		
			}
		$curdate = date("Y-m-d",strtotime($curdate . " + 1 day"));			
	}		


function getRecurPaymentsByDate($date){
	
	global $db;	
	try
       {		   
		//First, find the monthly users that need to pay today	
		//See who is too new to need to be checked
		$one_month_ago = date('Y-m-d H:i:s',strtotime(" - 1 month", strtotime($date . " 00:00:00")));	
		$cutoff = date('Y-m-d H:i:s',strtotime($one_month_ago));	
		//Get date 4 days ago to check that a payment has occurred		
		$isLeapYear = date('L',strtotime("-4 days", strtotime($date . " 00:00:00")));		
		$dayofmonth = date('j',strtotime("-4 days", strtotime($date . " 00:00:00")));
		$numofmonth = date('n',strtotime("-4 days", strtotime($date . " 00:00:00")));
		$range = "(" . $dayofmonth . ")";
		echo "Range is: " . $range . " and Cutoff is " . $cutoff;
		if(($dayofmonth=="30")){			
			if(($numofmonth=="4")||($numofmonth=="6")||($numofmonth=="9")||($numofmonth=="11")){//Months with 30 days
				//Also need to grab 31
				$range = "(30,31)";
			}
		}else if(($isLeapYear===1)&&($dayofmonth=="29")){//Leap year on the 29th
				if($numofmonth=="2"){					
					$range = "(29,30,31)";
				}			
		}else if(($isLeapYear===0)&&($dayofmonth=="28")){//Non-leap year on the 28th
			if($numofmonth=="2"){				
				$range = "(28,29,30,31)";
			}
		}		
		$users = false;
		$users_mon = $users_ann = array();
		//Get Monthly Ongoing recurrences
        //$stmt = $db->prepare("SELECT p.payment_id,u.user_id FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE u.`status` LIKE 'ongoing'  AND u.timestamp < :cutoff AND DAYOFMONTH(u.`timestamp`) IN " . $range . " AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'monthly')");
        $stmt = $db->prepare("SELECT p.payment_id,u.user_id FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE (u.`status` LIKE 'ongoing' OR u.user_id IN (SELECT user_id FROM activity_log WHERE activity = 'Cancelled Recurring Plan' AND details LIKE '%Success%' AND timestamp > DATE_SUB(:date, INTERVAL 1 MONTH))) AND u.timestamp < :cutoff AND DAYOFMONTH(u.`timestamp`) IN " . $range . " AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'monthly')");
		$stmt->bindparam(":cutoff", $cutoff);
		$stmt->bindparam(":date", $date);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $users_mon = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		//Get Yearly Ongoing recurrences, must match the day exactly
		$stmt = $db->prepare("SELECT p.payment_id,u.user_id FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE (u.`status` LIKE 'ongoing' OR u.user_id IN (SELECT user_id FROM activity_log WHERE activity = 'Cancelled Recurring Plan' AND details LIKE '%Success%' AND timestamp > DATE_SUB(:date, INTERVAL 1 YEAR)))  AND u.timestamp < :cutoff AND MONTH(u.`timestamp`) = :month AND DAYOFMONTH(u.`timestamp`) IN " . $range . " AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'yearly')");
		$stmt->bindparam(":date", $date);
		$stmt->bindparam(":month", $numofmonth);
		$stmt->bindparam(":cutoff", $cutoff);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $users_ann = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		$users = array_merge($users_mon,$users_ann);
		return $users;		
	  }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    	
}	
