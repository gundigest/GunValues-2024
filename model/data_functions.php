<?php
//Functions for Updating Gun Values from SCOF data
//Check if Manufacturer exists
function manufacturerExists($man_id){
	global $db;		
	try
       {
            $stmt = $db->prepare("SELECT Manufacturer FROM `scof_manufacturers` WHERE ID=:id");              
            $stmt->bindparam(":id", $man_id);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){		 
              return true;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Add new Manufacturer
function insertManufacturer($man_id,$new_data,$slug){
	global $db;		
	try
       {
            $stmt = $db->prepare("INSERT INTO `scof_manufacturers`(`ID`, `Manufacturer`, `Division`, `Manufacturer2`, `Location`, `Refer_to`, `Alpha`, `slug`, `Sort`, `Note`, `Subnote`, `Mfg_tab`) VALUES (:ID,:Manufacturer,:Division,:Manufacturer2,:Location,:Refer_to,:Alpha,:slug,:Sort,:Note,:Subnote,:Mfg_tab)");
            $stmt->bindparam(":ID", $man_id);
            $stmt->bindparam(":Manufacturer", $new_data['scf_title']);
            $stmt->bindparam(":Division", $new_data['scf_mfg_division']);
            $stmt->bindparam(":Manufacturer2", $new_data['scf_mfg2']);
            $stmt->bindparam(":Location", $new_data['scf_location']);
            $stmt->bindparam(":Refer_to", $new_data['scf_refer_to']);
            $stmt->bindparam(":Alpha", $new_data['scf_mfg_alpha']);
            $stmt->bindparam(":slug", $slug);
            $stmt->bindparam(":Sort", $new_data['scf_mfg_sort']);
            $stmt->bindparam(":Note", $new_data['scf_mfg_note']);
            $stmt->bindparam(":Subnote", $new_data['scf_mfg_subnote']);
            $stmt->bindparam(":Mfg_tab", $new_data['scf_mfg_tab']);
            $stmt->execute(); 
            return true;           
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Update Existing Manufacturer
function updateManufacturer($man_id,$new_data,$slug){
	global $db;		
	try
       {
           $stmt = $db->prepare("UPDATE `scof_manufacturers` SET `Manufacturer`=:Manufacturer,`Division`=:Division,`Manufacturer2`=:Manufacturer2,`Location`=:Location,`Refer_to`=:Refer_to,`Alpha`=:Alpha,`slug`=:slug,`Sort`=:Sort,`Note`=:Note,`Subnote`=:Subnote,`Mfg_tab`=:Mfg_tab WHERE ID=:ID");              
            $stmt->bindparam(":ID", $man_id);
            $stmt->bindparam(":Manufacturer", $new_data['scf_title']);
            $stmt->bindparam(":Division", $new_data['scf_mfg_division']);
            $stmt->bindparam(":Manufacturer2", $new_data['scf_mfg2']);
            $stmt->bindparam(":Location", $new_data['scf_location']);
            $stmt->bindparam(":Refer_to", $new_data['scf_refer_to']);
            $stmt->bindparam(":Alpha", $new_data['scf_mfg_alpha']);
            $stmt->bindparam(":slug", $slug);
            $stmt->bindparam(":Sort", $new_data['scf_mfg_sort']);
            $stmt->bindparam(":Note", $new_data['scf_mfg_note']);
            $stmt->bindparam(":Subnote", $new_data['scf_mfg_subnote']);
            $stmt->bindparam(":Mfg_tab", $new_data['scf_mfg_tab']);
            $stmt->execute(); 
                return true;           
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Check if Model exists
function modelExists($mod_id){
	global $db;		
	try
       {
            $stmt = $db->prepare("SELECT mod_model FROM `scof_gun_models` WHERE ID=:id");              
            $stmt->bindparam(":id", $mod_id);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){		 
              return true;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Add new Model
function insertModel($mod_id,$new_data,$series_slug){
	global $db;		
	try
       {
            $stmt = $db->prepare("INSERT INTO `scof_gun_models`(`id`, `mod_title`, `mod_mfg`, `mod_guntype`, `mod_subhead`, `mod_type`, `mod_series`, `series_slug`, `mod_model`, `mod_model_subset`, `mod_mfg_division`, `mod_british`, `mod_new_model`, `mod_model_sort`, `mod_notes`, `mod_item_note`, `mod_item_subnote`, `mod_image_1`, `mod_cutline_1`, `mod_courtesy_1`, `mod_image_2`, `mod_cutline_2`, `mod_courtesy_2`, `mod_image_3`, `mod_cutline_3`, `mod_courtesy_3`, `mod_image_4`, `mod_cutline_4`, `mod_courtesy_4`, `mod_sleeper`, `mod_chapter`, `mod_nib`, `mod_exc`, `mod_vg`, `mod_good`, `mod_fair`, `mod_poor`, `mod_price_note`, `profile_scope`,`antique`) VALUES (:id,:mod_title,:mod_mfg,:mod_guntype,:mod_subhead,:mod_type,:mod_series,:series_slug,:mod_model,:mod_model_subset,:mod_mfg_division,:mod_british,:mod_new_model,:mod_model_sort,:mod_notes,:mod_item_note,:mod_item_subnote,:mod_image_1,:mod_cutline_1,:mod_courtesy_1,:mod_image_2,:mod_cutline_2,:mod_courtesy_2,:mod_image_3,:mod_cutline_3,:mod_courtesy_3,:mod_image_4,:mod_cutline_4,:mod_courtesy_4,:mod_sleeper,:mod_chapter,:mod_nib,:mod_exc,:mod_vg,:mod_good,:mod_fair,:mod_poor,:mod_price_note,:profile_scope,:antique)");
            $stmt->bindparam(":id", $mod_id);            
			$stmt->bindparam(":mod_title", $new_data['mod_title']);
			$stmt->bindparam(":mod_mfg", $new_data['mod_mfg']);
			$stmt->bindparam(":mod_guntype", $new_data['mod_guntype']);
			$stmt->bindparam(":mod_subhead", $new_data['mod_subhead']);
			$stmt->bindparam(":mod_type", $new_data['mod_type']);
			$stmt->bindparam(":mod_series", $new_data['mod_series']);
			$stmt->bindparam(":series_slug", $series_slug);
			$stmt->bindparam(":mod_model", $new_data['mod_model']);
			$stmt->bindparam(":mod_model_subset", $new_data['mod_model_subset']);
			$stmt->bindparam(":mod_mfg_division", $new_data['mod_mfg_division']);
			$stmt->bindparam(":mod_british", $new_data['mod_british']);
			$stmt->bindparam(":mod_new_model", $new_data['mod_new_model']);
			$stmt->bindparam(":mod_model_sort", $new_data['mod_model_sort']);
			$stmt->bindparam(":mod_notes", $new_data['mod_notes']);
			$stmt->bindparam(":mod_item_note", $new_data['mod_item_note']);
			$stmt->bindparam(":mod_item_subnote", $new_data['mod_item_subnote']);
			$stmt->bindparam(":mod_image_1", $new_data['mod_image_1']);
			$stmt->bindparam(":mod_cutline_1", $new_data['mod_cutline_1']);
			$stmt->bindparam(":mod_courtesy_1", $new_data['mod_courtesy_1']);			
			$stmt->bindparam(":mod_image_2", $new_data['mod_image_2']);
			$stmt->bindparam(":mod_cutline_2", $new_data['mod_cutline_2']);
			$stmt->bindparam(":mod_courtesy_2", $new_data['mod_courtesy_2']);
			$stmt->bindparam(":mod_image_3", $new_data['mod_image_3']);
			$stmt->bindparam(":mod_cutline_3", $new_data['mod_cutline_3']);
			$stmt->bindparam(":mod_courtesy_3", $new_data['mod_courtesy_3']);
			$stmt->bindparam(":mod_image_4", $new_data['mod_image_4']);
			$stmt->bindparam(":mod_cutline_4", $new_data['mod_cutline_4']);
			$stmt->bindparam(":mod_courtesy_4", $new_data['mod_courtesy_4']);
			$stmt->bindparam(":mod_sleeper", $new_data['mod_sleeper']);
			$stmt->bindparam(":mod_chapter", $new_data['mod_chapter']);
			$stmt->bindparam(":mod_nib", $new_data['mod_nib']);
			$stmt->bindparam(":mod_exc", $new_data['mod_exc']);
			$stmt->bindparam(":mod_vg", $new_data['mod_vg']);
			$stmt->bindparam(":mod_good", $new_data['mod_good']);
			$stmt->bindparam(":mod_fair", $new_data['mod_fair']);
			$stmt->bindparam(":mod_poor", $new_data['mod_poor']);
			$stmt->bindparam(":mod_price_note", $new_data['mod_price_note']);
			$stmt->bindparam(":profile_scope", $new_data['profile_scope']);
			$stmt->bindparam(":antique", $new_data['antique']);
            $stmt->execute(); 
            return true;           
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Update Existing Model
function updateModel($mod_id,$new_data,$series_slug){
	global $db;		
	try
       {
           $stmt = $db->prepare("UPDATE `scof_gun_models` SET `mod_title`=:mod_title,`mod_mfg`=:mod_mfg,`mod_guntype`=:mod_guntype,`mod_subhead`=:mod_subhead,`mod_type`=:mod_type,`mod_series`=:mod_series,`series_slug`=:series_slug,`mod_model`=:mod_model,`mod_model_subset`=:mod_model_subset,`mod_mfg_division`=:mod_mfg_division,`mod_british`=:mod_british,`mod_new_model`=:mod_new_model,`mod_model_sort`=:mod_model_sort,`mod_notes`=:mod_notes,`mod_item_note`=:mod_item_note,`mod_item_subnote`=:mod_item_subnote,`mod_image_1`=:mod_image_1,`mod_cutline_1`=:mod_cutline_1,`mod_courtesy_1`=:mod_courtesy_1,`mod_image_2`=:mod_image_2,`mod_cutline_2`=:mod_cutline_2,`mod_courtesy_2`=:mod_courtesy_2,`mod_image_3`=:mod_image_3,`mod_cutline_3`=:mod_cutline_3,`mod_courtesy_3`=:mod_courtesy_3,`mod_image_4`=:mod_image_4,`mod_cutline_4`=:mod_cutline_4,`mod_courtesy_4`=:mod_courtesy_4,`mod_sleeper`=:mod_sleeper,`mod_chapter`=:mod_chapter,`mod_nib`=:mod_nib,`mod_exc`=:mod_exc,`mod_vg`=:mod_vg,`mod_good`=:mod_good,`mod_fair`=:mod_fair,`mod_poor`=:mod_poor,`mod_price_note`=:mod_price_note,`profile_scope`=:profile_scope,`antique`=:antique WHERE id=:id");              
            $stmt->bindparam(":id", $mod_id);            
			$stmt->bindparam(":mod_title", $new_data['mod_title']);
			$stmt->bindparam(":mod_mfg", $new_data['mod_mfg']);
			$stmt->bindparam(":mod_guntype", $new_data['mod_guntype']);
			$stmt->bindparam(":mod_subhead", $new_data['mod_subhead']);
			$stmt->bindparam(":mod_type", $new_data['mod_type']);
			$stmt->bindparam(":mod_series", $new_data['mod_series']);
			$stmt->bindparam(":series_slug", $series_slug);
			$stmt->bindparam(":mod_model", $new_data['mod_model']);
			$stmt->bindparam(":mod_model_subset", $new_data['mod_model_subset']);
			$stmt->bindparam(":mod_mfg_division", $new_data['mod_mfg_division']);
			$stmt->bindparam(":mod_british", $new_data['mod_british']);
			$stmt->bindparam(":mod_new_model", $new_data['mod_new_model']);
			$stmt->bindparam(":mod_model_sort", $new_data['mod_model_sort']);
			$stmt->bindparam(":mod_notes", $new_data['mod_notes']);
			$stmt->bindparam(":mod_item_note", $new_data['mod_item_note']);
			$stmt->bindparam(":mod_item_subnote", $new_data['mod_item_subnote']);
			$stmt->bindparam(":mod_image_1", $new_data['mod_image_1']);
			$stmt->bindparam(":mod_cutline_1", $new_data['mod_cutline_1']);
			$stmt->bindparam(":mod_courtesy_1", $new_data['mod_courtesy_1']);			
			$stmt->bindparam(":mod_image_2", $new_data['mod_image_2']);
			$stmt->bindparam(":mod_cutline_2", $new_data['mod_cutline_2']);
			$stmt->bindparam(":mod_courtesy_2", $new_data['mod_courtesy_2']);
			$stmt->bindparam(":mod_image_3", $new_data['mod_image_3']);
			$stmt->bindparam(":mod_cutline_3", $new_data['mod_cutline_3']);
			$stmt->bindparam(":mod_courtesy_3", $new_data['mod_courtesy_3']);
			$stmt->bindparam(":mod_image_4", $new_data['mod_image_4']);
			$stmt->bindparam(":mod_cutline_4", $new_data['mod_cutline_4']);
			$stmt->bindparam(":mod_courtesy_4", $new_data['mod_courtesy_4']);
			$stmt->bindparam(":mod_sleeper", $new_data['mod_sleeper']);
			$stmt->bindparam(":mod_chapter", $new_data['mod_chapter']);
			$stmt->bindparam(":mod_nib", $new_data['mod_nib']);
			$stmt->bindparam(":mod_exc", $new_data['mod_exc']);
			$stmt->bindparam(":mod_vg", $new_data['mod_vg']);
			$stmt->bindparam(":mod_good", $new_data['mod_good']);
			$stmt->bindparam(":mod_fair", $new_data['mod_fair']);
			$stmt->bindparam(":mod_poor", $new_data['mod_poor']);
			$stmt->bindparam(":mod_price_note", $new_data['mod_price_note']);
			$stmt->bindparam(":profile_scope", $new_data['profile_scope']);
			$stmt->bindparam(":antique", $new_data['antique']);
            $stmt->execute(); 
            return true;           
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Get last successful update
function getLastUpdate(){
	global $db;		
	try
       {
            $stmt = $db->prepare("SELECT timestamp FROM `data_updates` WHERE status='success' ORDER BY id DESC LIMIT 1");                          
            $stmt->execute(); 
			$count = $stmt->rowCount();	
			if($count>0){
			  $last_update = $stmt->fetchAll(PDO::FETCH_ASSOC);	
			  return $last_update[0]['timestamp'];
			}            
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Record Updates
function updateDataRecord($man_rec,$mod_rec,$status){
	global $db;		
	try
       {
            $stmt = $db->prepare("INSERT INTO `data_updates`(`manu_records`, `model_records`, `status`) VALUES (:man_rec,:mod_rec,:status)");              
            $stmt->bindparam(":man_rec", $man_rec);
            $stmt->bindparam(":mod_rec", $mod_rec);
            $stmt->bindparam(":status", $status);
            $stmt->execute(); 
            return true;           
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
		   return false;
       }    
}
//Download images
function downloadImage($name){
	global $ftp_server,$ftp_username,$ftp_userpass,$thumbPath;
	$last_update = getLastUpdate();

	$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
	$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
	// get the last modified time
	$lastchanged = ftp_mdtm($ftp_conn, $name);
	if ($lastchanged != -1)
	  {
		if(strtotime(date("Y-m-d H:i:s",$lastchanged)) > strtotime($last_update)){
			// try to download $server_file and save to $local_file
			if (ftp_get($ftp_conn, "../gunValues/images/Firearms2017_Fall/large/" . $name, $name, FTP_BINARY)) {
				list($width, $height, $type, $attr) = getimagesize("../gunValues/images/Firearms2017_Fall/large/" . $name);
				$newwidth = floor((100 * $width)/$height);
				$image = imageCreateFromAny("../gunValues/images/Firearms2017_Fall/large/" . $name);		
				$thumb = imagescale($image['im'], $newwidth);
				//file_put_contents("../gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $name,  $thumb);
				$filepath = $thumbPath . $name;
				saveAnyImage($thumb,$filepath,$image['type']);			
				$error = false;
			} else {
				$error = true;
			}
		}
	  }

	// close connection
	ftp_close($ftp_conn);
	
}
function imageCreateFromAny($filepath) { 
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
    $allowedTypes = array( 
        1,  // [] gif 
        2,  // [] jpg 
        3  // [] png 
    ); 
    if (!in_array($type, $allowedTypes)) { 
        return false; 
    } 
	$ret_arr = array();
    switch ($type) { 
        case 1 : 
            $ret_arr['im'] = imageCreateFromGif($filepath);
            $ret_arr['type'] = 'gif';
        break; 
        case 2 : 
             $ret_arr['im'] = imageCreateFromJpeg($filepath); 
			 $ret_arr['type'] = 'jpg';
        break; 
        case 3 : 
             $ret_arr['im'] = imageCreateFromPng($filepath); 
			 $ret_arr['type'] = 'png';
        break;         
    }    
    return $ret_arr;  
}
function saveAnyImage($image,$filepath,$type) {  
    switch ($type) { 
        case 'gif' : 
            imagegif($image, $filepath);
        break; 
        case 'jpg' : 
            imagejpeg($image, $filepath);
        break; 
        case 'png' :             
			imagepng($image, $filepath);
        break;    
		default:
			echo 'none';
			return false;
		break;		
    }    
    return true;  
}

?>