<?php
$page_name = "Your Account";
//Account Page Content
$loggedIn = checkLoggedIn();
if($loggedIn){
	$user_id = substr($_SESSION['user_id'], strpos($_SESSION['user_id'], "_") + 1);	
	if(isset($_GET['success'])){
	if($_GET['success']==1){//success!
			$banner = '<div class="announcement">Thank you, your account has been successfully updated.</div>';
			updateLoggedInUser($user_id);
		}else{//error :(
			$banner = '<div class="error">There has been an error with your purchase. Please <a href="' . $root . 'contact">Contact Us</a> to resolve the issue.</div>';
		}
	}else if(isset($_GET['err'])){
		$banner = '<div class="error">' .$_GET['err']. '</div>';
	}else $banner = "";
	$user = getUser($user_id);
	$plan = getPlan($user_id);	
	
$html .= <<<EOD
		{$banner}
		<h2>Your Account Details</h2>		
		<div class="one-third-page">
			<h5>Billing Details</h5>
			<p>{$user['fname']} {$user['lname']}<br/>{$user['address1']}<br/>{$user['city']}, {$user['state']} {$user['zipcode']}
EOD;
		if($user['country']!="US"){
			$html .= "<br/>" . $user['country'];
		}
	$html .= "</p><h5>Plan Details</h5>";

	if(!$plan){
		$html .= "<p>No Current Plan</p>";
	}else{		
		$html .= "<p>" . $plan['name'] . " Plan, <sup>$</sup>" . $plan['amount'] . ", charged " . $plan['frequency'] . "</p>";
		if($plan['status']=="cancelled"){
			$html .= "This Plan has been Cancelled.";
		}else if($plan['status']=="expiring"){
			$expiration = getExpiration($user_id);
			if($plan_active){
				$html .= "Access Expires ";
			}else $html .= "Access Expired ";	
				
			$html .= date("M d, Y h:i A",strtotime($expiration));
		}		
	}
	$html .= <<<EOD
			<h5>Email Preferences</h5>			
			<p>{$user['email']}<br/><a href="https://gundigest.com/email-preferences" target="_blank">Change your email settings</a></p>
EOD;
	$html .= "</div><div class='one-third-page'>";
		if(($plan) && ($plan['status']!="expiring")&& ($plan['status']!="cancelled")){
			$html .= '<h5>Change Plan</h5>';
			$html .= '<a class="button" href="' . $root . 'updatepayment">Update Payment Information</a>';
			$html .= '<a class="button" href="' . $root . 'cancel">Cancel Account</a>';
		}else{
			$html .= '<h5>Add Plan</h5>';
			$html .= '<a class="button" href="' . $root . 'addplan">Select Plan</a>';
		}
		$html .= '</div>';	
}else {
	header("Location: " . $root . "login/?err=You must be logged in to view Account Details"); /* Redirect browser */
	exit();
}
?>



