<?php
    include("../model/config.php");
    require_once("../model/user_functions.php");
	include("../includes/functions.php");
	setDefines();
    //get keys
    if(__DEBUG__){
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}

//Get recurring ID
	$user = array();	
	
	if(isset($_REQUEST['user_id'])&&isset($_REQUEST['recur_id'])){
		$return = $_SERVER['HTTP_REFERER'];
		$user[] = array('user_id' => (int)$_REQUEST['user_id'],'recurrence_id' =>(int)$_REQUEST['recur_id'] );
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
		if(!isset($exchange_token['access_token'])){
			 header("Location: $return");
			 die();
	 	}
        $token = $exchange_token['access_token'];
		
        //close session
        curl_close($ch);

//-------------------------------------------------------------------------------------------------------------Delete Recurring Process

 foreach($user AS $u){

	 $data = array(
      "recurrence"=>array('id' => $u['recurrence_id'])
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
			//TODO Add to Activity Log
			log_activity($u['user_id'],"Cancelled Recurring Plan","Success: " . $u['recurrence_id'] . " " . $results['status_message']);
		}else{
			//TODO Add to Activity Log
			log_activity($u['user_id'],"Cancelled Recurring Plan","Failed: " . $u['recurrence_id'] . " " . $results['status_message']);
		}
        //close session
        curl_close($ch);

	//Use this code if you also want to cancel the account within GunValues. Usually this has already occurred.
	//$date_value = date("Y-m-d H:i:s",strtotime("2019-02-19"));//specific day
	/*$date_value = date("Y-m-d H:i:s");//today, now
	addExpiration($u['user_id'],"1 day",$date_value,$date_value);
	updatePlanStatus($u['user_id'],"cancelled");*/
 }

 if($return){
	 header("Location: $return");
	 die();
 }
?>
