<?php

require(APPPATH.'libraries/REST_Controller.php');

class User extends REST_Controller {
	
	private $token_data;

	public function __construct()
	{       
		parent::__construct();
		$this->load->model('User_model');
		$this->load->helper('jwt');
		$this->load->helper('user');
		$this->load->helper('sendgrid');
		
		$this->token_data = get_token_data();		
		
		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
// 		$this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
// 		$this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
// 		$this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
	} 
	
	//Insere um usuario por convite
	public function users_post()
	{									
		if($this->post('password') == $this->post('pass_confirm')){		
			$user_data = array(
				'name' => $this->token_data->name,	
				'password' =>set_md5_pass( $this->post('password') ),	
				'email' => $this->token_data->user_email
			);
			$user = $this->User_model->save($user_data);
			
			if(is_numeric($user)){		
				$this->response([
						'status' => TRUE,
						'id' => $user, 
						'name' => $this->token_data->name,
						'email' => $this->token_data->user_email,
						'message' => 'Usuário Criado'
				], REST_Controller::HTTP_OK);
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => $user
				], REST_Controller::HTTP_OK);				
			}		
		}
		else{
			$this->response([
					'status' => FALSE,
					'message' => 'As duas senhas digitadas não estão iguais.'
			], REST_Controller::HTTP_OK);
		}
	}
	public function users_get( $id = null )
	{	

        // If the id parameter doesn't exist return all the users
        if ($id === NULL)
        {
           $users = $this->User_model->get_users();
        	
           $this->response([
           		'status' => TRUE,
           		'data' => $users
           ], REST_Controller::HTTP_OK);
        }

        else{
	        $id = (int) $id;	
	        
	        if ($id <= 0)
	        {
	            // Invalid id, set the response and exit.
	            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
	        }     
	        
	        $user = $this->User_model->get_users($id);
	        if (!empty($user))
	        {
	        	$this->response([
	        			'status' => TRUE,
	        			'data'   => $user
	        	], REST_Controller::HTTP_OK);
	        }
	        else
	        {
	            $this->set_response([
	                'status'  => FALSE,
	                'message' => 'User could not be found'
	            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
	        }
        }
	}
	
	public function users_put()
	{
		$this->load->helper('upload');
		$config = unserialize(FOTO_AVATAR);
		$error = null;
		$nome_arquivo = FALSE;
		if($this->put('avatar')){
			$nome_arquivo = upload_base64_image($this->put('avatar'), $config, $error);
		}

		$data_user = array(
			'id' 	=> $this->token_data->user_id,
			'name'	=> $this->put('name'),
			'email'	=> $this->put('email'),
			'phone'	=> $this->put('phone'),
			'occupation'	=> $this->put('occupation'),
			'avatar'		=> !$error && $nome_arquivo ? base_url().$config['upload_path'].$nome_arquivo : null
		);
				
		$user = $this->User_model->save($data_user);
		if(is_numeric($user)){
			//Mandar email caso mude
			if($data_user['email'] != ''){
				$send_mail = sendgrid_send_email(array(
						'to'		=> $data_user['email'],
						'subject'	=> 'Email Alterado',
						'text'	=> 'Seu email foi alterado no sistema.',
						'from'	=> 'contato@vaisershow.com',
				));
			}			
			//altera o token com as mudanças
			$token = $this->User_model->save_token(FALSE, $user);			
			
			$this->set_response([
					'status'  => TRUE,
					'message' => 'Usuário atualizado com sucesso.',
					'token' => $token,
					'upload_error' => $error
			], REST_Controller::HTTP_OK);			
		}
		else{
			$this->set_response([
					'status'  => FALSE,
					'message' => $user,
					'upload_error'   => $error
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function login_post()
	{		
            
		$user = $this->User_model->login( $this->post('email'), set_md5_pass($this->post('password')) );
                
		if($user){
			$token = create_token(array(
				'id' => $user['id'],
				'email' => $user['email'],
				'name' => $user['name'],
				'admin' => (bool)$user['admin'],
			));
			
			if($this->User_model->save_token($token, $user['id'])){		
				$data = [   
                        'id' => $user['id'],
						'token' => $token,
						'email' => $user['email'],
						'name' => $user['name']
				];
				
				$this->response([
						'status' => TRUE,
						'data'	=> $data,
						'message' => 'Login realizado'
				], REST_Controller::HTTP_OK);			
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => 'Erro ao salvar o token'
				], REST_Controller::HTTP_OK);
			}
			
		}
		else{
			$this->response([
					'status' => FALSE,
					'message' => 'E-mail ou senha inválida'
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function invite_post()
	{
		if($this->post('name') && $this->post('email')){
			if(!$this->User_model->exists($this->post('email'))){				
				
				$invite = $this->User_model->save_invite($this->post());
				
				if($invite){
					$send_mail = sendgrid_send_email(array(
							'to'		=> $this->post('email'),
							'toname'	=> $this->post('name'),
							'subject'	=> 'Envio de convite',
							'text'	=> 'Seu convite foi solicitado',
							'from'	=> 'contato@vaisershow.com',
					));
					
					if($send_mail['message'] == "success"){
						$this->response([
								'status' => TRUE,
								'message' => 'Convite solicitado com sucesso.'
						], REST_Controller::HTTP_OK);
					}
					else{
						$this->response([
								'status' => FALSE,
								'message' => 'Erro ao enviar o email.'
						], REST_Controller::HTTP_OK);
					}
				}
				else{
					$this->response([
							'status' => FALSE,
							'message' => 'Ocorreu um erro no servidor ao tentar salvar o convite'
					], REST_Controller::HTTP_OK);
				}
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => 'Este e-mail já está cadastrado no sistema.'
				], REST_Controller::HTTP_OK);
			}
		}
		else{
			$this->response([
					'status' => FALSE,
					'message' => 'Você precisa informar seu e-mail e nome'
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function invite_put()
	{
				
			$invite = $this->User_model->get_invites($this->put('id'));
			if($invite){
				
				$token = create_token(array(
						'id' => $invite['id'],
						'email' => $invite['email'],
						'name' => $invite['name'],
						'admin' => FALSE
				));
				$this->User_model->save_invite(array(
						'id' => $invite['id'],
						'token' => $token,
				));
				
				$send_mail = sendgrid_send_email(array(
						'to'		=> $invite['email'],
						'toname'	=> $invite['name'],
						'subject'	=> 'Seu convite chegou',
						'text'	=> "Parabens. Entre no link http://vsh/{$token} para se cadastrar.",
						'from'	=> 'contato@vaisershow.com',
				));
				
				$this->response([
						'status' => TRUE,
						'message' => 'Convite enviado com sucesso.'
				], REST_Controller::HTTP_OK);
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => 'Este convite não existe no sistema.'
				], REST_Controller::HTTP_OK);
			}
			
		
	}
	
	//função para receber solicitacao de alteracao de senha
	public function password_post()
	{
		if($this->post('email')){
			$user = $this->User_model->get_users(FALSE, $this->post('email'));
			if($user){						
				$token = create_token(array(
						'id' => $user['id'],
						'email' => $user['email'],
						'name' => $user['name'],
						'admin' => FALSE
				));
				
				$send_mail = sendgrid_send_email(array(
						'to'		=> $user['email'],
						'toname'	=> $user['name'],
						'subject'	=> 'Recuperação de senha',
						'text'	=> 'Entre no link para recuperar sua senha: http://vsh/'.$token,
						'from'	=> 'contato@vaisershow.com',
				));
					
				if($send_mail['message'] == "success"){
					$this->response([
							'status' => TRUE,
							'message' => 'Email enviado.'
					], REST_Controller::HTTP_OK);
				}
				else{
					$this->response([
							'status' => FALSE,
							'message' => 'Erro ao enviar o email. '
					], REST_Controller::HTTP_OK);
				}				
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => 'Este e-mail não está cadastrado no nosso sistema.'
				], REST_Controller::HTTP_OK);
			}
		}
		else{
			$this->response([
					'status' => FALSE,
					'message' => 'Você precisa informar seu e-mail.'
			], REST_Controller::HTTP_OK);
		}
	}
	
	//funcao para receber nova senha do usuario
	public function password_put()
	{		
		$user = $this->User_model->get_users($this->token_data->user_id);
		if($user && $this->put('password') == $this->put('pass_confirm') && strlen($this->put('password')) >= 5){
			//caso seja uma altercao de senha normal, checa a senha antiga
			if($this->put('old_pass') && set_md5_pass($this->put('old_pass')) != $user['password']){
				$this->response([
						'status' => FALSE,
						'message' => 'A senha antiga está incorreta.'
				], REST_Controller::HTTP_OK);
			}
			
			$user_data = array(
					'password' 	=> set_md5_pass( $this->put('password') ),
					'id'		=> $this->token_data->user_id
			);
			$user = $this->User_model->save($user_data);
			
			if(is_numeric($user)){
				$this->response([
						'status' => TRUE,
						'id' => $user,
						'name' => $this->token_data->name,
						'email' => $this->token_data->user_email,
						'message' => 'Senha alterada com sucesso.'
				], REST_Controller::HTTP_OK);
			}
			else{
				$this->response([
						'status' => FALSE,
						'message' => $user
				], REST_Controller::HTTP_OK);
			}
		}
		else{
			if($this->put('password') == ''){
				$msg_erro = "Por favor, informe sua nova senha";
			}
			elseif(strlen($this->put('password')) < 5) {
				$msg_erro = "Sua senha deve ter pelo menos 5 caracteres.";
			}
			else{
				$msg_erro = 'Este usuário não existe ou sua senha não foi digitada duas vezes iguais.';
			}
			
			$this->response([
					'status' => FALSE,
					'message' => $msg_erro
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function teste_put()
	{
		print_r($_POST);
		$this->load->helper('upload');
		
		$nome_arquivo = do_single_upload(unserialize(FOTO_AVATAR), $error, $data, 'file');
		
		$this->response([
				'status' => TRUE,
				'message' => $data,
				'error'	=> $error
		], REST_Controller::HTTP_OK);
	}
	
}