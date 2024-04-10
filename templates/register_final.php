<?php 
$errors = "";
if(!empty($_POST)){	
	require_once("model/user_functions.php");
    require_once("model/payment_functions.php");
    require_once("model/mail_functions.php");
//-------------------------------------------------------------------------------------------------------------Accept customer details via POST
 if(isset($_POST['plan'])){//PLAN will probably need to be pulled from the DB in the future
	$plan = $_POST['plan'];
	switch($plan){
		case "yearly":
			$plan_id = 2;
			$plan_status = "ongoing";
			$order_plan = "Yearly Access";
			$order_cost = 27.99;			
			$recurring = "yearly";
			$recur_code = "1";
			break;
		case "monthly":
			$plan_id = 3;
			$plan_status = "ongoing";
			$order_plan = "Monthly Access";
			$order_cost = 2.99;
			$recurring = "monthly";
			$recur_code = "3";
			break;
		case "3-day":
			$plan_id = 4;
			$plan_status = "expiring";
			$order_plan = "3-Day Access";
			$order_cost = 4.99;
			$recurring = false;
			$recur_code = 0;
			break;
	}
	//Create user Array
	$user_data = array();
	$user_data['email'] = $_POST['username'];
	$user_data['password'] = $_POST['pwd'];
	$user_data['fname'] = $_POST['fname'];
	$user_data['lname'] = $_POST['lname'];
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
		updateUser($customer_id,$user_data);
	}	

	$token = getPayTraceToken();	

	//-------------------------------------------------------------------------------------------------------------Determine type of Purchase (one-time or subscription) and Process
	 if($recurring){//this is an ongoing plan
		 //Create Customer	
		 $data = array(
		  "customer_id"=>"PTGV_" . $customer_id,
		  "credit_card"=>array(
			"encrypted_number"=>$_POST['ccNumber'],
			"expiration_month"=>$_POST['expiration_month'],
			"expiration_year"=>$_POST['expiration_year']
		  ),
		  "encrypted_csc"=>$_POST['ccCSC'],
		  "billing_address"=>array(
			"name"=>$user_data['fname'] . " " . $user_data['lname'],
			"street_address"=>$user_data['address'],
			"city"=>$user_data['city'],			
			"zip"=>$user_data['zip'],
			"country"=>$user_data['country']
			)
		);  
		
		$userCreated = createPaytraceUser($token,$data);
			if($userCreated===true){
				
			  //Create Recurring Payment Plan
				 $data = array(
				  "customer_id"=>"PTGV_" . $customer_id,
				  "recurrence"=>array(
					"amount"=>$order_cost,
					"customer_receipt"=>false,
					"frequency"=>$recur_code,
					"start_date"=>date('m/d/Y'),
					"total_count"=>"999",
					"transaction_type"=>"sale"
				  ),
				 );
				$paymentCreated = createRecurringPayment($token,$data,$customer_id);	
				if($paymentCreated===true){					
					addUserPlan($customer_id,$plan_id,$plan_status);
					log_activity($customer_id,"Purchased Recurring Plan",$order_plan . " purchased for " . $order_cost . " " . $recurring);
					sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " purchased for $" . $order_cost);
				}else{
					log_activity($customer_id,"Error Purchasing Recurring Plan","Error: " . json_encode($paymentCreated) . "; " . $order_plan . " could not be purchased for " . $order_cost . " " . $recurring);
					$errors = getErrorMessage($paymentCreated);
				}
			}else{//user Creation Failed
				log_activity($customer_id,"Error Creating Paytrace Customer",json_encode($userCreated) . "; " . $order_plan . " purchased for " . $order_cost . " " . $recurring);
				//Show error to user
				$errors = getErrorMessage($userCreated);				
			}		
	 }else{//this is a one-time purchase
		//Simple Charge Code
		 $data = array(
		  "amount"=>$order_cost,
		  "credit_card"=>array(
			"encrypted_number"=>$_POST['ccNumber'],
			"expiration_month"=>$_POST['expiration_month'],
			"expiration_year"=>$_POST['expiration_year']
		  ),
		  "encrypted_csc"=>$_POST['ccCSC'],
		  "billing_address"=>array(
			"name"=>$user_data['fname'] . " " . $user_data['lname'],
			"street_address"=>$user_data['address'],
			"city"=>$user_data['city'],			
			"zip"=>$user_data['zip'],
			"country"=>$user_data['country']
			)
		);
		$singlePayment = createSinglePayment($token,$data,$customer_id);
		if($singlePayment===true){
			addUserPlan($customer_id,$plan_id,$plan_status);
			log_activity($customer_id,"Purchased 3-Day Plan",$order_plan . " purchased for " . $order_cost);
			sendPurchaseThankYou($customer_id,$user_data['fname'],$user_data['lname'],$user_data['email'],$order_plan . " purchased for " . $order_cost);
			//Add Expiration Date for 3-Day access
			addExpiration($customer_id,'3 days');
		}else{//record error
			log_activity($customer_id,"Error Purchasing 3-Day Plan","Error: " . json_encode($singlePayment) . "; " . $order_plan . " could not be purchased for " . $order_cost);
			$errors = getErrorMessage($singlePayment);
		}
	 }
	 //check for errors
	 if($errors===""){
		 //Check for Email Subscription
		 if(isset($_POST['email_sub'])){
			 $email_results = newsletterSubscribe($user_data['email'],$user_data['fname'],$user_data['lname']);
			 if($email_results){
				log_activity($customer_id,"Newsletter Subscription","Successful for " . $user_data['email']);
			 }else log_activity($customer_id,"Newsletter Subscription","Failed for " . $user_data['email']);
		 }
		//if we are here, there have been no purchase errors  
		/*$_SESSION['track_conversion'] = <<<EOD
		<script>
			window.dataLayer = window.dataLayer || []
			dataLayer.push({
			'transactionId': '{$customer_id}',			   
			   'transactionTotal': {$order_cost},			   
			   'transactionProducts': [{
				   'sku': '{$plan_id}',
				   'name': '{$order_plan}',				   
				   'price': {$order_cost},
				   'quantity': 1
			   }]
			});
			</script>
EOD;*/
$_SESSION['track_conversion'] = <<<EOD
<script>
$(document).ready(function() {	
gtag('event', 'purchase', {
  "transaction_id": '{$customer_id}',
  "affiliation": "Gun Values",
  "value": {$order_cost},
  "currency": "USD",
  "items": [
    {
      "id": '{$plan_id}',
      "name": '{$order_plan}',
      "quantity": 1,
      "price": {$order_cost}
    }
  ]
});
//AdWords event
gtag('event', 'conversion', { 'send_to': 'AW-796286998/-zFNCMvUre4BEJbA2fsC', 'value': {$order_cost}, 'currency': 'USD', 'transaction_id': '{$customer_id}' });
});
</script>
EOD;
	error_log("here");
	error_log($_SESSION['track_conversion']); 
		
		header("Location: " . $root . "login/?success=1"); /* Redirect browser */
		exit();	
	 }
	
	}else{//No plan POSTed, go back and get one
		header("Location: " . $root . "register"); /* Redirect browser */
		exit();		
	}
	
}else{//We are not POSTing the form
	$origURL = "";
	if(isset($_SESSION['origURL'])){
		$origURL = $_SESSION['origURL'];
	}
}
//If we have not exited the file yet, render the form	
	$page_name = "Register";
	if(isset($_SESSION['gv_plan'])){
		$plan = $_SESSION['gv_plan'];
		switch($plan){
			case "yearly":
				$order_plan = "Yearly Access";
				$order_descrip = "A Full Year of Unlimited Access to Gun Values firearms pricing information on all models";
				$order_cost = "27.99";
				$order_suffix = "/year";
				break;
			case "monthly":
				$order_plan = "Monthly Access";
				$order_descrip = "Monthly Unlimited Access to Gun Values firearms pricing information on all models";
				$order_cost = "2.99";
				$order_suffix = "/month";
				break;
			case "3-day":
				$order_plan = "3-Day Access";
				$order_descrip = "72 Hours of Access to Gun Values firearms pricing information on all models";
				$order_cost = "4.99";
				$order_suffix = "";
				break;
		}		
	}else{ header("Location: register"); /* Redirect browser */
			exit();
	}
