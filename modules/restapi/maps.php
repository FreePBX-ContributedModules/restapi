<?php


$this->add_map('get', '/restapi/tokens/search', 'restapi', array(
		'path'		=> 'restapi.php',
		'controller'=> 'restapi_Restapi',
		'method'	=> 'get_restapi_tokens_search',
		'authentication' => false,
		'authorization'	=> false,
		'accounting'	=> false
));
