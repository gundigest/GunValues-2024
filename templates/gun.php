<?php
//Single Gun Page Content
if(!(is_numeric($gun_id))){
		$page_name = "Gun Model Not Found";
		$html .= <<<EOD
		<h2 id="mfg_title">Gun Model Not Found</h2>		
		<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
	}else{
		$manu = getManufacturer($slug);		
		$results = getGun($gun_id);
		//check that the slug is equal to the model's name
		if($model_slug != slugify($results['mod_model'])){
			//Redirect
			header('Location: ' . $root . $slug);
			exit();
		}
		$manu_display = proper_case($manu['make']);
		if (!(stripos($results['mod_model'],"model not given",0)===false)){
			$model_display = proper_case("All Models Available");		
		}else $model_display = proper_case($results['mod_model']);		
		$display_name =$manu_display . " " . $model_display;
		$breadcrumbs .= '<div class="breadcrumbs"><div class="breadcrumbs_text"><a href="' . $root . $slug . '">' . $manu_display . '</a> &gt; ' . $model_display . '</div></div>';
		$page_name = $display_name;
		if($plan_active){
			$user_id = $_SESSION['user_id'];
		}else $user_id = 0;	
		log_history($user_id,"Model",null,$slug,$gun_id,null);		
		//Ads
		$banner_ad = showAd("banner");		
$html .= <<<EOD
		<div class="banner-ad centered">$banner_ad</div><h2 id="mfg_title">$model_display</h2>		
		<div class="two-third-page">		
EOD;
		//Gallery
		if ($results['mod_image_1'] != "") {
			$html .= "<div id='gallery'>";
			$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $results['mod_image_1'] . '" alt="' . $results['mod_model'] . '" /></div>';			
		
			if ($results['mod_courtesy_1'] != "") {
				$html .= '<div class="courtesy">' . $results['mod_courtesy_1'] . '</div>';			 
			}
			if ($results['mod_image_2'] != "") {
				$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $results['mod_image_2'] . '" alt="' . $results['mod_model'] . '" /></div>';
				
			}
			if ($results['mod_image_3'] != "") {
				$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $results['mod_image_3'] . '" alt="' . $results['mod_model'] . '" /></div>';				
			}
			if ($results['mod_image_4'] != "") {
				$html .= '<div><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $results['mod_image_4'] . '" alt="' . $results['mod_model'] . '" /></div>';				
			}
			$html .= "</div>";
		}
		$html_header = '<div class="model_display">';
		$html_footer = '</div>';	
		
		if ($results['mod_item_note'] != "") {	
			$long_description = str_replace("&#038;break;","<br/>",$results['mod_item_note']);				
			$long_description = str_replace("&break;","<br/>",$long_description);
			$meta_description = htmlentities(substr($long_description,0,320));
			$html .= '<div class="description">' . $long_description . '</div>';
		}else{
			$meta_description = "Pricing Information for the " . $manu_display . "  " . $model_display;
		}
		//for "model not given" when the gun description is only in Manufacturer
		if ((!(stripos($results['mod_model'],"model not given",0)===false))&&($results['mod_item_note'] == "")&&($manu['Note'] != "")&&($results['mod_model_sort'] == "10")){
			$long_description = str_replace("&#038;break;","<br/>",$manu['Note']);				
			$long_description = str_replace("&break;","<br/>",$long_description);
			$meta_description = htmlentities(substr($long_description,0,320));
			$html .= '<div class="description">' . $long_description . '</div>';
		}
		if ($results['mod_item_subnote'] != "") {
			$item_subnote = str_replace("&#038;break;","<br/>",$results['mod_item_subnote']);				
			$item_subnote = str_replace("&break;","<br/>",$item_subnote);	
			$html .= '<div class="description">' . $item_subnote . '</div>';   
		}
		if ($results['mod_guntype'] != "") {			
			if((strlen($results['mod_guntype']))>5){//Catch items with two guntypes				
				$guntypes = explode(", ",$results['mod_guntype']);										
			}else{
				$guntypes[0] = $results['mod_guntype'];					
			}
			$guntype_list = "";
			if((is_array($guntypes)) && (count($guntypes)>0)){
				foreach($guntypes AS $guntype){				
					$guntype_name = getGunTypeNameSingle($guntype);
					$guntype_list .= $guntype_name . ", ";				
				}
				$guntype_list = trim($guntype_list,", ");
			}else $guntype_list = getGunTypeNameSingle($guntypes[0]);				
				$html .= $html_header . '<td class="textlayer_black" align="left">Gun Type: </td><td  class="textlayer_blue">' . $guntype_list . '</td>' . $html_footer;					   		
		}
		//Check for Antiques
		if($results['antique'] == 1) $html .= "<div class='antique_tag'>Antique</div>";
	//Check if we should show custom model ad
	$ad = getModelAd($gun_id,$model_display);
	$html .= $ad;
	//Check for editions and change layout as appropriate	
	if(!($results['editions']===false)){		
		//Check for Commemorative Editions and display accordingly
		
		if($results['id'] == "15694"){//only one "model" is set with these specific requirements right now
			foreach($results['editions'] AS $edition) {				

				if ($edition['model'] != "") {					
					$html .= $html_header . '<strong>' . $edition['mod_model'] . '</strong><br/>';										
					$html .=  '<table class="series-pricing"><thead><td>Current Value</td><td>Issue Price</td><td>No. Manufactured</td></thead><tr>';
					$html .='<!–sse–>';//To stop scraping	
					if($plan_active){
						$html .=  '<td><sup>$</sup>' . $edition['mod_nib'] . '</td>';					
						$html .=  '<td class="exc"><sup>$</sup>' . $edition['mod_vg'] . '</td>';				
						$html .=  '<td>' . $edition['mod_fair'] . '</td>';									
					}else{
						$html .=  '<td class="blur"><sup>$</sup>0000</td>';					
						$html .=  '<td class="exc blur"><sup>$</sup>0000</td>';				
						$html .=  '<td class="blur">0000</td>';
						$html .=  '</tr><tr><td colspan="3" class="pricing_nosub"><a href="' . $root . 'login/" class="button">Login</a><a href="' . $root . 'register/" class="button bright right"><span>Get Prices</span></td>';												
					}
					$html .='<!–/sse–>';//End stop scraping code
					$html .=  '</tr></table>' . $html_footer;				
				}
				
			}
		}else{
			foreach($results['editions'] AS $edition) {	
				if ($edition['mod_model_subset'] != "") {
					$html .= $html_header . '<strong>' . $edition['mod_model_subset'] . '</strong><br/>';
					if ($edition['mod_item_note'] != "") {
						$item_note = str_replace("&#038;break;","<br/>",$edition['mod_item_note']);				
						$item_note = str_replace("&break;","<br/>",$item_note);	
						$html .= '<p class="description">' . $item_note . '</p>';   
					}
					if ($edition['mod_image_1'] != "") {						
						$html .= '<div class="edition_image"><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/' . $edition['mod_image_1'] . '" alt="' . $edition['mod_model_subset'] . '" /></div>';								
						if ($edition['mod_courtesy_1'] != "") {
							$html .= '<div class="courtesy">' . $edition['mod_courtesy_1'] . '</div>';			 
						}
					}
					
					$html .=  '<table class="series-pricing set-width"><thead><td>NIB</td><td>Exc</td><td>V.G.</td><td>Good</td><td>Fair</td><td>Poor</td></thead><tr>';
					$html .='<!–sse–>';//To stop scraping					
					if($plan_active){
						$html .=  '<td>$' . $edition['mod_nib'] . '</td>';
						$html .=  '<td class="exc"><sup>$</sup>' . str_replace('&#038;','&',$edition['mod_exc']) . '</td>';
						$html .=  '<td class="vg"><sup>$</sup>' . str_replace('&#038;','&',$edition['mod_vg']) . '</td>';				
						$html .=  '<td class="good"><sup>$</sup>' . str_replace('&#038;','&',$edition['mod_good']) . '</td>';				
						$html .=  '<td class="fair"><sup>$</sup>' . str_replace('&#038;','&',$edition['mod_fair']) . '</td>';				
						$html .=  '<td class="poor"><sup>$</sup>' . str_replace('&#038;','&',$edition['mod_poor']) . '</td>';				
					}else{
						$html .=  '<td class="blur"><sup>$</sup>0000</td>';
						$html .=  '<td class="exc blur"><sup>$</sup>0000</td>';
						$html .=  '<td class="vg blur"><sup>$</sup>0000</td>';				
						$html .=  '<td class="good blur"><sup>$</sup>0000</td>';				
						$html .=  '<td class="fair blur"><sup>$</sup>0000</td>';				
						$html .=  '<td class="poor blur"><sup>$</sup>0000</td>';
						$html .=  '</tr><tr><td colspan="6" class="pricing_nosub"><a href="' . $root . 'login/" class="button">Login</a><a href="' . $root . 'register/" class="button bright right">Get Prices</a></td>';												
					}
					$html .='<!–/sse–>';//End stop scraping code
					$html .=  '</tr></table>' . $html_footer;					
				}
			}
		}
		
	}else{//NO editions, use single-model layout
		if($results['antique'] == 1){
			if($plan_active){
				if ($results['mod_nib'] != "") {
					$html .= '<div class="nib price-display"><span class="title">Excellent</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_nib']) . '<!–/sse–></span></div>';						
				}
				if ($results['mod_exc'] != "") {
					$html .= '<div class="exc price-display"><span class="title">Fine</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_exc']) . '<!–/sse–></span></div>';			
				}			
				if ($results['mod_vg'] != "") {
					$html .= '<div class="vg price-display"><span class="title">Very Good</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_vg']) . '<!–/sse–></span></div>';			
				} 
				if ($results['mod_good'] != "") {
					$html .= '<div class="good price-display"><span class="title">Good</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_good']) . '<!–/sse–></span></div>';			
				}      
				if ($results['mod_fair'] != "") {
					$html .= '<div class="fair price-display"><span class="title">Fair</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_fair']) . '<!–/sse–></span></div>';			
				}
				if ($results['mod_poor'] != "") {
					$html .= '<div class="poor price-display"><span class="title">Poor</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_poor']) . '<!–/sse–></span></div>';			
				}
			}else{
				if ($results['mod_nib'] != "") {
					$html .= '<div class="nib price-display"><span class="title">Excellent</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';						
				}
				if ($results['mod_exc'] != "") {
					$html .= '<div class="exc price-display"><span class="title">Fine</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}			
				if ($results['mod_vg'] != "") {
					$html .= '<div class="vg price-display"><span class="title">Very Good</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				} 
				if ($results['mod_good'] != "") {
					$html .= '<div class="good price-display"><span class="title">Good</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}      
				if ($results['mod_fair'] != "") {
					$html .= '<div class="fair price-display"><span class="title">Fair</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}
				if ($results['mod_poor'] != "") {
					$html .= '<div class="poor price-display"><span class="title">Poor</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}
				$html .=  '<div class="pricing_nosub"><a href="' . $root . 'login/" class="button center">Login</a><a href="' . $root . 'register/" class="button bright right">Get Prices</a></div>';				
				
			}
		}else{
			if($plan_active){
				if ($results['mod_nib'] != "") {
					$html .= '<div class="nib price-display"><span class="title">NIB</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_nib']) . '<!–/sse–></span></div>';						
				}
				if ($results['mod_exc'] != "") {
					$html .= '<div class="exc price-display"><span class="title">Excellent</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_exc']) . '<!–/sse–></span></div>';			
				}			
				if ($results['mod_vg'] != "") {
					$html .= '<div class="vg price-display"><span class="title">Very Good</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_vg']) . '<!–/sse–></span></div>';			
				} 
				if ($results['mod_good'] != "") {
					$html .= '<div class="good price-display"><span class="title">Good</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_good']) . '<!–/sse–></span></div>';			
				}      
				if ($results['mod_fair'] != "") {
					$html .= '<div class="fair price-display"><span class="title">Fair</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_fair']) . '<!–/sse–></span></div>';			
				}
				if ($results['mod_poor'] != "") {
					$html .= '<div class="poor price-display"><span class="title">Poor</span><span class="price"><!–sse–><sup>$</sup>' . str_replace('&#038;','&',$results['mod_poor']) . '<!–/sse–></span></div>';			
				}
			}else{
				if ($results['mod_nib'] != "") {
					$html .= '<div class="nib price-display"><span class="title">NIB</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';						
				}
				if ($results['mod_exc'] != "") {
					$html .= '<div class="exc price-display"><span class="title">Excellent</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}			
				if ($results['mod_vg'] != "") {
					$html .= '<div class="vg price-display"><span class="title">Very Good</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				} 
				if ($results['mod_good'] != "") {
					$html .= '<div class="good price-display"><span class="title">Good</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}      
				if ($results['mod_fair'] != "") {
					$html .= '<div class="fair price-display"><span class="title">Fair</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}
				if ($results['mod_poor'] != "") {
					$html .= '<div class="poor price-display"><span class="title">Poor</span><span class="price blur"><!–sse–><sup>$</sup>0000<!–/sse–></span></div>';			
				}
				$html .=  '<div class="pricing_nosub"><a href="' . $root . 'login/" class="button center">Login</a><a href="' . $root . 'register/" class="button bright right">Get Prices</a></div>';				
				
			}
		}	
		
		

		if ($results['mod_subhead'] != "") {			
			$html .=  $html_header . '<td class="textlayer_black" align="left">Subhead: </td><td width: 70%;" class="textlayer_blue">' . $results['mod_subhead'] .  '</td>' . $html_footer;			
		}	
		if ($results['mod_mfg_division'] != "") {
			$html .=  $html_header . '<td class="textlayer_black">Mfg Division: </td><td  class="textlayer_blue">' . $results['mod_mfg_division'] . '</td>' . $html_footer;
		}
		
		if ($results['mod_british'] != "") {
			$html .=  $html_header . '<td class="textlayer_black" align="left">British: </td><td  class="textlayer_blue">' . $results['mod_british'] . '</td>' . $html_footer;
		}                          
		
		if ($results['mod_model_subset'] != "") {			
			$html .=  $html_header . '<td class="textlayer_black" align="left">Model Subset: </td><td class="textlayer_blue">' . $results['mod_model_subset'] . '</td>' . $html_footer;
		}

		if ($results['mod_cutline_1'] != "") {
			$html .=  $html_header . '<td class="textlayer_black" align="left">Cutline: </td><td  class="textlayer_blue">' . $results['mod_cutline_1'] . '</td>' . $html_footer;			
		}
		
		//Series Name/Link
		if ($results['mod_series'] != "") {
			$html .= '<h4>In Series:</h4>';
			$html .= '<a class="button" href="' . $root . $slug . "/" . $results['series_slug'] . '">' . proper_case($results['mod_series']) . '</a>';			
		}
		//Other guns with the same name (subsets)
		$sameName = getSameNameGuns($gun_id,$results['mod_model'],$manu['id']);
		if($sameName){
			$html .= '<h4>Similar Models:</h4>';
			foreach($sameName AS $link){
				//Check image Status
				if($link['Image_Status']=="Photo Avail"){
					$photos_avail = true;
					$photo_icon = '<div>&#128247</div>';
				}else $photo_icon = '';
				$model_name = proper_case($link['mod_model']);	
				$model_subset = "";
				if($link['mod_model_subset']!='') $model_subset = "<br/><span>" . $link['mod_model_subset'] . "</span>";	
				$html .= "<a class='model button' href='" . $root . $slug . "/" . $link['id'] . "/" . slugify($link['mod_model']) . "/'>" . $model_name . $model_subset . $photo_icon . "</a>";
			}
		}
}		
//Manufacturer Name/Link in right side column	
		$html .= "</div><div class='one-third-page'>";
			$html .= $html_header . '<h4>Manufactured By</h4>';
			$html .= '<a class="button" href="' . $root . $slug . '">' . $manu_display . '</a>' . $html_footer;		
		//adsense ads
		$html .= showAd("large-rect1");
		$html .= showAd("large-rect2") . "</div>";

}
		//For Ads
		$DFPTargeting = "googletag.pubads().setTargeting('GVmodel',['" . addslashes($model_display) . "']);";
		$DFPTargeting .= "googletag.pubads().setTargeting('GVmanufacturer',['" . addslashes($manu_display) . "']);";
		$DFPTargeting .= "googletag.pubads().setTargeting('GVguntype',['" . $guntype_list . "']);";
?>



