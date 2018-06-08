<?php
if ( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

class SessionHook {
	private $CI;
	private $header;

	function __construct(){
        $this->CI =& get_instance();	
        $this->header = getallheaders();
		$this->CI->load->model('User_model');
		$this->CI->load->helper('jwt');		
    }

	public function check_login() {		
		
		
		if( isset($this->header['Authorization']) 
				&& $this->CI->router->fetch_method() != "login"
				&& $this->CI->router->fetch_method() != "teste"
				&& $this->CI->router->fetch_method() != "invite"
				&& $this->CI->router->fetch_method() != "password"){
		
			$token = token_validate( explode(" ", $this->header['Authorization'])[1] );
			
			if(!$token){
				//header("HTTP/1.1 401 Unauthorized");
				header('Content-Type: application/json; charset=UTF8');
				echo json_encode(array(
						"errorDescription" => "Access token is unknown or invalid",
						"error" => "not_authorized"
				));			
	    		exit;	
			}
			elseif ($token->exp < strtotime('now')){
				//header("HTTP/1.1 401 Unauthorized");
				header('Content-Type: application/json; charset=UTF8');
				echo json_encode(array(
						"errorDescription" => "Token has expired",
						"error" => "not_authorized"
				));
				exit;
			}
		}
		elseif (!isset($this->header['Authorization']) 
				&& $this->CI->router->fetch_method() != "login"
				&& $this->CI->router->fetch_method() != "teste"
				&& $this->CI->router->fetch_method() != "invite"
				&& $this->CI->router->fetch_method() != "password"){
			//header("HTTP/1.1 401 Unauthorized");
			header('Content-Type: application/json; charset=UTF8');
			echo json_encode(array(
					"errorDescription" => "Access token is unknown or invalid",
					"error" => "not_authorized"
			));			
    		exit;
		}
		
	}

}