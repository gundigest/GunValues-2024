<?php
    include("../model/config.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;
	/*try
       {
           $stmt = $db->prepare("SELECT id, manufacturer AS make FROM scof_manufacturers GROUP BY make ORDER BY manufacturer");                          
           $stmt->execute(); 
           $count = $stmt->rowCount();
           if($count>0){
                $manRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
				//var_dump($manRows);
				foreach($manRows AS $i => $man){
					$slug = slugify($man['make']);
					$stmt = $db->prepare("UPDATE scof_manufacturers SET slug='" . $slug . "' WHERE id=" . $man['id']); 
					$stmt->execute();                         
				}
			}
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }   */ 
		

?>