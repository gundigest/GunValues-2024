<?php
include("../model/payment_functions.php");
if(isset($_GET['uid'])){
	$user_id = $_GET['uid'];
	$user = getUser($user_id);
	$payments = getAllPayments($user_id);
	$refunds = getAllRefunds($user_id);
	$plan_active = checkPlanActiveById($user_id);
}else{
	header("Location: " . $root . "admin/?err=No User ID Selected"); /* Redirect browser */
	exit();
}
$banner = "";
if(isset($_GET['error'])){
	$banner = "<div class='error'><strong>There has been an error with your last activity.</strong></div>";	
}else if(isset($_GET['success'])){
	$banner = "<div class='announcement'><strong>Success</strong></div>";
}
$page_name = "{$user['fname']} {$user['lname']}'s Account";
//Admin User Account Page Content
	
	$plan = getPlan($user_id);	
	
$html .= <<<EOD
		{$banner}
		<h2>Account Details</h2>		
		<div class="one-third-page">
			<h5>Billing Details</h5>
			<p>{$user['fname']} {$user['lname']}<br/>{$user['address1']}<br/>{$user['city']}, {$user['state']} {$user['zipcode']}
EOD;
		if($user['country']!="US"){
			$html .= "<br/>" . $user['country'];
		}
	$html .= "</p>";
$html .= <<<EOD
		<h5>Login Details</h5>
		<p>{$user['email']}</p>
		<a class="button" id="edit_email">Edit Email Address</a>
		<form action="{$root}model/forgotten_process.php?admin=1" method="POST"><input type="hidden" value="{$user_id}" name="user_id"/><input type="hidden" value="{$user['email']}" name="email"/><button type="submit" class="button center full">Send Password Reset Email</button></form>
		<a class="button" id="reset_password">Reset Password Manually</a>		
EOD;
	$html .= "<h5>Plan Details</h5>";

	if(!$plan){
		$html .= "<p>No Current Plan</p>";
	}else{		
		$html .= "<p>" . $plan['name'] . " Plan, <sup>$</sup>" . $plan['amount'] . ", charged " . $plan['frequency'] . "</p>";
		if($plan['status']=="cancelled"){
			$html .= "This Plan has been Cancelled.";
		}else if($plan['status']=="expiring"){
			$expiration = getExpiration($user_id);
			if($plan_active){
				$html .= "Cancelled: Access Expires ";
			}else $html .= "Cancelled: Access Expired ";	
				
			$html .= date("M d, Y h:i A",strtotime($expiration));
		}		
		$html .= '<p>Last Update: ';
		if($plan['updated'])
			$html .= date("M d, Y h:i A",strtotime($plan['updated']));
		elseif($plan['timestamp'])
			$html .= date("M d, Y h:i A",strtotime($plan['timestamp']));
		else
			$html .= 'N/A';
		$html .= '</p>';
			
	}	
	$html .= "</div><div class='two-third-page'>";
		if(($plan) && ($plan['status']!="expiring")&& ($plan['status']!="cancelled")){
			$html .= '<h5>Change Plan</h5>';
			$html .= '<a class="button" href="' . $root . 'admin/update_payment/?uid=' . $user_id . '">Update ' . $user['fname'] . '\'s Payment Information</a>';
			$html .= '<a class="button" id="cancel">Cancel Account</a>';
		}
		//Payment history
		if($payments){
			$html .= '<h5>Payments</h5>';
$html .= <<<EOD
		<table class="admin">
			<thead>
			<td></td>
			<td>Payments ID</td>	
			<td>Date</td>	
			<td>Status</td>
			<td class='currency'>Amount</td>
			<td></td>
			<td>Refund For</td>
			</thead>
EOD;
		if($payments){			
			$tally = 0;
			foreach($payments AS $pay){
				$refund_button = true;												
				if(in_array(strval($pay['payment_id']),$refunds)){
					//No refund button, this one has already been refunded
					$refund_button = false;
				}
				
				$tally += $pay['amount'];
				$html .= "<tr>";
				$html .= "<td>";
					if(strlen($pay['payment_id'])>5)
						$html .= "<a class='small' href='/sandbox/manual_cancel_process.php?user_id=".$user_id."&recur_id=".$pay['payment_id']."'>(Cancel PT)</a>";
				$html .= "</td>";
				$html .= "<td>" . $pay['payment_id']. "</td>";
				$html .= "<td>" . $pay['timestamp']. "</td>";
				$html .= "<td>" . $pay['status'] . "</td>";			
				$html .= "<td class='currency'>" . $pay['amount'] . "</td>";
				if($refund_button){
					if($pay['status'] == "single"){					
						$html .= '<td><form action="' . $root . 'model/refund_process.php" method="POST"><input type="hidden" value="' . $user_id . '" name="user_id"/><input type="hidden" value="' . $pay["payment_id"] . '" name="transaction_id"/><input type="hidden" value="' . $pay["amount"] . '" name="amount"/><button type="submit" class="refund button center full"  onclick="this.value=\'Submitting ..\';this.disabled=\'disabled\'; this.form.submit();">Refund Transaction</button></form></td>';	
					}elseif(($pay['status'] == "recurring")||($pay['status'] == "expiring")){					
						$html .= '<td><form action="' . $root . 'model/refund_process.php" method="POST"><input type="hidden" value="' . $user_id . '" name="user_id"/><input type="hidden" value="' . $pay["payment_id"] . '" name="GV_transaction_id"/><input type="hidden" value="' . $pay["amount"] . '" name="amount"/><button type="submit" class="refund button center full" onclick="this.value=\'Submitting ..\';this.disabled=\'disabled\'; this.form.submit();">Refund Amount</button></form></td>';	
					}else $html .= '<td></td>';
				}else $html .= '<td>Refunded</td>';
				$html .= "<td>" . $pay['refund_for'] . "</td>";
				$html .= "</tr>";		
			}
			$html .= "<tr><td colspan='3' class='currency'><strong>Total:</strong></td><td class='currency'><strong>$" . $tally . "</strong></td><td colspan='2'></td></tr>";
		}
$html .= "</table>";
		}
		$html .= '</div>';	
