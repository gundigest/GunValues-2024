<?php
$link = new mysqli("localhost", "root", "root","gun_values");

if ($link->connect_errno) {
    die("Failed to connect to MySQL: (" . $link->connect_errno . ") " . $link->connect_error);
}

//DB
	$dbtype = "mysql"; 
	$db_host = "localhost";
	$db_user = "root";
	$db_pass = "root";
	$db_name = "gun_values";

//connect to DB
$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$payment_un = "GunDigestDemoAPI";
$payment_pw = "password1";

$ftp_server = "132.148.87.233";
$ftp_username = "gv_updates@gundigestmedia.com";
$ftp_userpass = 'L]4%.s1R*iHn';
?>
