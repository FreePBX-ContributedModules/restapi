<?php

$this->add_map('get', '/timeconditions', 'timeconditions', array(
	'path' => 'timeconditions.php',
	'controller' => 'restapi_Timeconditions',
	'method' => 'get_timeconditions',
));

$this->add_map('get', '/timeconditions/:id', 'timeconditions', array(
	'path' => 'timeconditions.php',
	'controller' => 'restapi_Timeconditions',
	'method' => 'get_timeconditions_id'
));

$this->add_map('put', '/timeconditions/:id', 'timeconditions', array(
	'path' => 'timeconditions.php',
	'controller' => 'restapi_Timeconditions',
	'method' => 'put_timeconditions_id'
));

?>
