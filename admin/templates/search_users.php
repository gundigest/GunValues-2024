<?php
//Search Results
$page_name = "User Search Results";
if(isset($_POST)){
	$users = searchUsers($_POST['u_email'],$_POST['u_fname'],$_POST['u_lname']);
	$u_email = $_POST['u_email'];
	$u_fname = $_POST['u_fname'];
	$u_lname = $_POST['u_lname'];
}
$html =<<<EOD
<form method="POST" action="" id="search">
<h3>Search Again</h3>
<div class="half-page">					
	<input type="text" id="u_email" name="u_email" placeholder="Email" autocomplete="new-password" value="$u_email"/>					
</div>
<div class="half-page">					
	<div class="half first">
		<input type="text" name="u_fname"  placeholder="First Name" value="$u_fname"/>
	</div>
	<div class="half last">	
		<input type="text" name="u_lname"  placeholder="Last Name" value="$u_lname"/>									
	</div>
	<input type="submit" class="button full center green" id="search_button" value="Search for Users">
</div>
</form>
<h2 class="subhead">User Search Results</h2>
<table class="admin">
	<thead>
	<td>Name</td>	
	<td>Email</td>
	<td class='currency'>Timestamp</td>
	</thead>
EOD;
		if($users){
			foreach($users AS $data){
				$html .= "<tr>";
				$html .= "<td><a target='_blank' href='" . $root . "admin/user_account/?uid=" . $data['id'] . "'>" .$data['fname'] . " " . $data['lname']. "</a></td>";
				$html .= "<td>" .$data['email'] . "</td>";			
				$html .= "<td class='currency'>" .$data['timestamp'] . "</td>";
				$html .= "</tr>";		
			}
		}
$html .= "</table>";

$head = <<<EOD
<link rel="stylesheet" href="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.css">
<script src="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	
});
</script>
EOD;
?>



