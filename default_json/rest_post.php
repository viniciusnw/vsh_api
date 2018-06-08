<?php

$diretorio = "user";
$arquivo_json = "coringa.json";

$data_string = file_get_contents("json/". $diretorio ."/". $arquivo_json); 

$token = 'your token here';

$authorization = "Authorization: Bearer eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1MDEzNTM4MzUsIm5hbWUiOiJGcmVkYW8gZGEgbWFzc2EiLCJ1c2VyX2lkIjoiNiIsInVzZXJfZW1haWwiOiJmMTI3MzQxOUBtdnJodC5jb20iLCJhZG1pbiI6ZmFsc2V9.fLf_xdBgHe3--dfRkpi0kGTdEotOMK3DX7phWSS79ho";

//UPLOAD
$file_name_with_full_path = realpath('./avatar.jpeg');
$data_string = json_encode(array('extra_info' => '123456','file_contents'=>'@'.$file_name_with_full_path));


// set up the curl resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://vsh-api/user/teste");								

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string),
	$authorization
));       


$output = curl_exec($ch);

echo($output) . PHP_EOL;
