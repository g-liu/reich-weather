<?php
	define('API_ENDPOINT', 'http://freegeoip.net/');
	define('FORMAT', 'json');
	$userIP = $_SERVER['REMOTE_ADDR'];
	
	$request_url = API_ENDPOINT . FORMAT . '/' . $userIP;
	
	$ch = curl_init($request_url);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$content = curl_exec($ch);
	curl_close($ch);
	
	echo $content;
?>