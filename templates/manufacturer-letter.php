<?php
//Single Gun Page Content
if(!(isset($letter))){
		$page_name = "No Results Found";
		$html .= <<<EOD
		<h2 id="mfg_title">There are no Manufacturer Results for this letter</h2>			
		<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
	}else{
		$results = getMakes($letter);	
		$page_name = "Manufacturers " . $letter;
		$html = "<div class='drop-cap'>" . $letter . "</div><div class='top-featured .letter-page'><h2 class='subhead'>Choose a Manufacturer</h2></div>";
		$count = 0;
		foreach($results AS $result){
			$count++;
			$html .= "<a class='button' href='" . $root . $result['slug'] . "/'>" . proper_case($result['make']) . "</a>";
		}
		if($count<1){
		$page_name = "No Results Found";
		$html .= <<<EOD
		<h2 id="mfg_title">There are no Manufacturer Results for this letter</h2>		
		<a class="button" href="' . $root . '">Search or Browse</a>
EOD;
		}
		
}
?>



