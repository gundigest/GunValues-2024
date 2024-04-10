<?php
    include("config.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    if (isset($_POST["code"]))
    {
        $code = $_POST["code"];        
		//Check code is valid
		$codeCheck = checkCode($code,"Password Reset");
		if(!$codeCheck){
			header('Location: ' . $root . "login/?err=The link you used is not valid" );
			exit;
		}else{
			//reset password
			$passwordReset = resetPassword($codeCheck,$_POST['password']);
			if(!$passwordReset){
				header('Location: ' . $root . "login/?err=Your password could not be reset. Please try again." );
				exit;
			}else{	
				//disable code
				disableCode($code);
				//redirect to login
				header('Location: ' . $root . "login" );
			}
		}	
		
		
	    
	}else echo "No code";
?>