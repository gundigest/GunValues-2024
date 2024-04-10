<?php
    include("config.php");
    require_once("user_functions.php");        
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
if(!empty($_POST)){
	$result = checkEmailUnique($_POST['email']);
	$browser=$_SERVER['HTTP_USER_AGENT'];
	if($result){
		echo "true";	
		error_log("Result for " .$_POST['email']. " is true/unique " . $browser);
	}else{
		echo "false";
		error_log("Result for " .$_POST['email']. " is false/dupe " . $browser);
	}
	
}