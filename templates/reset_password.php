<?php 
$page_name = "Choose a New Password";
if(isset($_GET['code'])){
	
$html=<<<EOD
		<!--Choose New Password Form-->
		<div class="half-page first">
			<div class="search-box-text">Choose a New Password</div>
			<form action="{$root}model/reset_process.php" method="POST">				
				<input type="hidden" name="code" value="{$_GET['code']}"/>
				<input type="password" name="password" placeholder="New Password"/>				
				<button type="submit" class="button center bright full-width">Save New Password</button>
			</form>			
		</div>		
	</div> 
EOD;
}else header('Location: ' . $root . "login/?err=You do not have permission to access that page" );
?>
