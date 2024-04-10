<?php
    include("config.php");
    require_once("user_functions.php");
    //get keys
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    if (isset($_POST["username"]) && isset($_POST["pwd"]))
    {        
        $username = $_POST["username"];
        //$password_enc = crypt($_GET["pwd"],$salt);
        $password = $_POST["pwd"];
		$origURL = $_POST["origURL"];
		$user = login($username,$password);			
	    if(isset($user['reason'])){//There is an error for this user or their subscription
			header('Location: ' . $root . 'login/?err=' . $user['reason']);
		}else{//success!			
			//Set Session var
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['user_name'] = $user['user_name'];
			$_SESSION['plan'] = $user['plan'];			
			if(isset($user['access'])){
				$_SESSION['access'] = $user['access'];
			}
			$querystring = "";
			if(isset($user['notice'])){
				$querystring = "?not=" . $user['notice']; 
			}
			//Redirect
			if($origURL!=""){
				header('Location: ' . $origURL . $querystring);
			}else header('Location: ' . $root . $querystring);
		}
	}
?>