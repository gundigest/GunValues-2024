<?php
//--By Michelle Woodruff 08/28/2019
//Check for ReCaptcha Results

  $data = array(
      "secret" => "6LdJf7UUAAAAAHeoyo8D00hCT3Us6Zdv1gbT4qz1",
      "response" => $_POST['g-recaptcha-response'],
      "remoteip" => $_SERVER['REMOTE_ADDR']
  );
        
  $data_fields = http_build_query($data);       
  //initialize session
  $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");

  //set options
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_fields);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

  //execute session
  $response = curl_exec($ch);        
  $success = json_decode($response,true);
  if ($success["success"]==true){
    echo "Success";
  }else echo "Fail";
  //close session
  curl_close($ch);
?>