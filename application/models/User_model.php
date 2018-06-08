<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}
	
	public function save($user_data)
	{
		$this->db->db_debug = false;
		$user_data = clean_db_data_array($user_data);
		
		if(key_exists('id', $user_data)){
			$this->db->where('id', $user_data['id']);
			$this->db->update('user', $user_data);
			$user_id = $user_data['id'];
		}
		else{
			$this->db->insert('user', $user_data);
			$user_id = $this->db->insert_id();
		}
		
		$error = $this->db->error();
		
		if($this->db->affected_rows() > 0 && $error['code'] == 0){
			return $user_id;
		}
		elseif($error['code'] == 1062){ //entrada duplicada
			return "Este email já existe";
		}
		else{
			return "Nenhuma alteração feita";
		}
	}
	
	public function get_users($user_id = FALSE, $email = FALSE)
	{
		if($user_id){
			$this->db->where('id', $user_id);
		}

		if($email){
			$this->db->where('email', $email);
		}
		
		$query = $this->db->get('user');
		
		if($query->num_rows() > 0){
			if($query->num_rows() > 1){
				return $query->result_array();
			}
			else{
				return $query->row_array();
			}
		}
		else{
			return FALSE;
		}
	}
	
	public function get_invites($invite_id = FALSE)
	{
		if($invite_id){
			$this->db->where('id', $invite_id);
			$this->db->where('converted', 0);
			$query = $this->db->get('user_invite');
			return $query->row_array();
		}
		else{
			$query = $this->db->get('user_invite');
			return $query->result_array();
		}
	
	}
	
	public function login($email, $pass)
	{
		$user = $this->db->get_where('user', array('email' => $email, 'password' => $pass));
		
		if($user->num_rows() > 0){
			return $user->row_array();
		}
		else{
			return FALSE;
		}
	}
	
	public function save_token($token, $user_id)
	{
		if(!$token){
			$user = self::get_users($user_id);
			$token = create_token(array(
					'id' => $user['id'],
					'email' => $user['email'],
					'name' => $user['name'],
					'admin' => (bool)$user['admin'],
			));
		}
		
		$this->db->where('id', $user_id);
		$this->db->update('user', array('code' => $token));
		
		if($this->db->affected_rows() > 0){
			return $token;
		}
		else{
			return FALSE;
		}
	}
	
	public function save_invite($data)
	{
		if(!isset($data['id'])){
			$data['date'] = date("Y-m-d H:i:s");
			$this->db->insert('user_invite', $data);
		}
		else{
			$this->db->where('id', $data['id']);
			$this->db->update('user_invite', $data);
		}
		
		if($this->db->affected_rows() > 0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	public function exists($email)
	{
		$exists = $this->db->get_where('user', array('email' => $email));
		
		if($exists->num_rows() > 0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	
}