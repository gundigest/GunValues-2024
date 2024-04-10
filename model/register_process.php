<?php
    include("config.php");
    require_once("user_functions.php");
    require_once("payment_functions.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){	
error_log(json_encode($_POST));
 //-------------------------------------------------------------------------------------------------------------Accept customer details via POST
 if(isset($_POST['plan'])){//PLAN will probably need to be pulled from the DB in the future
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
	header("Location: " . $root . "register"); /* Redirect browser */
	exit();
}
$user_data = array();
$user_data['email'] = $_POST['username'];
$user_data['password'] = $_POST['pwd'];
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
//Add to DB
//TODO: if user exists, update instead of adding
$customer_id = addUser($user_data);

$token = getPayTraceToken();		
 
//-------------------------------------------------------------------------------------------------------------Determine type of Purchase (one-time or subscription) and Process
 if($recurring){//this is an ongoing plan
	 //Create Customer	
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
        "state"=>$user_data['state'],
        "zip"=>$user_data['zip'],
		"country"=>$user_data['country']
		)
	);  
	
	$userCreated = createPaytraceUser($token,$data,$customer_id);
		if($userCreated){
			
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
				sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " purchased for $" . $order_cost);
				$_SESSION['track_conversion'] = <<<EOD
			<script>
				window.dataLayer = window.dataLayer || []
				dataLayer.push({
				'transactionId': '{$customer_id}',			   
				   'transactionTotal': {$order_cost},			   
				   'transactionProducts': [{
					   'sku': '{$plan_id}',
					   'name': '{$order_plan}',				   
					   'price': {$order_cost},
					   'quantity': 1
				   }]
				});
				</script>
EOD;
				error_log("here");
				error_log($_SESSION['track_conversion']);

			}else{
				log_activity($customer_id,"Error Purchasing Recurring Plan","Error: " . $paymentCreated . "; " . $order_plan . " could not be purchased for " . $order_cost . " " . $recurring);
				header("Location: " . $root . "login/?success=0"); /* Redirect browser */
				exit();
			}
		}else{//user Creation Failed
			log_activity($customer_id,"Error Creating Paytrace Customer",$userCreated . "; " . $order_plan . " purchased for " . $order_cost . " " . $recurring);
			header("Location: " . $root . "login/?success=0"); /* Redirect browser */
			exit();
		}		
 }else{//this is a one-time purchase
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
        "state"=>$user_data['state'],
        "zip"=>$user_data['zip'],
		"country"=>$user_data['country']
		)
	);
	$singlePayment = createSinglePayment($token,$data,$customer_id);
	if($singlePayment===true){
		addUserPlan($customer_id,$plan_id,$plan_status);
		log_activity($customer_id,"Purchased 3-Day Plan",$order_plan . " purchased for " . $order_cost);
		sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " purchased for " . $order_cost);
		//Add Expiration Date for 3-Day access
		addExpiration($customer_id,'3 days');
		$_SESSION['track_conversion'] = <<<EOD
		<script>
			window.dataLayer = window.dataLayer || []
			dataLayer.push({
			'transactionId': '{$customer_id}',			   
			   'transactionTotal': {$order_cost},			   
			   'transactionProducts': [{
				   'sku': '{$plan_id}',
				   'name': '{$order_plan}',				   
				   'price': {$order_cost},
				   'quantity': 1
			   }]
			});
			</script>
EOD;
	error_log("here");
	error_log($_SESSION['track_conversion']);
	}else{//record error
		log_activity($customer_id,"Error Purchasing 3-Day Plan","Error: " . $singlePayment . "; " . $order_plan . " could not be purchased for " . $order_cost);
		header("Location: " . $root . "login/?success=0"); /* Redirect browser */
		exit();	
	}

 }	
 //Check for Email Subscription
 if(isset($_POST['email_sub'])){
	 $email_results = newsletterSubscribe($user_data['email'],$user_data['fname'],$user_data['lname']);
	 if($email_results){
		log_activity($customer_id,"Newsletter Subscription","Successful for " . $user_data['email']);
	 }else log_activity($customer_id,"Newsletter Subscription","Failed for " . $user_data['email']);
 }
	
	//header("Location: " . $root . "login/?success=1"); /* Redirect browser */
	exit();	
		
}else{	
	header("Location: " . $root . "register"); /* Redirect browser */
	exit();		
}
?>