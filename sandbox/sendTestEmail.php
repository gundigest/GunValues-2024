<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    include("../model/config.php");
    require_once("../model/mail_functions.php");
    require_once("../model/user_functions.php");
    $recipient=addslashes("Jamie Olsen <jamie@gundigest.com>");  
    $template=file_get_contents("../model/email_templates/purchase.html");
    $template=str_replace("{{first_name}}","Jamie",$template);    
    $template=str_replace("{{details}}","Test",$template);
    echo sendEmail($recipient,"System Test Email",$template);    
    ?>