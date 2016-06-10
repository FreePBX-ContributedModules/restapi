<?php

$this->add_map('get', '/presencestate', 'presencestate', array(
	'path' => 'presencestate.php',
	'controller' => 'restapi_Presencestate',
));

$this->add_map('get', '/presencestate/list', 'presencestate', array(
	'path' => 'presencestate.php',
	'controller' => 'restapi_Presencestate',
	'method' => 'get_presencestate_list',
));

$this->add_map('get', '/presencestate/types', 'presencestate', array(
	'path' => 'presencestate.php',
	'controller' => 'restapi_Presencestate',
	'method' => 'get_presencestate_types',
));

$this->add_map('get', '/presencestate/prefs/:extension', 'presencestate', array(
	'path' => 'presencestate.php',
	'controller' => 'restapi_Presencestate',
	'method' => 'get_presencestate_prefs_extension',
        'type' => 'user'
));

$this->add_map('put', '/presencestate/prefs/:extension', 'presencestate', array(
        'path' => 'presencestate.php',
        'controller' => 'restapi_Presencestate',
	'method' => 'put_presencestate_prefs_extension',
        'type' => 'user'
));

?>
