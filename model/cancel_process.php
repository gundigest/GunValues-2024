<?php
    include("config.php");
    require_once("payment_functions.php");
    require_once("user_functions.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){

$user_id = $_POST['user_id'];
//Get payment schedule and calculate expiring date
	$plan = getPlan($user_id);
	$expire_length = '1 month';
	if($plan['frequency']=='yearly'){
		$expire_length = '1 year';
	}else if($plan['frequency']=='monthly'){
		$expire_length = '1 month';		
	}
//Get recurring ID
	$payment_id = getPaymentID($user_id);	
	//MANUALLY Get last good payment date from both sources and compare to get last good date
	$last_recur = getLastRecurringPaymentDB($user_id);
	$last_reg = getLastRegularPaymentDB($user_id);	
	if($last_reg == false){//no payment has ever been made
		header("Location: " . $root . "cancel/?err=Your Account could not be cancelled at this time. Please contact Customer Service."); /* Redirect browser */
		exit;
	}elseif($last_recur == false){//No recurrences have happened yet, so use Regular Payment date
		$expiring_date = $last_reg;
	}else{//Compare dates for the latest one
		if($last_recur > $last_reg){
			$expiring_date = $last_recur;
		}else $expiring_date = $last_reg;
	}
	$expiring_date = date("Y-m-d H:i:s",strtotime($expiring_date . " + " . $expire_length));
	
	$token = getPayTraceToken();
	$deletion = deleteRecurringProcess($token,$payment_id,$user_id);
	if($deletion == false){
		header("Location: " . $root . "cancel/?err=Your Account could not be cancelled at this time. Please contact Customer Service.");
		exit;
	}else{		
		
		addExpiration($user_id,$expire_length,"",$expiring_date);
		updatePlanStatus($user_id,"expiring");
	}	       
 
	sendCancellationEmail($user_id);
	if((isset($_GET['admin']))&&($_GET['admin']==1)){
		header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1");
		exit();	
	}else{
		header("Location: " . $root . "cancel/?success=1");
		exit();	
	}
}else{	
	header("Location: " . $root . "cancel");
	exit();		
}
?>