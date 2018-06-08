<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller_constructor'][] = array(
		'class'    => 'SessionHook',
		'function' => 'check_login',
		'filename' => 'SessionHook.php',
		'filepath' => 'hooks'
);
