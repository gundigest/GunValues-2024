<?php
//Manufacturer Photo Page Content
if(!(isset($slug))){
		$page_name = "Model Results for this manufacturer cannot be displayed";
		$html .= <<<EOD
		<h2 id="mfg_title">Model Results for this manufacturer cannot be displayed</h2>		
		<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
	}else{
		$manu = getManufacturer($slug);				
		$display_name =proper_case($manu['make']);
		if($plan_active){
			$user_id = $_SESSION['user_id'];
		}else $user_id = 0;	
		log_history($user_id,"Manufacturer by Photo",null,$slug,null,null);
		//check for filters				
		$filter_value = "";
		if((isset($_GET['filter']))&&(isset($_GET['value']))){			
			$filter_type = $_GET['filter'];
			$filter_value = $_GET['value'];
			$filter_name = getGunTypeName($filter_value);			
		}
		$models = getManufacturerPhotos($slug);
		$page_name = $display_name . " Model Photos";			
		//Ads
		$banner_ad = showAd("banner");		
		$guntype_list = array();
		//if logo, display here. If not, display drop-cap
		$letter = substr($display_name,0,1);
		$manu_html = "<a href='" . $root . $slug . "/' class='drop-cap'>" . $letter . "</a>";
		$manu_html .= "<div class='top-featured'><h2>" . $display_name . "</h2>";
		$count_locations = strpos($manu['location'],"&break;");
		$location = str_replace("&break;","<br/>",$manu['location']);
		if($count_locations>0){
			$manu_html .= "<div class='details'>Locations: " . $location . "</div>";
		}else $manu_html .= "<div class='details'>Location: " . $location . "</div>";
		$manu_html .= "<div class='details'>" . $manu['Note'] . "</div>";
		$manu_html .="</div><div class='banner-ad centered'>$banner_ad</div><div class='divider'></div><h3>Choose your Firearm</h3>";
		//Create Text Manufacturer Link
			$photo_html = "<h3>By Model Name</h3>";
			if((isset($_GET['filter']))&&(isset($_GET['value']))){
				$photo_html .= "<a class='button' href='" . $root . $slug . "/?filter=guntype&value=" .$filter_value. "'>Browse By Model Name</a>";
			}else $photo_html .= "<a class='button' href='" . $root . $slug . "/'>Browse By Model Name</a>";
			$photo_html .= "<div class='divider'></div>";
			
			$html .= "<h3>By Photo</h3>";
			$count = 0;
				if($models!=false){
					foreach($models AS $model){
						//For Guntype Filtering
						$guntypes = array();
						$guntype_class = "";
						if((strlen($model['mod_guntype']))>5){//Catch items with two guntypes
							$guntypes = explode(",",$model['mod_guntype']);										
						}else{
							$guntypes[0] = $model['mod_guntype'];									
						}
						if(count($guntypes)>0){
							foreach($guntypes AS $guntype){
								$guntype_class .= " " . $guntype;
								$guntype_name = getGunTypeName($guntype);
								$guntype_list[$guntype] = $guntype_name;						
							}
						}
						if ($model['mod_image_1'] != "") {
							$arr = explode(".", $model['mod_image_1'], 2);
							$image_1 = $arr[0] . ".jpg";
							if(substr($model['mod_image_1'],0,1) != "h") $image_1 = $model['mod_image_1'];																				
							if (file_exists($file_root. "gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $image_1)) {
								$html .= "<a class='photo-button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'><img src='" .$root. "gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $image_1 . "' alt='" . $model['mod_model'] . "' loading='lazy' /></a>";
							}else if (file_exists($file_root. "gunValues/images/Firearms2017_Fall/large/JPEG/" . $image_1)) {
								$html .= "<a class='photo-button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'><img src='" .$root. "gunValues/images/Firearms2017_Fall/large/JPEG/" . $image_1 . "' alt='" . $model['mod_model'] . "' loading='lazy'/></a>";
							}
							$count++;
						}
						if ($model['mod_image_2'] != "") {	
							$arr = explode(".", $model['mod_image_2'], 2);
							$image_2 = $arr[0] . ".jpg";						
							$html .= "<a class='photo-button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'><img src='" .$root. "gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $image_2 . "' alt='" . $model['mod_model'] . "'  loading='lazy'/></a>";
						}
						if ($model['mod_image_3'] != "") {	
							$arr = explode(".", $model['mod_image_3'], 2);
							$image_3 = $arr[0] . ".jpg";						
							$html .= "<a class='photo-button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'><img src='" .$root. "gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $image_3 . "' alt='" . $model['mod_model'] . "'  loading='lazy'/></a>";
						}
						if ($model['mod_image_4'] != "") {	
							$arr = explode(".", $model['mod_image_4'], 2);
							$image_4 = $arr[0] . ".jpg";						
							$html .= "<a class='photo-button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'><img src='" .$root. "gunValues/images/Firearms2017_Fall/thumbnails/JPEG/" . $image_4 . "' alt='" . $model['mod_model'] . "'  loading='lazy'/></a>";
						}
					}
					if($count<1){
						$html .= "There are no photos available to display for this Manufacturer";
					}else 
							$html .= '<div class="page-bottom-ads">';
							$html .= showAd("large-rect1");
							$html .= showAd("large-rect2") . "</div>";					
				}else{
					$html .= "There are no photos available to display for this Manufacturer";
				}				
		//$html .= "</div><div class='one-third-page'>" . showAd("large-rect") . "</div>";
		//Create Gun Type drop down		
		$dropdown = '<div class="announcement">Results filtered by <select name="guntype_filter" id="guntype_filter">';
		$dropdown .= '<option value="">Show All Gun Types</option>';		
		foreach($guntype_list AS $key => $gt){
			$dropdown .= '<option value="' . $key . '">' . $gt . '</option>';
		}
		$dropdown .= "</select></div>";		
		$html = $manu_html . $dropdown . $photo_html . $html;
		$head = <<<EOD
<script type="text/javascript">
	$(document).ready(function() {
		$('#guntype_filter').change( function(){
			 $(this).find(":selected").each(function () {
				var sel_value = $(this).val(); 
				changeGuntype(sel_value);
			 });			
		});
		$('#guntype_filter').val('{$filter_value}');
		changeGuntype('{$filter_value}');
	});
function changeGuntype(guntype){
	if(guntype==""){
		$(".photo-button").show();
	}else{
		$(".photo-button").hide();
		$("." + guntype).show();
	}						
}
</script>
EOD;
}
?>



