<?php
//Manufacturer Photo Page Content
if((!(isset($slug)))||(!(isset($series_slug)))){
				$page_name = "Series Not Found";
		$html .= <<<EOD
				<h2 id="mfg_title">Series Not Found</h2>		
				<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
	}else{
		$manu = getManufacturer($slug);		
		$results = getSeries($series_slug,$slug);
		//Ads
		$banner_ad = showAd("banner");
if(!$results){
				$page_name = "Series Not Found";	
		$html .= <<<EOD
				<div class="banner-ad centered">$banner_ad</div><h2 id="mfg_title">Series Not Found</h2>		
				<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
}else{
		$manu_display = proper_case($manu['make']);
		$series_display = proper_case($results[0]['mod_series']);
		$display_name = $manu_display . " " . $series_display;
		if($plan_active){
			$user_id = $_SESSION['user_id'];
		}else $user_id = 0;	
		log_history($user_id,"Series",null,$slug,null,$series_display);
		$page_name = $display_name . " Models";
		$breadcrumbs .= '<div class="breadcrumbs"><div class="breadcrumbs_text"><a href="' . $root . $slug . '">' . $manu_display . '</a> &gt; ' . $series_display . '</div></div>';
		$html .= '<div class="banner-ad">' . $banner_ad . '</div><h2 id="mfg_title">' . $series_display . '</h2>';
		//Check if we should show custom manufacturer ad
		$ad = getSeriesAd($slug,$series_slug,$series_display);
		$html .= $ad;
		$html .= '<div class="two-third-page series-page">';
		
		if ($results[0] != "") {    //The first one pulled is the Series header 
			if ($results[0]['long_description'] != "") {
				$html .= '<div class="series-description">' . $results[0]['mod_item_note'] . '</div>';				
			}
		}
		$count = 0;
		foreach($results AS $result){
			if (($result['mod_model'] != "")&&($count>0)) {				
				$html .= '<h4 class="edition"><a href="' . $root . $slug . "/" . $result['id'] . "/" . slugify($result['mod_model']) . '/">' . proper_case($result['mod_model']) . '</a></h4>';				
				$html .= '<hr/>';
				$html .='<div class="half-page">';
				if ($result['mod_image_1'] != "") {
					$html .='<div class="gallery"><a href="' . $root . $slug . "/" . $result['id'] . "/" . slugify($result['mod_model']) . '/"><img src="' .$root. 'gunValues/images/Firearms2017_Fall/large/'.$result['mod_image_1'] . '" alt="' . $result['mod_model'] . '" /></a></div>';				
					if ($result['mod_courtesy_1'] != "") { 
						$html .='<div class="courtesy">' . $result['mod_courtesy_1'] . '</div>';			 
					}					
				}else{
					$html .='<div class="gallery no-image">No Photo Available</div>';	
				}
				
				if ($result['mod_item_note'] != "") {					
					$long_description = str_replace("&#038;break;","<br/>",$result['mod_item_note']);					
					$html .='<div class="series-description">' . $long_description . '</div>';								
				}
				$html .= "</div>";
				$html .='<div class="half-page">';								
				if ($result['mod_nib'] != "") {				
						$html .= '<table class="series-pricing"><thead><td>NIB</td><td>Exc</td><td>V.G.</td><td>Good</td><td>Fair</td><td>Poor</td></thead><tr>';
						$html .='<!–sse–>';//To stop scraping
						if($plan_active){
							$html .= '<td><sup>$</sup>' . $result['mod_nib'] . '</td>';
							$html .= '<td class="exc"><sup>$</sup>' . $result['mod_exc'] . '</td>';
							$html .= '<td class="vg"><sup>$</sup>' . $result['mod_vg'] . '</td>';				
							$html .= '<td class="good"><sup>$</sup>' . $result['mod_good'] . '</td>';				
							$html .= '<td class="fair"><sup>$</sup>' . $result['mod_fair'] . '</td>';				
							$html .= '<td class="poor"><sup>$</sup>' . $result['mod_poor'] . '</td>';				
						}else{
							$html .= '<td class="blur"><sup>$</sup>0000</td>';
							$html .= '<td class="exc blur"><sup>$</sup>0000</td>';
							$html .= '<td class="vg blur"><sup>$</sup>0000</td>';				
							$html .= '<td class="good blur"><sup>$</sup>0000</td>';				
							$html .= '<td class="fair blur"><sup>$</sup>0000</td>';				
							$html .= '<td class="poor blur"><sup>$</sup>0000</td>';
							$html .= '</tr><tr><td colspan="6" class="pricing_nosub"><a href="' . $root . 'login/" class="button">Login</a><a href="' . $root . 'register/" class="button bright right">Get Prices</a></td>';												
						}
						$html .='<!–/sse–>';//End stop scraping code
						$html .= '</tr></table>';										
				}				
				
				//Button to view model individually			
				if($count>0){
					$html .= "<a class='nav-button small' href='" . $root . $slug . "/" . $result['id'] . "/" . slugify($result['mod_model']) . "/'>View Model Details</a>";   				
				}
				$html .= '</div>';			
			}else 	$count++;
		}
		$html_header = '<div class="model_display">';
		$html_footer = '</div>';	
//Manufacturer Name/Link in right side column	
		$html .= "</div><div class='one-third-page'>";
		$html .= $html_header . '<h4>Manufactured By</h4>';
		$html .= '<a class="button" href="' . $root . $slug . '">' . $manu_display . '</a>' . $html_footer;			
		$html .= showAd("large-rect1");
		$html .= showAd("large-rect2") . "</div>";
}
	}		
?>



