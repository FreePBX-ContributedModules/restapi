<?php

$this->add_map('get', '/fax', 'fax', array(
	'path'		=> 'fax.php',
	'controller'=> 'restapi_Fax',
));

$this->add_map('get', '/fax/detect', 'fax', array(
	'path'		=> 'fax.php',
	'controller'=> 'restapi_Fax',
	'method'	=> 'get_fax_detect'
));

$this->add_map('post', '/fax', 'fax', array(
	'path'		=> 'fax.php',
	'controller'=> 'restapi_Fax',
	'method'	=> 'post_fax'
));

$this->add_map('put', '/fax', 'fax', array(
	'path'		=> 'fax.php',
	'controller'=> 'restapi_Fax',
	'type'		=> 'user',
	'method'	=> 'put_fax'
));

$this->add_map('get', '/fax/users', 'fax', array(
	'path'		=> 'fax_users.php',
	'controller'=> 'restapi_FaxUsers',
	'method'	=> 'get_fax_users',
	'type'		=> 'user'
));

$this->add_map('get', '/fax/users/:id', 'fax', array(
	'path'		=> 'fax_users.php',
	'controller'=> 'restapi_FaxUsers',
	'method'	=> 'get_fax_users_id',
	'type'		=> 'user'
));

$this->add_map('put', '/fax/users/:id', 'fax', array(
	'path'		=> 'fax_users.php',
	'controller'=> 'restapi_FaxUsers',
	'type'		=> 'user'
));


?>
