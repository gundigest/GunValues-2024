<?php
//Functions for Gun Values
//get guns by Seach Terms
function searchGuns($query,$params=""){
	global $db;

	$qString = "";
	//Have to separate words for regular search
	if(strpos($query,'"')>-1){//we have quotes
		$keywords[]=strtolower($query);				
	}else{//Then separate by spaces
		$keywords=preg_split( "/( |-)/", $query );	 
	}	
    foreach ($keywords AS $word){
		if(($word != "")&&(strcasecmp($word, "model") != 0)&&(strcasecmp($word, "and") != 0)){
			$qString .= " +" . $word;
		}
	}
	//echo $qString;
	//Have to determine if we have been given any parameters to ignore
	//m=manufacturer
	//g=gun model
	//s=series
	//d=description	
	$match_string = array();	
	$where_string = array();	
	if(stripos($params,"m")===false){//m is not in the list of ignore parameters
		$match_string[] = "MATCH (m.Manufacturer) AGAINST (:qString IN BOOLEAN MODE) AS title_score";
		$where_string[] = " MATCH (m.Manufacturer) AGAINST (:qString IN BOOLEAN MODE) > 0 ";
	}	
	if(stripos($params,"g")===false){//g is not in the list of ignore parameters
		$match_string[] = "MATCH (g.mod_model,g.mod_model_subset) AGAINST (:qString IN NATURAL LANGUAGE MODE) AS model_score";		
		$where_string[] = " MATCH (g.mod_model,g.mod_model_subset) AGAINST (:qString IN NATURAL LANGUAGE MODE) > 0 ";		
	}	
	if(stripos($params,"s")===false){//s is not in the list of ignore parameters
		$match_string[] = "MATCH (g.mod_series) AGAINST (:qString IN BOOLEAN MODE) AS series_score";
		$where_string[] = " MATCH (g.mod_series) AGAINST (:qString IN BOOLEAN MODE) > 0 ";	
	}
	if(stripos($params,"d")===false){//d is not in the list of ignore parameters
		$match_string[] = "MATCH (g.mod_item_note) AGAINST (:qString IN BOOLEAN MODE) AS description_score";
		$where_string[] = " MATCH (g.mod_item_note) AGAINST (:qString IN BOOLEAN MODE) > 0 ";
	}
	if(count($match_string)>0){
		$match_string_final = "," . implode(",",$match_string);	
		$where_string_final = "WHERE" . implode("OR",$where_string);	
	}else{
		return false;		
	}
	try{		
			//echo $qString;			
			//echo "SELECT g.id, m.Manufacturer AS make, m.slug, g.mod_model " . $match_string_final . "  from scof_gun_models g LEFT JOIN scof_manufacturers m ON m.id=g.mod_mfg " . $where_string_final . " group by g.mod_model order by m.Manufacturer,g.mod_model";
		    $stmt = $db->prepare("SELECT g.id, m.Manufacturer AS make, m.slug, g.mod_model ,g.mod_series,g.series_slug" . $match_string_final . "  from scof_gun_models g LEFT JOIN scof_manufacturers m ON m.id=g.mod_mfg " . $where_string_final . " group by g.mod_model order by m.Manufacturer,g.mod_model");
            $stmt->bindparam(":qString", $qString);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $gunRows['models']=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $gunRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Makes by Letter
function getMakes($letter){
	global $db;		
	try
       {
           $stmt = $db->prepare("SELECT id, manufacturer AS make,slug FROM scof_manufacturers WHERE alpha=:letter GROUP BY make ORDER BY manufacturer");              
            $stmt->bindparam(":letter", $letter);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $manRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		 
              return $manRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Makes by Guntype
function getMakesByGunType($gid){
	global $db;		
	try
       {
			$gid = '%' . $gid . '%';
           $stmt = $db->prepare("SELECT m.id, m.Manufacturer AS make,slug FROM scof_manufacturers m JOIN scof_gun_models g ON m.id = g.mod_mfg WHERE g.mod_guntype LIKE :gid GROUP BY make ORDER BY m.Manufacturer");              
            $stmt->bindparam(":gid", $gid);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $modelRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Makes by Guntype
function getGunTypeName($gid){
	global $db;		
	try
       {
           $stmt = $db->prepare("SELECT name FROM gun_types WHERE gid=:gid LIMIT 1");              
            $stmt->bindparam(":gid", $gid);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $modelRows[0]['name'];
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}

//get Makes by Guntype, siungular version
function getGunTypeNameSingle($gid){
	global $db;
	try
       {
           $stmt = $db->prepare("SELECT name_singular, name FROM gun_types WHERE gid=:gid LIMIT 1");              
            $stmt->bindparam(":gid", $gid);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
			  if(is_null($modelRows[0]['name_singular'])){
				return $modelRows[0]['name'];
			  }else	return $modelRows[0]['name_singular'];
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}

//get All Available Makes Letters
function getAllMakeLetters(){
	global $db;
	
	try
       {
           $stmt = $db->prepare("SELECT DISTINCT alpha FROM scof_manufacturers ORDER BY alpha");
           
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $modelRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get All Available Gun Types
function getAllGunTypes(){
	global $db;	
	try
       {
           $stmt = $db->prepare("SELECT DISTINCT gid,name FROM gun_types WHERE active=1 ORDER BY sort_order");           
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $gtRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $gtRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Get Manufacturer Display Details by Slug
function getManufacturer($slug){
	global $db;
	
	try
       {
		    //get Manufacturer details			
			$stmt = $db->prepare("SELECT id,manufacturer AS make, location, Note, slug, refer_to FROM scof_manufacturers WHERE slug=:slug LIMIT 1 ");              
			$stmt->bindparam(":slug", $slug);			
			$stmt->execute(); 
			$count = $stmt->rowCount();
			if($count>0){
			  $mfgRows=$stmt->fetchAll(PDO::FETCH_ASSOC);				  
			  return $mfgRows[0];
			}else return false;
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Get Colt Manufacturer Display Details (special case)
function getColtManufacturer(){
	global $db;
	
	try
       {
		    //get Colt Manufacturer details, including IDs and divisions		
			$stmt = $db->prepare("SELECT id,manufacturer AS make, location, Note, slug, refer_to, Division FROM scof_manufacturers WHERE id BETWEEN 5811 AND 5830");
			$stmt->execute(); 
			$count = $stmt->rowCount();
			if($count>0){
			  $mfgRows=$stmt->fetchAll(PDO::FETCH_ASSOC);				  
			  return $mfgRows;
			}else return false;
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}

//Get Manufacturer Models & Series by Slug
function getManufacturerModels($slug,$filter="",$value=""){
	global $db;
	
	$series_model = array();
	$filter_query = "";
	try
       {
		   if($filter!=""){//we only have one filter right now		   
			   $filter_query = " AND g.mod_guntype LIKE '%" . $value . "%'";
		   }					
				//Get all models individually
				$stmt = $db->prepare("SELECT g.id, g.mod_guntype, g.mod_item_note,g.mod_model_sort,g.mod_type,mod_model,g.mod_model_subset, IF(((mod_image_1='' AND mod_image_2='' AND mod_image_3='' AND mod_image_4='') OR (mod_image_1 IS NULL AND mod_image_2 IS NULL AND mod_image_3 IS NULL AND mod_image_4 IS NULL)), 'NA', 'Photo Avail') as Image_Status,antique from scof_gun_models g,scof_manufacturers m WHERE slug=:slug AND m.id=g.mod_mfg AND g.edition_flag IS NULL AND mod_model!=''" . $filter_query . " order by ABS(g.mod_model_sort) ");              
				$stmt->bindparam(":slug", $slug);
				$stmt->execute(); 
				$count = $stmt->rowCount();
				if($count>0){
				  $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
				  //TODO: Organize by mod_type
				  foreach($modelRows AS $mod){
						if($mod['mod_type']!=''){//mod_type set
							$series_model['models'][$mod['mod_type']][] = $mod;
						}else $series_model['models']['none'][] = $mod;
				  }
				  //$series_model['models'] = $modelRows;
			   }
			   //Get all Series
			   $stmt = $db->prepare("SELECT DISTINCT g.mod_series,g.series_slug from scof_gun_models g,scof_manufacturers m WHERE slug=:slug AND m.id=g.mod_mfg AND g.mod_series != '' AND g.edition_flag IS NULL AND mod_model!=''" . $filter_query . " order by ABS(g.mod_model_sort)");              
				$stmt->bindparam(":slug", $slug);
				$stmt->execute(); 
				$series_count = $stmt->rowCount();
				if($series_count>0){
				  $seriesRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
				  $series_model['series'] = $seriesRows;
			   }
			   return $series_model;		   
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Get Manufacturer Models & Series for Colt only
function getColtManufacturerModels($filter="",$value=""){
	global $db;
	
	$series_model = array();
	$filter_query = "";
	try
       {
		   if($filter!=""){//we only have one filter right now		   
			   $filter_query = " AND g.mod_guntype LIKE '%" . $value . "%'";
		   }					
				//Get all models individually
				$stmt = $db->prepare("SELECT g.id, m.id AS mfg_id,m.Note,g.mod_guntype, g.mod_type,mod_model, IF((mod_image_1='' AND mod_image_2='' AND mod_image_3='' AND mod_image_4=''), 'NA', 'Photo Avail') as Image_Status,m.Division from scof_gun_models g,scof_manufacturers m WHERE m.id BETWEEN 5811 AND 5830 AND m.id=g.mod_mfg AND g.edition_flag IS NULL AND mod_model!=''" . $filter_query . " order by m.Sort,ABS(g.mod_model_sort) ");
				
				$stmt->execute(); 
				$count = $stmt->rowCount();
				if($count>0){
				  $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
				  //TODO: Organize by Division and mod_type
				  foreach($modelRows AS $mod){
						$division = 'nodiv';
						if($mod['Division']!='') $division = $mod['Division'];//Division set						
						if($mod['mod_type']!=''){//mod_type set
							$series_model['models'][$division][$mod['mod_type']][] = $mod;							
						}else $series_model['models'][$division]['none'][] = $mod;										
				  }
				  //$series_model['models'] = $modelRows;
			   }
			   //Get all Series
			   $stmt = $db->prepare("SELECT DISTINCT g.mod_series,m.id AS mfg_id,m.Note,g.series_slug,m.Division from scof_gun_models g,scof_manufacturers m WHERE m.id BETWEEN 5811 AND 5830 AND m.id=g.mod_mfg AND g.mod_series != '' AND g.edition_flag IS NULL AND mod_model!=''" . $filter_query . " order by ABS(g.mod_model_sort)");              				
				$stmt->execute(); 
				$series_count = $stmt->rowCount();
				if($series_count>0){
				  $seriesRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
				  $series_model['series'] = $seriesRows;
			   }
			   return $series_model;		   
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Manufacturer Photos
function getManufacturerPhotos($slug,$filter="",$value=""){
	global $db;	

	$filter_query = "";
	try
       {
		    if($filter!=""){//we only have one filter right now		   
			   $filter_query = " AND scof_gun_models.mod_guntype LIKE '%" . $value . "%'";
		   }
		    //Get all models individually
			$stmt = $db->prepare("SELECT scof_gun_models.id,mod_guntype, mod_model, mod_image_1, mod_image_2, mod_image_3, mod_image_4 FROM scof_gun_models LEFT JOIN scof_manufacturers ON mod_mfg=scof_manufacturers.id WHERE slug=:slug AND mod_model!='' AND mod_image_1!=''" . $filter_query . " order by ABS(mod_model_sort)");              
            $stmt->bindparam(":slug", $slug);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $modelRows;
           }else return false;				
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Series by Series Slug & Manufacturer Slug
function getSeries($series_slug,$slug){	
	global $db;	
	try
       {
		   //Get all models individually
			$stmt = $db->prepare("SELECT scof_gun_models.id,mod_model,scof_gun_models.mod_item_note,mod_series,mod_image_1,mod_courtesy_1,mod_nib,mod_exc,mod_vg,mod_good,mod_fair,mod_poor FROM scof_gun_models LEFT JOIN scof_manufacturers ON mod_mfg=scof_manufacturers.id WHERE series_slug=:series_slug AND slug=:slug order by ABS(mod_model_sort)");              
            $stmt->bindparam(":series_slug", $series_slug);
            $stmt->bindparam(":slug", $slug);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $modelRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $modelRows;
           }else return false;
		  
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Gun by ID, original design with editions
function getGun($id){
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT * FROM scof_gun_models WHERE id=:id limit 1");              
		$stmt->bindparam(":id", $id);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		  $gunRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  //check for editions of this gun
			$stmt = $db->prepare("SELECT * FROM scof_gun_model_editions e LEFT JOIN scof_gun_models g ON e.edition_id = g.id WHERE e.model_id=:id ORDER BY ABS(g.mod_model_sort)");              
			$stmt->bindparam(":id", $id);
			$stmt->execute(); 
			$edition_count = $stmt->rowCount();
			if($edition_count>0){
				$gunRows[0]['editions']=$stmt->fetchAll(PDO::FETCH_ASSOC);
			}else $gunRows[0]['editions']=false;
		  return $gunRows[0];
	   }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Gun by ID, original design with editions
function getSameNameGuns($id,$mod_name,$man_id){
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT id,mod_model,mod_model_subset,IF((mod_image_1='' AND mod_image_2='' AND mod_image_3='' AND mod_image_4=''), 'NA', 'Photo Avail') as Image_Status FROM scof_gun_models WHERE mod_model=:mod_name AND mod_mfg=:man_id AND id!=:id");              
		$stmt->bindparam(":mod_name", $mod_name);
		$stmt->bindparam(":man_id", $man_id);
		$stmt->bindparam(":id", $id);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		  $gunRows=$stmt->fetchAll(PDO::FETCH_ASSOC);		  
		  return $gunRows;
	   }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get ad by Gun ID
function getModelAd($id,$mod_name){
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT content,link FROM aff_ad WHERE mod_id=:id AND status='active' ORDER BY id DESC LIMIT 1");              		
		$stmt->bindparam(":id", $id);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		  $gunRows = $stmt->fetchAll(PDO::FETCH_ASSOC);		  
		  return '<a href="' . $gunRows[0]['link'] . '" target="_blank">' . str_replace("{mod_name}",$mod_name,$gunRows[0]['content']) . '</a>';
	   }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get ad by manufacturer slug
function getManufacturerAd($man_slug,$man_name){
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT content,link FROM aff_ad WHERE man_slug=:man_slug AND series_slug IS NULL AND status='active' ORDER BY id DESC LIMIT 1");              		
		$stmt->bindparam(":man_slug", $man_slug);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		  $gunRows = $stmt->fetchAll(PDO::FETCH_ASSOC);		  
		  return '<a href="' . $gunRows[0]['link'] . '" target="_blank">' . str_replace("{man_name}",$man_name,$gunRows[0]['content']) . '</a>';
	   }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get ad by series slug
function getSeriesAd($man_slug,$series_slug,$series_name){
	global $db;	
	try
       {
        $stmt = $db->prepare("SELECT content,link FROM aff_ad WHERE man_slug=:man_slug AND series_slug=:series_slug AND status='active' ORDER BY id DESC LIMIT 1");              		
		$stmt->bindparam(":man_slug", $man_slug);
		$stmt->bindparam(":series_slug", $series_slug);
		$stmt->execute(); 
		$count = $stmt->rowCount();
		if($count>0){
		  $gunRows = $stmt->fetchAll(PDO::FETCH_ASSOC);		  
		  return '<a href="' . $gunRows[0]['link'] . '" target="_blank">' . str_replace("{series_name}",$series_name,$gunRows[0]['content']) . '</a>';
	   }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}

function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  //$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}
function proper_case($string){
/**
* function proper_case
* returns string with each word proper case
* @param string $strIn string that will be proper cased
* @return string proper cased 
*/
	if(strlen($string)>2){		
		$name = ' '. $string.' ';//space is removed below
		$name = str_replace('&#038;', '&', $name);
		$name = str_replace('&break;', ' ', $name);
		$name = str_replace('&quot;','"',$name);
		/*$name = join(".", array_map('ucwords', explode(".", $name)));		
		$name = join("/", array_map('ucwords', explode("/", $name)));
		$name = join("-", array_map('ucwords', explode("-", $name)));
		$name = join("(", array_map('ucwords', explode("(", $name)));
		$name = join("Mac", array_map('ucwords', explode("Mac", $name)));
		$name = join("Mc", array_map('ucwords', explode("Mc", $name)));	*/
		//$name = ucwords($name, ' "/-.(;');//should correct first letter of quoted text		
		
		//special fix - easier with the spaces - case sensitive
		
		$name = str_replace(' Pm ', ' pm ', $name);
		$name = str_replace(' Pp ', ' PP ', $name);
		$name = str_replace(' Am ', ' am ', $name);
		$name = str_replace(' Po ', ' PO ', $name);
		$name = str_replace('Md', 'MD', $name);
		$name = str_replace('Dds', 'DDS', $name);
		$name = str_replace(' Nw ', ' NW ', $name);
		$name = str_replace(' Se ', ' SE ', $name);
		$name = str_replace(' Ne ', ' NE ', $name);
		$name = str_replace(' Sw ', ' SW ', $name);
		$name = str_replace(' Of ', ' of ', $name);
		$name = str_replace(' The ', ' the ', $name);
		$name = str_replace(' Qq ', ' QQ ', $name);
		$name = str_replace(' Dd ', ' DD ', $name);
		$name = str_replace(' "y" ', ' "Y" ', $name);
		$name = str_replace(' Iii ', ' III ', $name);
		$name = str_replace(' Vi ', ' VI ', $name);
		$name = str_replace(' Vii ', ' VII ', $name);
		$name = str_replace(' Viii ', ' VIII ', $name);
		$name = str_replace(' Ii ', ' II ', $name);
		$name = str_replace(' Ix ', ' IX ', $name);
		$name = str_replace(' Iv ', ' IV ', $name);
		$name = str_replace(' Xxv ', ' XXV ', $name);
		$name = str_replace(' Xd ', ' XD ', $name);
		$name = str_replace('-Li ', '-LI ', $name);
		$name = str_replace(' Nra ', ' NRA ', $name);


	}else $name = $string;

    return trim($name);  
}
if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}
?>