<?php
    include("config.php");
    require_once("user_functions.php");
    require_once("model/payment_functions.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){
//-------------------------------------------------------------------------------------------------------------Accept customer details via POST
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

//-------------------------------------------------------------------------------------------------------------Update User Billing Info 
		//Customer info 
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

			//Update Customer
				$userUpdated = updatePaytraceUser($token,$data);
				
				if($userUpdated===true){		
					log_activity($customer_id,"Success Updating Paytrace Information","");				
					header("Location: " . $root . "account/?success=1"); /* Redirect browser */
					exit();	
				}else{
					log_activity($customer_id,"Error Updating Paytrace Information","Error: " . $userUpdated . "; ");
					header("Location: " . $root . "account/?success=0"); // Redirect browser
					exit();
				}
		
}else{	
	header("Location: " . $root . "updatepayment"); /* Redirect browser */
	exit();		
}
?>