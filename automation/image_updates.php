<?php
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/data_functions.php");
$ftp_username = 'gv_updates@gundigestmedia.com';
$ftp_userpass = 'L]4%.s1R*iHn';
//Cron to get image updates from SCOF site
//Runs weekly, before data update (since we don't add this data to DB)
//Checks for changes made since last successful update and updates fields as appropriate
//Get last update date
$last_update = getLastUpdate();
$error = false;
downloadImage("190625194309-1.jpg");
//  Initiate FTP connection
//  Find images that are new or updated since last update
//  Move them to tmp files and compress if over 1MB
// connect and login to FTP server	
/*	$ftp_server = "132.148.87.233";	
	$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
	$login = ftp_login($ftp_conn, $ftp_username, 'L]4%.s1R*iHn');

	$file = "190625194309-1.jpg";
	//$file = "181106173550-1.png";

	// get the last modified time
	$lastchanged = ftp_mdtm($ftp_conn, $file);
	if ($lastchanged != -1)
	  {
		echo "$file was last modified on : " . date("Y-m-d H:i:s.",$lastchanged);
		if(strtotime(date("Y-m-d H:i:s",$lastchanged)) > strtotime($last_update)){
			echo "Updated recently";
			// try to download $server_file and save to $local_file
			if (ftp_get($ftp_conn, "../gunValues/images/Firearms2017_Fall/large/" . $file, $file, FTP_BINARY)) {
				echo "Successfully written to ../gunValues/images/Firearms2017_Fall/large/" . $file . "\n";
			} else {
				echo "There was a problem\n";
			}
		}else echo "Updated before last update";
	  }
	else
	  {
	  echo "Could not get last modified";
	  }

	// close connection
	ftp_close($ftp_conn);
*/
?>