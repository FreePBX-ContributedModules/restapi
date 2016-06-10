<?php

$this->add_map('get', '/callforward', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
));

$this->add_map('get', '/callforward/users', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
        'method'        => 'get_callforward_users',
	'type'		=> 'user'
));

$this->add_map('get', '/callforward/users/:id', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
        'method'        => 'get_callforward_users_id',
        'type'          => 'user'
));

$this->add_map('get', '/callforward/users/:id/ringtimer', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
        'method'        => 'get_callforward_users_id_ringtimer',
        'type'          => 'user'
));

$this->add_map('put', '/callforward/users/:id', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
        'method'        => 'put_callforward_users_id',
        'type'          => 'user'
));

$this->add_map('put', '/callforward/users/:id/ringtimer', 'callforward', array(
        'path'          => 'callforward.php',
        'controller'=> 'restapi_Callforward',
        'method'        => 'put_callforward_users_id_ringtimer',
        'type'          => 'user'
));

?>
