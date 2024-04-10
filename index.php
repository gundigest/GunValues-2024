<?php
//Main display page for Gun Values
include("model/config.php");
//These values are needed to populate the page below
$html = $head = $breadcrumbs = $DFPTargeting = $defineAdSlots = $meta_description = "";
$subheader = "<div class='top-spacer'></div>";
$logged_in = checkLoggedIn();
$plan_active = checkPlanActive();
$is_admin = checkAdmin();
//Get Path
$type_path = $_SERVER['REQUEST_URI'];
$url_elements = parse_url($type_path);
if($remove_path!=""){
	$type_path = str_replace($remove_path,"",$url_elements['path']);
}else $type_path = $url_elements['path'];
$path_parts = array_filter(explode('/', $type_path));
$path_parts = array_values($path_parts);
$items = sizeof($path_parts);
$show_back_button = true;
$page_routed = false;
//Check for Notices for the User
$notice = false;
if(isset($_GET['not'])){
	$notice = true;
}
//Maintenance mode enabled for all but one IP address
$show_maintenance = false;
$maintenance = false;//CHANGE TO TRUE for Maintenance Mode
if($maintenance){
	$ip_address = $_SERVER['REMOTE_ADDR'];
	//echo $ip_address;
	if($ip_address == '172.68.133.198'||$ip_address='76.245.71.8'){//CHANGE TO YOUR IP for Maintenance Mode
		$show_maintenance = true;
		include("templates/maintenance.php");
		$page_routed = true;
	}
}
if(!$show_maintenance){
	if($items==0){	
		$show_back_button = false;
		include("templates/home.php");	
		$page_routed = true;
	}else{
		/*if(!(stripos($type_path,".php",0)===false)){//display page as called
			$show_back_button = false;
			include($type_path);
			$page_routed = true;
		}else*/
		if(!(stripos($type_path,"login",0)===false)){//display Login Page		
			include("templates/login.php");
			$page_routed = true;
		}elseif(!(stripos($type_path,"logout",0)===false)){//display Login Page
			$show_back_button = false;
			include("templates/logout.php");
			$page_routed = true;
		}elseif(!(stripos($type_path,"forgot_ty",0)===false)){//display Forgot Thank You
			$show_back_button = false;
			include("templates/forgot_ty.php");
			$page_routed = true;
		}elseif(!(stripos($type_path,"forgot",0)===false)){//display Forgot Password
			$show_back_button = false;
			include("templates/forgot.php");
			$page_routed = true;
		}elseif(!(stripos($type_path,"reset_password",0)===false)){//display Reset Password
			$show_back_button = false;
			include("templates/reset_password.php");
			$page_routed = true;				
		}elseif(!(stripos($type_path,"grading-system",0)===false)){//display Grading System		
			include("templates/grading-system.php");
			$page_routed = true;		
		}elseif((stripos($type_path,"about",0)===1)){//display About		
			include("templates/about.php");
			$page_routed = true;			
		}elseif(!(stripos($type_path,"faq",0)===false)){//display FAQ		
			include("templates/faq.php");
			$page_routed = true;		
		}elseif(!(stripos($type_path,"privacy",0)===false)){//display Privacy
			include("templates/privacy.php");
			$page_routed = true;		
		}elseif(!(stripos($type_path,"terms",0)===false)){//display Terms		
			include("templates/terms.php");
			$page_routed = true;				
		}elseif(!(stripos($type_path,"register_final",0)===false)){//display Registration Page
			include("templates/register_final.php");	
			$page_routed = true;
		}elseif(!(stripos($type_path,"register",0)===false)){//display Registration 2nd Page
			include("templates/register.php");	
			$page_routed = true;
		}elseif(!(stripos($type_path,"account",0)===false)){//display Account Page
			include("templates/account.php");		
			$page_routed = true;
		}elseif(!(stripos($type_path,"addplan",0)===false)){//display Add a Plan Page
			include("templates/addplan.php");		
			$page_routed = true;
		}elseif(!(stripos($type_path,"updatepayment",0)===false)){//display Update Payment Page
			include("templates/updatepayment.php");		
			$page_routed = true;			
		}elseif(!(stripos($type_path,"cancel",0)===false)){//display Cancellation Page
			include("templates/cancel.php");		
			$page_routed = true;
		}elseif(!(stripos($type_path,"contact",0)===false)){//display Contact Page
			$show_back_button = false;
			include("templates/contact.php");
			$page_routed = true;		
		}elseif(stripos($type_path,"/search/",0)===0){//display Search Results
			//Get Search Term
			$term = str_replace("/search/","",$type_path);
			$page_name = "Search";
			include("templates/search.php");
			$page_routed = true;
		}else{	
			if($items == 1){//we are on a manufacturer Letter or individual page
				if(!(stripos($path_parts[0],"manufacturers",0)===false)){//manufacturer letter page
					$letter = str_replace("manufacturers-","",$path_parts[0]);				
					include("templates/manufacturer-letter.php");
					$page_routed = true;				
				}else{//individual manufacturer page
					$slug = $path_parts[0];				
					include("templates/manufacturer.php");
					$page_routed = true;
				}
			}elseif($items == 2){//we are on a series or photo page
				if(!(stripos($path_parts[1],"by-photo",0)===false)){//manufacturer photo page
					$slug = $path_parts[0];							
					include("templates/manufacturer-photo.php");
					$page_routed = true;
				}else{//individual series page				
					$slug = $path_parts[0];
					$series_slug = $path_parts[1];
					include("templates/series.php");
					$page_routed = true;
				}	
			}elseif($items == 3){//we are on a guntype or single gun page
				if(!(stripos($path_parts[0],"guntype",0)===false)){//guntype page
					$gid = $path_parts[1];				
					include("templates/manufacturer-guntype.php");
					$page_routed = true;
				}else{//single gun page
					$slug = $path_parts[0];
					$gun_id = $path_parts[1];
					$model_slug = $path_parts[2];
					include("templates/gun.php");
					$page_routed = true;
				}
			}
		}
	}
}//end maintenance check
if(!$page_routed){
	include("templates/404.php");
}
//After creating appropriate values, echo to page
include("includes/html_header.php");
//echo "<!–sse–>" . $html . "<!–/sse–>";
echo $html;
include("includes/footer.php");
if($notice){
	echo '<div class="notice_modal active">' . $_GET['not']. '<div class="close">&times;</div></div>';
	echo '<div class="modal-bg active"></div>';
$js = <<<EOD
'<script type="text/javascript"> 
$(document).ready(function() {	
	$(".close").click(function() {
    //modal close
		$('.modal-bg').removeClass('active');
		$('.notice_modal').removeClass('active');		
	});
});
</script>
EOD;
echo $js;
}
?>