<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Main display page for Gun Values Admin
include("../model/config.php");
include("../model/admin_functions.php");
include("../includes/functions.php");
setDefines();

//These values are needed to populate the page below
$html = $head = $breadcrumbs = $DFPTargeting = $defineAdSlots = $meta_description = "";
$subheader = "<div class='top-spacer'></div>";
$logged_in = checkLoggedIn();
$is_admin = checkAdmin();
if(!$is_admin){
	header("Location: " . $root); /* Redirect browser if not an admin */
	exit();
}
//Get Path
$type_path = $_SERVER['REQUEST_URI'];
$url_elements = parse_url($type_path);
$type_path = str_replace($remove_path . "admin/","",$url_elements['path']);
$path_parts = array_filter(explode('/', $type_path));
$path_parts = array_values($path_parts);
$items = sizeof($path_parts);
$show_back_button = false;
$show_admin_button = true;
$page_routed = false;
if($items==0){
	$html = "we are on the index page";
	$show_admin_button = false;
	include("templates/main.php");
	$page_routed = true;
}else{
	if(!(stripos($type_path,"recurring_report",0)===false)){//display Recurring Report
		include("templates/recurring_report.php");
		$page_routed = true;
	}elseif(!(stripos($type_path,"payments_report",0)===false)){//display Payments Report
		include("templates/payments_report.php");
		$page_routed = true;
	}elseif(!(stripos($type_path,"user_account",0)===false)){//display User_account
		include("templates/user_account.php");
		$page_routed = true;
	}elseif(!(stripos($type_path,"search_users",0)===false)){//display Search results page
		include("templates/search_users.php");
		$page_routed = true;
	}elseif(!(stripos($type_path,"update_payment",0)===false)){//display Payment Info Update page
		include("templates/update_payment.php");
		$page_routed = true;
	}elseif(!(stripos($type_path,"users_report",0)===false)){//display User Trends Report page
		include("templates/users_report.php");
		$page_routed = true;
	}
}
if(!$page_routed){
	include("../templates/404.php");
}
//After creating appropriate values, echo to page
include("../includes/html_header.php");
echo $html;
include("../includes/footer.php");
?>