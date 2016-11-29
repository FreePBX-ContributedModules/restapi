<?php
//redirect to config.php if it seems like thats what we really want
if (empty($_SERVER['PATH_INFO'])) {
	header("Location: /admin/config.php");
} else {
	// bootstrap freepbx
	$bootstrap_settings['freepbx_auth'] = false;
	$restrict_mods = array('restapi' => true);
	include '/etc/freepbx.conf';

	$api = new Api;
	$api->main();
	
}

?>
