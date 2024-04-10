<?php
    include("config.php");
    require_once("user_functions.php");
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){

	$user_id = $_POST['user_id'];
	$email = $_POST['email'];
	if(checkEmailUnique($email)){	
		updateUserEmail($user_id,$email);
	}else{
		if((isset($_GET['admin']))&&($_GET['admin']==1)){
			header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
			exit();	
		}else{
			header("Location: " . $root . "cancel/?error=1"); /* Redirect browser */
			exit();	
		}
	} 
	if((isset($_GET['admin']))&&($_GET['admin']==1)){
		header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1"); /* Redirect browser */
		exit();	
	}else{
		header("Location: " . $root . "cancel/?success=1"); /* Redirect browser */
		exit();	
	}
}else{	
	header("Location: " . $root . "cancel"); /* Redirect browser */
	exit();		
}
?>