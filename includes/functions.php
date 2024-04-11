<?
function setDefines(){
	//Set to any development domains
	define("__PROD__",'https://gunvalues.gundigest.com/');
	
	if($_SERVER['HTTP_HOST']=='gunvalues-staging.gundigestmedia.com'){
		define("__DEBUG__",1);
		define("__DOMAIN__",$_SERVER['HTTP_HOST']);
	}else{
		define("__DOMAIN__",__PROD__);
	}
}

function devprint_r($str){
	if(__DEBUG__)
		echo '<pre>'.print_r($str,true).'</pre>';
}
function devecho($str){
	if(__DEBUG__)
		echo $str;
}
function devo($str){
	if(__DEBUG__)
		return $str;
}
function devdie($str){
	if(__DEBUG__)
		die($str);
}