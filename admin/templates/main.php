<?php
$errors = $success = "";
if(!empty($_POST)){	
	require_once("../model/user_functions.php");    
    require_once("../model/mail_functions.php");
//-------------------------------------------------------------------------------------------------------------Accept customer details via POST
 if(isset($_POST['plan'])){
	$plan = $_POST['plan'];
	$admin = false;
	switch($plan){
		case "2":
			$plan_id = 2;
			$plan_status = "ongoing";
			$order_plan = "Yearly Access";
			$order_cost = 0;			
			$recurring = "yearly";
			$admin = true;
			break;
		case "7":
			$plan_id = 7;
			$plan_status = "expiring";
			$expiration = "1 year";
			$order_plan = "Yearly Access";
			$order_cost = 0;			
			$recurring = false;
			break;	
		case "8":
			$plan_id = 8;
			$plan_status = "expiring";
			$expiration = "1 month";
			$order_plan = "Monthly Access";
			$order_cost = 0;
			$recurring = false;
			break;
		case "4":
			$plan_id = 4;
			$plan_status = "expiring";
			$expiration = "3 days";
			$order_plan = "3-Day Access";
			$order_cost = 0;
			$recurring = false;
			break;
	}
	//Create user Array
	$user_data = array();
	$user_data['email'] = $_POST['username'];	
	$user_data['fname'] = $_POST['fname'];
	$user_data['lname'] = $_POST['lname'];
	$user_data['password'] = "placeholder";
	$user_data['address'] = $_POST['address'];
	$user_data['city'] = $_POST['city'];
	if((isset($_POST['state-alt']))&&($_POST['state-alt']!="")){
		$user_data['state'] = $_POST['state-alt'];
	}else if((isset($_POST['state']))&&($_POST['state']!="")){
		$user_data['state'] = $_POST['state'];
	}
	$user_data['zip'] = $_POST['zip'];
	$user_data['country'] = $_POST['country'];
	if($user_data['country']=="US"){//Strip hyphen and numbers after from US zip codes
		if(strlen($user_data['zip'])>5){
			$user_data['zip'] = substr( $user_data['zip'], 0, 5);
		}	
	}

	//Add to DB
	//If user exists, update instead of adding
	$customer_id = getUserId($user_data['email']);
	if($customer_id===false){
		$customer_id = addUser($user_data);
	}else{
		if(!checkPlanActiveById($customer_id)){
			updateUser($customer_id,$user_data);
		}else{//user already has an active plan
			$errors = "User currently has an active plan. You cannot grant a plan when one already exists.";
		}
	}
	
	//Add admin access
	if($admin){
		makeUserAdmin($customer_id);
	}
	
	if($errors===""){
		//Add Empty Payment
		$payment = array(
					"user_id" => $customer_id,
					"payment_id" => "comped",
					"amount" => $order_cost,
					"status" => "single"
				);
				addPayment($payment);

		//-------------------------------------------------------------------------------------------------------------Determine type of Purchase (one-time or subscription) and Process	
		 if($recurring){//this is an ongoing plan
			addUserPlan($customer_id,$plan_id,$plan_status);
			log_activity($customer_id,"Granted Ongoing Plan",$order_plan . " purchased for " . $order_cost . " " . $recurring);
			sendAccountCreatedEmail($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " granted.");		
		 }else{//this is an expiring plan		
				addUserPlan($customer_id,$plan_id,$plan_status);
				log_activity($customer_id,"Granted Expiring Plan",$order_plan . " purchased for " . $order_cost);
				sendAccountCreatedEmail($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " granted.");	
				//Add Expiration Date for 3-Day access
				addExpiration($customer_id,$expiration);					
		 }
				
		//If we get here, we have been successful
		$success = "Access Has Been Granted. The user will receive with instructions to create their new Password and access the GunValues website.";
	}
}else{//No plan POSTed, go back and get one
	$errors = "No Plan was selected. Please use the Back button to return to the previous page.";	
}
	
}
//Index/Main Admin Page Content
$page_name = "Admin Dashboard";
$start_date = isset($_GET['sd'])? date('Y-m-d',strtotime(urldecode($_GET['sd']))) : date('Y-m-d', strtotime("-1 weeks"));
$end_date = isset($_GET['ed'])? date('Y-m-d',strtotime(urldecode($_GET['ed']))) : date('Y-m-d');
$dashboard_data = getOverview($start_date,$end_date);
$error_banner = "";
if($errors!=""){
	$error_banner = "<div class='error'><strong>" . $errors . "</strong></div>";	
}else if($success!=""){
	$error_banner = "<div class='announcement'><strong>" . $success . "</strong></div>";
}
$html =<<<EOD
{$error_banner}
<h2 class="subhead">Current Users &amp; Plans</h2>
<form method="POST" action="/admin/search_users/" id="search">
<h3>Search for Users</h3>
<div class="half-page">					
	<input type="text" id="u_email" name="u_email" placeholder="Email" autocomplete="new-password" value=""/>					
