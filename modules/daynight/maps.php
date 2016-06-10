<?php

$this->add_map('get', '/daynight', 'daynight', array(
	'path' => 'daynight.php',
	'controller' => 'restapi_Daynight',
	'method' => 'get_daynight',
));

$this->add_map('get', '/daynight/:id', 'daynight', array(
	'path' => 'daynight.php',
	'controller' => 'restapi_Daynight',
	'method' => 'get_daynight_id'
));

$this->add_map('put', '/daynight/:id', 'daynight', array(
	'path' => 'daynight.php',
	'controller' => 'restapi_Daynight',
	'method' => 'put_daynight_id'
));

?>
