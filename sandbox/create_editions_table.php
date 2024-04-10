<?php
    include("../model/config.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;
	/*try
       {
           $stmt = $db->prepare("SELECT id,mod_mfg, mod_model,mod_model_subset FROM scof_gun_models WHERE mod_series = '' AND mod_model_subset != '' ORDER BY id,mod_model,mod_model_sort");                          
           $stmt->execute(); 
           $count = $stmt->rowCount();
           if($count>0){
                $modRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				//var_dump($modRows);
				foreach($modRows AS $mod){					
					$stmt = $db->prepare("SELECT id, mod_model FROM scof_gun_models WHERE mod_model = :model AND mod_model_subset = '' AND mod_mfg=:mfg LIMIT 1"); 
					$stmt->bindparam(":model", $mod['mod_model']);
					$stmt->bindparam(":mfg", $mod['mod_mfg']);
					$stmt->execute();
				    $count = $stmt->rowCount();
					if($count>0){
						$parentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						echo "Parent = " . $parentRows[0]['id'] . " " . $parentRows[0]['mod_model'] . "<br/>";
						echo "Child = " . $mod['id'] . " " . $mod['mod_model']. " " . $mod['mod_model_subset'] . "<br/><hr/>";
						//Create edition table entries
						$stmt = $db->prepare("INSERT INTO scof_gun_model_editions (model_id,edition_id) VALUES (:parent_id,:edition_id)");
						$stmt->bindparam(":parent_id", $parentRows[0]['id']);
						$stmt->bindparam(":edition_id", $mod['id']);
						$stmt->execute();
						//add edition flags where appropriate
						$stmt = $db->prepare("UPDATE scof_gun_models SET edition_flag = 'true' WHERE id=:id");
						$stmt->bindparam(":id", $mod['id']);
						$stmt->execute();
					}
				}
			}
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }  */
		

?>