<?php
    include("config.php");
    require_once("user_functions.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
$errors = false;	
if(!empty($_POST)){	
 //-------------------------------------------------------------------------------------------------------------Accept customer details via POST
 if(isset($_POST['plan'])){
	$plan = $_POST['plan'];
	switch($plan){
		case "yearly":
			$plan_id = 2;
			$plan_status = "ongoing";
			$order_plan = "Yearly Access";
			$order_cost = 27.99;			
			$recurring = "yearly";
			$recur_code = "1";
			break;
		case "monthly":
			$plan_id = 3;
			$plan_status = "ongoing";
			$order_plan = "Monthly Access";
			$order_cost = 2.99;
			$recurring = "monthly";
			$recur_code = "3";
			break;
		case "3-day":
			$plan_id = 4;
			$plan_status = "expiring";
			$order_plan = "3-Day Access";
			$order_cost = 4.99;
			$recurring = false;
			$recur_code = 0;
			break;
	}		
}else{ 	
	header("Location: " . $root . "addplan"); /* Redirect browser */
	exit();
}
//Get billing info for this card
$user_data = array();
$user_data['fname'] = $_POST['fname'];
$user_data['lname'] = $_POST['lname'];
$user_data['address'] = $_POST['address'];
$user_data['city'] = $_POST['city'];
if((isset($_POST['state-alt']))&&($_POST['state-alt']!="")){
	$user_data['state'] = $_POST['state-alt'];
}else if((isset($_POST['state']))&&($_POST['state']!="")){
	$user_data['state'] = $_POST['state'];
}
$user_data['zip'] = $_POST['zip'];
$user_data['country'] = $_POST['country'];
if($user_data['country']=="US"){//Strip hyphen and numbers after from US zip codes
	if(strlen($user_data['zip'])>5){
		$user_data['zip'] = substr( $user_data['zip'], 0, 5);
	}	
}
//Update DB
$customer_id = substr($_SESSION['user_id'], strpos($_SESSION['user_id'], "_") + 1);	
updateUser($customer_id,$user_data);

$token = getPayTraceToken();   
 
//-------------------------------------------------------------------------------------------------------------Determine type of Purchase (one-time or subscription) and Process
 if($recurring){//this is an ongoing plan
 	
	$userExists = getPaytraceUser($token,$customer_id);
	error_log("User Exists check for " . $customer_id . " result is " . $userExists);
		
		//Customer info format is the same for creating or updating
			 $data = array(
				  "customer_id"=>"PTGV_" . $customer_id,
				  "credit_card"=>array(
					"encrypted_number"=>$_POST['ccNumber'],
					"expiration_month"=>$_POST['expiration_month'],
					"expiration_year"=>$_POST['expiration_year']
				  ),
				  "encrypted_csc"=>$_POST['ccCSC'],
				  "billing_address"=>array(
					"name"=>$user_data['fname'] . " " . $user_data['lname'],
					"street_address"=>$user_data['address'],
					"city"=>$user_data['city'],					
					"zip"=>$user_data['zip'],
					"country"=>$user_data['country']
					)
				);  
		
		if($userExists){//we have a customer already, update billing info
			//Update Customer
				$userUpdated = updatePaytraceUser($token,$data);
				
				if($userUpdated===true){		
					log_activity($customer_id,"Success Updating Paytrace Information","");									
				}else{
					log_activity($customer_id,"Error Updating Paytrace Information","Error: " . $userUpdated . "; ");
					$errors = true;
				}
		}else{ 
			//Create Customer
			$userCreated = createPaytraceUser($token,$data);
			if($userCreated!=true){//user Creation Failed
				log_activity($customer_id,"Error Creating Paytrace Customer",$userCreated . "; " . $order_plan . " purchased for " . $order_cost . " " . $recurring);
				//Show error to user
				$errors = getErrorMessage($userCreated);				
			}		
		}
		
  //Create Recurring Payment Plan
	 $data = array(
      "customer_id"=>"PTGV_" . $customer_id,
      "recurrence"=>array(
        "amount"=>$order_cost,
        "customer_receipt"=>false,
        "frequency"=>$recur_code,
        "start_date"=>date('m/d/Y'),
        "total_count"=>"999",
        "transaction_type"=>"sale"
      ),
     );  
	$paymentCreated = createRecurringPayment($token,$data,$customer_id);	
	if($paymentCreated===true){
		addUserPlan($customer_id,$plan_id,$plan_status);
		log_activity($customer_id,"Purchased Recurring Plan",$order_plan . " purchased for " . $order_cost . " " . $recurring);
		$email = getUserEmail($customer_id);
		sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$email,$order_plan . " purchased for $" . $order_cost);
	}else{
		log_activity($customer_id,"Error Purchasing Recurring Plan","Error: " . $paymentCreated . "; " . $order_plan . " could not be purchased for " . $order_cost . " " . $recurring);
		$errors = getErrorMessage($paymentCreated);
	}		
	
 }else{//this is a one-time new purchase, so the customer is not saved
	//Simple Charge Code
	 $data = array(
      "amount"=>$order_cost,
      "credit_card"=>array(
        "encrypted_number"=>$_POST['ccNumber'],
        "expiration_month"=>$_POST['expiration_month'],
        "expiration_year"=>$_POST['expiration_year']
      ),
      "encrypted_csc"=>$_POST['ccCSC'],
      "billing_address"=>array(
        "name"=>$user_data['fname'] . " " . $user_data['lname'],
        "street_address"=>$user_data['address'],
        "city"=>$user_data['city'],        
        "zip"=>$user_data['zip'],
		"country"=>$user_data['country']
		)
	);
	$singlePayment = createSinglePayment($token,$data,$customer_id);
	if($singlePayment===true){
		addUserPlan($customer_id,$plan_id,$plan_status);
		log_activity($customer_id,"Purchased 3-Day Plan",$order_plan . " purchased for " . $order_cost);
		$email = getUserEmail($customer_id);
		sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$email,$order_plan . " purchased for " . $order_cost);
		//Add Expiration Date for 3-Day access
		addExpiration($customer_id,'3 days');
	}else{//record error
		log_activity($customer_id,"Error Purchasing 3-Day Plan","Error: " . $singlePayment . "; " . $order_plan . " could not be purchased for " . $order_cost);
		$errors = getErrorMessage($singlePayment);
	}
 }
//TODO: Check for errors and display on page 
//if we are here, there have been no purchase errors 
	header("Location: " . $root . "account/?success=1"); /* Redirect browser */
	exit();	
		
}else{	
	header("Location: " . $root . "addplan"); /* Redirect browser */
	exit();		
}
?>