</div>
<div class="half-page">					
	<div class="half first">
		<input type="text" name="u_fname"  placeholder="First Name" value=""/>
	</div>
	<div class="half last">	
		<input type="text" name="u_lname"  placeholder="Last Name" value=""/>									
	</div>
	<input type="submit" class="button full center green" id="search_button" value="Search for Users">
</div>
</form>
<div>Refine Date Range: <form><input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="start_date" name="sd" maxlength="10" readonly="readonly"/> to <input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="end_date" name="ed" maxlength="10" readonly="readonly"/><input type="submit" value="View Date Range"/></form></div>
<table class="admin">
	<thead>
	<td>Name</td>	
	<td>Email</td>
	<td>Location</td>
	<td>Plan</td>
	<td>Status</td>
	<td class='currency'>Amount</td>
	<td class='currency'>Timestamp</td>
	</thead>
EOD;
$total = 0;
$plan_totals = array();
		if(is_array($dashboard_data)){
			foreach($dashboard_data AS $data){
				$html .= "<tr>";
				$html .= "<td>" .$data['fname'] . " " . $data['lname']. "</td>";
				$html .= "<td>" .$data['email'] . "</td>";
				$html .= "<td>" .$data['city'] . ", " . $data['state']. "</td>";
				$html .= "<td>" .$data['name'] . "</td>";
				if(($data['status']=="expiring")&&($data['name']!="3-Day")){
					$html .= "<td>CANCELLED</td>";
				}else $html .= "<td>" .$data['status'] . "</td>";
				$html .= "<td class='currency'>$" .$data['amount'] . "</td>";
				$html .= "<td class='currency'>" .$data['timestamp'] . "</td>";
				$html .= "</tr>";		
				$total += $data['amount'];
				if(!array_key_exists($data['name'],$plan_totals)){
					$plan_totals[$data['name']] = 1;
				}else $plan_totals[$data['name']]++;
			}
		}
$display_total = "$" . number_format($total,2);
$html .= <<<EOD
	<tr>
	<td colspan="5"></td>	
	<td class='currency'>Total: $display_total </td>
	<td></td>	
	</tr>
EOD;
$html .= "</table>";
$html .=<<<EOD
<h2 class="subhead">Plan Totals</h2>
<table class="admin">
	<thead>
	<td>Name</td>	
	<td>Total Users</td>	
	</thead>
EOD;
foreach($plan_totals AS $name => $amount){
			$html .= "<tr>";
			$html .= "<td>" .$name . "</td>";		
			$html .= "<td class='currency'>" .$amount . "</td>";		
			$html .= "</tr>";					
		}
