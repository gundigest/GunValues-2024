<?php
    include("../model/config.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;
	/*try
       {
           $stmt = $db->prepare("SELECT id, mod_series AS series FROM scof_gun_models WHERE mod_series != '' ORDER BY series");                          
           $stmt->execute(); 
           $count = $stmt->rowCount();
           if($count>0){
                $modRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				//var_dump($manRows);
				foreach($modRows AS $i => $mod){
					$slug = slugify($mod['series']);
					$stmt = $db->prepare("UPDATE scof_gun_models SET series_slug='" . $slug . "' WHERE id=" . $mod['id']); 
					$stmt->execute();                         
				}
			}
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }  */ 
		

?>