<?php
//Search Page Content
if(strlen($term)<1){
		$html = "No Search Term";
	}else{
		$term = urldecode($term);
		$term_parts = array_filter(explode('|', $term));		
		$results = searchGuns($term_parts[0],$term_parts[1]);
		$display_term = str_replace("/","",$term_parts[0]);
		//var_dump($results["models"]);
		//$manufacturers = array();
		$manufacturers = "";
		$manu_key = 0;//TODO see if we can ditch this
		$models = "";
		$current_manufacturer = "";	
		if($plan_active){
			$user_id = $_SESSION['user_id'];
		}else $user_id = 0;	
		log_history($user_id,"Search Results",$term,null,null,null);	
		if(!$results){
			$html = "No Results were found for that Search Term.";
			//TODO ADD Form here
		}else{
			foreach($results["models"] AS $result){
				$manu_slug = $result['slug'];
				if($result['make']!=$current_manufacturer){
					$manufacturers .= "<div class='filter_item'><input type='checkbox' class='search-filter' id='" . $manu_slug . "' name='" . $manu_slug . "' checked='checked'/><label for='" . $manu_slug . "'>" . proper_case($result['make']) . "</label></div>";
					$current_manufacturer = $result['make'];
					$models .= "<h4 class='" . $manu_slug . " search_item_header'><a name='" . $manu_slug . "' href='" . $root .  $manu_slug . "' >" . $current_manufacturer . "</a></h4>";
					$manu_key++;
				}			
				if(strlen($result['mod_model'])>1){//check this is not a series
					$model_slug = slugify($result['mod_model']);
					$models .= "<a href='" . $root . $manu_slug . "/" . $result['id'] . "/" . $model_slug . "' class='button search_item " . $manu_slug . "'>" . proper_case($result['mod_model']) . "</a>";
				}else{
					if(strlen($result['mod_series'])>1){
						$models .= "<a href='" . $root . $manu_slug . "/" . $result['series_slug'] . "' class='button search_item " . $result['series_slug'] . "'>" . proper_case($result['mod_series']) . "</a>";
					}
				}
			}	
			$manufacturers .= "<div class='filter_item'><a id='clear_all' class='button'/>Clear All</a></div>";
			$html = "<div class='side-bar'><div class='search-box-text'>Filter Results</div>" . $manufacturers . "</div><div class='search_main'><div class='search-box-text'>Search Results for '" . $display_term . "'</div>" . $models . "</div>";
		$head = <<<EOD
<script type="text/javascript">
	$(document).ready(function() {
		$('.search-filter').click( function(){			
			id = $(this).attr("id");			
			if( $(this).is(':checked') ){
				$("." + id).show();
			}else{
				$("." + id).hide();
			}
		});
		$('#clear_all').click( function(){	
			if($(this).hasClass('off')){
				$('.search-filter').each(function(){
					$(this).prop('checked', true);
				});
				$(this).removeClass('off');
				$('.search_item_header').show();
				$('.search_item').show();
				$('#clear_all').html("Clear All");
			}else{
				$('.search-filter').each(function(){
					$(this).prop('checked', false);
				});
				$(this).addClass('off');
				$('.search_item_header').hide();
				$('.search_item').hide();
				$('#clear_all').html("Show All");				
			}
		});
	});

</script>
EOD;
	}
}
?>



