<?php 
$page_name = "Reset Your Password";
if(isset($_GET['success'])){
	if($_GET['success']==1){//success!
		$banner = '<div class="announcement">Please find instructions in your Email to reset your password.</div>';
	}else{//error :(
		$banner = '<div class="error">We cannot find an account with the Email Address you entered.</div>';
	}
}else $banner = "";
$html=<<<EOD
		{$banner}
		<!--Reset Your Password Form-->
		<div class="half-page first">
			<div class="search-box-text">Reset Your Password</div>
			<form action="{$root}model/forgotten_process.php" method="POST">				
				<input type="text" name="email" placeholder="Email"/>				
				<button type="submit" class="button center bright full-width">Send Password Reset Email</button>
			</form>			
		</div>		
	</div> 
EOD;
?>
