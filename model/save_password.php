<?php
    include("config.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    if (isset($_POST["password"]))
    {
			$user_id = $_POST['user_id'];
			//reset password
			$passwordReset = resetPassword($user_id,$_POST['password']);
			if(!$passwordReset){
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
				exit;
			}else{	
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1"); /* Redirect browser */
				exit;
			}
	}	

?>