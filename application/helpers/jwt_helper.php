<?php
function create_token($dados){
	$ci =& get_instance();
	$ci->load->library('JWT');
	
	$CONSUMER_SECRET = JWT_KEY;
	return $ci->jwt->encode(
			array(					
					"exp" => strtotime('+1 year'),
					"name" => $dados['name'],
					"user_id" => $dados['id'],
					"user_email" => $dados['email'],
					"admin" => $dados['admin']
				
	), $CONSUMER_SECRET);
	
}

function token_validate($token){
	$ci =& get_instance();
	$ci->load->library('JWT');
	
	try{
		return $ci->jwt->decode($token,  JWT_KEY, $verify = true);
	}
	catch(UnexpectedValueException $e){
		return false;
	}
}

function get_token_data()
{
	$header = getallheaders();
	if(isset($header['Authorization'])){
		return token_validate( explode(" ", $header['Authorization'])[1] );
	}
	else{
		return FALSE;
	}
}

function get_token()
{
	$header = getallheaders();
	if(isset($header['Authorization'])){
		return explode(" ", $header['Authorization'])[1];
	}
	else{
		return FALSE;
	}
}

