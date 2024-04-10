<?php 
//Page title for all pages. Requires $page_name Variable
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title><?php if($page_name!="") echo $page_name . " :: ";?>Gun Values by Gun Digest</title>
  <meta name="description" content="<?php echo $meta_description?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Gun Digest">
  <link rel="stylesheet" href="<?php echo $root?>css/style.css" type='text/css'>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel='stylesheet' id='google_font_roboto-css' href='https://fonts.googleapis.com/css?family=Open+Sans%3A500%2C400%2C900%2C500%2C300|Roboto+Slab%3A400%2C700|Rye&display=swap' type='text/css' media='all' />
  <!--Favicon-->
	<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $root?>images/favicons/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $root?>images/favicons/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $root?>images/favicons/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $root?>images/favicons/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $root?>images/favicons/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $root?>images/favicons/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $root?>images/favicons/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $root?>images/favicons/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $root?>images/favicons/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="<?php echo $root?>images/favicons/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $root?>images/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $root?>images/favicons/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $root?>images/favicons/favicon-16x16.png">
	<!--<link rel="manifest" href="/images/manifest.json">-->
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/images/favicons/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
  <!-- JS -->  
  <script src="<?php echo $root?>js/jquery-3.2.1.min.js"></script>
  <script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>

  <?php echo $head; ?>
  <script>
  var googletag = googletag || {};
  googletag.cmd = googletag.cmd || [];
</script>
<script>
	googletag.cmd.push(function() {
	<?php echo $defineAdSlots ?>		
	<?php echo $DFPTargeting ?>		
	googletag.enableServices();
	  });
</script>
  </head>
  <body>
  <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M97ZHJK" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php 
if($logged_in){//we are logged in 
		$plan = getPlan($_SESSION['user_id']);				
		if($plan['name'] == 'Yearly Subscription'){?>
			<div class="announcement">Help us make GunValues better! Take our <a href="https://cariboumedia.typeform.com/to/ZfSC59y9" target="_blank">Survey</a></div>
		<?php }
}?>
<header>
	<div class="header_content">
		<a href="<?php echo $root;?>"><h1 class="title"></h1></a>
		<div class="user_info">
		<a href='<?php echo $root?>'>Find Gun Values</a> |
		<a href='<?php echo $root?>grading-system/'>Grading System</a> | 
		<?php
			if($logged_in){//we are logged in
				echo "Logged in as <a href='" . $root . "account/'>" . $_SESSION['user_name'] . "</a> | <a href='" . $root . "logout/'>Logout</a>";
				if($is_admin) echo " | <a href='" . $root . "admin/'>Admin</a>";
				
			}else{//We are NOT logged in
				echo "<a href='" . $root . "login/'>Login</a>";
			}			
		?>
		</div>
	</div>
</header>
<nav>
  <?php if($show_back_button){?>
	<div class="nav-text"><button class="back button" onclick="history.go(-1);"><span>&#171;</span> Back</button></div>	
  <?php }else if((isset($show_admin_button))&&($show_admin_button)){?>
	<div class="nav-text"><a class="back button" href="/admin/"><span>&#171;</span> Admin Home</a></div>
<?php	}
  echo $breadcrumbs; 
?>  
</nav>
<!--<div class="maintenance">GunValues will undergo necessary Maintenance on <strong>Wednesday, January 27, 2021 between 10:30 am and 2 pm Central Time</strong>.<br/>We are sorry for this inconvenience.</div>-->
  <?php echo $subheader; ?>
  <div class="page">
  <div class="content" role="main">