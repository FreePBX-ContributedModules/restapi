<?php
// bootstrap freepbx
$bootstrap_settings['freepbx_auth'] = false;
$restrict_mods = array('restapi' => true);
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) { 
	include_once('/etc/asterisk/freepbx.conf'); 
}

$api = new Api;
$get = array(
	'action:',
	'url:',
	'verb:',
	'nonce:',
	'body:',
	'token:',
	'tokenkey:'
);

if (php_sapi_name() == 'cli') {
	$vars = getopt('', $get);
	foreach ($get as $key) {
		$k = trim($key, ':');
		$vars[$k] = isset($vars[$k]) ? $vars[$k] : '';
	}
} else {
	foreach ($get as $key) {
		$k = trim($key, ':');
		$vars[$k] = isset($_GET[$k]) ? $_GET[$k] : '';
	}
}
//print_r($vars);

switch ($vars['action']) {
	case 'nonce':
		echo 'Nonce: ' . restapi_tokens_generate() . PHP_EOL;
		break;
	default:
		$data = $api->auth->get_data_hash($vars['token'], $vars['url'], $vars['verb'], $vars['nonce'], $vars['body']);
		$sig = $api->auth->get_signature($data, $vars['tokenkey']);
		echo 'Body: ' . $data . PHP_EOL;
		echo 'Signature: ' . $sig . PHP_EOL;
		break;
}
?>
