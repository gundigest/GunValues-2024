<?php
$page_name = "Your Account";
//Account Page Content
$loggedIn = checkLoggedIn();
if($loggedIn){
	$user_id = substr($_SESSION['user_id'], strpos($_SESSION['user_id'], "_") + 1);	
	$user = getUser($user_id);
	$plan = getPlan($user_id);
if(isset($_GET['success'])){
	if($_GET['success']==1){//success!
		$banner = '<div class="announcement">Your plan has been successfully cancelled. Please note your plan\'s expiration data below.</div>';
	}else{//error :(
		$banner = '<div class="error">There has been an error with your purchase. Please <a href="">Contact Us</a> to resolve the issue.</div>';
	}
}else if(isset($_GET['err'])){
	$banner = '<div class="error">' .$_GET['err']. '</div>';
}else $banner = '<div class="error">Warning: Cancellation Cannot be Undone</div>';
$html = $banner;
$html .= <<<EOD
		<h2 id="mfg_title">Cancel Your Account</h2>		
		<div class="one-third-page">
			<h5>User Details</h5>
			<p>{$user['fname']} {$user['lname']}<br/>{$user['email']}</p>
			<h5>Plan Details</h5>
			<p>{$plan['name']} Plan, <sup>$</sup>{$plan['amount']}, charged {$plan['frequency']}</p>
EOD;
	if($plan['status']=="expiring"){
		$expiration = getExpiration($user_id);		
	$html .= "Access Expires " . date("M d, Y h:i A",strtotime($expiration));
	}
	$html .= "</div><div class='one-third-page'>";
		if($plan['status']!="expiring"){
			$html .= '<h5>Are You Sure you Want to Cancel?</h5>';
			$html .= '<p>Cancellation will be processed immediately, and no further charges will be placed on your credit card. No refund will be issued for previous charges. </p>';
			$html .= '<p>Your plan will remain active for the remaining time for which you already paid, either through the end of the month or year. Your account will reflect this with an "Access Expires" date.</p>';
			$html .= '<form action="' . $root . 'model/cancel_process.php" method="POST"><input type="hidden" value="' . $user_id . '" name="user_id"/><button type="submit" class="button center">Yes, Cancel My Account Immediately</button></form>';
		}else{
			$html .= '<h5>No Recurring Plan Exists for Your Account</h5>';
		}		
}else {
	header("Location: " . $root . "login/?err=You must be logged in to view Cancellation Details"); /* Redirect browser */
	exit();
}
?>



