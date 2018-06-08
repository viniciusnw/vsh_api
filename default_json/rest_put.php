<?php

$diretorio = "user";
$arquivo_json = "coringa.json";

$data_string = file_get_contents("json/". $diretorio ."/". $arquivo_json); 

$token = 'your token';
$authorization = "Authorization: Bearer eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1MDE2MTUyNzcsIm5hbWUiOiJKYWNrIiwidXNlcl9pZCI6IjEiLCJ1c2VyX2VtYWlsIjoiamFja25hbmV0QGdtYWlsLmNvbSIsImFkbWluIjpmYWxzZX0.hBjCChl2T5CwnxWayWBvTLWomgFoIYHcdvluQHf3Nmk";

// set up the curl resources

$ch = curl_init();

//echo ("https://api.copernica.com/database/$databaseID/profiles/?$url").PHP_EOL;

//curl_setopt($ch, CURLOPT_URL, "https://api.copernica.com/database/$databaseID/profiles/?access_token=$token&$url");
curl_setopt($ch, CURLOPT_URL, "http://vsh-api/user/password");	

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string),
	$authorization
));       

// execute the request

$output = curl_exec($ch);

// output the profile information - includes the header
 
echo($output) . PHP_EOL;

// close curl resource to free up system resources

curl_close($ch);