<?php
    include("../model/config.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;
	try
       {
           $stmt = $db->prepare("SELECT mod_model FROM scof_gun_models ORDER BY id,mod_model,mod_model_sort");                          
           $stmt->execute(); 
           $count = $stmt->rowCount();
           if($count>0){
                $modRows = $stmt->fetchAll(PDO::FETCH_ASSOC);				
				foreach($modRows AS $mod){	
					echo proper_case($mod['mod_model']) . "<br/>";
				}
			}
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }  
		

?>