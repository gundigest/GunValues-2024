<?php
    include("config.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    if (isset($_POST["email"]))
    {
        $username = $_POST["email"];        
        $user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : 0;        
		$sent = sendPasswordReset($username);
	    if(!$sent){//Error with reset password
			if((isset($_GET['admin']))&&($_GET['admin']==1)){
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&error=1"); /* Redirect browser */
				exit();	
			}else{				
				header('Location: ' . $root . 'forgot/?success=0');
				exit();
			}
		}else{//success!									
			//Redirect
			if((isset($_GET['admin']))&&($_GET['admin']==1)){
				header("Location: " . $root . "admin/user_account/?uid=" . $user_id . "&success=1"); /* Redirect browser */
				exit();	
			}else{	
				header('Location: ' . $root . 'forgot/?success=1');
				exit();
			}
		}
	}
?>