$html .= "</table><br/><a href='{$root}admin/recurring_report.php' class='button'>View Recurring Payments Report</a>";
$html .= "<a href='{$root}admin/payments_report.php' class='button'>View All Payments Report</a>";
$html .= "<a href='{$root}admin/users_report.php' class='button'>View User Trends Report</a>";
$html .=<<<EOD
<h2 class="subhead">Grant Access</h2>
<form action="{$root}admin/" id="grant_access" method="POST">
<h3>Use this form to grant access to GunValues for Free. New users will be added, existing users will be updated with what you enter below.</h3>
<div class="half-page">
				<div class="search-box-text">Please Enter the User's  Information</div>				
					<input type="email" id="username" name="username" placeholder="Email" autocomplete="new-password" value=""/>					
					<div class="half first">
						<input type="text" name="fname"  placeholder="First Name" value=""/>
					</div>
					<div class="half last">	
						<input type="text" name="lname"  placeholder="Last Name" value=""/>									
					</div>
					
					<div class="search-box-text">Choose a Plan</div>
					<select name="plan" id="plan" required>
						<option value="">Select One</option>
						<option value="4">3-Day</option>			
						<option value="8">Monthly (Expiring)</option>
						<option value="7">Yearly (Expiring)</option>							
						<option value="2">Admin User</option>
					</select>
				</div>
				
				<div class="half-page">
					<div class="search-box-text">Billing</div>
					<div class="half first">
						<select name="country" id="country" autocomplete="country" >						
							<option value="">Select a country…</option>
							<option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AP">Azores</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BY">Belarus</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BL">Bonaire</option><option value="BA">Bosnia</option><option value="BW">Botswana</option><option value="BR">Brazil</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="IC">Canary Islands</option><option value="CV">Cape Verde Islands</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CD">Channel Islands</option><option value="CL">Chile</option><option value="CN">China, Peoples Republic of</option><option value="CO">Colombia</option><option value="CG">Congo</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CU">Cuba</option><option value="CB">Curacao</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="EN">England</option><option value="GQ">Equitorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FO">Faeroe Islands</option><option value="FM">Federated States of Micronesia</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HO">Holland</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="CI">Ivory Coast</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KO">Kosrae</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macau</option><option value="MK">Macedonia</option><option value="MG">Madagascar</option><option value="ME">Madeira</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="MX">Mexico</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="AN">Netherlands Antilles</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NF">Norfolk Island</option><option value="NB">Northern Ireland</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn Island</option><option value="PL">Poland</option><option value="PO">Ponape</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="IE">Republic of Ireland</option><option value="YE">Republic of Yemen</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RT">Rota</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="SS">Saba</option><option value="SP">Saipan</option><option value="SM">San Marino</option><option value="ST">Sao Tome and Principe</option><option value="SA">Saudi Arabia</option><option value="SF">Scotland</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="NT">St. Barthelemy</option><option value="SW">St. Christopher</option><option value="SX">St. Croix</option><option value="EU">St. Eustatius</option><option value="UV">St. John</option><option value="KN">St. Kitts and Nevis</option><option value="LC">St. Lucia</option><option value="MB">St. Maarten</option><option value="TB">St. Martin</option><option value="VL">St. Thomas</option><option value="VC">St. Vincent and the Grenadines</option><option value="SD">Sudan</option><option value="SR">Suriname</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TA">Tahiti</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TI">Tinian</option><option value="TG">Togo</option><option value="TO">Tonga</option><option value="TL">Tortola</option><option value="TT">Trinidad and Tobago</option><option value="TU">Truk</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="UI">Union Island</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US" selected="">United States</option><option value="UY">Uruguay</option><option value="VI">US Virgin Islands</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican City</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="VR">Virgin Gorda</option><option value="WK">Wake Island</option><option value="WL">Wales</option><option value="WF">Wallis and Futuna Islands</option><option value="WS">Western Samoa</option><option value="YA">Yap</option><option value="YU">Yugoslavia</option><option value="ZR">Zaire</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>
						</select>	
					</div>					
					<input type="text" name="address"  placeholder="Address" value="Placeholder Address"/>
					<div class="third first">
						<input type="text" name="city" placeholder="City" value="Placeholder City"/>
					</div>
					<div class="third">
					<input id="state-alt" type="text" name="state-alt" placeholder="State or Province"/>
				<select id="state" name="state">
				  <option value="">State</option>
				  <option value="AL">Alabama</option>
				  <option value="AK">Alaska</option>
				  <option value="AZ">Arizona</option>
				  <option value="AR">Arkansas</option>
				  <option value="CA" selected="selected">California</option>
				  <option value="CO">Colorado</option>
				  <option value="CT">Connecticut</option>
				  <option value="DE">Delaware</option>
				  <option value="DC">District Of Columbia</option>
				  <option value="FL">Florida</option>
				  <option value="GA">Georgia</option>
				  <option value="HI">Hawaii</option>
				  <option value="ID">Idaho</option>
				  <option value="IL">Illinois</option>
				  <option value="IN">Indiana</option>
				  <option value="IA">Iowa</option>
				  <option value="KS">Kansas</option>
				  <option value="KY">Kentucky</option>
				  <option value="LA">Louisiana</option>
				  <option value="ME">Maine</option>
				  <option value="MD">Maryland</option>
				  <option value="MA">Massachusetts</option>
				  <option value="MI">Michigan</option>
				  <option value="MN">Minnesota</option>
				  <option value="MS">Mississippi</option>
				  <option value="MO">Missouri</option>
				  <option value="MT">Montana</option>
				  <option value="NE">Nebraska</option>
				  <option value="NV">Nevada</option>
				  <option value="NH">New Hampshire</option>
				  <option value="NJ">New Jersey</option>
				  <option value="NM">New Mexico</option>
				  <option value="NY">New York</option>
				  <option value="NC">North Carolina</option>
				  <option value="ND">North Dakota</option>
				  <option value="OH">Ohio</option>
				  <option value="OK">Oklahoma</option>
				  <option value="OR">Oregon</option>
				  <option value="PA">Pennsylvania</option>
				  <option value="RI">Rhode Island</option>
				  <option value="SC">South Carolina</option>
				  <option value="SD">South Dakota</option>
				  <option value="TN">Tennessee</option>
				  <option value="TX">Texas</option>
				  <option value="UT">Utah</option>
				  <option value="VT">Vermont</option>
				  <option value="VA">Virginia</option>
				  <option value="WA">Washington</option>
				  <option value="WV">West Virginia</option>
				  <option value="WI">Wisconsin</option>
				  <option value="WY">Wyoming</option>
			  </select>
					</div>
					<div class="third last">
						<input type="text" name="zip" placeholder="Zip Code" value="90000"/>
					</div>
					<input type="submit" class="button full center green" id="submit_button" value="Grant Access"/>
				</div>
				
				
