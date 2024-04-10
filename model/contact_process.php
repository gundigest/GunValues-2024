<?php
    include("config.php");
    require_once("mail_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    if (isset($_POST["email"]))
    {               
		if (strpos($_POST["message"], 'http') !== false) {//This is a bot
			$sent = true;
			$content = "From: " . $_POST['name'] . ", " . $_POST["email"] . ": " . $_POST["message"];
			log_activity(0,"Contact Form Email NOT Sent","Content: " . $content);
		}else $sent = sendContactForm($_POST["email"],$_POST["name"],$_POST["message"]);
	    if(!$sent){//Error with send
			header('Location: ' . $root . 'contact/?success=0');
		}else{//success!									
			//Redirect
			header('Location: ' . $root . 'contact/?success=1');
		}
	}
?>