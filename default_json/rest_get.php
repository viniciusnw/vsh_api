<?php


$id = 6;

// the token

$token = 'your token here';

// set up the curl resource

//expirado
$authorization = "Authorization: Bearer eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE0Njk2NTU3OTQsIm5hbWUiOiJKYWNrIiwidXNlcl9pZCI6IjEiLCJ1c2VyX2VtYWlsIjoiamFja25hbmV0QGdtYWlsLmNvbSIsImFkbWluIjpmYWxzZX0.0P7lES6k9HstAHRGb1ia012wXs7FuGWBm65COExyDeQ";

//normal
//$authorization = "Authorization: Bearer eyJ0eXAiOiJqd3QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1MDExOTAxNzUsIm5hbWUiOiJKYWNrIiwidXNlcl9pZCI6IjEiLCJ1c2VyX2VtYWlsIjoiamFja25hbmV0QGdtYWlsLmNvbSIsImFkbWluIjpmYWxzZX0.IRRZzLVq2ycR6rjx1iJL3pLvFL2GgyhB62MyEOaCrvU";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://vsh-api/user/users/" . $id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string),
		$authorization
));

// execute the request

$output = curl_exec($ch);

// output the profile information - includes the header

echo $output . PHP_EOL;

// close curl resource to free up system resources

curl_close($ch);