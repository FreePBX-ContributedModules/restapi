<?php

$this->add_map('get', '/queues', 'queues', array(
        'path' => 'queues.php',
        'controller' => 'restapi_Queues',
	'method' => 'get_queues',
));

$this->add_map('get', '/queues/members', 'queues', array(
        'path' => 'queues.php',
        'controller' => 'restapi_Queues',
	'method' => 'get_queues_members',
));

$this->add_map('get', '/queues/queue/:id', 'queues', array(
        'path'          => 'queues.php',
        'controller'=> 'restapi_Queues',
        'method'        => 'get_queue_id',
));

?>
