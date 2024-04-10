<?php
//Manufacturers By Guntype
if(!(isset($gid))){
		echo "This query cannot be displayed. The ID is not valid.";
	}else{
		$results = getMakesByGunType($gid);		
		$page_name = getGunTypeName($gid);
		$singular = getGunTypeNameSingle($gid);
		$html = "<h2>Choose " . proper_case($singular) . " Manufacturer</h2>";
		foreach($results AS $result){
			$html .= "<a class='button' href='" . $root . $result['slug'] . "/?filter=guntype&value=" .$gid. "'>" . proper_case($result['make']) . "</a>";
		}
		
}
?>



