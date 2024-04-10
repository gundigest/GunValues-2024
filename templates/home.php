<?php
//Index/Home Page Content
		$letters = getAllMakeLetters();	
		$guntypes = getAllGunTypes();
		$page_name = "";
$html =<<<EOD
<h2 class="subhead">Let&apos;s Find Your Gun</h2>
<div class='half-page first'>

	<h2>Search</h2>
	<form action="" method="GET" id="search_form">	
		<div class="error"></div>
		<input type="text" name="search_term" placeholder="Manufacturer, Model, Series..." pattern='[a-zA-Z0-9 &-\\\"]+' required/>
		<div class="search_options">
			<div><input type="checkbox" name="search_option" value="Manufacturers" id="m" checked/><label for="search_option_manufacturers">Manufacturers</label></div>
			<div><input type="checkbox" name="search_option" value="Models" id="g" checked/><label for="search_option_models">Models</label></div>
			<div><input type="checkbox" name="search_option" value="Series" id="s" checked/><label for="search_option_series">Series</label></div>
			<div><input type="checkbox" name="search_option" value="Descriptions" id="d"/><label for="search_option_series">Descriptions</label></div>
		</div>
		<button type="submit" class="button center bright">Search</button>
	</form>
</div>
<div class='half-page'>
<h2>Browse by Manufacturer</h2>
	<div class="letters">
EOD;
		foreach($letters AS $letter){
			$html .= "<div class='button letter'><a href='" . $root . "manufacturers-" . $letter['alpha'] . "'>" . $letter['alpha'] . "</a></div>";
		}		
/*$html .=<<<EOD
	</div>
	<div class='search-box-text'>By Gun Type</div>
	<div class="guntypes">
EOD;
//Gun Types from the DB
		foreach($guntypes AS $guntype){
			$gt_slug = slugify($guntype['name']);
			$html .= "<div class='button guntype'><a href='" . $root . "guntype/" . $guntype['gid'] . "/" . $gt_slug . "'>" . $guntype['name'] . "</a></div>";
		}*/

$html .=<<<EOD
	</div>	
</div>
<div class="subsection">		
		<div class="subhead">Gun values when you need them.</div>
		<div class="tagline">The most complete online reference and price guide for the gun enthusiast.</div>
		<a class='button center bright' href='{$root}register'>Choose a Plan</a>
</div>

EOD;
$head = <<<EOD
<script type="text/javascript">
	$(document).ready(function() {
		//Don't show top-spacer
		//$('.top-spacer').css('height','40px');
		$('#search_form').submit( function(){			
			$('.error').hide();
			if ( $( "input[name=search_term]" ).val().length >0 ) {				
				search_term = encodeURI($( "input:first" ).val());
				params = "";
				$( "input[name=search_option]" ).each(function(){
					if(!$(this).is(':checked')) params += $(this).attr('id');
				});
				if(params.length>0) params = "|" + params;							
				window.location = "search/" + search_term + params;	
			}else{
				error="Please Enter at Least one search term";
				$('.error').show();
			}
			event.preventDefault();
		});
		$( "input[name=search_term]" ).keyup(function() {
			  var val_old = $(this).val();
			  var val = val_old.replace(/[^A-Za-z0-9 &-\\\"]/g, '');			  			  
			  if (val != val_old) $(this).focus().val('').val(val);			
		});		
	});

</script>
EOD;
$meta_description = "Easily look up new and used firearm values. Gun Values by Gun Digest brings you the authority of our annual gun pricing guide, The Standard Catalog of Firearms, in a simple online package. Search or browse models and manufacturer info for free.";	
?>



