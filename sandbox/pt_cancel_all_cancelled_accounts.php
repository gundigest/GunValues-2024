<?php
//The purpose of this script is to find all cancelled accounts and force cancel them at Paytrace
//This script was created and run on April 17, 2023
    include("../model/config.php");
    require_once("../model/user_functions.php");  
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	
//Get recurring IDs for all cancelled accounts
$previous_id = $_SESSION['last_id'];
$_SESSION['last_id'] += 1000;
global $db;
	try
       {
		$stmt = $db->prepare("SELECT distinct(user_id) FROM `user_subscription` WHERE `plan_id` IN (2,3) AND `status` LIKE 'cancelled' AND user_id > " . $previous_id . " AND user_id <= " . $_SESSION['last_id'] . " AND user_id NOT IN (SELECT user_id FROM user_subscription WHERE status = 'ongoing')");		
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC);		  
	   }else $userRows = false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }


//-------------------------------------------------------------------------------------------------------------Authenticate with Paytrace
global $payment_un;
global $payment_pw;
  //Authorization Code
	 $data = array(
		"grant_type" => "password",
		"username" => $payment_un,
		"password" => $payment_pw
		);
	 //initialize session
        $ch=curl_init("https://api.paytrace.com/oauth/token");

        //set options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:'));
        //execute session
        $token_json = curl_exec($ch);
        $exchange_token = json_decode($token_json,true);
		$token = $exchange_token['access_token'];		
        //close session
        curl_close($ch);        

//-------------------------------------------------------------------------------------------------------------Delete Recurring Process   
 
 foreach($userRows AS $u){
 
	 $data = array(
      "recurrence" => array('id' => getPaymentID($u['user_id']))
     ); 
	 //initialize session
        $ch=curl_init("https://api.paytrace.com/v1/recurrence/delete");

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
		if($results['success']==true){			
			log_activity($u['user_id'],"Cancelled Recurring Plan","Success: " . $data['recurrence']['id'] . " " . $results['status_message']);
			echo "Success, cancelled " . $u['user_id'] . "'s Recurring plan " . $data['recurrence']['id'] . "<br/>";
		}else{					
			log_activity($u['user_id'],"Cancelled Recurring Plan","Failed: " . $data['recurrence']['id'] . " " . $results['status_message']);
			echo "Failure, cound not cancel " . $u['user_id'] . "'s Recurring plan " . $data['recurrence']['id'] . "<br/>";
		}
        //close session
        curl_close($ch);	
 }

?>