</form>
EOD;

$sd_js = date("m/d/Y",strtotime($start_date));
$ed_js = date("m/d/Y",strtotime($end_date));
$head = <<<EOD
<link rel="stylesheet" href="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.css">
<script src="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    $( ".datepicker" ).datepicker({
      dateFormat: 'mm/dd/yy',
      minDate: new Date("2018-03-26"),
      maxDate: '+1d'
    });
	$('#start_date').datepicker("setDate", "{$sd_js}" );
	$('#end_date').datepicker("setDate", "{$ed_js}" );

	//State switching for non-US countries
	$('#country').change(function(){
		var country_value = $(this).val();
		if(country_value!="US"){
			$('#state').hide();
			$('#state').removeAttr('required');
			$('#state-alt').show();
		}else{
			$('#state').show();
			$('#state').attr('required');
			$('#state-alt').hide();
		}				
	});			
	
	$('#submit_button').click(function (event) {
				event.preventDefault();
				alert_text = "";
				error=false;
				
				username_content = $('input[name=username]').val();
				if (username_content.length==0){
				  alert_text += "Please complete the user's Email Address \\n ";      
				  $('input[name=username]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else if(!(validateEmail(username_content))){
					alert_text+='Please enter a valid Email Address \\n ';
					$('input[name=username]').removeClass('complete').addClass('incomplete');
					error=true;
				}else $('input[name=username]').removeClass('incomplete').addClass("complete");			
				
				if (($('input[name=fname]').val()).length==0){
				  alert_text += "Please complete the user's First Name \\n ";
				  $('input[name=fname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=fname]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=lname]').val()).length==0){
				  alert_text += "Please complete the user's Last Name \\n ";
				  $('input[name=lname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=lname]').removeClass('incomplete').addClass("complete");			
				
				if (($('#plan').val()).length==0){
				  alert_text += "Please choose a Plan \\n ";
				  $('#plan').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('#plan').removeClass('incomplete').addClass("complete");
				
				if (!error){
				  $(this).prop('disabled', true);
				  $(this).val('Submitting, please wait...');
				  //submit the validated form
				  $('#grant_access').submit();
				}else{
				   event.preventDefault();
				   alert(alert_text);				  
				}
			  });
	
	
});
function validateEmail(email) 
{
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}
</script>
EOD;
?>



