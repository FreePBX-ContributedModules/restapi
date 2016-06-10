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

?>
