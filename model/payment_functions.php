<?php
//All Payment processes
//-------------------------------------------------------------------------------------------------------------Authenticate with Paytrace
function getPayTraceToken(){

	global $payment_un,$payment_pw,$paytrace_url;
	  //Authorization Code
		 $data = array(
			"grant_type" => "password",
			"username" => $payment_un,
			"password" => $payment_pw
			);
		 //initialize session
			$ch=curl_init($paytrace_url . "/oauth/token");

			//set options
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:'));
			//execute session
			$token_json = curl_exec($ch);
			$exchange_token = json_decode($token_json,true);
			error_log($token_json);
			error_log(json_encode($exchange_token));
			$token = $exchange_token['access_token'];		
			//close session
			curl_close($ch);        
			
			return $token;
			//Todo: add error handling
}
//-------------------------------------------------------------------------------------------------------------Create Paytrace Customer
function createPaytraceUser($token,$data){
	global $paytrace_url,$integrator_id;
	 //initialize session
	 error_log("In CreatePaytraceUser function.");
        $ch=curl_init($paytrace_url . "/v1/customer/create");
		$data['integrator_id'] = $integrator_id;
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
        $purchase_json = curl_exec($ch);
		error_log($purchase_json);
        $results = json_decode($purchase_json,true);		
        //close session
        curl_close($ch);
		
		if($results['success']){
			return true;
		}else return $results;			
}
//-------------------------------------------------------------------------------------------------------------Update Paytrace Customer
function updatePaytraceUser($token,$data){
	global $paytrace_url,$integrator_id;
		 //initialize session
		 error_log("In UpdatePaytraceUser function.");
			$ch=curl_init($paytrace_url . "/v1/customer/update");
			$data['integrator_id'] = $integrator_id;
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
			$purchase_json = curl_exec($ch);
			error_log($purchase_json);
			$results = json_decode($purchase_json,true);			
			//close session
			curl_close($ch);
			
			if($results['success']){
				return true;
			}else return $results;	
	
}
//-------------------------------------------------------------------------------------------------------------Get Existing Paytrace Customer
function getPaytraceUser($token,$customer_id){
	global $paytrace_url,$integrator_id;
	$checkdata = array(      
      "customer_id"=>"PTGV_" . $customer_id,
	  'integrator_id' => $integrator_id
	);
	
	//Check Paytrace to see if we have an existing customer profile for this person already
	$ch=curl_init($paytrace_url . "/v1/customer/export");

        //set options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type:application/json",
			"Authorization: Bearer " . $token)
			);
        //execute session
        $purchase_json = curl_exec($ch);
        $results = json_decode($purchase_json,true);		
        //close session
        curl_close($ch);
		if(is_array($results)){
			if(count($results['customers'])>0){
				return true;
			}else return false;
		}else return false;	
}
//-------------------------------------------------------------------------------------------------------------Create Recurring Payment
function createRecurringPayment($token,$data,$customer_id){
	global $paytrace_url,$integrator_id;
		//initialize session
		$ch = curl_init($paytrace_url . "/v1/recurrence/create");
		$data['integrator_id'] = $integrator_id;
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
		$purchase_json = curl_exec($ch);
		error_log($purchase_json);
		$results = json_decode($purchase_json,true);		
		//close session
		curl_close($ch);
  
		if($results['success']){
			$payment = array(
				"user_id" => $customer_id,
				"payment_id" => $results['recurrence']['id'],
				"amount" => $data['recurrence']['amount'],
				"status" => "recurring"
			);
			addPayment($payment);
			return true;
		}else{//send back error
			return $results;
		}	
}
//-------------------------------------------------------------------------------------------------------------Create Single Payment
function createSinglePayment($token,$data,$customer_id){
	global $paytrace_url,$integrator_id;
		 //initialize session
        $ch=curl_init($paytrace_url . "/v1/transactions/sale/keyed");
		$data['integrator_id'] = $integrator_id;
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
        $purchase_json = curl_exec($ch);
		error_log($purchase_json);		
        $results = json_decode($purchase_json,true);						
        //close session
        curl_close($ch);   
		//Add Payment to DB
		if($results['success']){//if success is true
			$payment = array(
				"user_id" => $customer_id,
				"payment_id" => $results['transaction_id'],
				"amount" => $data['amount'],
				"status" => "single"
			);
			addPayment($payment);
			return true;
		}else{//send back error
			return $results;
		}	
}
//-------------------------------------------------------------------------------------------------------------Refund Single Payment
function refundSinglePayment($customer_id,$transaction_id,$amount){
	global $paytrace_url,$integrator_id;
		 //initialize session
        $ch = curl_init($paytrace_url . "/v1/transactions/refund/for_transaction");
		$token = getPayTraceToken();
		//Set data
		$data = array(
			"transaction_id" => $transaction_id,
			"amount" => $amount,
			"integrator_id" => $integrator_id
		);
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
        $refund_json = curl_exec($ch);
		error_log($refund_json);		
        $results = json_decode($refund_json,true);		
        //close session
        curl_close($ch);   
		//Add Payment to DB
		if($results['success']=="true"){//if success is true
			$payment = array(
				"user_id" => $customer_id,
				"payment_id" => $results['transaction_id'],
				"amount" => -$data['amount'],
				"status" => "refund"
			);
			addPayment($payment,$transaction_id);
			return true;
		}else{//send back error
			return false;
		}	
}
//-------------------------------------------------------------------------------------------------------------Refund Single Amount (not based on transaction)
function refundSingleAmount($user_id,$customer_id,$amount,$transaction_id){
	global $paytrace_url,$integrator_id;
		 //initialize session
        $ch = curl_init($paytrace_url . "/v1/transactions/refund/to_customer");
		$token = getPayTraceToken();
		//Set data
		$data = array(
			"customer_id" => $customer_id,
			"amount" => $amount,
			"integrator_id" => $integrator_id
		);
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
        $refund_json = curl_exec($ch);
		error_log($refund_json);		
        $results = json_decode($refund_json,true);		
        //close session
        curl_close($ch);   
		//Add Payment to DB
		if($results['success']=="true"){//if success is true
			$payment = array(
				"user_id" => $user_id,
				"payment_id" => $results['transaction_id'],
				"amount" => -$data['amount'],
				"status" => "refund"
			);
			addPayment($payment,$transaction_id);
			return true;
		}else{//send back error
			return false;
		}	
}
//--------------------------------------------------------------------------------------------------------------Add a recurrence of a payment for our records
function addRecurPayment($payment){
	global $db;	
	//First, check it's a not a duplicate
	try
       {		
		$stmt = $db->prepare("SELECT pt_id FROM `payment_recurrences` WHERE `user_id`=:user_id AND `payment_date`=:payment_date LIMIT 1");   		
		$stmt->bindparam(":user_id", $payment['user_id']);						
		$stmt->bindparam(":payment_date", $payment['payment_date']);		
		$stmt->execute();
		$count = $stmt->rowCount();		
		if($count>0){
              $dataRows=$stmt->fetchAll(PDO::FETCH_ASSOC);			
			if($dataRows[0]['pt_id']==$payment['pt_id']){
				return false;
			}
        }else echo "none";		
	}   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    
	try
       {
echo "adding the payment for " . $payment['user_id']; 
		$stmt = $db->prepare("INSERT INTO `payment_recurrences`(`user_id`, `payment_id`, `pt_id`, `amount`, `payment_date`) VALUES (:user_id,:payment_id,:pt_id,:amount,:payment_date)");              
		$stmt->bindparam(":user_id", $payment['user_id']);
		$stmt->bindparam(":payment_id", $payment['payment_id']);
		$stmt->bindparam(":pt_id", $payment['pt_id']);
		$stmt->bindparam(":amount", $payment['amount']);
		$stmt->bindparam(":payment_date", $payment['payment_date']);		
		$stmt->execute();
		return true;		
   }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    
}
//-------------------------------------------------------------------------------------------------------------Get Last good payment to see when to expire account
function getLastRecurringPayment($token,$user_id){
	global $paytrace_url,$integrator_id;
  //Get Last Recurring Payment
	 $data = array(
		"customer_id"=>"PTGV_" . $user_id,
		"integrator_id" => $integrator_id
	);  
	 //initialize session
        $ch=curl_init($paytrace_url . "/v1/recurrence/export_approved");

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
        $results = json_decode($last_payment_json,true);
		return $results;
		//close session
        curl_close($ch);
}
//-------------------------------------------------------------------------------------------------------------Get all payments for a given customer
function getAllRecurringPayments($token,$user_id,$start_date,$end_date){
	global $paytrace_url,$integrator_id;
  //Get Last Recurring Payment
	 $data = array(
		"customer_id"=>"PTGV_" . $user_id,
		"start_date"=>$start_date,
		"end_date"=>$end_date,
		"integrator_id" => $integrator_id
		);  
	 //initialize session
        $ch=curl_init($paytrace_url . "/v1/transactions/export/by_date_range");

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
        $results = json_decode($last_payment_json,true);
		return $results;
		//close session
        curl_close($ch);
}
//-------------------------------------------------------------------------------------------------------------Get list of customers to check today
//																											   These are payments that should have run 4 days ago							
function getRecurTodayPayments(){
	
	global $db;	
	try
       {		   
		//First, find the monthly users that need to pay today	
		//See who is too new to need to be checked
		$one_month_ago = date('Y-m-d H:i:s',strtotime("- 1 month"));	
		$cutoff = date('Y-m-d H:i:s',strtotime("-3 days", strtotime ( $one_month_ago ) ));	
		//Get date 4 days ago to check that a payment has occurred		
		$isLeapYear = date('L',strtotime("-4 days"));		
		$dayofmonth = date('j',strtotime("-4 days"));
		$numofmonth = date('n',strtotime("-4 days"));
		$range = "(" . $dayofmonth . ")";
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
        $stmt = $db->prepare("SELECT p.payment_id,u.user_id,u.status FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE (u.`status` LIKE 'ongoing' OR u.user_id IN (SELECT user_id FROM activity_log WHERE activity = 'Cancelled Recurring Plan' AND details LIKE '%Success%' AND timestamp > DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) AND u.timestamp < :cutoff AND DAYOFMONTH(u.`timestamp`) IN " . $range . " AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'monthly')");
		$stmt->bindparam(":cutoff", $cutoff);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $users_mon = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		//Get Yearly Ongoing recurrences, must match the day exactly
		$stmt = $db->prepare("SELECT p.payment_id,u.user_id,u.status FROM `user_subscription` u LEFT JOIN `user_sub_to_payment` p ON u.id=p.user_subscription_id WHERE (u.`status` LIKE 'ongoing' OR u.user_id IN (SELECT user_id FROM activity_log WHERE activity = 'Cancelled Recurring Plan' AND details LIKE '%Success%' AND timestamp > DATE_SUB(CURDATE(), INTERVAL 1 YEAR)))  AND u.timestamp < :cutoff AND MONTH(u.`timestamp`) = :month AND DAYOFMONTH(u.`timestamp`) IN " . $range . " AND u.`plan_id` IN (SELECT `id` FROM `subscription` WHERE `frequency` = 'yearly')");
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
//-------------------------------------------------------------------------------------------------------------Get list of customers to check today
//																											   These are payments that should have run 4 days ago							
function getNewRecurPayments(){
	
	global $db;	
	try
       {		   
		//Get all new recurring customers from 2 days ago
		$two_days_ago = date('Y-m-d',strtotime("-2 days"));				
		$users = false;		
		//Get new customer from 2 days ago recurrences        
        $stmt = $db->prepare("SELECT payment_id,user_id FROM `payment` WHERE DATE(timestamp)=:two_days_ago AND `status`='recurring'");
		$stmt->bindparam(":two_days_ago", $two_days_ago);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}	
		return $users;		
	  }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    	
}
//-------------------------------------------------------------------------------------------------------------delete Recurring process at PT
function deleteRecurringProcess($token,$recurrence_id,$user_id){
	//-------------------------------------------------------------------------------------------------------------Delete Recurring Process   
 global $paytrace_url,$integrator_id;
	 $data = array(
      "recurrence"=>array('id' => $recurrence_id),
	  "integrator_id" => $integrator_id
     ); 
	 //initialize session
        $ch=curl_init($paytrace_url . "/v1/recurrence/delete");

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
        $cancel_json = curl_exec($ch);
        $results = json_decode($cancel_json,true);
		error_log("Deleting a recurring payment. User ID: " . $user_id);
		error_log($cancel_json);
		if($results['success']==true){
			//TODO Add to Activity Log
			log_activity($user_id,"Cancelled Recurring Plan","Success: " . $recurrence_id . " " . $results['status_message']);
			return true;
		}else{
			//TODO Add to Activity Log					
			log_activity($user_id,"Cancelled Recurring Plan","Failed: " . $recurrence_id . " " . $results['status_message']);
			return false;
		}
        //close session
        curl_close($ch);
}
//-------------------------------------------------------------------------------------------------------------Get the last recurring payment in the DB
function getLastRegularPaymentDB($user_id){
	
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT timestamp FROM `payment` WHERE `status`='recurring' ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $payments[0]['timestamp'];
		}else return false;		
	  }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    	
}
//-------------------------------------------------------------------------------------------------------------Get the last recurring payment in the DB
function getLastRecurringPaymentDB($user_id){
	
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT timestamp FROM `payment_recurrences` ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $payments[0]['timestamp'];
		}else return false;		
	  }   
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }    	
}

//-------------------------------------------------------------------------------------------------------------getErrorMessage
function getErrorMessage($results){	
	
	$error_message['head'] = $results['status_message'];
	$error_message['body'] = "<ul>";
	if(isset($results['errors'])){
		foreach($results['errors'] AS $error){
			$error_message['body'] .= "<li>" . $error[0] . "</li>";
		}
	}else{
		if(isset($results['approval_message'])){
			$error_message['body'] = $results['approval_message'];
		}
	}
	$error_message['body'] .= "</ul>";
	return $error_message;
	
}
?>