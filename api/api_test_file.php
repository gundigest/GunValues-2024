<?php
//Test POST file for API
	$random = rand(0,1000);
	//$user_data['email'] = "testemail" . $random . "@mwoodruff.net";	
	$user_data['email'] = "testemail577@mwoodruff.net";	
	$user_data['fname'] = "API";
	$user_data['lname'] = "Test";	
	$user_data['address'] = "222 Fake Address";
	$user_data['city'] = "Faketown";
	$user_data['state'] = "CA";
	$user_data['zip'] = "90000";
	$user_data['country'] = "US"; 		
	$user_data['amount'] = 27.99;
	$user_data['purchase_id'] = 123456;	
 //initialize session
			$ch=curl_init("https://gunvalues.gundigest.com/api/purchase_plan.php?UXUPGSTNxV4JXbxaaCCSs3");

			//set options
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $user_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);			
			//execute session
			$purchase_json = curl_exec($ch);
			echo $purchase_json;
			//close session
			curl_close($ch); 
?>