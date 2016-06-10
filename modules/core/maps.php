<?php

$this->add_map('get', '/core/users', 'core', array(
        'path'          => 'core.php',
        'controller'=> 'restapi_Core',
        'method'        => 'get_users',
));

$this->add_map('get', '/core/users/:id', 'core', array(
        'path'          => 'core.php',
        'controller'=> 'restapi_Core',
	'method'	=> 'get_user_id',
));


?>
