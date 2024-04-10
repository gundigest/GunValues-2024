<?php
include("../model/config.php");
global $db;
//Manufacturer Photo Page Content
try
       {
		    //Get all models individually
			$stmt = $db->prepare("SELECT id,model, image_1, image_2, image_3, image_4 FROM gun_models WHERE image_1!='' order by image_1");              
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $models=$stmt->fetchAll(PDO::FETCH_ASSOC);
              }				
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
foreach($models AS $model){
						if ($model['image_1'] != "") {
							$arr = explode(".", $model['image_1'], 2);
							$image_1 = $arr[0] . ".jpg";
							$url = "C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/thumbnails/JPEG/" . $image_1;
							if(!file_exists($url)){
								$html .= $image_1 . "<br/>";
								copy("C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/large/" . $model['image_1'], "C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/" . $model['image_1']);							
							}
						}
						if ($model['image_2'] != "") {	
							$arr = explode(".", $model['image_2'], 2);
							$image_2 = $arr[0] . ".jpg";
							if(!file_exists($url)){
								$html .= $image_2 . "<br/>";
								copy("C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/large/" . $model['image_2'], "C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/" . $model['image_2']);							
							}
							
						}
						if ($model['image_3'] != "") {				
							$arr = explode(".", $model['image_3'], 2);
							$image_3 = $arr[0] . ".jpg";												
							if(!file_exists($url)){
								$html .= $image_3 . "<br/>";
								copy("C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/large/" . $model['image_3'], "C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/" . $model['image_3']);							
							}
						}
						if ($model['image_4'] != "") {				
							$arr = explode(".", $model['image_4'], 2);
							$image_4 = $arr[0] . ".jpg";						
							if(!file_exists($url)){
								$html .= $image_4 . "<br/>";
								copy("C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/large/" . $model['image_4'], "C:\Users\Michelle\Dropbox\UniServerZ\www\app.gundigest.com\gunValues\images\Firearms2017_Fall/" . $model['image_4']);							
							}
						}
}

echo $html;
?>