$html .= <<<EOD
		<div class="modal-bg"></div>
		<div class="cancel_modal">
			<div class="close">&times;</div>
			<h5>Are You Sure you Want to Cancel?</h5>
			<p>Cancellation will be processed immediately, and no further charges will be placed on the credit card on file. No refund will be issued automatically for previous charges. </p>
			<p>The user's plan will remain active for the remaining time already paid, either through the end of the month or year. The account will reflect this with an "Access Expires" date.</p>
			<form action="{$root}model/cancel_process.php?admin=1" method="POST"><input type="hidden" value="{$user_id}" name="user_id"/><button type="submit" class="button center full">Yes, Cancel {$user['fname']}'s Account Immediately</button></form>
		</div>
		<div class="edit_email_modal">
			<div class="close">&times;</div>
			<h5>Edit Email Address for {$user['fname']}</h5>
			<p><strong>WARNING: </strong>This will change the username for this user.</p>
			<form action="{$root}model/edit_email.php?admin=1" method="POST"><input type="hidden" value="{$user_id}" name="user_id"/><input class="full" type="email" value="{$user['email']}" name="email"/><button type="submit" class="button center full">Save Email Changes</button></form>
		</div>
		<div class="edit_password_modal">
			<div class="close">&times;</div>
			<h5>Manually Edit Password for {$user['fname']}</h5>
			<p><strong>WARNING: </strong>This will change the password for this user. Be sure to have this password in a safe place to send to the user before submitting!</p>
			<form action="{$root}model/save_password.php" method="POST"><input type="hidden" value="{$user_id}" name="user_id"/><input class="full" type="text" value="" name="password"/><button type="submit" class="button center full">Save Password Changes</button></form>
		</div>
EOD;
//Javascript for opening modals
$head = <<<EOD
<script type="text/javascript">
$(document).ready(function() {
	$("#cancel").click(function() {
		$(".modal-bg").fadeIn();
		$(".cancel_modal").fadeIn();
	});
	$("#edit_email").click(function() {
		$(".modal-bg").fadeIn();
		$(".edit_email_modal").fadeIn();
	});
	$("#reset_password").click(function() {
		$(".modal-bg").fadeIn();
		$(".edit_password_modal").fadeIn();
	});
	$(".close").click(function() {
		parent = $(this).parent('div');
		$(".modal-bg").fadeOut();
		parent.fadeOut();
	});
});
</script>
EOD;
?>