<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function sendgrid_send_email($data)
{		
	$data_string = array(
		'to'		=> $data['to'],
		'subject'	=> $data['subject'],
		'text'	=> $data['text'],
		'from'	=> $data['from'],
	);
	if(isset($data['toname'])){
		$data_string['toname'] = $data['toname'];
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/api/mail.send.json");
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. SENDGRID_KEY));
	curl_setopt($ch, CURLOPT_HEADER, false);	
	
	$output = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	
	return json_decode($output, 1);	
}