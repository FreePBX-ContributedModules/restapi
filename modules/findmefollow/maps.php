<?php

$this->add_map('get', '/findmefollow', 'findmefollow', array(
        'path'          => 'findmefollow.php',
        'controller'=> 'restapi_Findmefollow',
));

$this->add_map('get', '/findmefollow/users', 'findmefollow', array(
        'path'          => 'findmefollow.php',
        'controller'=> 'restapi_Findmefollow',
        'method'        => 'get_findmefollow_users',
	'type'		=> 'user'
));

$this->add_map('get', '/findmefollow/users/:id', 'findmefollow', array(
        'path'          => 'findmefollow.php',
        'controller'=> 'restapi_Findmefollow',
        'method'        => 'get_findmefollow_users_id',
        'type'          => 'user'
));

$this->add_map('put', '/findmefollow/users/:id', 'findmefollow', array(
        'path'          => 'findmefollow.php',
        'controller'=> 'restapi_Findmefollow',
	'method'	=> 'put_findmefollow_users_id',
        'type'          => 'user'
));

?>
