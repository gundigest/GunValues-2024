<?php
	require_once("../model/user_functions.php");    
    require_once("../model/mail_functions.php");
//API Acceptance for store purchases
if((isset($_GET))&&(isset($_GET['UXUPGSTNxV4JXbxaaCCSs3']))){
	if(!(empty($_POST))){
//API requires the following fields
	$user_data['email'] = $_POST['email'];	
	$user_data['fname'] = $_POST['fname'];
	$user_data['lname'] = $_POST['lname'];	
	$user_data['address'] = $_POST['address'];
	$user_data['city'] = $_POST['city'];
	$user_data['state'] = $_POST['state'];
	$user_data['zip'] = $_POST['zip'];
	$user_data['country'] = $_POST['country']; 		
	//Add placeholder password: 
	$user_data['password'] = "placeholder";
	$amount = $_POST['amount'];
	$purchase_id = $_POST['purchase_id'];
		$customer_id = getUserId($user_data['email']);
		if($customer_id===false){
			$customer_id = addUser($user_data);
		}else{
			if(!checkPlanActiveById($customer_id,true)){
				updateUser($customer_id,$user_data);
			}else{//user already has an active plan
				$return = array(
					"status" => "Error",
					"Message" => "User currently has an active, recurring plan. A plan can not be added when one already exists."
				);
				echo json_encode($return);
				exit;
			}
		}
		//Add Empty Payment
		$payment = array(
					"user_id" => $customer_id,
					"payment_id" => "GDS_" . $purchase_id,
					"amount" => $amount,
					"status" => "single"
				);
				addPayment($payment);
		//Add plan
		$plan_id = 7;
		$plan_status = "expiring";
		$expiration = "1 year";
		$order_plan = "One Year Access";
		addUserPlan($customer_id,$plan_id,$plan_status);
		log_activity($customer_id,"Purchased 1 Year Plan",$order_plan . " purchased for " . $amount);
		sendAccountCreatedEmail($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " purchased.");	
		//Add Expiration Date for access
		addExpiration($customer_id,$expiration);					
		 
		$return = array(
			"status" => "Success",
			"Message" => "User created, access purchased."
		);
		
	}else{
		$return = array(
			"status" => "Error",
			"Message" => "No Data Posted"
		);
	}
}else{
	$return = array(
		"status" => "Error",
		"Message" => "Access Not Authorized"
	);
}
echo json_encode($return);
exit;
?>