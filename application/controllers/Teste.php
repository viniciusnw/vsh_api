<?php
class Teste extends CI_Controller {
	private $header;
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('jwt');
		$this->load->helper('sendgrid');
		$this->header = getallheaders();
	}
	
	public function teste()
	{
		$data = array(
				'to'		=> 'jacknanet',
				'subject'	=> 'teste sendgrid',
				'text'	=> 'aaaaoooooowwww sendgrid',
				'from'	=> 'jack@vsh.com',
		);
		print_r(sendgrid_send_email($data));
	}
}