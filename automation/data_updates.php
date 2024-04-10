<?php
date_default_timezone_set("America/Los_Angeles");
include("../model/config.php");
include("../model/data_functions.php");
//Cron to get data updates from SCOF database
//Runs weekly
//Checks for changes made since last successful update and updates fields as appropriate
//Get last update date
$last_update = getLastUpdate();
$error = false;
//  Initiate curl
	$url = "https://scof.gundigestmedia.com/automation/data_changes.php?dt=" . urlencode($last_update);	
	$ch = curl_init();
	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
	));
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);	
	curl_close($ch);
	 
	$json_result = json_decode($result, true);
	//var_dump($json_result);
	$man_updates = 0;
	if(isset($json_result['manufacturers'])){
		foreach($json_result['manufacturers'] AS $manu){//loop through all manufacturer changes
			//get new data only
			$new_man = json_decode($manu['new_data'],true);
			//insert it into the DB
			//Check for manufacturer exists
			$exists = manufacturerExists($manu['man_id']);	
			if(!$exists){//If it doesn't exist, INSERT
				$success = insertManufacturer($manu['man_id'],$new_man,slugify($new_man['scf_title']));
			}else{//If it does exist, UPDATE appropriate fields
				$success = updateManufacturer($manu['man_id'],$new_man,slugify($new_man['scf_title']));
			}
			//increase update tally
			if($success){
				$man_updates++;
			}else{
				$error = true;
				break;
			}	
		}
	}
	$mod_updates = 0;
	if(isset($json_result['models'])){
		foreach($json_result['models'] AS $mod){//loop through all manufacturer changes
			//get new data only
			$new_mod = json_decode($mod['new_data'],true);
			var_dump($new_mod);
			//Check for series and update slugs
			$series_slug = null;
			if($new_mod['mod_series'] != ""){
				$series_slug = slugify($new_mod['mod_series']);
			}
			//insert it into the DB	
			//Check for manufacturer exists
			$exists = modelExists($mod['mod_id']);	
			if(!$exists){//If it doesn't exist, INSERT
				$success = insertModel($mod['mod_id'],$new_mod,$series_slug);
			}else{//If it does exist, UPDATE appropriate fields
				$success = updateModel($mod['mod_id'],$new_mod,$series_slug);
			}	
			//TODO: Check for editions
			//download new images
			if($new_mod['mod_image_1'] != ""){
				if(stripos($new_mod['mod_image_1'],"h-",0)===false){
					echo $new_mod['mod_image_1'];
					downloadImage($new_mod['mod_image_1']);
				}
			}
			if($new_mod['mod_image_2'] != ""){
				if(stripos($new_mod['mod_image_1'],"h-",0)===false){
					downloadImage($new_mod['mod_image_2']);		
				}
			}
			if($new_mod['mod_image_3'] != ""){
				if(stripos($new_mod['mod_image_1'],"h-",0)===false){
					downloadImage($new_mod['mod_image_3']);		
				}
			}
			if($new_mod['mod_image_4'] != ""){
				if(stripos($new_mod['mod_image_1'],"h-",0)===false){
					downloadImage($new_mod['mod_image_4']);		
				}
			}
			//increase update tally
			if($success){
				$mod_updates++;
			}else{
				$error = true;
				break;
			}	
		}
	}
//When done, update date_updates table
if($error){
	echo "There has been an error, update paused.";
	updateDataRecord($man_updates,$mod_updates,"error");
}else{
	echo "Data updates complete";
	updateDataRecord($man_updates,$mod_updates,"success");
}
?>