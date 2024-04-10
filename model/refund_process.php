<?php
    include("config.php");
	include("payment_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){
    if (isset($_POST["transaction_id"]))
    {
			$transaction_id = $_POST['transaction_id'];
			$user_id = $_POST['user_id'];
			$amount = $_POST['amount'];
			//check that this transaction has not been refunded before
			$refunds = getAllRefunds($user_id);
			if(in_array(strval($transaction_id),$refunds,true)){
				//Already refunded
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
				exit;
			}
			//refund transaction
			$refund = refundSinglePayment($user_id,$transaction_id,$amount);			
			if(!($refund==true)){
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
				exit;
			}else{	
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1"); /* Redirect browser */
				exit;
			}
	}else{//This is a recurring payment so we have to issue a refund to the customer, not the transaction
			$transaction_id = $_POST['GV_transaction_id'];
			$customer_id = "PTGV_" . $_POST['user_id'];
			$user_id = $_POST['user_id'];
			$amount = $_POST['amount'];
			//refund transaction
			$refund = refundSingleAmount($user_id,$customer_id,$amount,$transaction_id);			
			if(!($refund==true)){
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
				exit;
			}else{	
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1"); /* Redirect browser */
				exit;
			}
	}	
}else header("Location: " . $root . "admin/?error=1"); /* Redirect browser */
?>