<?php
//Manufacturer Page Content
if(!(isset($slug))){
		$page_name = "Model Results for this manufacturer cannot be displayed";
		$html .= <<<EOD
		<h2 id="mfg_title">Model Results for this manufacturer cannot be displayed</h2>		
		<a class="button" href="$root">Search or Browse</a>
EOD;
	}else{
		$colt = false;
		if($slug=="colt-s-patent-fire-arms-manufacturing-company"){//This is Colt, treat it differenetly
			$colt = true;
			$manus = getColtManufacturer();		
			$manu = $manus[0];//For main page details
		}else $manu = getManufacturer($slug);		
		$display_name = proper_case($manu['make']);
		if($plan_active){
			$user_id = $_SESSION['user_id'];
		}else $user_id = 0;	
		log_history($user_id,"Manufacturer",null,$slug,null,null);
		//check for filters	
		$filter_value = "";
		if((isset($_GET['filter']))&&(isset($_GET['value']))){			
			$filter_type = $_GET['filter'];
			$filter_value = $_GET['value'];
			$filter_name = getGunTypeName($filter_value);			
		}
		if(!$colt){
			$results = getManufacturerModels($slug);		
		}else $results = getColtManufacturerModels();		
		$photos_avail = $antique = false;
		$page_name = $display_name . " All Models";
		//Ads
		$banner_ad = showAd("banner");
	
		$guntype_list = array();
		//if logo, display here. If not, display drop-cap
		//MW 2021-01-27 removing logo as NONE exist
		/*if(!(is_null($manu['logo']))){
			$manu_html = "<a href='" . $root . $slug . "/' class='manu-logo'><img src='" . $root . "/images/manufacturers/" .$manu['logo']. "' title='" .$display_name. "' alt='" .$display_name. "'/></a>";
		}else{*/
			$letter = substr($display_name,0,1);
			$manu_html = "<a href='" . $root . $slug . "/' class='drop-cap'>" . $letter . "</a>";
		//}
		$manu_html .= "<div class='top-featured'><h2>" . $display_name . "</h2>";
		if($manu['refer_to']!=""){
			$manu_html .= "<div class='details'>" . $manu['refer_to'] . "</div>";
		}
		if($manu['location']!=""){
			$count_locations = strpos($manu['location'],"&break;");
			$location = str_replace("&break;","<br/>",$manu['location']);
			if($count_locations>0){
				$manu_html .= "<div class='details'>Locations: " . $location . "</div>";
			}else $manu_html .= "<div class='details'>Location: " . $location . "</div>";
		}	
		$manu_html .= "<div class='details'>" . $manu['Note'] . "</div>";
		//Check if we should show custom manufacturer ad
		$ad = getManufacturerAd($slug,$display_name);
		$manu_html .= $ad;
		$manu_html .="</div><div class='banner-ad centered'>$banner_ad</div><div class='divider'></div><h3>Choose your Firearm</h3>";			
			
		if(isset($results['series'])){			
			$html .= "<h3>By Series</h3>";
			foreach($results['series'] AS $series){
				$html .= "<a class='button' href='" . $root . $slug . "/" . $series['series_slug'] . "/'>" . proper_case($series['mod_series']) . "</a>";
			}
			$html .= "<div class='divider'></div>";
		}

		if($colt){//---------------------------------------------------------------We're in Colttown
			if(isset($results['models'])){				
				$html .= "<h3>By Model</h3>";
				foreach($results['models'] AS $div_name => $division){
					if($div_name != 'nodiv'){
						$html .= "<h4 class='division_header'>" . $div_name . "</h4>";
						if($division[array_key_first($division)][0]['Note']!=''){
							$html .= "<p class='division_note'>" . str_replace("&break;","<br/>",$division[array_key_first($division)][0]['Note']) . "</p>";	
						}
					}			
					
					//Show all models with no category/mod_type
					if(is_array($division['none'])){
						foreach($division['none'] AS $model){
									//Check image Status
									if($model['Image_Status']=="Photo Avail"){
										$photos_avail = true;
										$photo_icon = '<div>&#128247</div>';
									}else $photo_icon = '';
									if($model['antique']==1){
										$antique = true;
										$antique_icon = '<div class="antique">A</div>';
									}else $antique_icon = '';
									//For Guntype Filtering
									$guntype_class = "";
									$guntypes = array();				
									if((strlen($model['mod_guntype']))>5){//Catch items with two guntypes
										$guntypes = explode(", ",$model['mod_guntype']);										
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
									$model_name = proper_case($model['mod_model']);		
									$model_subset = "";
									if($model['mod_model_subset']!='') $model_subset = "<br/><span>" . $model['mod_model_subset'] . "</span>";			
									$html .= "<a class='model button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'>" . $model_name . $model_subset . $photo_icon . $antique_icon . "</a>";
						}
					}	
					//Show models with categories
					foreach($division AS $sec_name=>$section){
						if($sec_name!='none'){//only show those with categories
							$html .= "<h4 class='mod_type_header'>" . str_replace("&break;","",$sec_name) . "</h4>";				
							foreach($section AS $model){
								//Check image Status
								if($model['Image_Status']=="Photo Avail"){
									$photos_avail = true;
									$photo_icon = '<div>&#128247</div>';
								}else $photo_icon = '';
								//For Guntype Filtering
								$guntype_class = "";
								$guntypes = array();				
								if((strlen($model['mod_guntype']))>5){//Catch items with two guntypes
									$guntypes = explode(", ",$model['mod_guntype']);										
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
								$model_name = proper_case($model['mod_model']);	
								$model_subset = "";
								if($model['mod_model_subset']!='') $model_subset = "<br/><span>" . $model['mod_model_subset'] . "</span>";		
								$html .= "<a class='model button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'>" . $model_name . $model_subset . $photo_icon . "</a>";
							}
						}
					}
				}
			}
		}else{
			//---------------------------------------------------------Not Colt
			if(isset($results['models'])){				
				$html .= "<h3>By Model</h3>";			
				//Show all models with no category/mod_type
				if(isset($results['models']['none'])){
					foreach($results['models']['none'] AS $model){						
								//Check image Status
									if($model['Image_Status']=="Photo Avail"){
										$photos_avail = true;
										$photo_icon = '<div>&#128247</div>';
									}else $photo_icon = '';
									if($model['antique']==1){
										$antique = true;
										$antique_icon = '<div class="antique">A</div>';
									}else $antique_icon = '';
								//For Guntype Filtering
								$guntype_class = "";
								$guntypes = array();				
								if((strlen($model['mod_guntype']))>5){//Catch items with two guntypes
									$guntypes = explode(", ",$model['mod_guntype']);										
								}else{
									$guntypes[0] = $model['mod_guntype'];								
								}
								if((is_array($guntypes)) && (count($guntypes)>0)){
									foreach($guntypes AS $guntype){
										$guntype_class .= " " . $guntype;
										$guntype_name = getGunTypeName($guntype);
										$guntype_list[$guntype] = $guntype_name;						
									}
								}
								//Check if this is a true model or just a note/image in the book
								if(!(stripos($model['mod_model'],"model not given",0)===false)){//not a real model
										if($model['mod_model_sort']!="10"){
											if($model['mod_item_note']!=""){
												$extra_note = str_replace('&#038;', '&', $model['mod_item_note']);
												$extra_note = str_replace('&break;', '<br/>', $extra_note);
												$html .= "<p class='extra_note'>" . $extra_note . "</p>";
											}									
											if($model['Image_Status']=="Photo Avail"){
												//Get images for this model to display for Manufacturer
												$manu_model = getGun($model['id']);										
												//Gallery
												if ($manu_model['mod_image_1'] != "") {
													$html .= "<div id='gallery' class='manu'>";
													$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_1'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';			
												
													if ($manu_model['mod_courtesy_1'] != "") {
														$html .= '<div class="courtesy">' . $manu_model['mod_courtesy_1'] . '</div>';			 
													}
													if ($manu_model['mod_image_2'] != "") {
														$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_2'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';
														
													}
													if ($manu_model['mod_image_3'] != "") {
														$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_3'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';				
													}
													if ($manu_model['mod_image_4'] != "") {
														$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_4'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';				
													}
													$html .= "</div>";
												}	
											}							
										}else{
											$html .= "<a class='model button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify("All Models Available") . "/'>All Models Available</a>";
										}
								}else{
									$model_name = proper_case($model['mod_model']);		
									$model_subset = "";
									if($model['mod_model_subset']!='') $model_subset = "<br/><span>" . $model['mod_model_subset'] . "</span>";	
									$html .= "<a class='model button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'>" . $model_name . $model_subset . $photo_icon . $antique_icon . "</a>";
								}
					}
				}//end isset check for $results['models']['none']	
				//Show models with categories
				foreach($results['models'] AS $sec_name=>$section){
					if($sec_name!='none'){//only show those with categories
						$html .= "<h4 class='mod_type_header'>" . str_replace("&break;","",$sec_name) . "</h4>";				
						foreach($section AS $model){
							//Check image Status
								if($model['Image_Status']=="Photo Avail"){
									$photos_avail = true;
									$photo_icon = '<div>&#128247</div>';
								}else $photo_icon = '';
							//For Guntype Filtering
							$guntype_class = "";
							$guntypes = array();				
							if((strlen($model['mod_guntype']))>5){//Catch items with two guntypes
								$guntypes = explode(", ",$model['mod_guntype']);										
							}else{
								$guntypes[0] = $model['mod_guntype'];								
							}
							if((is_array($guntypes)) && (count($guntypes)>0)){
								foreach($guntypes AS $guntype){
									$guntype_class .= " " . $guntype;
									$guntype_name = getGunTypeName($guntype);
									$guntype_list[$guntype] = $guntype_name;						
								}
							}

							//Check if this is a true model or just a note/image in the book
							if(!(stripos($model['mod_model'],"model not given",0)===false)){//not a real model							
								if($model['mod_item_note']!=""){
										$extra_note = str_replace('&#038;', '&', $model['mod_item_note']);
										$extra_note = str_replace('&break;', '<br/>', $extra_note);
										$html .= "<p class='extra_note'>" . $extra_note . "</p>";
									}
									if($model['Image_Status']=="Photo Avail"){
										//Get images for this model to display for Manufacturer
										$manu_model = getGun($model['id']);										
										//Gallery
										if ($manu_model['mod_image_1'] != "") {
											$html .= "<div id='gallery' class='manu'>";
											$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_1'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';			
										
											if ($manu_model['mod_courtesy_1'] != "") {
												$html .= '<div class="courtesy">' . $manu_model['mod_courtesy_1'] . '</div>';			 
											}
											if ($manu_model['mod_image_2'] != "") {
												$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_2'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';
												
											}
											if ($manu_model['mod_image_3'] != "") {
												$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_3'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';				
											}
											if ($manu_model['mod_image_4'] != "") {
												$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $manu_model['mod_image_4'] . '" alt="' . $manu_model['mod_model'] . '" /></div>';				
											}
											$html .= "</div>";
										}	
									}
							}else{
								$model_name = proper_case($model['mod_model']);
								$model_subset = "";
								if($model['mod_model_subset']!='') $model_subset = "<br/><span>" . $model['mod_model_subset'] . "</span>";	
								$html .= "<a class='model button". $guntype_class."' href='" . $root . $slug . "/" . $model['id'] . "/" . slugify($model['mod_model']) . "/'>" . $model_name . $model_subset . $photo_icon . "</a>";
							}
						}
					}
				}
			}
		}//end not Colt
		//Create Photo Link
		//Check for images for this manufacturer
		$photo_html = "";
		if($photos_avail){
			$photo_html .= "<h3>By Photos</h3>";
			if((isset($_GET['filter']))&&(isset($_GET['value']))){
				$photo_html .= "<a class='button' href='" . $root . $slug . "/by-photo/?filter=guntype&value=" .$filter_value. "'>Browse By Photo &#128247;</a>";
			}else $photo_html .= "<a class='button' href='" . $root . $slug . "/by-photo/'>Browse By Photo &#128247;</a>";
			$photo_html .= "<div class='divider'></div>";
		}	
		//Create Gun Type drop down		
		$dropdown = '<div class="announcement">Results filtered by <select name="guntype_filter" id="guntype_filter">';
		$dropdown .= '<option value="">Show All Gun Types</option>';		
		foreach($guntype_list AS $key => $gt){
			$dropdown .= '<option value="' . $key . '">' . $gt . '</option>';
		}
		$dropdown .= "</select></div>";
		if(!isset($results['models'])){
			$html .= 'No Models to Display';
		}else{
			$html .= '<div class="page-bottom-ads">';
			$html .= showAd("large-rect1");
			$html .= showAd("large-rect2") . "</div>";			
		}
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
		$(".model.button").show();
	}else{
		$(".model.button").hide();
		$("." + guntype).show();
	}						
}
</script>
EOD;
$DFPTargeting .= "googletag.pubads().setTargeting('GVmanufacturer',['" . addslashes($display_name) . "']);";
}
?>



