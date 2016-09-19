<?php

$this->add_map('get', '/conferences', 'conferences', array(
		'path' => 'conferences.php',
		'controller' => 'restapi_Conferences',
		'method' => 'get_conferences',
));

$this->add_map('get', '/conferences/:id', 'conferences', array(
		'path'          => 'conferences.php',
		'controller'=> 'restapi_Conferences',
		'method'        => 'get_conference_id',
));

$this->add_map('delete', '/conferences/:id', 'conferences', array(
		'path'          => 'conferences.php',
		'controller'=> 'restapi_Conferences',
		'method'        => 'delete_conference_id',
));

$this->add_map('put', '/conferences/:id', 'conferences', array(
		'path' => 'conferences.php',
		'controller' => 'restapi_Conferences',
		'method' => 'put_conference_id',
));

?>
