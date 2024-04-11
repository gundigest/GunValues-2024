<?php
    include("config.php");
    require_once("user_functions.php");
//--------------------------------------------------------------------------------Email Functions
global $_mailgun_api_url, $_maigun_api_key;

function sendPurchaseThankYou($customer_id,$first_name,$last_name,$email,$details){ 
  global $root; 
  $recipient=addslashes($first_name . " " . $last_name . ' <' . $email . '>');  
  if(!$recipient){    
	log_activity($customer_id,"Error sending Purchase Email","Recipient could not be found");
    return false;
  }else{    
    $template=file_get_contents("model/email_templates/purchase.html");
    $template=str_replace("{{first_name}}",$first_name,$template);    
    $template=str_replace("{{details}}",$details,$template);
    sendEmail($recipient,$first_name . ", Thank You for Your Purchase",$template);    
	log_activity($customer_id,"Purchase Email Sent","Content: " . $template);
    return true;
  }
}
function sendAccountCreatedEmail($customer_id,$first_name,$last_name,$email,$details){
  global $root,$file_root;
  $code = getCode($email,"Password Reset");
  $recipient = $email;  
  if(!$code){
    log_activity(0,"Error sending Account Created Email","Recipient could not be found");
    return false; 
  }else if(!$recipient){    
	log_activity(0,"Error sending Account Created Email","Recipient could not be found");
    return false;
  }else{
	$user_id = $customer_id;	  
    $template=file_get_contents($file_root . "model/email_templates/granted.html");
    $template=str_replace("{{first_name}}",$first_name,$template);    
    $template=str_replace("{{details}}",$details,$template);	
    $template=str_replace("{{url}}",$root . "reset_password/?code=" . $code,$template);     
    sendEmail($recipient,"Welcome to Gun Values",$template);    
	log_activity($user_id,"Account Created Email Sent","Code: " . $code);
    return true;
  }
}
function sendCancellationEmail($user_id){
  $user = getUser($user_id);
  $recipient=addslashes($user['fname'] . " " . $user['lname'] . ' <' . $user['email'] . '>');  
  if(!$recipient){    
	log_activity($customer_id,"Error sending Cancellation Email","Recipient could not be found");
    return false;
  }else{    
    $template=file_get_contents("email_templates/cancellation.html");
    $template=str_replace("{{first_name}}",$user['fname'],$template);        
    sendEmail($recipient,$user['fname'] . ", Your Account has Been Cancelled",$template);    
	log_activity($user_id,"Cancellation Email Sent","Content: " . $template);
    return true;
  }
}
function sendFirstPaymentFailedEmail($user_id){
  global $file_root;
  $user = getUser($user_id);
  $recipient = addslashes($user['fname'] . " " . $user['lname'] . ' <' . $user['email'] . '>');  
  if(!$recipient){    
	log_activity($customer_id,"Error sending First Payment Failed Email","Recipient could not be found");
    return false;
  }else{    
    $template=file_get_contents($file_root . "model/email_templates/first_pay_failed_cancel.html");
    $template=str_replace("{{first_name}}",$user['fname'],$template);        
    sendEmail($recipient,$user['fname'] . ", Your Payment Method has been Declined",$template);    
	log_activity($user_id,"First Payment Failed Email Sent","Content: " . $template);
    return true;
  }
}
function sendPasswordReset($username){
  global $root;
  $code = getCode($username,"Password Reset");
  $recipient = $username;  
  if(!$code){
    log_activity(0,"Error sending Password Reset Email","Recipient could not be found");
    return false; 
  }else if(!$recipient){    
	log_activity(0,"Error sending Password Reset Email","Recipient could not be found");
    return false;
  }else{
	$user_id = getUserId($username);	  
    $template=file_get_contents("email_templates/password_reset.html");
    $template=str_replace("{{username}}",$username,$template);  	
    $template=str_replace("{{url}}",$root . "reset_password/?code=" . $code,$template);     
    sendEmail($recipient,"Reset Your Password",$template);    
	log_activity($user_id,"Password Reset Email Sent","Code: " . $code);
    return true;
  }
}
function sendContactForm($email,$name,$message){    
  //$recipient = "woody@gundigest.com";    
  $recipient = "woody@gundigest.com,jamie@gundigest.com";    
	$content = "From: " . $name . ", " . $email . ": " . $message;
    sendEmail($recipient,"A Contact Form has been sent from Gun Values",$content);    
	log_activity(0,"Contact Form Email Sent","Content: " . $content);
    return true;  
}
function sendEmail($to,$subject,$html,$from=null,$attachment=null){
	global $_mailgun_api_url, $_maigun_api_key;
  
  if(is_null($from)){
    $params['from']=addslashes('Gun Values by Gun Digest <sales@gundigestmedia.com>');
  }else $params['from']=$from;    
	$params['to']=$to;	
	//$params['cc']="";	
	$params['subject'] = $subject;	
	$params['html'] = $html;  
	$params['h:Reply-To'] = "<gunvalues@gundigest.com>";  
  $ch = curl_init($_mailgun_api_url . 'messages');  
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS,  $params);
  curl_setopt($ch, CURLOPT_HEADER,false);    
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_VERBOSE,0);
  curl_setopt($ch, CURLOPT_HEADER,1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
  curl_setopt($ch, CURLOPT_USERPWD,"api:" . $_maigun_api_key);
  $response=curl_exec($ch);    
}

?>