<?php

$this->add_map('get', '/voicemail/mailboxes/:id', 'voicemail', array(
	'path' => 'voicemail.php',
	'controller' => 'restapi_Voicemail',
	'method' => 'get_mailbox_id',
));

$this->add_map('put', '/voicemail/password/:id', 'voicemail', array(
	'path' => 'voicemail.php',
	'controller' => 'restapi_Voicemail',
	'method' => 'put_password_id',
));

?>
