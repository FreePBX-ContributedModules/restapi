<?php

$this->add_map('get', '/parking', 'parking', array(
        'path'          => 'parking.php',
        'controller'=> 'restapi_Parking',
        'method'        => 'get_parking',
));

$this->add_map('put', '/parking', 'parking', array(
        'path'          => 'parking.php',
        'controller'=> 'restapi_Parking',
	'method'	=> 'put_parking',
));


?>
