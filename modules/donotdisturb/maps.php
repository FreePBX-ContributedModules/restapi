<?php

$this->add_map('get', '/donotdisturb', 'donotdisturb', array(
        'path'		=> 'donotdisturb.php',
        'controller'=> 'restapi_Donotdisturb',
));

$this->add_map('get', '/donotdisturb/users', 'donotdisturb', array(
        'path'		=> 'donotdisturb.php',
        'controller'=> 'restapi_Donotdisturb',
        'method'	=> 'get_donotdisturb_users',
		'type'		=> 'user'
));

$this->add_map('get', '/donotdisturb/users/:id', 'donotdisturb', array(
        'path'		=> 'donotdisturb.php',
        'controller'=> 'restapi_Donotdisturb',
        'method'	=> 'get_donotdisturb_users_id',
        'type' 		=> 'user'
));

$this->add_map('put', '/donotdisturb/users/:id', 'donotdisturb', array(
        'path'		=> 'donotdisturb.php',
        'controller'=> 'restapi_Donotdisturb',
        'method'	=> 'put_donotdisturb_users_id',
        'type'		=> 'user'
));

?>