//Display errors to the user
$error_banner = "";
$email = $fname = $lname = $address = $city = $state = $zip = $country = "";
if($errors!=""){
	$error_banner = "<div class='error'><strong>" . $errors['head'] . "</strong><br/>" . $errors['body'] . "</div>";
	$email = $_POST['username'];
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$address = $_POST['address'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$country = $_POST['country'];	
}	
$html = <<<EOD
{$error_banner}
<!--Registration Form Part 2-->		
		<h2 class="subhead">Account Details</h2>
		<form action="{$root}register_final" METHOD="POST" id="payment-form">			
			<input type="hidden" name="plan" id="plan" value="{$plan}"/>					
			<div class="half-page">
				<div class="search-box-text">Please Enter Your Information</div>				
					<input type="email" id="username" name="username" placeholder="Email" autocomplete="email" value="{$email}"/>
					<div id="email_duplicate">
						It looks like you already have an account! Please <a href="/login">login</a>.
					</div>
					<input type="email" id="email-check" name="email-check" placeholder="Email, Again" autocomplete="none"/>					
					<div id="email_retype">
						Your email address entries do not match. Please check and type again.
					</div>
					<div class="email_signup">
						<input type="checkbox" name="email_sub"/><label for="email_sub">Subscribe to the Gun Digest Newsletter</label>
					</div>	
					<input type="password" name="pwd" placeholder="Password" autocomplete="new-password"/>
					<div class="half first">
						<input type="text" name="fname"  placeholder="First Name" value="{$fname}"/>
					</div>
					<div class="half last">	
						<input type="text" name="lname"  placeholder="Last Name" value="{$lname}"/>									
					</div>
					<div class="search-box-text">Billing</div>
					<div class="half first">
						<select name="country" id="country" autocomplete="country" >						
							<option value="">Select a country…</option>
							<option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AP">Azores</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BY">Belarus</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BL">Bonaire</option><option value="BA">Bosnia</option><option value="BW">Botswana</option><option value="BR">Brazil</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="IC">Canary Islands</option><option value="CV">Cape Verde Islands</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CD">Channel Islands</option><option value="CL">Chile</option><option value="CN">China, Peoples Republic of</option><option value="CO">Colombia</option><option value="CG">Congo</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CU">Cuba</option><option value="CB">Curacao</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="EN">England</option><option value="GQ">Equitorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FO">Faeroe Islands</option><option value="FM">Federated States of Micronesia</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HO">Holland</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="CI">Ivory Coast</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KO">Kosrae</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macau</option><option value="MK">Macedonia</option><option value="MG">Madagascar</option><option value="ME">Madeira</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="MX">Mexico</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="AN">Netherlands Antilles</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NF">Norfolk Island</option><option value="NB">Northern Ireland</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn Island</option><option value="PL">Poland</option><option value="PO">Ponape</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="IE">Republic of Ireland</option><option value="YE">Republic of Yemen</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RT">Rota</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="SS">Saba</option><option value="SP">Saipan</option><option value="SM">San Marino</option><option value="ST">Sao Tome and Principe</option><option value="SA">Saudi Arabia</option><option value="SF">Scotland</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="NT">St. Barthelemy</option><option value="SW">St. Christopher</option><option value="SX">St. Croix</option><option value="EU">St. Eustatius</option><option value="UV">St. John</option><option value="KN">St. Kitts and Nevis</option><option value="LC">St. Lucia</option><option value="MB">St. Maarten</option><option value="TB">St. Martin</option><option value="VL">St. Thomas</option><option value="VC">St. Vincent and the Grenadines</option><option value="SD">Sudan</option><option value="SR">Suriname</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TA">Tahiti</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TI">Tinian</option><option value="TG">Togo</option><option value="TO">Tonga</option><option value="TL">Tortola</option><option value="TT">Trinidad and Tobago</option><option value="TU">Truk</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="UI">Union Island</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US" selected="">United States</option><option value="UY">Uruguay</option><option value="VI">US Virgin Islands</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican City</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="VR">Virgin Gorda</option><option value="WK">Wake Island</option><option value="WL">Wales</option><option value="WF">Wallis and Futuna Islands</option><option value="WS">Western Samoa</option><option value="YA">Yap</option><option value="YU">Yugoslavia</option><option value="ZR">Zaire</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>
						</select>	
					</div>					
					<input type="text" name="address"  placeholder="Address" value="{$address}" autocomplete="street-address"/>
					<div class="third first">
						<input type="text" name="city" placeholder="City" value="{$city}" autocomplete="address-level2"/>
					</div>
					<div class="third">
					<input id="state-alt" type="text" name="state-alt" placeholder="State or Province"/>
				<select id="state" name="state" required autocomplete="address-level1">
				  <option value="" selected="selected">State</option>
				  <option value="AL">Alabama</option>
				  <option value="AK">Alaska</option>
				  <option value="AZ">Arizona</option>
				  <option value="AR">Arkansas</option>
				  <option value="CA">California</option>
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
						<input type="text" name="zip" placeholder="Zip Code" value="{$zip}"  autocomplete="postal-code"/>
					</div>
					
				<div class="form-row">
					<!--CC details-->
				<div class="card-element">
					<span id="card_type"></span>
				    <input type="tel" class="pt-encrypt" id="ccNumber" name="ccNumber" pattern="[0-9 ]+" placeholder="Credit card number" maxlength="19" size="17">
					<input type="tel" id="expiration_month" name="expiration_month" placeholder="MM" maxlength="2" size="2"><span class="slash">/</span><input type="tel" id="expiration_year" name="expiration_year" placeholder="YY" maxlength="4" size="2">
					<input type="tel" class="pt-encrypt" id="ccCSC" name="ccCSC" placeholder="CSC" maxlength="4" size="3">
				</div>
				<!-- Used to display Element errors -->
				<div id="card-errors" class="alert"></div>
			  </div>			  
			</div>			
			<div class="half-page">
				<div class="search-box-text">Complete Your Order</div>	
					<div class="order">
						<div class="order_plan">1x {$order_plan}</div>						
						<div class="order_descrip">{$order_descrip}</div>						
						<div class="order_cost">\${$order_cost}{$order_suffix}</div>
						<div class="order_total_text">Total to be Charged Today:</div>						
						<div class="order_total_cost">\${$order_cost}</div>
						<div class="order_notes">
							<ul>
EOD;
						if($plan=="3-day") $html .= '<li>3-Day Access Plan access will begin immediately after you click "Purchase Plan," and you will have 72 Hours of access from that time.</li>';
$html .= <<<EOD
								<li>You will need to use your email address and password to login to Gun Values to view pricing information.</li>
								<li>Recurring plans will continue until you cancel.</li>
								<li>You may cancel your subscription at any time by logging in and visiting your Account page. </li>
							</ul>
						</div>
					</div>					
					<input type="submit" class="button full center green" id="submit_button" value="Purchase Plan""/>
				</div>
		</form>
EOD;
$head = <<<EOD
<script src="{$root}js/paytrace-e2ee.js"></script>
<script>        
        $(document).ready(function() {
			
			//Encrypt data to go to PayTrace		
		// set the key from an AJAX call (in this case via a relative URL)
		paytrace.setKeyAjax('{$root}model/public_key.pem');	
			
			var email_dupe = false;
EOD;
 if($errors===""){
$head .= <<<EOD
		
			//Ajax to check unique email
			$("#username").blur(function(){				
				var email = $(this).val();
				if(email.length > 2){
					$.ajax({
					  method: "POST",
					  url: "{$root}model/checkEmailUnique.php",
					  data: { email: email }
					})
					 .done(function( results ) {						 
						if(results==="true"){
							$('#email_duplicate').slideUp();
							email_dupe = false;		
							$('input[name=username]').removeClass('incomplete').addClass("complete");						
						}else{
							$('#email_duplicate').slideDown();
							email_dupe = true;	
							$('input[name=username]').removeClass('complete').addClass('incomplete');						
						}
					 });
				}
				if($('#email_retype').is(':visible')){
					$("#email-check").blur();
				}
			});
			//Script to check email addresses match
			$("#email-check").blur(function(){
				var email_check = $(this).val();
				var correct = "true";
				if(email_check.length > 2){
					if($("#username").val().length > 2){
						if(email_check!=$("#username").val()){
							correct = "false";
						}
					}
				}
				if(correct==="true"){
					$('#email_retype').slideUp();					
					$('input[name=email-check]').removeClass('incomplete').addClass("complete");						
				}else{
					$('#email_retype').slideDown();					
					$('input[name=email-check]').removeClass('complete').addClass('incomplete');						
				}
			});
EOD;
}else{
	//if we've returned to the page with errors, set values for drop-downs
	$head .= "$('#country').val('{$country}');";
	$head .= "$('#state').val('{$state}');";
	//set Newsletter to match user preference, default is already checked
	if(!(isset($_POST['email_sub']))){
		$head .= '$( "input[name=email_sub]" ).prop( "checked", false );';
	}
}
$head .= <<<EOD
			//check current country
			if($('#country').val()!="US"){
				$('#state').hide();
				$('#state').removeAttr('required');
				$('#state-alt').show();
			}
			

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
			
			//Credit Card Visual Aid
			$("#ccNumber").keyup(function(){			
			  var val_old = $(this).val();
			  var val = val_old.replace(/[^0-9]/g, '');
			  var len = val.length;
			  //Determine card type
			  amex = false;
			  if(val.length == 0){
				  $('#card_type').removeClass('amex');
				  $('#card_type').removeClass('visa');
				  $('#card_type').removeClass('mc');
				  $('#card_type').removeClass('disc');
			  }else if(val.substring(0,1)==3){
				  $('#card_type').addClass('amex');
				  amex = true;
			  }else if(val.substring(0,1)==4){
				  $('#card_type').addClass('visa');
			  }else if(val.substring(0,1)==5){
				  $('#card_type').addClass('mc');
			  }else if(val.substring(0,1)==6){
				  $('#card_type').addClass('disc');
			  }
			  //Add Spaces
			  if (len >= 12){
				val = val.substring(0, 4) + ' ' + val.substring(4,8) + ' ' + val.substring(8,12) + ' ' + val.substring(12);				
			  }else if (len >= 8){
				val = val.substring(0, 4) + ' ' + val.substring(4,8) + ' ' + val.substring(8);
			  }else if (len >= 4){
				val = val.substring(0, 4) + ' ' + val.substring(4);
			  }
			  
			  if (val != val_old){
				  $(this).val(val);
			  }
				  
				if ((len==15)&&(amex)){					
					$('#expiration_month').focus();
				}else if((len==16)&&(!amex)){								
					$('#expiration_month').focus();
				}else $(this).focus();						  
			});			
			$("#expiration_month").keyup(function(){  
			  var len = $(this).val().length;
			  if (len>1)					
				$('#expiration_year').focus();
			});
			$("#expiration_year").keyup(function(){						  
			  var len = $(this).val().length;
			  if (len>1)				
				$('#ccCSC').focus();
			});						
			
			
			$('#submit_button').click(function (event) {
				event.preventDefault();
				alert_text = "";
				error=false;
				//disable button
				$(this).prop('disabled', true);
				$(this).val('Submitting, please wait...');
				
				username_content = $('input[name=username]').val();
				if (username_content.length==0){
				  alert_text += "Please complete your Email Address \\n ";      
				  $('input[name=username]').removeClass('complete').addClass('incomplete');
				  error=true;	
				}else if (email_dupe==true){
					alert_text+='You already have an account with that Email Address. \\n Please login to continue. \\n ';
					$('input[name=username]').removeClass('complete').addClass('incomplete');
					error=true;
				}else if(!(validateEmail(username_content))){
					alert_text+='Please enter a valid Email Address \\n ';
					$('input[name=username]').removeClass('complete').addClass('incomplete');
					error=true;
				}else $('input[name=username]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=pwd]').val()).length<6){
				  alert_text += "Please choose a Password that is at least six characters long \\n ";      
				  $('input[name=pwd]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=pwd]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=fname]').val()).length==0){
				  alert_text += "Please complete your First Name \\n ";
				  $('input[name=fname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=fname]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=lname]').val()).length==0){
				  alert_text += "Please complete your Last Name \\n ";
				  $('input[name=lname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=lname]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=address]').val()).length<3){
				  alert_text += "Please complete your billing Address \\n ";
				  $('input[name=address]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=address]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=city]').val()).length==0){
				  alert_text += "Please complete your billing City \\n ";
				  $('input[name=city]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=city]').removeClass('incomplete').addClass("complete");
				if($('select[name=country]').val()=="US"){//We only care about state for US
					if (($('select[name=state]').val()).length<2){
					  alert_text += "Please complete your billing State \\n ";
					  $('select[name=state]').removeClass('complete').addClass('incomplete');
					  error=true;
					}else $('select[name=state]').removeClass('incomplete').addClass("complete");
				}
				if (($('input[name=zip]').val()).length<5){
				 alert_text += "Please complete your billing Zip Code \\n ";
				  $('input[name=zip]').removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $('input[name=zip]').removeClass('incomplete').addClass("complete");
				if (($('select[name=country]').val()).length<2){
				 alert_text += "Please complete your billing Country \\n ";
				  $('select[name=country]').removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $('select[name=country]').removeClass('incomplete').addClass("complete");	
				if(($("#ccNumber").val()).length<15){
				 alert_text += "Please complete your Credit Card Number \\n ";
				  $(".card-element").removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $(".card-element").removeClass('incomplete').addClass("complete");
				if(($("#expiration_month").val()).length<2){
				 alert_text += "Please complete your Credit Card Expiration Month \\n ";
				  $(".card-element").removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $(".card-element").removeClass('incomplete').addClass("complete");
				if(($("#expiration_year").val()).length<2){
				 alert_text += "Please complete your Credit Card Expiration Year \\n ";
				  $(".card-element").removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $(".card-element").removeClass('incomplete').addClass("complete");
				if(($("#ccCSC").val()).length<2){
				 alert_text += "Please complete your Credit Card Security Code \\n ";
				  $(".card-element").removeClass('complete').addClass('incomplete');
				  error=true;  
				}else $(".card-element").removeClass('incomplete').addClass("complete");				
				if (!error){
				  //submit the validated form
				  paytrace.submitEncrypted('#payment-form');					  
				}else{
				   event.preventDefault();
				   alert(alert_text);
				   $(this).prop('disabled', false);
				   $(this).val('Purchase Plan');
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
