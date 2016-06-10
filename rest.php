<?php
//redirect to config.php if it seems like thats what we really want
if (empty($_SERVER['PATH_INFO'])) {
	header("Location: /admin/config.php");
} else {
	// bootstrap freepbx
	$bootstrap_settings['freepbx_auth'] = false;
	$restrict_mods = array('restapi' => true);
	if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) { 
		include_once('/etc/asterisk/freepbx.conf'); 
	}

	$api = new Api;
	$api->main();
	
}

?>
