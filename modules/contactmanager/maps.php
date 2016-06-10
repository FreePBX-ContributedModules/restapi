<?php

$this->add_map('get', '/contactmanager', 'contactmanager', array(
	'path' => 'contactmanager.php',
	'controller' => 'restapi_Contactmanager',
	'method' => 'get_contactmanager',
));

$this->add_map('get', '/contactmanager/groups/:id', 'contactmanager', array(
	'path' => 'contactmanager.php',
	'controller' => 'restapi_Contactmanager',
	'method' => 'get_contactmanager_groups_id',
));

$this->add_map('get', '/contactmanager/groups/:id/:groupid', 'contactmanager', array(
	'path' => 'contactmanager.php',
	'controller' => 'restapi_Contactmanager',
	'method' => 'get_contactmanager_groups_id_groupid',
));

$this->add_map('get', '/contactmanager/groups/:id/:groupid/:entryid', 'contactmanager', array(
	'path' => 'contactmanager.php',
	'controller' => 'restapi_Contactmanager',
	'method' => 'get_contactmanager_groups_id_groupid_entryid',
));

$this->add_map('get', '/contactmanager/entries/:id', 'contactmanager', array(
	'path' => 'contactmanager.php',
	'controller' => 'restapi_Contactmanager',
	'method' => 'get_contactmanager_entries_id',
));

?>
