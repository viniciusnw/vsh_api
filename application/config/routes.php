<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['user/(:num)'] = 'user/users/id/$1'; 
$route['user/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'user/users/id/$1/format/$3$4'; 

$route['login/(:num)'] = 'user/login/id/$1'; 
$route['login/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'user/login/id/$1/format/$3$4'; 

$route['invite/(:num)'] = 'user/invite/id/$1'; 
$route['invite/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'user/invite/id/$1/format/$3$4'; 

$route['password/(:num)'] = 'user/password/id/$1'; 
$route['password/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'user/password/id/$1/format/$3$4'; 

$route['teste/(:num)'] = 'user/teste/id/$1'; 
$route['teste/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'user/teste/id/$1/format/$3$4'; 

