<?php 
$origURL = "";
if(!isset($_SESSION['origURL'])){
	if (isset($_SERVER["HTTP_REFERER"])){
		$origURL = $_SERVER["HTTP_REFERER"];
		if((stripos($origURL,$root,0)===false)||(!(stripos($origURL,'login',0)===false))||(!(stripos($origURL,'password',0)===false))){//check that URL is on our server, and we were not referred by the login page
			$origURL = "";
		}		
	}
}else $origURL = $_SESSION['origURL'];
$page_name = "Login or Register";
if(isset($_GET['success'])){
	if($_GET['success']==1){//success!
		if(isset($_SESSION['track_conversion'])){			
			$head .= $_SESSION['track_conversion'];			
			$_SESSION['track_conversion'] = "";
		}
		$banner = '<div class="announcement">Thank you for your purchase! Please log in below to continue.</div>';
	}else{//error :(
		$banner = '<div class="error">There has been an error with your purchase. Please <a href="' . $root . 'contact">Contact Us</a> to resolve the issue.</div>';
	}
}else if(isset($_GET['err'])){
	$banner = '<div class="error">' .$_GET['err']. '</div>';
}else $banner = "";
$html=<<<EOD
		{$banner}
		<!--Login Form-->		
		<div class="half-page first">
			<div class="search-box-text">Login to Gun Values</div>
			<form action="{$root}/model/login_process.php" method="POST">
				<input type="hidden" name="origURL" value="{$origURL}" />
				<input type="text" name="username" placeholder="Username"/>
				<input type="password" name="pwd" placeholder="Password"/>
				<button type="submit" class="button center bright">Login</button>
			<a class="forgot" href="{$root}forgot/">Forgot Your Username or Password?</a>				
			</form>

		</div>
		<div class="half-page">
			<div class="search-box-text">Join Gun Values</div>
			<div class="plan_emphasis">
				<h3>How Does Gun Values Work?</h3>
				<p>Gun Values by Gun Digest gives you free information on every firearm produced in the USA or imported since the early 1800s. Manufacturer, Series, and Model details including photos are publicly available and always free.</p>				
				<p>Unlimited Access to Pricing information for every model across <a href="{$root}grading-reset/">several different grading points</a> is available through <a href="{$root}register/">one of our reasonable access plans</a>.</p>
			</div>	
			<a href="{$root}register/" class="button center bright">View Plans</a>
		</div>
	</div> 
EOD;
?>
