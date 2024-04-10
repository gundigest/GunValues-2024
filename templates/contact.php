<?php session_unset();
$page_name = "Contact Us";
if(isset($_GET['success'])){
	if($_GET['success']==1){//success!
		$banner = '<div class="announcement">Thank you for contacting Gun Values by Gun Digest! One of our team members will respond to you shortly.</div>';
	}else{//error :(
		$banner = '<div class="error">There has been an error with your message. Please try again.</div>';
	}
}
$html = <<<EOD
{$banner}
<!--Contact Us Form-->											
			<div class="half-page">
				<div class="search-box-text">Contact Us</div>					
				<p class="form-width">Do you have questions about our <strong>Gun Values program</strong> or <strong>your Account</strong>? No problem! Please choose your reason for contacting us.</p>
			<div class="search-box-text">Have More Questions?</div>
				<p class="form-width">Try taking a look at <a href="/faq/">our FAQ</a></p>
			</div>			
			<div class="half-page">
					<div class="search-box-text contact_button">Why do you want to Contact Gun Values?</div>
					<a type="submit" class="contact_button button full center bright" href="javascript:showParts()">I am looking to purchase parts for a firearm</a>
					<p class="parts_contact form-width">Unfortunately GunValues only provides values and <strong>does not have any information about firearm parts or where to acquire them</strong>. We wish you good luck in your search.</p>
					<a type="submit" class="contact_button button full center bright" href="javascript:showIdentify()">I need help identifying a firearm</a>
					<p class="identify_contact form-width">While GunValues does provide pricing information for over 20,000 distinct firearms this information is provided <em>as a reference only</em>. GunValues by Gun Digest is <strong>not able to provide appraisal or identification services</strong> at this time.</p>
					<a type="submit" class="contact_button button full center bright" href="javascript:showCancel()">I want to cancel my account</a>
					<p class="cancel_contact form-width">You may easily cancel your subscription to GunValues by logging into your account and navigating to your Account Page. To reach your Account Page, first <a href="/login/">log into your account</a>. Once you are logged in to your account click on your name in the upper right-hand corner after the words &ldquo;Logged in as&rdquo; to reach your Account Page. From your Account Page you can manage your email settings, or cancel your account outright.</p>					
					<a type="submit" class="contact_button button full center bright" href="javascript:openContact()">I cannot log in to my account</a>
					<a type="submit" class="contact_button button full center bright" href="javascript:openContact()">I am having a billing issue</a>
					<form action="{$root}model/contact_process.php" METHOD="POST" id="contact-form"">
						<div class="search-box-text"></div>				
						<p class="form-width">Please complete this form and describe your issue in detail. A Gun Values representative will contact you soon.<br/><br/><em>Please do NOT paste any website addresses (URLs) into your message, or it will not be sent.</em></p>
						<div class="half first">
							<input type="text" name="name"  placeholder="Name" required/>
						</div>
						<div class="half last">
							<input type="email" id="email" name="email" placeholder="Email Address" required/>
						</div>
						<textarea name="message" id="" rows="5" placeholder="Your Message" required></textarea>
						<div class="full">
							<div class="g-recaptcha" data-theme="light" data-sitekey="6LdJf7UUAAAAAOtWcG_IDiBQu9aXnS9T-iIf2EZW"></div>
						</div>
						<button type="submit" class="button full center bright" id="submit_button">Send Message</button>
					</form>
				</div>

		
EOD;
$head = <<<EOD
<!-- This is the PayTrace End-to-End Encryption library: -->
<script src="https://api.paytrace.com/assets/e2ee/paytrace-e2ee.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
		//Contact form displays
		function openContact(){
			$('#contact-form').show();
			//$('.contact_button').hide();
		}
		function showCancel(){
			$('.cancel_contact').show();
			//$('.contact_button').hide();
		}
		function showIdentify(){
			$('.identify_contact').show();
			//$('.contact_button').hide();
		}
		function showParts(){
			$('.parts_contact').show();
			//$('.contact_button').hide();
		}
$(document).ready(function() {
	$('#submit_button').click(function (event) {
		event.preventDefault();
		//Check that all fields have been completed
		alert_text = "";
		error=false;
		name_content = $('input[name=name]').val();
		if (name_content.length==0){
		  alert_text += "Please complete your Name.<br/> ";      
		  $('input[name=name]').removeClass('complete').addClass('incomplete');
		  error=true;	
		}
		email_content = $('input[name=email]').val();
		if (email_content.length==0){
		  alert_text += "Please complete your Email.<br/>";      
		  $('input[name=email]').removeClass('complete').addClass('incomplete');
		  error=true;	
		}
		msg_content = $('textarea[name=message]').val();
		if (msg_content.length==0){
		  alert_text += "Please add your Message.<br/>";      
		  $('textarea[name=message]').removeClass('complete').addClass('incomplete');
		  error=true;	
		}
		if(error==true){
			$( '#contact-form' ).prepend('<div class="error">There has been an error with your message.<br/> ' + alert_text + '</div>');
		}else{
			var postData = $('#contact-form').serialize();
			$.post( "{$root}model/reCaptchaProcessing.php", postData, function( data ) {	
				console.log(data);
			   if (data==="Success"){
				 $('#contact-form').submit();	
			   }else{		
					console.log("not recaptcha");
					$( '#contact-form' ).prepend('<div class="error">There has been an error with your message. Please try again.</div>');
				}	
			});
		}
	});//end sub_button click
});
</script>
EOD;
