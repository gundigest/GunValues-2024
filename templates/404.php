<?php 
//session_unset();
$page_name = "Page Not Found";
$html=<<<EOD
		<!--Login Form-->
		<div class="two-third-page">
			<div class="search-box-text">We Could not Find that Page</div>
			<a class="button" href="' . $root . '">Search or Browse</a>
			<a class="button" href="' . $root . '/login">Login or Choose a Plan</a></div>
		</div>
		
	</div> 
EOD;
?>
