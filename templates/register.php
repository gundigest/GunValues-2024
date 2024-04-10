<?php 
if(isset($_POST['plan'])){
	$plan = $_POST['plan'];
	$_SESSION['gv_plan'] = $plan;
	header("Location: register_final"); /* Redirect browser */		
}else{
	if (isset($_SERVER["HTTP_REFERER"])){
		$origURL = $_SERVER["HTTP_REFERER"];
		if((stripos($origURL,$root,0)===false)||(!(stripos($origURL,'login',0)===false))){//check that URL is on our server, and we were not referred by the login page
			$origURL = "";
		}
		$_SESSION['origURL'] = $origURL;	
	}
}	
$page_name = "Register";
$subheader =<<<EOD
	<div class='top-spacer'></div>
	<div class="subheader">		
		<div class="subhead">Gun values when you need them.</div>
		<div class="tagline">The most complete online reference and price guide for the gun enthusiast.</div>
	</div>
EOD;
$html = <<<EOD
<!--Registration Form-->
		<h2 class="subhead">3 Great Plans to Accommodate any Budget</h2>
		<form action="" METHOD="POST" id="payment-form">			
			<input type="hidden" name="plan" id="plan"/>
			<div class="plans">
				<div class="sub_plan" id="3-day">
					<div class="sub_price"><sup>$</sup>4<sup>.99</sup><span class="sub">&nbsp;&nbsp;</span></div>
					<div class="sub_name">3-day Access</div>
					<ul>
						<li>72 Hours of Access to Gun Values firearms pricing information on all models</li>
						<li>One-time charge. No recurring billing.</li>
						<li>Access begins when you register</li>
					</ul>
					<a href="javascript:void(0);" class="button center bright">Get 3-Day Access</a>
				</div>
				<div class="sub_plan" id="monthly">
					<div class="sub_price"><sup>$</sup>2<sup>.99</sup><span class="sub">/MO</span></div>
					<div class="sub_name">Monthly Access</div>
					<ul>
						<li>Monthly Unlimited Access to Gun Values firearms pricing information on all models</li>
						<li>Convenient Automatic Payments</li>
						<li>Discounts on Gun Digest Store products</li>
					</ul>
					<a href="javascript:void(0);" class="button center bright">Get Monthly Access</a>
				</div>
				
				<div class="sub_plan" id="yearly">
					<div class="sub_price"><sup>$</sup>27<sup>.99</sup><span class="sub">/YR</span></div>
					<div class="sub_name">Annual Access</div>
					<ul>
						<li>Save Over 20% Off the Monthly Rate</li>
						<li>A Full Year of Unlimited Access to Gun Values firearms pricing information on all models</li>
						<li>Convenient Automatic Payments</li>
						<li>Discounts on Gun Digest Store Products</li>
					</ul>
					<a href="javascript:void(0);" class="button center bright">Get Annual Access</a>
					<div class="corner-ribbon-container"><div class="corner-ribbon top-right green shadow">Best Value</div></div>
				</div>				
			</div>								
		</form>
		<h2>Frequently Asked Questions</h2>
		<div class='half-page faq'>
		<h3>Why can&apos;t I see the values of any of the models?</h3>
		<p>The general model information for all makes and models contained in the Gun Values database is offered free of charge. If you wish to see pricing information you will need to purchase an access plan. We have low-cost plans for one-time access to pricing data, or lower cost subscription plans which give you unlimited access while your subscription is current.</p>
		<h3>How do I get access to pricing information?</h3>
		<p>Visit our <a href="{$root}register">plans page</a> to get access. Choose the plan that is right for you and create an account to access pricing. You&apos;ll need to be logged in to see pricing information, so make sure to remember your username and password.</p>
		<h3>What kinds of access plans do you offer?</h3>
		<p>We offer conservatively-priced monthly and annual plans for ongoing access to Gun Values by Gun Digest. We also offer a one-time 3-day access plan for customers who don&apos;t want a recurring subscription. </p>
		<h3>Can I just pay once? I don&apos;t want a plan.</h3>
		<p>We have a one-time 3 day plan just for you.</p>
		<h3>How do I find the model I&apos;m looking for?</h3>
		<p>Our Gun Values system offers multiple ways for you to locate the exact firearm you&apos;re looking for. </p>		
		<p><strong><a href="{$root}">Browse by Manufacturer</a></strong></p>
		<p>Browse our firearm values database to find the model you want. Begin by choosing the first letter of the Manufacturer of your model, then choose the manufacturer name from the list, then choose the name of the model. If model photos are available you can click the &ldquo;Browse by Photo&rdquo; button to see all the model photos available for that manufacturer.</p>
		<p><strong><a href="{$root}">Searching</a></strong></p>
		<p>If you know the name of the model you&apos;re looking for you can enter search terms into the search box to return a list of matching results. If there are too many results returned we recommend running your search again and unchecking some of the criteria boxes to narrow your search results. Checking the &ldquo;Descriptions&rdquo; criteria box is likely to return many results, so we recommend leaving that one unchecked.</p>
		</div>
		<div class='half-page faq'>
		<h3>How Do I Cancel My Account?</h3>
		<p>You may easily cancel your subscription to GunValues by logging into your account and navigating to your Account Page. To reach your Account Page, first <a href="/login/">log into your account</a>. Once you are logged in to your account click on your name in the upper right-hand corner after the words “Logged in as” to reach your Account Page. From your Account Page you can manage your email settings, or cancel your account outright.</p>
		<h3>Where does your pricing information come from?</h3>
		<p>The pricing and information contained in the Gun Values by Gun Digest database is the same information contained in the Standard Catalog of Firearms&#8212;an annual firearm values book published by Gun Digest Media. Keeping up with the change in values is an ongoing process. Our contributing editors, collectors, and experts in the various categories monitor the sales of used and collectible guns from numerous sources to provide us with up-to-date prices. Take some time to become familiar with our <a href="/grading-system">Grading System information</a>, which explains how to assess the condition of any firearm you might want to buy or sell. Remember, prices shown are estimated retail values. A dealer will most likely offer less to give some room for a profit margin. The retail estimates should be considered as a guide to what an individual would expect to pay.</p>
		<h3>What information does Gun Values by Gun Digest contain?</h3>
		<p>Our database contains estimated values and detailed descriptions for virtually every rifle, shotgun, and handgun manufactured in the United States or imported since the early 1800s. Values are shown for up to six condition grades with premiums added for special features. The complete range of firearms is covered by this database, from highly sought-after collectibles to those commonly seen on the used gun rack at your local gun store. Current production models are updated every year.</p>
		<h3>Why isn&apos;t there a picture of every single gun?</h3>
		<p>Where appropriate we include an image of every firearm that we have in the database. Exceptions include series where one image may serve to convey the sense of the entire series, or situations where different models are virtually identical. If the model you are looking at does not include an image, check the description for information on similar models, or guns in the same series. We are endeavoring to increase the number of photos so that no model is visually unrepresented. Have a picture of a model we&apos;re missing that you would like to submit? Send the image and model information to <a href="mailto:info@gundigest.com">info@gundigest.com</a>.</p>
		</div>
EOD;
$head = <<<EOD
<!-- This is the PayTrace End-to-End Encryption library: -->
<script src="https://api.paytrace.com/assets/e2ee/paytrace-e2ee.js"></script>
<script>
        // This binds the form's submit event
        $(document).ready(function() {		
			
			//Subscription Choice
			$('.sub_plan').click(function(){
				var choice = $(this).attr('id');
				$('.sub_plan').removeClass("chosen");
				$(this).addClass("chosen");
				$("#plan").val(choice);				
				$("#payment-form").submit();
			});
			
			// do this first, or wrap in a try/catch to ensure the form is never un-hooked
			//paytrace.hookFormSubmit('#payment-form');
			// set the key from an AJAX call (in this case via a relative URL)
			//paytrace.setKeyAjax('{$root}model/public_key.pem');
        });
</script>
EOD;
