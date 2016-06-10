<?php

$this->add_map('get', '/callwaiting', 'callwaiting', array(
        'path'          => 'callwaiting.php',
        'controller'=> 'restapi_Callwaiting',
));

$this->add_map('get', '/callwaiting/users', 'callwaiting', array(
        'path'          => 'callwaiting.php',
        'controller'=> 'restapi_Callwaiting',
        'method'        => 'get_callwaiting_users',
	'type'		=> 'user'
));

$this->add_map('get', '/callwaiting/users/:id', 'callwaiting', array(
        'path'          => 'callwaiting.php',
        'controller'=> 'restapi_Callwaiting',
        'method'        => 'get_callwaiting_users_id',
        'type'          => 'user'
));

$this->add_map('put', '/callwaiting/users/:id', 'callwaiting', array(
        'path'          => 'callwaiting.php',
        'controller'=> 'restapi_Callwaiting',
        'method'        => 'put_callwaiting_users_id',
        'type'          => 'user'
));

?>
